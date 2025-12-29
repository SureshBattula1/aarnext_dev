<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Auth;
use Log;

class CreditController extends Controller
{
    //
    public function index()
    {
        return view('creditnote.list');
    }

    public function NewCreditpage()
    {
        return view('creditnote.new');
    }

    public function CreditItemsIist(Request $request , $creditNo){

      
        $order = DB::table('credit_memos')
            ->where('id', $creditNo)
            ->first();

        if ($order) {
            $orderItems = DB::table('credit_memo_items')
                ->where('credit_id', $order->id)
                ->get();

            $customer = DB::table('customers')
                ->where('card_code', $order->customer_id)
                ->first();


        } else {
            $orderItems = $customer = $payments = null;
        }
        return view('creditnote.credit_items_list' , compact('order', 'orderItems', 'customer'));


    }
    
    public function creditNoteJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = [
                'cn.id', 'cn.order_date', 'cn.customer_id', 'cn.total', 'cn.status',
                'cn.credit_no', 'cn.ref_id', 'cn.user_id', 'cn.bill_to', 'cn.ship_to', 'c.card_name','c.grp_name','s.name','u.code','cn.sales_emp_id'
            ];

            try {
                $userId = Auth::user()->id;
                $warehouseCode = Auth::user()->ware_house;
                 
                $query = DB::table('credit_memos as cn')
                    ->leftjoin('customers as c', 'cn.customer_id', '=', 'c.card_code')
                    ->leftjoin('sales_employees as s', 'cn.sales_emp_id', '=', 's.id')
                    ->leftjoin('invoices as i', 'cn.invoice_id', '=', 'i.id')
                    ->leftjoin('users as u', 'cn.user_id', '=', 'u.id')
                    ->select(
                        'cn.id', 'cn.customer_id', 'cn.ref_id', 'cn.ship_to', 'cn.user_id',
                        'cn.order_date', 'cn.status', 'cn.total', 'cn.credit_no', 'cn.bill_to', 'c.card_name','c.grp_name','s.name','u.code','cn.sap_created_id',
                        'cn.sap_updated' , 'cn.sap_updated_at' , 'cn.sap_error_msg', 's.memo','cn.sales_invoice_no', 'i.sales_type', 'cn.is_credit_approved', 'cn.credit_date_req', 'cn.credit_req_uid', 'cn.approval_remarks', 'cn.approved_by', 'cn.approved_at',
                        DB::raw("(CASE
                                WHEN cn.status = '0' THEN CONCAT('<span class=\"text-warning\"><b>', 'Pending', '</b></span>')
                                WHEN cn.status = '1' THEN CONCAT('<span class=\"text-success\"><b>', 'Completed', '</b></span>')
                                ELSE CONCAT('<span class=\"text-muted\"><b>', 'Unknown', '</b></span>')
                            END) as status_display"),
                            DB::raw("(CASE
                            WHEN cn.status = '0' THEN CONCAT('<span class=\"text-warning\">', c.card_code, '</span>')
                            WHEN cn.status = '1' THEN CONCAT('<span class=\"text-success\">', c.card_code, '</span>')
                            ELSE CONCAT('<span class=\"text-muted\">', c.card_code, '</span>') -- Unknown status
                        END) as customer_code_display"),
                        DB::raw("(SELECT ROUND(SUM(price_total_af_disc) , 2) as total_price FROM `credit_memo_items` WHERE `credit_id` = cn.id) as total_price"),
                        DB::raw("(SELECT ROUND(SUM(gst_value * quantity), 2) as total_tax FROM `credit_memo_items` WHERE `credit_id` = cn.id) as total_tax"),
                        DB::raw("(SELECT ROUND(SUM(subtotal), 2) FROM `credit_memo_items`  WHERE `credit_id` = cn.id) as total_with_tax_sum")
                    )
                       ->where('u.ware_house', '=', $warehouseCode)
                    ->orderBy('cn.id', 'DESC');

                    if (!empty($request->start_date) && !empty($request->end_date)) {
                        try {
                            $query->whereBetween('cn.order_date', [$request->start_date, $request->end_date]);
                        } catch (\Exception $e) {
                            return response()->json(['error' => 'Invalid date format'], 400);
                        }
                    }

                if (!empty($data['search_user'])) {
                    $searchTerm = '%' . $data['search_user'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('cn.customer_id', 'LIKE', $searchTerm)
                          ->orWhere('c.card_name', 'LIKE', $searchTerm)
                          ->orWhere('cn.credit_no', 'LIKE', $searchTerm)
                          ->orWhere('cn.sap_created_id', 'LIKE', $searchTerm);
                    });
                }

                $totalRecords = $query->count();

                // Ordering
                if (isset($data['order'][0]['column']) && isset($columnArray[$data['order'][0]['column']])) {
                    $query->orderBy(
                        $columnArray[$data['order'][0]['column']],
                        $data['order'][0]['dir'] ?? 'asc'
                    );
                }

                // Pagination
                if (isset($data['start']) && $data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                $order = $query->get();

                // Return DataTables JSON format
                return response()->json([
                    "draw" => intval($data['draw']),
                    "recordsTotal" => $totalRecords,
                    "recordsFiltered" => $totalRecords,
                    "data" => $order,
                ]);
            } catch (\Exception $e) {
                Log::error('Error: ' . $e->getMessage());

                return response()->json([
                    "draw" => intval($data['draw']),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => [],
                    "error" => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            "draw" => 0,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
        ]);
    }

    public function CreditCustomerSearch(Request $request)
    {
        $data = $request->all();
        $warehouseCode = auth()->user()->ware_house;
      
        $query = DB::table('invoices as i')
            ->leftJoin('customers as c', 'c.card_code', '=', 'i.customer_id')
            ->select(
                'c.card_code as id','c.nif_no',
                DB::raw("
                    CONCAT(
                        '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', c.card_code, '</span>',
                        ' - ',
                        '<span style=\"color:black; font-weight:bold; font-size:11px;\">', c.card_name, '</span>'
                    ) as text"),
                DB::raw("i.invoice_no as latest_invoice_no")  // Group by card_code, select latest invoice_no
            )
           // ->where('i.store_code',$warehouseCode)
            ->groupBy('c.card_code');
        if (!empty($data['search'])) {
            $query->where(function ($query) use ($data) {
                $query->where('c.card_name', 'like', '%' . $data['search'] . '%')
                    ->orWhere('c.card_code', 'like', '%' . $data['search'] . '%');
            });
        }


            $res = $query->get();
        return response()->json($res);

    }

    public function customerCreditNoteOrders(Request $request){

        $customer_id = $request->customer_id;
        $warehouseCode = auth()->user()->ware_house;
        $customerSalesOrder = DB::table('invoices')
                                ->select('invoices.id', 'invoices.customer_id', 'invoices.invoice_no')
                                ->where('store_code',$warehouseCode)
                                ->where('invoices.customer_id', $customer_id)
                                ->where('credit_status',0)
                                ->get();
        return response()->json($customerSalesOrder);
    }

    public function salesEmployee(Request $request)
    {
        try {
            $salesEmp = DB::table('sales_employees')
                ->select('*')
                ->get();

            // Format the data for select2
            $formattedData = $salesEmp->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'text' => $emp->name,
                ];
            });

            return response()->json(['results' => $formattedData], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch sales employees',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    
    public function getInvoiceOrderData($orderId) {
        $warehouseCode = auth()->user()->ware_house;
        $order = DB::table('invoice_items as oi')
           
            ->where('oi.invoice_id', $orderId)
            ->whereRaw('oi.quantity != oi.credit_used_qty')
            ->select('oi.id','oi.item_desc','oi.item_id','oi.item_no','oi.invoice_id','oi.order_id','oi.price','oi.price_af_disc',
            'oi.price_total_af_disc','oi.gst_value','oi.gst_rate','oi.disc_value','oi.disc_percent', DB::raw('(oi.quantity - oi.credit_used_qty) as quantity'))
            ->orderBy('id','DESC')
            ->get();
            $remarks = DB::table('invoices as o')
                                ->join('sales_employees as s', 's.id', '=', 'o.sales_emp_id')
                                ->where('o.id', $orderId)
                                ->select('o.*', 's.name as sales_employee_name')
                                ->orderBy('id','DESC')
                                ->first();
        
                                $response = [
                                    'ref_id' => isset($remarks->ref_id) ? $remarks->ref_id : '',
                                    'sales_employee_name' => isset($remarks->sales_employee_name) ? $remarks->sales_employee_name : '',
                                    'remarks'=> isset($remarks->remarks) ? $remarks->remarks : '',
                                    'invoice_no' => $remarks->invoice_no,
                                    'order' => $order
                                ];

                            return response()->json($response);
    }

    public function StoreCreditMemos(Request $request)
    {
         //echo "<pre>"; print_r($request->all()); exit;
        try {

        $existingInvoice = DB::table('credit_memos')
                ->where('invoice_id', $request->invoice_number)
                ->first();

            if ($existingInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice aginest Credit already exist',
                ], 400);
            }

           DB::beginTransaction();
            //echo"<pre>"; print_r($request->all()); exit;
            $userId = Auth::user()->id;
            $invoiceStrCode = Auth::user()->code;
            $warehouseCode = Auth::user()->ware_house;

        $invoice_details = DB::table('invoices')->select('id','invoice_no','customer_sub_grp')->where('id',$request->invoice_number)->first();

       $creditNo = $this->generateUniqueCreditNo($warehouseCode, $invoice_details->customer_sub_grp);

            $salesEmp = DB::table('sales_employees')
                        ->where('name', $request->sales_emp_id)
                        ->orWhere('id', $request->sales_emp_id)
                        ->select('id')
                        ->first();

                $salesEmpId = $salesEmp ? $salesEmp->id : null;
                $InvoiceNumber = $request->invoice_number;

            // Insert Invoice
            $systemName = gethostname();
            $systemIP = $this->getLaptopIp();
            $creditId = DB::table('credit_memos')->insertGetId([
                'customer_id' => $request->customer_id,
                'order_id' => $request->order_id,
                'ref_id' => $request->ref_id,
                'sales_invoice_no' => $request->invoice_no,
                'motivo' => $request->motivo,
                'user_id' => $userId,
                'store_code' => $warehouseCode,
                'sales_emp_id'=> $salesEmpId,
                'address' => $request->Cust_address,
                'remarks' => $request->remarks,
                'status' => 0,
                'system_name' => $systemName,
                'system_ip' => $systemIP,
                'order_date' => date('Y-m-d'),
                'discount_status' => 0,
                'credit_no' => $creditNo,
                'invoice_id' => $InvoiceNumber,
            ]);
            $total = 0;
            $hasDiscount = false;
            $invoiceItems = [];
            //  exit;
            $item_i = 0;
            $quantity = 0;
             $itemNos = $request->input('row_item_no');
             //echo "MU".count($itemNos);
            foreach ($request->row_item_id as $index => $rowId) {

               // echo "IT".$itemId;
                if (empty($rowId) || empty($request->row_item_qty[$index]) || empty($request->row_item_price[$index])) {
                    Log::warning("Skipping empty item at index $index");
                    continue;
                }
                //echo "ITEMCO".$itemId;
                $item_details = DB::table('invoice_items')->select('*')
                                        ->where('invoice_id',$InvoiceNumber)
                                        ->where('id',$rowId)
                                        ->first();
                //echo "<pre>"; print_r($item_details); exit;
                $rowcreditId = $request->row_item_order_id[$index] ?? 0;
                $rowCeditItems = $request->row_item_no[$index];
               // echo "<pre>"; print_r($rowCeditItems); exit;
                //echo "ITEM".$itemId;
                $quantity = $request->row_item_qty[$index];
                $price = $item_details->price;
                $discPercent = $item_details->disc_percent;

                $itemInfo = DB::table('items')->select('item_code','item_name')->where('item_code', $item_details->item_no)->first();

                //echo "<pre>"; print_r($itemInfo);
                if (!$itemInfo) {
                    throw new \Exception("Item not found for ID: {$rowId}");
                }

                $discValue = $price * $discPercent / 100;
                $priceAfterDisc = $price - $discValue;
                $priceTotalAfterDisc = $quantity * $priceAfterDisc;

                // GST Calculation
                $gstRate =  $item_details->gst_rate;
                $gstValue =  $item_details->gst_value;
                $subtotal = $priceTotalAfterDisc + ($quantity * $gstValue);

                $total += $subtotal;

                if ($discValue > 0) {
                    $hasDiscount = true;
                }

                $batches = DB::table('warehouse_codebatch_details as ws')
                    ->where('ws.item_code', '=', $itemInfo->item_code)
                    ->where('ws.whs_code', '=', $warehouseCode)
                    ->select('ws.*')
                    ->first();

                    // Add to GST summary
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

              $creditItems =  DB::table('credit_memo_items')->insertGetId([
                    'order_id' => $rowcreditId,
                    'credit_id' => $creditId,
                    'item_id' => $rowId,
                    'item_no' => $itemInfo->item_code,
                    'item_desc' => $itemInfo->item_name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'disc_value' => $discValue,
                    'disc_percent' => $discPercent,
                    'price_af_disc' => $priceAfterDisc,
                    'price_total_af_disc' => $priceTotalAfterDisc,
                    'gst_rate' => $gstRate ?? 0,
                    'gst_value' => $gstValue ?? 0,
                    'subtotal' => $subtotal,
                    'batch_number' => $batches->batch_num ?? '',
                    'batch_qty' => $batches->quantity ?? 0,
                ]);

                if($quantity){
                    $invItems = DB::table('invoice_items')
                        ->where('invoice_id', $InvoiceNumber)
                        ->where('item_no', $itemInfo->item_code)
                        ->update([
                            'credit_used_qty' => $quantity,
                        ]);
                }

                $invoiceItems[] = [
                    'item_no' => $itemInfo->item_code,
                    'item_desc' => $itemInfo->item_name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'price_af_disc' => $priceAfterDisc,
                    'gst_rate' => $gstRate,
                    'gst_value' => $gstValue,
                    'subtotal' => $subtotal
                ];

                $rowItemBatches = $request->input('row_item_batches');

                if (empty($rowItemBatches) || !is_array($rowItemBatches) || count(array_filter($rowItemBatches)) === 0) {
                    throw new \Exception("Please Select Batches Item ID: {$rowCeditItems}");
                }
                
                $rowItemBatches = array_values(array_filter($rowItemBatches));

                if (is_array($rowItemBatches)) {

                $data = json_decode($rowItemBatches[$item_i], true);

               // echo "<pre>"; print_r($data); exit;


                if(!empty($data)){
                foreach ($data as $batchData) {


                    if(!empty($data)){
                        //echo "Suresh";
                        if ($batchData !== '') {
                          $itemCode = $itemInfo->item_code;
                        $updateQty = 0;
                        foreach ($data['batches'] as $batch) {
                                //  echo "<pre>"; print_r($batch); exit;
                                    $batchQty = $batch['qty'];
                                    $batchNum = $batch['batch'];
                                    $Exp = $batch['Exp'];

                            // echo "insert_before";

                            $ins_params = array(
                            'credit_id' => $creditId,
                            'item_code' => $itemCode,
                            'invoice_id' => $InvoiceNumber,
                            'batch_num' => $batchNum,
                            'quantity' => $batchQty,
                            'expiry_date' => $Exp,
                            'item_row_id' => $creditItems,
                            'created_at' => now()
                            );

                            $updateQty += $batchQty;
                            // echo "updateQty"; print_r($updateQty); exit;
                            $insert_data =  DB::table('credit_batches')->insertGetId($ins_params);

                                    $existingBatch = DB::table('warehouse_codebatch_details')
                                                        ->where('item_code', $itemCode)
                                                        ->where('batch_num', '=', $batchNum)
                                                        ->where('whs_code', $warehouseCode)
                                                        ->first();
                                  //  echo "<pre>"; print_r($existingBatch); exit;

                                    if ($existingBatch) {
                                        DB::table('warehouse_codebatch_details')
                                            ->where('item_code', $itemCode)
                                            ->where('whs_code', $warehouseCode)
                                            ->where('batch_num', '=', $batchNum)
                                            ->update([
                                                'credit_qty' => $existingBatch->credit_qty + $batchQty,
                                            ]);
                                    }else{
                                        $new_batch = array('item_code' => $itemCode,
                                                           'item_name' => '',
                                                           'batch_num' => $batchNum,
                                                           'whs_code'  => $warehouseCode,
                                                           'quantity'  => $batchQty,
                                                           'in_date'   => $Exp,
                                                           'exp_date'  => $Exp,
                                                           'created_at' => now()
                                                         );
                                    $insert_data =  DB::table('warehouse_codebatch_details')->insertGetId($new_batch);
                                    }
                                }

                                 // echo "creditItems".$creditItems;
                                    //         exit;
                            if ($updateQty) {
                                DB::table('credit_memo_items')
                                    ->where('id', $creditItems)
                                    ->update([
                                        'quantity' =>  $updateQty,
                                    ]);
                            }

                        } else {

                            throw new \Exception('Invalid batch data format');
                        }
                    }

                    }
                }
                }
              $item_i++;
             }

            

             DB::table('invoices')
                         ->where('id',$InvoiceNumber)
                         ->update(['credit_status' => 1]);

            DB::table('credit_memos')
                ->where('id', $creditId)
                ->update(['total' => $total, 'discount_status' => $hasDiscount ? 1 : 0]);

            $creditPdfUrl = $this->generateCreditPDF($creditId);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Credit Memos Dispatched successfully.',
                'orderItems' => $invoiceItems,
                'pdfUrl' => $creditPdfUrl,
            ], 201);
        } catch (\Exception $e) {
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

    public function generateUniqueCreditNo($warehouseCode, $custSubGrp)
    {

      $lastCode = DB::table('ware_houses')->select('str_credit_hosp_series','str_credit_gen_series')
                                        ->where('whs_code',$warehouseCode)
                                        ->first();

        if($custSubGrp == 'HOSPITAL')
        { 
          $last_series =  $lastCode->str_credit_hosp_series;
        }else{
           $last_series =  $lastCode->str_credit_gen_series; 
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
                            ->update(['str_credit_hosp_series'=>$unique_number]);   
             }       

        }else{
            if (preg_match('/\d/', $unique_number)) {
             $update = DB::table('ware_houses')
                            ->where('whs_code',$warehouseCode)
                            ->update(['str_credit_gen_series'=>$unique_number]); 
             }          
        }

        return $unique_number;
    }

    private function getLaptopIp() {

        $output = array();
        exec('ipconfig /all', $output);


        foreach ($output as $line) {
            if (strpos($line, 'IPv4 Address') !== false) {
                $parts = explode(':', $line);
                return trim(end($parts));
            }
        }


        return '';
    }
    

    function generateCreditPDF($creditNo)
    {
        //echo "creditNo".$creditNo; exit;
        $warehouseCode = Auth::user()->ware_house;
        $order = DB::table('credit_memos')
        ->join('customers as c','credit_memos.customer_id', '=', 'c.card_code')
        ->select('credit_memos.*','c.card_name as customer_name','c.nif_no')
        ->where('credit_memos.id', $creditNo)->first();
        $items = DB::table('credit_memo_items')->where('credit_id', $order->id)->get();
        //log::info('PDF ::'.$items);

        $total = 0;
        $gstSummary = [];
        $creditBatches = DB::table('credit_batches')
        ->where('credit_id', $order->id)
        ->where('invoice_id', $order->invoice_id)
        ->get();
        $ware_house_data = DB::table('ware_houses')->where('whs_code', $warehouseCode)->first();


        foreach ($items as $item) {
            $price = $item->price;
            $quantity = $item->quantity;
            $disc_percent = $item->disc_percent;
            $disc_value = $price * $disc_percent / 100;
            $price_af_disc = $price - $disc_value;
            $price_total_af_disc = $quantity * $price_af_disc;

            $gstRate = $item->gst_rate;
            $gstValue = $item->gst_value;

            $subtotal = $price_total_af_disc + ($quantity * $gstValue);
            $total += $subtotal;

            if (!isset($gstSummary[$gstRate])) {
                $gstSummary[$gstRate] = [
                    'count' => 0,
                    'sum' => 0,
                    'subTotal' => 0,
                ];
            }
            $gstSummary[$gstRate]['count'] += $quantity;
            $gstSummary[$gstRate]['subTotal'] += $price_total_af_disc;
            $gstSummary[$gstRate]['sum'] += ($quantity * $gstValue);


            $item->batches = collect($creditBatches)
            ->where('item_code', $item->item_no)
            ->map(function($batch) {
                return [
                    'quantity' => $batch->quantity,
                    'expiry_date' => $batch->expiry_date
                ];
            })
            ->values()
            ->all();
        }

        ksort($gstSummary);

        if (!$order) {
            throw new \Exception("Order with invoice number $creditNo not found.");
        }

        $itemsPerPage = 11; // Number of items per page
         $totalPages = ceil(count($items) / $itemsPerPage);

        // Load the invoice view
        $pdf = PDF::loadView('pdf.credit-items-pdf', compact('order', 'items','ware_house_data' , 'warehouseCode', 'gstSummary','totalPages'))
                    ->setPaper('a4')
                    ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // Define the directory and file name
        $folderPath = 'pdf/credits';
        $pdfDirectory = public_path($folderPath);

        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        $pdfName = 'credit_copy' . $order->id . '.pdf';
        $pdfFilePath = $pdfDirectory . '/' . $pdfName;

        $pdf->render();
        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $domPdf->set_option('isHtml5ParserEnabled', true);
        $canvas = $domPdf->get_canvas();
        $font = $domPdf->getFontMetrics()->get_font('arial', 'sans-serif');
        $canvas->page_text(500, 800, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));

        $pdf->save($pdfFilePath);

        if (!file_exists($pdfFilePath)) {
            throw new \Exception("PDF file was not created successfully at $pdfFilePath.");
        }

        // Return the URL to the PDF file
        return url($folderPath . "/" . $pdfName);
    }

    public function creditBatches($invoiceId , $itemCode ){
        try{


            $batches = DB::table('invoice_batches as ib')
                ->leftJoin('invoice_items as ii', function($join) {
                    $join->on('ib.invoice_id', '=', 'ii.invoice_id')
                        ->on('ib.item_code', '=', 'ii.item_no');
                })
                ->where('ib.invoice_id', $invoiceId)
                ->where('ib.item_code', $itemCode)
                ->select(
                    'ib.id',
                    'ib.invoice_id',
                    'ib.item_code',
                    'ib.batch_num',
                    'ib.expiry_date',
                    DB::raw('(ii.quantity - ii.credit_used_qty) as quantity'),
                    'ib.created_at'
                )
                ->get();

            //echo "<pre>"; print_r($batches); exit;
            return response()->json($batches);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch sales employees',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
