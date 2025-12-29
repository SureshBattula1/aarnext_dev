<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\QuotationController;
use Carbon\Carbon;

class CreateInvoiceController extends Controller
{

    public function storeInvoice(Request $request)
    {

            Log::info('STARTTime: ' . now()->toDateTimeString());

            $existingInvoice = DB::table('invoices')
                                ->where('gu_id',$request->guid)
                                ->first();

                if ($existingInvoice) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Duplicate submission detected',
                    ], 400);
                    exit;
                }

        try {
     
            DB::beginTransaction();
            $userId = Auth::user()->id;
            $warehouseCode = Auth::user()->ware_house;
            $custSubGrp = $request->customer_sub_grp ?? 'GENERAL';

            $salesEmpId = DB::table('sales_employees')
                ->where('name', $request->sales_emp_id)
                ->orWhere('id', $request->sales_emp_id)
                ->value('id');
             $due_date = $request->due_date < Carbon::now() ? Carbon::now() : $request->due_date;
            // Insert Invoice
            $isZipCustomer = ($request->cust_default_discount > 0) ? 'Yes' : 'No';

            $hikePercent = ($request->hike_percent !== null && $request->hike_percent !== 'null') ? $request->hike_percent : 0;
            $salesCommission = ($request->cust_sales_commission !== null && $request->cust_sales_commission !== 'null') ? $request->cust_sales_commission : 0;
            $invoiceId = DB::table('invoices')->insertGetId([
                'customer_id' => $request->customer_id,
                'order_id' => $request->order_id,
                'ref_id' => $request->ref_no,
                'sales_emp_id' => $salesEmpId,
                'address' => $request->cust_address_id,
                'user_id' => $userId,
                'store_code' => $warehouseCode,
                'sales_type' => $request->sales_type,
                'customer_sub_grp' => $custSubGrp,
                'status' => 0,
                'remarks' => $request->remarks,
                'pymnt_grp' => $request->pymnt_grp,
                'due_date' => $due_date,
                'order_date' => $request->current_date,
                'discount_status' => 0,
                'gu_id' => $request->guid,
                'invoice_no' => bin2hex(openssl_random_pseudo_bytes(6)),
                'is_zip_customer' => $isZipCustomer,
                'hike_percent' => $hikePercent,
                'sales_commission' => $salesCommission,
            ]);

            $total = 0;
            $gstSummary = [];
            $hasDiscount = false;

            $invoiceItemsData = [];
            $batchInsertData = [];
            $updateBatchesQtyData = [];

            foreach ($request->row_item_no as $index => $itemId) {
                if (empty($itemId)) {
                    Log::warning("Skipping empty item at index $index");
                    continue;
                }

                // Retrieve Item Info once per itemId
                $itemInfo = DB::table('items')
                    ->select('item_code', 'item_name')
                    ->where('item_code', $itemId)
                    ->first();

                if (!$itemInfo) {
                    throw new \Exception("Item not found for ID: {$itemId}");
                }

                // Calculate values outside of DB insertion
                $quantity = $request->row_item_qty[$index];
                $price = $request->row_item_price[$index];
                if ($price <= 0) 
                {
                    throw new \Exception("Item : {$itemId} has zero Price.");
                }

                $discPercent = $request->row_item_disc_percent[$index];
                $gstRate = $request->row_item_gst_rate[$index];
                $gstValue = $request->row_item_gst_rate_value[$index];

                $discValue = $price * $discPercent / 100;


                $priceAfterDisc = $price - $discValue;
                $priceTotalAfterDisc = $quantity * $priceAfterDisc;
                $subtotal = $priceTotalAfterDisc + ($quantity * $gstValue);
                $total += $subtotal;

                if ($discValue > 0) {
                    $hasDiscount = true;
                }

                // Add to GST summary
                if (!isset($gstSummary[$gstRate])) {
                    $gstSummary[$gstRate] = ['count' => 0, 'sum' => 0, 'subTotal' => 0];
                }
                $gstSummary[$gstRate]['count'] += $quantity;
                $gstSummary[$gstRate]['subTotal'] += $priceTotalAfterDisc;
                $gstSummary[$gstRate]['sum'] += ($quantity * $gstValue);

                // Prepare bulk insert data for invoice_items
                $invoiceItemsData[] = [
                    'order_id' => $request->row_item_order_id[$index] ?? 0,
                    'invoice_id' => $invoiceId,
                    'item_id' => $itemId,
                    'item_no' => $itemInfo->item_code,
                    'item_desc' => $itemInfo->item_name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'disc_value' => $discValue,
                    'disc_percent' => $discPercent,
                    'price_af_disc' => $priceAfterDisc,
                    'price_total_af_disc' => $priceTotalAfterDisc,
                    'gst_rate' => $gstRate,
                    'gst_value' => $gstValue,
                    'subtotal' => $subtotal,
                    'sap_base_line' => $index
                ];

                // Process batch data
                $batches_data = $this->batchQuantity($itemInfo->item_code, $quantity);
                if (empty($batches_data) || !is_array($batches_data)) {
                    throw new \Exception("No batches available for item with code: {$itemInfo->item_code}. Invoice creation aborted.");
                }

                foreach ($batches_data as $sele_batches) {
                    $batchInsertData[] = [
                        'invoice_id' => $invoiceId,
                        'item_code' => $itemInfo->item_code,
                        'batch_num' => $sele_batches['batch'],
                        'quantity' => $sele_batches['qty'],
                        'expiry_date' => $sele_batches['exp']
                    ];

                    // Prepare update data for batch quantities
                    $updateBatchesQtyData[] = [
                        'whs_code' => $warehouseCode,
                        'item_code' => $itemInfo->item_code,
                        'batch_num' => $sele_batches['batch'],
                        'invoice_qty' => $sele_batches['inv_quantity'] + $sele_batches['qty']
                    ];
                }
            }

            // Bulk Insert for invoice items
            DB::table('invoice_items')->insert($invoiceItemsData);

            // Bulk Insert for invoice batches
            DB::table('invoice_batches')->insert($batchInsertData);

            // Bulk update warehouse batch quantities
            foreach ($updateBatchesQtyData as $batchQty) {
                DB::table('warehouse_codebatch_details')
                    ->where('whs_code', $batchQty['whs_code'])
                    ->where('item_code', $batchQty['item_code'])
                    ->where('batch_num', $batchQty['batch_num'])
                    ->update(['invoice_qty' => $batchQty['invoice_qty']]);
            }

            if ($total <= 0) {
                throw new \Exception("Invoice Amount Mismatching");
            }

            // Update invoice total and discount status
            DB::table('invoices')
                ->where('id', $invoiceId)
                ->update(['total' => $total, 'discount_status' => $hasDiscount ? 1 : 0]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'inv_id' => $invoiceId
            ], 201);

        } catch (\Exception $e) {
            Log::info('RollbackTime: ' . now()->toDateTimeString());
            DB::rollback();
            Log::error('Error creating Invoice: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating Invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

        public function GenerateInvoicePDF($invoice_id)
        {

            $invoice_details = DB::table('invoices')->select('*')->where('id',$invoice_id)->first();

            if(isset($invoice_details->generated_pdf)){



                $pdf_url = url('').'/'.$invoice_details->generated_pdf;

                 return response()->json([
                                         'success' => 1,
                                         'message' => 'Generated PDF',
                                         'pdf_url' => $pdf_url
                                        ], 200);
            }else{
               // echo "esle"; exit;
            $warehouseCode = $invoice_details->store_code;
            $custSubGrp  = $invoice_details->customer_sub_grp;
            //$latestInvoice = $this->getLatestInvoice($warehouseCode, $custSubGrp);
           // echo "<pre>"; print_r($latestInvoice); exit;
           // $wareHouseNum = $this->getWarehouseSeries($warehouseCode);

            // Generate unique invoice number
            $invoiceNo = $this->generateUniqueInvoiceNo($warehouseCode, $custSubGrp);

            $inv_details = array(
                'date' => $invoice_details->order_date,
                'warehouse_code' => $warehouseCode,
                'due_date' => $invoice_details->due_date,
                'web_id' => $invoiceNo,
                'doc_total' => $invoice_details->total
            );



            // $hashKey = QuotationController::HashkeyGenerationInvoice($inv_details,$warehouseCode);

            $quotationController = new QuotationController();
            $hashKey = $quotationController->HashkeyGenerationInvoice($inv_details, $warehouseCode);
            
        //$pdfDirectory = public_path('pdf/invoices');
        $pdfName = 'pdf/invoices/invoice_copy_' . $invoice_id . '.pdf';

           // $pdfFilePath = $pdfDirectory . '/' . $pdfName;
            $invoiceUpdate = DB::table('invoices')
                ->where('id', $invoice_id)
                ->update([
                    // 'hash_key' => $hashKey['hash_key'],
                    'hash_key' => $hashKey['hash_key'],
                    'hash_key_code' => $hashKey['generated_code'],
                    'invoice_no'    => $invoiceNo,
                    'generated_pdf' => $pdfName
                ]);
            //  $invoicePdfUrl = QuotationController::generateInvoicePDF($invoice_id);
            $invoicePdfUrl = $quotationController->generateInvoicePDF($invoice_id);


                if($invoiceUpdate){
                    return response()->json([
                                             'success' => 1,
                                             'message' => 'Payment successfully submitted',
                                             'pdf_url' => $invoicePdfUrl
                                            ], 200);                    

                }
            }
            


            
            
        }


    /**
     * Check for duplicate invoice number and regenerate if necessary.
     */
    public function generateUniqueInvoiceNo($warehouseCode, $custSubGrp)
    {

      $lastCode = DB::table('ware_houses')->select('str_inv_hosp_series','str_inv_gen_series')
                                        ->where('whs_code',$warehouseCode)
                                        ->first();

        if($custSubGrp == 'HOSPITAL')
        {
          $last_series =  $lastCode->str_inv_hosp_series;
        }else{
           $last_series =  $lastCode->str_inv_gen_series; 
        }


        $numericPart = substr($last_series, strrpos($last_series, '/') + 1);
        $newNumericPart = str_pad((int)$numericPart + 1, 5, '0', STR_PAD_LEFT);
        $stringPart = strtok($last_series, '/');
        $unique_number = $stringPart.'/'.$newNumericPart;
        if($custSubGrp == 'HOSPITAL')
        {
            if (preg_match('/\d/', $unique_number)) {
            $update = DB::table('ware_houses')
                            ->where('whs_code',$warehouseCode)
                            ->update(['str_inv_hosp_series'=>$unique_number]);   
             }       

        }else{
            if (preg_match('/\d/', $unique_number)) {
             $update = DB::table('ware_houses')
                            ->where('whs_code',$warehouseCode)
                            ->update(['str_inv_gen_series'=>$unique_number]); 
             }          
        }

        return $unique_number;
    }


        private function getLatestInvoice($warehouseCode, $custSubGrp)
        {
            return DB::table('invoices')
                ->where('store_code', $warehouseCode)
                ->where('customer_sub_grp', $custSubGrp)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

        }

        private function getWarehouseSeries($warehouseCode)
        {
            return DB::table('ware_houses as w')
                ->select('str_inv_hosp_series', 'str_inv_gen_series')
                ->where('whs_code', $warehouseCode)
                ->first();
        }
        private function getSalesEmpId($salesEmpIdentifier)
        {
            $salesEmp = DB::table('sales_employees')
                ->where('name', $salesEmpIdentifier)
                ->orWhere('id', $salesEmpIdentifier)
                ->select('id')
                ->first();

            return $salesEmp ? $salesEmp->id : null;
        }

        private function createInvoice($request, $userId, $warehouseCode, $custSubGrp, $salesEmpId)
        {
           // $systemName = gethostname();
           // $systemIP = $this->getLaptopIp();

            return DB::table('invoices')->insertGetId([
                'customer_id' => $request->customer_id,
                'order_id' => $request->order_id,
                'ref_id' => $request->ref_no,
                'sales_emp_id' => $salesEmpId,
                'address' => $request->cust_address_id,
                'user_id' => $userId,
                'store_code' => $warehouseCode,
                'sales_type' => $request->sales_type,
                'customer_sub_grp' => $custSubGrp,
                'status' => 0,
              //  'system_name' => $systemName,
                //'system_ip' => $systemIP,
                'remarks' => $request->remarks,
                'pymnt_grp' => $request->pymnt_grp,
                'due_date' => $request->due_date,
                'order_date' => $request->current_date,
                'discount_status' => 0
                //'invoice_no' => $invoiceNo,
            ]);
        }

        private function processInvoiceItem($request, $index, $itemId, $invoiceId, $warehouseCode)
        {
            $rowInvoiceId = $request->row_item_order_id[$index] ?? 0;
            $quantity = $request->row_item_qty[$index];
            $discPercent = $request->row_item_disc_percent[$index];
            $gstRate = $request->row_item_gst_rate[$index];
            $gstValue = $request->row_item_gst_rate_value[$index];

            $itemInfo = DB::table('item_priceses')->where('id', $itemId)->first();
            if (!$itemInfo) {
                throw new \Exception("Item not found for ID: {$itemId}");
            }

            // Perform calculations
            $price = $itemInfo->price;
            $discValue = $price * $discPercent / 100;
            $priceAfterDisc = $price - $discValue;
            $priceTotalAfterDisc = $quantity * $priceAfterDisc;
            $subtotal = $priceTotalAfterDisc + ($quantity * $gstValue);

            // Insert invoice items and manage batch quantities
            $this->insertInvoiceItem($invoiceId, $itemInfo, $quantity, $discValue, $discPercent, $priceAfterDisc, $priceTotalAfterDisc, $gstRate, $gstValue, $subtotal, $index);
            $this->updateBatchQuantities($itemInfo, $warehouseCode, $quantity);

            // Return summary for calculations
            return [
                'subtotal' => $subtotal,
                'gstRate' => $gstRate,
                'priceTotalAfterDisc' => $priceTotalAfterDisc,
                'quantity' => $quantity,
                'gstValue' => $gstValue,
                'hasDiscount' => $discValue > 0,
            ];
        }
        private function updateGstSummary($gstSummary, $itemDetails, $index)
        {
            $gstRate = $itemDetails['gstRate'];
            $quantity = $itemDetails['quantity'];
            $priceTotalAfterDisc = $itemDetails['priceTotalAfterDisc'];
            $gstValue = $itemDetails['gstValue'];

            if (!isset($gstSummary[$gstRate])) {
                $gstSummary[$gstRate] = [
                    'count' => 0,
                    'sum' => 0,
                    'subTotal' => 0,
                ];
            }

            $gstSummary[$gstRate]['count'] += $quantity;
            $gstSummary[$gstRate]['subTotal'] += $priceTotalAfterDisc;
            $gstSummary[$gstRate]['sum'] += ($quantity * $gstValue);

            ksort($gstSummary);

            return $gstSummary;
        }
        private function insertInvoiceItem($invoiceId, $itemInfo, $quantity, $discValue, $discPercent, $priceAfterDisc, $priceTotalAfterDisc, $gstRate, $gstValue, $subtotal, $index)
        {
            DB::table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'item_id' => $itemInfo->id,
                'item_no' => $itemInfo->item_code,
                'item_desc' => $itemInfo->item_name,
                'quantity' => $quantity,
                'price' => $itemInfo->price,
                'disc_value' => $discValue,
                'disc_percent' => $discPercent,
                'price_af_disc' => $priceAfterDisc,
                'price_total_af_disc' => $priceTotalAfterDisc,
                'gst_rate' => $gstRate,
                'gst_value' => $gstValue,
                'subtotal' => $subtotal,
                'sap_base_line' => $index,
            ]);
        }

        private function updateBatchQuantities($itemInfo, $warehouseCode, $quantity)
        {
            $batches_data = $this->batchQuantity($itemInfo->item_code, $quantity);

            if (empty($batches_data) || !is_array($batches_data)) {
                throw new \Exception("No batches available for item with code: {$itemInfo->item_code}. Invoice creation aborted.");
            }

            foreach ($batches_data as $sele_batches) {
                $batchNumber = $sele_batches['batch'];
                $batchQuantity = $sele_batches['qty'];
                $existingInvoiceQty = $sele_batches['inv_quantity'];

                $expiry_date = $sele_batches['exp'];

                // Insert into invoice_batches
                DB::table('invoice_batches')->insert([
                    'invoice_id' => $invoiceId,
                    'item_code' => $itemInfo->item_code,
                    'batch_num' => $batchNumber,
                    'quantity' => $batchQuantity,
                    'expiry_date' => $expiry_date,
                ]);

                // Update warehouse batch quantity
                $newQty = $existingInvoiceQty + $batchQuantity;
                DB::table('warehouse_codebatch_details')
                    ->where('whs_code', $warehouseCode)
                    ->where('item_code', $itemInfo->item_code)
                    ->where('batch_num', $batchNumber)
                    ->update(['invoice_qty' => $newQty]);

                Log::info('Batch quantities updated for item', [
                    'item_code' => $itemInfo->item_code,
                    'batch_num' => $batchNumber,
                    'new_quantity' => $newQty
                ]);
            }
        }

        public function batchQuantity($itemId , $quantity)
        {
            $warehouse = auth()->user()->ware_house;
            $batches = DB::table('warehouse_codebatch_details as ws')
                        ->where('ws.item_code', '=', $itemId)
                        ->where('ws.whs_code', '=', $warehouse)
                        ->select('ws.*')
                        ->orderBy('exp_date' , 'ASC')
                        ->get();
            $check_batch = DB::table("warehouse_codebatch_details")
                ->select(DB::raw("SUM(quantity) as total_qty"),DB::raw("SUM(invoice_qty) as inv_qty"),DB::raw("SUM(credit_qty) as credit_qty"))
                ->where('item_code', '=', $itemId)
                ->where('whs_code', '=', $warehouse)
                ->first();

            $total_credit_de = ($check_batch->total_qty - $check_batch->inv_qty) + $check_batch->credit_qty;
            if($total_credit_de < $quantity)
            {
                return response()->json(["Item is Outof Stock - Available Qty -".$total_credit_de]);
            }else{
                $batch_addition = 0;
                $my_array = array();
                $remaining_quantity = $quantity;
        
            foreach ($batches as $key => $batch) {

                
                 $available_batch = ($batch->quantity - $batch->invoice_qty) + $batch->credit_qty;
                 if($available_batch > 0)
                 {
                    
                    if ($remaining_quantity <= $available_batch) {
                            $batch_addition = $remaining_quantity;
                           
                            $arrayName = array('batch' => $batch->batch_num, 'qty' => $batch_addition,'inv_quantity'=>$batch->invoice_qty,'exp'=>date('Y-m-d', strtotime($batch->exp_date)));
                            $my_array[] = $arrayName; 
                            return $my_array;  
                        } else {
                            $batch_addition = $available_batch;
                            $remaining_quantity -= $available_batch;
                            $arrayName = array('batch' => $batch->batch_num, 'qty' => $batch_addition,'inv_quantity'=>$batch->invoice_qty , 'exp'=>date('Y-m-d', strtotime($batch->exp_date)));
                            $my_array[] = $arrayName;
                        } 
                        
                        
                        
                 }else{
                    continue;
                 }
                 

                }
                  return response()->json($my_array);
            }
        }


        private function finalizeInvoice($invoiceId, $request, $warehouseCode, $total, $hasDiscount, $invoiceNo)
        {
            $inv_details = [
                'date' => $request->current_date,
                'warehouse_code' => $warehouseCode,
                'due_date' => $request->due_date,
                'web_id' => $invoiceNo,
                'doc_total' => $total
            ];

            $hashKey = $this->HashkeyGenerationInvoice($inv_details);

            DB::table('invoices')
                ->where('id', $invoiceId)
                ->update([
                    'hash_key' => $hashKey['hash_key'],
                    'hash_key_code' => $hashKey['generated_code'],
                    'total' => $total,
                    'discount_status' => $hasDiscount ? 1 : 0,
                ]);
        }





}
