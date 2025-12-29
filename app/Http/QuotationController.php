<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Spatie\Browsershot\Browsershot;
use Mpdf\Mpdf;

class QuotationController extends Controller
{
    function index()
    {
        return view('quotation.list');
    }

    public function NewInvoicePage()
    {
        return view('pdf.new-format-pdf');
    }




    function getQuotationsJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = [
                'q.id', 'q.sales_type', 'q.order_date', 'q.customer_id', 'q.total', 'q.paid_amount', 'q.status',
                'q.invoice_no', 'q.ref_id', 'q.user_id', 'q.bill_to', 'q.ship_to', 'c.card_name', 'q.store_code', 'q.due_date', 
                'q.pymnt_grp', 'q.sap_created_id', 'q.sap_error_msg', 'q.sap_updated_at', 'q.sap_updated', 'c.grp_name', 
                'u.code', 's.name', 'q.sales_emp_id',
            ];

            try {
                $userId = Auth::user()->id;
                $warehouseCode = Auth::user()->ware_house;

                $query = DB::table('invoices as q')
                ->leftJoin('customers as c', 'q.customer_id', '=', 'c.card_code')
                ->leftJoin('users as u', 'q.user_id', '=', 'u.id')
                ->leftJoin('sales_employees as s', 'q.sales_emp_id', '=', 's.id')
                ->leftJoin('invoice_items as i', 'q.id', '=', 'i.invoice_id')
                ->select(
                    'q.id', 'q.sales_type', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id',
                    'q.order_date', 'q.status', 'q.total', 'q.invoice_no', 'q.bill_to', 'c.card_name', 'q.store_code',
                    'q.due_date', 'q.pymnt_grp', 'q.paid_amount', 'q.sap_created_id', 'q.sap_error_msg',
                    'q.sap_updated_at', 'q.hash_key', 'q.sap_updated', 'c.grp_name', 'u.code', 's.name', 's.memo', 'c.sub_grp',
                    'q.status as status_display',
                    'c.card_code as customer_code_display',
                    'q.sap_updated as sap_status',
                    DB::raw('q.total - q.paid_amount as balance_amount'),
                    DB::raw('SUM(i.subtotal) as total_with_tax_sum')
                )
                ->where('q.store_code', '=', $warehouseCode)
                ->groupBy(
                    'q.id', 'q.sales_type', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id',
                    'q.order_date', 'q.status', 'q.total', 'q.invoice_no', 'q.bill_to', 'c.card_name', 'q.store_code',
                    'q.due_date', 'q.pymnt_grp', 'q.paid_amount', 'q.sap_created_id', 'q.sap_error_msg',
                    'q.sap_updated_at', 'q.hash_key', 'q.sap_updated', 'c.grp_name', 'u.code', 's.name', 's.memo', 'c.sub_grp'
                )
                ->orderBy('q.id', 'DESC');

                $limit = null;
                
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    //$limit = null;

                    try {
                        // Ensure only the date part is considered
                        $query->whereBetween(DB::raw('DATE(q.created_at)'), [$request->start_date, $request->end_date]);
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Invalid date format'], 400);
                    }
                } else {
                    // Apply default filter for today's records
                    $today = date('Y-m-d'); // Get today's date
                    $query->whereDate('q.created_at', $today);
                    $limit = 1000;

                }
                

                if (!empty($data['search_user'])) {
                    $searchTerm = '%' . $data['search_user'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('q.customer_id', 'LIKE', $searchTerm)
                        ->orWhere('c.card_name', 'LIKE', $searchTerm)
                        ->orWhere('q.invoice_no', 'LIKE', $searchTerm)
                        ->orWhere('q.sap_created_id', 'LIKE', $searchTerm);
                    });
                }

                if (!empty($data['invoice_generate'])) {
                    if ($data['invoice_generate'] == "2") {
                        $query->whereNull('q.hash_key');
                    } elseif ($data['invoice_generate'] == "1") {
                        $query->whereNotNull('q.hash_key');
                    }
                }

                if (!empty($data['sap_err_msgs'])) {
                    $searchTerm = '%' . $data['sap_err_msgs'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('q.sap_error_msg', 'LIKE', $searchTerm); 
                    });
                }

                if (!empty($data['sync_status'])) {
                    if ($data['sync_status'] == "2") {
                        $query->whereNull('q.sap_created_id');
                    } elseif ($data['sync_status'] == "1") {
                        $query->whereNotNull('q.sap_created_id');
                    }
                }

                if (!empty($data['sales_type_search'])) {
                    $query->where('q.sales_type', $data['sales_type_search']);
                }

                if (!empty($data['sub_grp_search'])) {
                    $query->where('c.sub_grp', $data['sub_grp_search']);
                }

                if ($data['status_search'] != '') {
                    $query->where('q.status', $data['status_search']);
                }

                

                if ($limit !== null) {
                    $query->limit($limit);
                } elseif (isset($data['start']) && $data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }
                $totalRecords = (clone $query)->count();


            // echo "count" . $totalRecords;

                if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                    $column = $columnArray[$data['order'][0]['column']];
                    $direction = strtolower($data['order'][0]['dir']);

                    if (in_array($direction, ['asc', 'desc'])) {
                        $query->orderBy($column, $direction);
                    } else {
                        $query->orderBy($column, 'asc');
                    }
                }

                // Pagination
                if (isset($data['start']) && $data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                $order = $query->get();
            } catch (\Exception $e) {
                $order = [];
                $totalRecords = 0;
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'store_code' => $warehouseCode,
                'data' => $order
            ]);
        }
    }

    function newQuotation()
    {
        return view('quotation.new');
    }

    public function searchCustomer(Request $request)
    {
        //$warehouse = Auth()->ware_house;
        $warehouseCode = auth()->user()->ware_house;
        $data = $request->all();
        $query = DB::table('customers')
            ->select(
                'customers.*',
                'customers.card_code as id',
                DB::raw("
                CONCAT(
                    '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', customers.card_code, '</span>',
                    ' - ',
                    '<span style=\"color:black; font-weight:bold; font-size:11px;\">', customers.card_name, '</span>'
                ) as text
            "), // Corrected DB::raw
                'customers.product_list'
            )
            ->where('customers.status' , 1);
            //->selectRaw("(SELECT invoice_no FROM invoices ORDER BY created_at DESC LIMIT 1) as latest_invoice_no");
                if($warehouseCode == 'AAR-CAB')
                {
                    $query->whereIn('customers.grp_code',[108,109]);
                }else{
                    $query->whereNotIn('customers.grp_code',[108,109]);
                }

        if (!empty($data['search'])) {
            $query->where(function ($query) use ($data) {
                $query->where('customers.card_name', 'like', '%' . $data['search'] . '%')
                    ->orWhere('customers.card_code', 'like', '%' . $data['search'] . '%');
            });
        }
    //  $invoice_no = DB::raw("(SELECT o.invoice_no FROM orders o ORDER BY o.created_at DESC LIMIT 1) as latest_invoice_no");
        $res = $query->get();
        return response()->json($res);

    }

    public function sapSatusUpdate(Request $request)
    {
        try {
            $invoiceIds = $request->input('ids'); // Correct input retrieval
    
            if (!is_array($invoiceIds) || empty($invoiceIds)) {
                return response()->json(['error' => 'Invalid or empty invoice IDs'], 400);
            }
    
            // Updating the sap_status to 0 for all given IDs
            DB::table('invoices')->whereIn('id', $invoiceIds)->update(['sap_updated' => 0]);
    
            return response()->json([
                'success' => true,
                'message' => 'SAP Status updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error while SAP Status Update: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    

    public function getItemSearch($type = null, Request $request)
    {
        $data = $request->all();
        $warehouseCode = auth()->user()->ware_house;
        $customer = DB::table('customers')
            ->select('product_list','list_num','factor')
            ->where('card_code', $data['card_code'])
            ->first();

        $query = DB::table('items as i')
            ->leftJoin('warehouse_codebatch_details as ws', function ($join) use ($warehouseCode) {
                $join->on('i.item_code', '=', 'ws.item_code')
                    ->where('ws.whs_code', '=', $warehouseCode);
            })
            ->select(
                'i.id',
                'i.item_code',
                'i.item_name',
                'i.others_iva',
                'i.cabinda_iva',
                DB::raw("ROUND((i.item_price) * {$customer->factor}) as item_price_factor"),
                DB::raw("ROUND(COALESCE(SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty), 0), 0) as available_quantity")
            )
            ->where(function ($query) use ($data) {
                if (!empty($data['search'])) {
                    $query->where('i.item_name', 'like', '%' . $data['search'] . '%')
                        ->orWhere('i.item_code', 'like', '%' . $data['search'] . '%');
                }
            })
            ->groupBy('i.id', 'i.item_code', 'i.item_name')  // Group by necessary fields when using aggregation
            ->orderBy('i.item_code')
            ->get();



        // Post-process the result for formatting HTML as needed.
        $results = $query->map(function($item) {
            $warehouseCode = auth()->user()->ware_house;

            $iva_value = 0;
            if($warehouseCode == 'AAR-CAB')
            {
              $iva_value = $item->cabinda_iva;  
            }else{
              $iva_value = $item->others_iva;
            }

            $str = preg_replace("/[^0-9]/","",$iva_value);
            if($str){
            $tax = $str;
            }else{
            $tax = 0;
            }
            return [
                'id' => $item->id,
                'result' => [
                            'id' => $item->id,
                            'item_code' => $item->item_code,
                            'item_name' => $item->item_name,
                            'price' => $item->item_price_factor,
                            'quantity' => $item->available_quantity,
                            'tax'  => $tax
                        ],
                'text' => "<span style=\"color:gray; font-weight:bold; font-size:11px;\">{$item->item_code}</span> - " .
                          "<span style=\"color:black; font-weight:bold; font-size:11px;\">{$item->item_name}</span> - " .
                          "<span style=\"color:black; font-weight:bold; font-size:11px;\">kz {$item->item_price_factor}</span> - " .
                          "<span style=\"color:gray; font-weight:bold; font-size:11px;\">{$item->available_quantity}</span>"
            ];
        });
        return response()->json($results);
    }

    function getItem($itemId,$custoer_id)
    {

         $customer = DB::table('customers')
            ->select('product_list','list_num','factor')
            ->where('card_code', $custoer_id)
            ->first();

        try {
            $warehouseCode = auth()->user()->ware_house;
            $item = DB::table('items as i')
                    ->leftJoin('warehouse_codebatch_details as ws', function ($join) use ($warehouseCode) {
                        $join->on('i.item_code', '=', 'ws.item_code')
                            ->where('ws.whs_code', '=', $warehouseCode);
                    })

            ->select(
                'i.id as id',
                'i.item_code',
                'i.item_name',
                //DB::raw('SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty) as quantity'),
               DB::raw("ROUND((i.item_price) * {$customer->factor}) as price"),
                'i.others_iva',
                'i.cabinda_iva',
                DB::raw('SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty) as quantity')
            )
            ->where('i.id', $itemId)
            ->first();

            $warehouseCode = Auth::user()->ware_house;
            $iva_value = 0;
            if($warehouseCode == 'AAR-CAB')
            {
              $iva_value = $item->cabinda_iva;  
            }else{
              $iva_value = $item->others_iva;
            }

            $str = preg_replace("/[^0-9]/","",$iva_value);
            if($str){
            $tax = $str;
            }else{
            $tax = 0;
            }

            $item->tax = $tax;

            if ($item) {
                return $item;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the item.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    

    public function getInvoicePaymentDetails($invoiceId)
    {
        $invoicePayments = DB::table('order_payments as op')
            ->leftjoin('order_payment_sub as ops', 'op.id', '=', 'ops.order_payment_id')
            ->leftjoin('invoices as i', 'i.id', '=', 'ops.invoice_id')
            ->select('op.*', 'ops.amount as payment_amount' , 'ops.created_at as payment_date' , 'ops.customer_code as customer_id','i.total' , 'i.exceess_payment','ops.invoice_id')
            ->where('ops.invoice_id', $invoiceId)
            ->get();

        return response()->json(['data' => $invoicePayments]);
    }

    
    //public function storeInvoice(Request $request)
    //{
    //    //dd($request->all());
    //    try {
    //      //  DB::beginTransaction();
    //        $userId = Auth::user()->id;
    //        $invoiceStrCode = Auth::user()->code;
    //        $warehouseCode = Auth::user()->ware_house;
    //        // Generate Invoice Number
    //        $latestInvoice = DB::table('invoices')
    //            ->where('user_id', $userId)
    //            ->orderBy('id', 'desc')
    //            ->first();

    //        if ($latestInvoice) {
    //            $invoiceParts = explode('-', $latestInvoice->invoice_no);
    //            $invoiceNumericPart = isset($invoiceParts[2]) ? intval($invoiceParts[2]) : 0;
    //            $nextInvoiceNo = sprintf('%04d', $invoiceNumericPart + 1);
    //        } else {
    //            $nextInvoiceNo = '0001';
    //        }

    //        $invoiceNo = $warehouseCode . '-' . $nextInvoiceNo;

    //        $newSeries = $this->generateDaySeries($request->current_date, $warehouseCode);

    //        $salesEmp = DB::table('sales_employees')
    //                    ->where('name', $request->sales_emp_id)
    //                    ->orWhere('id', $request->sales_emp_id)
    //                    ->select('id')
    //                    ->first();


    //            $salesEmpId = $salesEmp ? $salesEmp->id : null;

    //        // Insert Invoice
    //        $invoiceId = DB::table('invoices')->insertGetId([
    //            'customer_id' => $request->customer_id,
    //            'order_id' => $request->order_id,
    //            'ref_id' => $request->ref_no,
    //            'sales_emp_id'=> $salesEmpId,
    //            'address' => $request->Cust_address,
    //            'user_id' => $userId,
    //            'day_series' => $newSeries,
    //            'store_code'=>$warehouseCode,
    //            'sales_type' => $request->sales_type,
    //            'status' => 0,
    //            'remarks' => $request->remarks,
    //            'pymnt_grp'=> $request->pymnt_grp,
    //            'due_date' => $request->due_date,
    //            'order_date' => $request->current_date,
    //            'discount_status' => 0,
    //            'invoice_no' => $invoiceNo,
    //        ]);

    //        $total = 0;
    //        $gstSummary = [];

    //        $hasDiscount = false;


    //        foreach ($request->row_item_id as $index => $itemId) {
    //            if (empty($itemId)) {
    //                Log::warning("Skipping empty item at index $index");
    //                continue;
    //            }
    //            $rowinvoiceId = $request->row_item_order_id[$index] ?? 0;
    //            $quantity = $request->row_item_qty[$index];
    //            $discPercent = $request->row_item_disc_percent[$index];
    //            $gstRate =  $request->row_item_gst_rate[$index];
    //            $gstValue =  $request->row_item_gst_rate_value[$index];

    //            $itemInfo = DB::table('item_priceses')->where('id', $itemId)->first();
    //            if (!$itemInfo) {
    //                throw new \Exception("Item not found for ID: {$itemId}");
    //            }

    //            $price = $itemInfo->price;
    //            $discValue = $price * $discPercent / 100;
    //            $priceAfterDisc = $price - $discValue;
    //            $priceTotalAfterDisc = $quantity * $priceAfterDisc;

    //            // GST Calculation
    //            //$gstRate = $itemInfo->gst_rate ?? 0;
    //            //$gstValue = $priceAfterDisc * $gstRate / 100;
    //            $subtotal = $priceTotalAfterDisc + ($quantity * $gstValue);
    //            $total += $subtotal;

    //            if ($discValue > 0) {
    //                $hasDiscount = true;
    //            }

    //        // Add to GST summary
    //        if (!isset($gstSummary[$gstRate])) {
    //            $gstSummary[$gstRate] = [
    //                'count' => 0,
    //                'sum' => 0,
    //                'subTotal' => 0,
    //            ];
    //        }
    //        $gstSummary[$gstRate]['count'] += $quantity;
    //        $gstSummary[$gstRate]['subTotal'] += $priceTotalAfterDisc;
    //        $gstSummary[$gstRate]['sum'] += ($quantity * $gstValue);

    //        ksort($gstSummary);

    //          $invoiceItems=  DB::table('invoice_items')->insert([
    //                'order_id' => $rowinvoiceId,
    //                'invoice_id' => $invoiceId,
    //                'item_id' => $itemId,
    //                'item_no' => $itemInfo->item_code,
    //                'item_desc' => $itemInfo->item_name,
    //                'quantity' => $quantity,
    //                'price' => $price,
    //                'disc_value' => $discValue,
    //                'disc_percent' => $discPercent,
    //                'price_af_disc' => $priceAfterDisc,
    //                'price_total_af_disc' => $priceTotalAfterDisc,
    //                'gst_rate' => $gstRate,
    //                'gst_value' => $gstValue,
    //                'subtotal' => $subtotal
    //                //'batch_number' => $batches->batch_num,
    //                // 'batch_qty' => $batches->quantity,
    //            ]);


    //            $wareHouseQty =    DB::table('warehouse_codebatch_details as ws')
    //             ->where('ws.item_code', '=', $itemId)
    //             ->where('ws.whs_code', '=', $warehouseCode)
    //             ->select('ws.*')
    //             ->first();


    //            $batches_data = $this->batchQuantity($itemInfo->item_code , $quantity);

    //            foreach ($batches_data as $key => $sele_batches) {
    //                    $batchNumber = $sele_batches['batch'];
    //                    $quantity = $sele_batches['qty'];
    //                    $invv_qty = $sele_batches['inv_quantity'];
    //                    $expiry_date = $sele_batches['exp'];
    //                    $insert_b_array = array(
    //                        'invoice_id' => $invoiceId,
    //                        'item_code' => $itemInfo->item_code,
    //                        'batch_num' => $batchNumber,
    //                        'quantity' => $quantity,
    //                        'expiry_date' => $expiry_date,
    //                         );


    //                    $batchItems = DB::table('invoice_batches')->insertGetId($insert_b_array);
    //                    //echo "binsert".$batchItems;
    //                    //exit;

    //                    //DB::table('warehouse_codebatch_details as ws')
    //                    //                    ->where('ws.item_code',$itemInfo->item_code)
    //                    //                    ->where('ws.batch_num',$batchNumber)
    //                    //                    ->select('')
    //                    //                    ->get()
    //                // $update_batches_qty = DB::table('warehouse_codebatch_details')
    //                //                         ->where('item_code',$itemInfo->item_code)
    //                //                         ->where('batch_num',$batchNumber)
    //                //                         ->increment(['invoice_qty' =>$quantity]);

    //                //         echo "check_qtyyy".$update_batches_qty; exit;
    //                 $update_qty = $invv_qty + $quantity;
    //                $update_batches_qty = DB::table('warehouse_codebatch_details')
    //                                        ->where('item_code',$itemInfo->item_code)
    //                                        ->where('batch_num',$batchNumber)
    //                                        ->update(['invoice_qty' =>$update_qty]);


    //                }
    //              // echo "update_batches_qty"; echo "YES".$update_batches_qty;
    //               // exit;
    //            if ($rowinvoiceId) {
    //                    // Update orders table status to 0
    //                    DB::table('orders')
    //                        ->where('id', $rowinvoiceId)
    //                        ->update(['status' => 1]);

    //            }
    //           // exit;
    //        }
    //        $inv_details = array(
    //            'date' => $request->current_date,
    //            'warehouse_code' => $warehouseCode,
    //            'due_date' => $request->due_date,
    //            'web_id' => $request->$newSeries,
    //            'doc_total' => $total
    //        );

    //        $hashKey = $this->HashkeyGenerationInvoice($inv_details);

    //        $invoiceUpdate = DB::table('invoices')
    //            ->where('id', $invoiceId)
    //            ->update([
    //                'hash_key' => $hashKey['hash_key'],
    //                'hash_key_code' => $hashKey['generated_code'],
    //                'total' => $total,
    //                'discount_status' => $hasDiscount ? 1 : 0,
    //            ]);

    //        // DB::table('invoices')->where('id', $invoiceId)->update(['total' => $total, 'discount_status' => $hasDiscount ? 1 : 0,]);

    //        DB::commit();

    //        //$invoicePdfUrl = $this->generateInvoicePDF($invoiceId , $gstSummary);

    //        $invoicePdfUrl = $this->generateInvoicePDF($invoiceId);

    //        return response()->json([
    //                'success' => true,
    //                'message' => ' Invoice created successfully.',
    //                'orderItems' => $invoiceItems,
    //                'invoice_pdf_url' => $invoicePdfUrl,
    //        ], 201);
    //    } catch (\Exception $e) {
    //        DB::rollback();
    //        Log::error('Error creating Invoice: ' . $e->getMessage(), [
    //            'request_data' => $request->all(),
    //            'trace' => $e->getTraceAsString(),
    //        ]);

    //        return response()->json([
    //            'success' => false,
    //            'message' => 'Error creating Invoice: ' . $e->getMessage(),
    //        ], 500);
    //    }
    //}

    public function storeInvoice(Request $request)
    {
        //dd($request);
        try {
            DB::beginTransaction();
            $userId = Auth::user()->id;
            $invoiceStrCode = Auth::user()->code;
            $warehouseCode = Auth::user()->ware_house;
            $custSubGrp = $request->customer_sub_grp ?? 'GENERAL';

            $latestInvoice = DB::table('invoices')
                ->where('store_code', $warehouseCode)
                ->where('customer_sub_grp', $custSubGrp)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();
            $wareHouseNum = DB::table('ware_houses as w')
                            ->select('str_inv_hosp_series','str_inv_gen_series')
                            ->where('whs_code', $warehouseCode)
                            ->first();
            
        $prefix = ($custSubGrp == 'HOSPITAL')?($wareHouseNum->str_inv_hosp_series):$wareHouseNum->str_inv_gen_series;

            $lastOrderNumber = 0;
            if ($latestInvoice) {
                $lastOrderNumber = (int) substr($latestInvoice->invoice_no, strrpos($latestInvoice->invoice_no, '/') + 1);
            }

            // Increment the last order number or start from 00001
        $newOrderNumber = $lastOrderNumber ? $lastOrderNumber + 1 : 1;
        $formattedOrderNumber = str_pad($newOrderNumber, 5, '0', STR_PAD_LEFT);
        $newOrderCode = $prefix . $formattedOrderNumber;
        $invoiceNo = $newOrderCode;

        //exit;
       // $newSeries = $this->generateDaySeries($warehouseCode);
        $salesEmp = DB::table('sales_employees')
                    ->where('name', $request->sales_emp_id)
                    ->orWhere('id', $request->sales_emp_id)
                    ->select('id')
                    ->first();

            $salesEmpId = $salesEmp ? $salesEmp->id : null;
            // Insert Invoice
            $systemName = gethostname();
            $systemIP = $this->getLaptopIp();
            $invoiceId = DB::table('invoices')->insertGetId([
                'customer_id' => $request->customer_id,
                'order_id' => $request->order_id,
                'ref_id' => $request->ref_no,
                'sales_emp_id'=> $salesEmpId,
                'address' => $request->cust_address_id,
                'user_id' => $userId,
               // 'day_series' => $newSeries,
                'store_code'=>$warehouseCode,
                'sales_type' => $request->sales_type,
                'customer_sub_grp' => $custSubGrp,
                'status' => 0,
                'system_name' => $systemName,
                'system_ip' => $systemIP,
                'remarks' => $request->remarks,
                'pymnt_grp'=> $request->pymnt_grp,
                'due_date' => $request->due_date,
                'order_date' => $request->current_date,
                'discount_status' => 0,
                'invoice_no' => $invoiceNo,

            ]);

            $total = 0;
            $gstSummary = [];
            $hasDiscount = false;
            foreach ($request->row_item_id as $index => $itemId) {
                if (empty($itemId)) {
                    Log::warning("Skipping empty item at index $index");
                    continue;
                }
                $rowinvoiceId = $request->row_item_order_id[$index] ?? 0;
                $quantity = $request->row_item_qty[$index];
                $discPercent = $request->row_item_disc_percent[$index];
                $gstRate =  $request->row_item_gst_rate[$index];
                $gstValue =  $request->row_item_gst_rate_value[$index];

                $itemInfo = DB::table('item_priceses')->where('id', $itemId)->first();
                if (!$itemInfo) {
                    throw new \Exception("Item not found for ID: {$itemId}");
                }

                $price = $itemInfo->price;
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

              $invoiceItems=  DB::table('invoice_items')->insert([
                    'order_id' => $rowinvoiceId,
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
                    'sap_base_line' => $index,
                   
                ]);

                $wareHouseQty =    DB::table('warehouse_codebatch_details as ws')
                 ->where('ws.item_code', '=', $itemId)
                 ->where('ws.whs_code', '=', $warehouseCode)
                 ->select('ws.*')
                 ->first();

               

                $batches_data = $this->batchQuantity($itemInfo->item_code , $quantity);
                
                if (empty($batches_data) || !is_array($batches_data)) {
                    throw new \Exception("No batches available for item with code: {$itemInfo->item_code}. Invoice creation aborted.");
                }
                foreach ($batches_data as $key => $sele_batches) {
                        $batchNumber = $sele_batches['batch'];
                        $quantity = $sele_batches['qty'];
                        $invv_qty = $sele_batches['inv_quantity'];
                //echo "batches details", print_r($invv_qty); exit;
                        
                        $expiry_date = $sele_batches['exp'];
                        $insert_b_array = array(
                            'invoice_id' => $invoiceId,
                            'item_code' => $itemInfo->item_code,
                            'batch_num' => $batchNumber,
                            'quantity' => $quantity,
                            'expiry_date' => $expiry_date,
                             );

                    $batchItems = DB::table('invoice_batches')->insertGetId($insert_b_array);    
                    $update_qty = $invv_qty + $quantity;


                        Log::info('Invoice Batch Update: ' . $invoiceId, [
                            'item_code'  => $itemInfo->item_code,
                            'update_qty' => $update_qty,
                            'exist_qty' =>  $sele_batches['inv_quantity'],
                            'aad_qty'   => $update_qty
                        ]);

                    $update_batches_qty = DB::table('warehouse_codebatch_details')
                                            ->where('whs_code',$warehouseCode)
                                            ->where('item_code',$itemInfo->item_code)
                                            ->where('batch_num',$batchNumber)
                                            ->update(['invoice_qty' =>$update_qty]);


                    }
                  // echo "update_batches_qty"; echo "YES".$update_batches_qty;
                   // exit;
                if ($rowinvoiceId) {
                        // Update orders table status to 0
                        DB::table('orders')
                            ->where('id', $rowinvoiceId)
                            ->update(['status' => 1]);

                }
               // exit;
            }
            $inv_details = array(
                'date' => $request->current_date,
                'warehouse_code' => $warehouseCode,
                'due_date' => $request->due_date,
                'web_id' => $invoiceNo,
                'doc_total' => $total
            );

            $hashKey = $this->HashkeyGenerationInvoice($inv_details);

            $invoiceUpdate = DB::table('invoices')
                ->where('id', $invoiceId)
                ->update([
                    'hash_key' => $hashKey['hash_key'],
                    'hash_key_code' => $hashKey['generated_code'],
                    'total' => $total,
                    'discount_status' => $hasDiscount ? 1 : 0,
                ]);

            // DB::table('invoices')->where('id', $invoiceId)->update(['total' => $total, 'discount_status' => $hasDiscount ? 1 : 0,]);

            $invoicePdfUrl = $this->generateInvoicePDF($invoiceId);

            DB::commit();

            //$invoicePdfUrl = $this->generateInvoicePDF($invoiceId , $gstSummary);


            return response()->json([
                    'success' => true,
                    'message' => ' Invoice created successfully.',
                    'orderItems' => $invoiceItems,
                    'invoice_pdf_url' => $invoicePdfUrl,
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

    public function generateDaySeries($warehouseCode ){
        $latestDateSeries = DB::table('invoices')
            ->select('day_series')
            ->where('store_code', '=', $warehouseCode)
            ->orderBy('id', 'desc')
            ->first();

        if (!empty($latestDateSeries) && !empty($latestDateSeries->day_series)) {
            $lastSeries = (int) $latestDateSeries->day_series;
            $newSeries = sprintf('%04d', $lastSeries + 1);
        } else {
            $newSeries = sprintf('%04d', 1);
        }

        return $newSeries;
    }

    

    // public function storePayments()
    // {
    //     try {
    //         request()->validate([
    //             'invoice_id' => 'required',
    //             'amount' => 'required|numeric|min:1',
    //             'payment_method' => 'required',
    //             'payment_type' => 'required',
    //         ]);
    //         $userId = Auth::user()->id;
    //         $invoiceId = request('invoice_id');
    //         $customer_code = request('customer_id');
    //         $amount = request('amount') ?? 0;
    //         $paymentMethod = request('payment_method');
    //         $paymentType = request('payment_type');
    //         $warehouseCode = Auth::user()->ware_house;


    //         $method = null;
    //         if(request('payment_method') == 'tpa' ){
    //             $method = 'tpa';
    //             $online = request('amount');
    //         } 

    //         if(request('payment_method') == 'bank'){
    //             $method = 'bank';
    //             $online = request('amount');
    //         }

    //         if(request('payment_method') == 'cash'){
    //             $cash = request('amount');
    //         }

    //         $orderPayments = DB::table('order_payments')->insertGetId([
    //             'invoice_id' => $invoiceId,
    //             'payment_method' => $paymentMethod,
    //             'payment_type' => $paymentType,
    //             'amount' => $amount,
    //             'bank_tpa' => $method,
    //             'online' => $online ?? 0,
    //             'cash' => $cash ?? '',
    //             'customer_code' => $customer_code,
    //             'user_id' => $userId,
    //             'created_at' => date('Y-m-d H:i:s'),
    //             'warehouse_code' => $warehouseCode,
    //         ]);

    //         $orderPaymentsSub = DB::table('order_payment_sub')->insert([
    //             'order_payment_id' => $orderPayments,
    //             'customer_code' => $customer_code,
    //             'invoice_id' => $invoiceId,
    //             'amount' => $amount,
    //             'created_at' => date('Y-m-d H:i:s'),
    //         ]);

    //         $currentPaidAmount = DB::table('order_payment_sub')
    //             ->where('invoice_id', $invoiceId)
    //             ->sum('amount');

    //         DB::table('invoices')->where('id', $invoiceId)->update([
    //             'paid_amount' => $currentPaidAmount,
    //         ]);

    //         //$invoiceId = DB::table('invoices')->where('id' , $invoiceId)->value('order_id');
    //         $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
    //         if ($currentPaidAmount >= $invoice->total  ) {
    //             DB::table('invoices')->where('id', $invoiceId)->update([
    //                 'status' => 1,
    //             ]);
    //         }
    //         return response()->json(['message' => 'Payment successfully submitted'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
    //     }

    // }

    public function storePayments()
    {
        DB::beginTransaction();
    
        try {
            // Validate input
            request()->validate([
                'invoice_id' => 'required',
                'amount' => 'required|numeric|min:1',
                'payment_method' => 'required|string',
                'payment_type' => 'required|string',
                'customer_id' => 'required|string',
            ]);
    
            $userId = Auth::user()->id;
            $warehouseCode = Auth::user()->ware_house;
            $invoiceId = request('invoice_id');
            $customerCode = request('customer_id');
            $amount = request('amount') ?? 0;
            $paymentMethod = request('payment_method');
            $paymentType = request('payment_type');
    
            $method = null;
            $online = 0;
            $cash = '';
    
            // Assign method-specific fields
            if ($paymentMethod == 'tpa') {
                $method = 'tpa';
                $online = $amount;
            } elseif ($paymentMethod == 'bank') {
                $method = 'bank';
                $online = $amount;
            } elseif ($paymentMethod == 'cash') {
                $cash = $amount;
            }
    
            $paymentsData = [
                'invoice_id' => $invoiceId,
                'payment_method' => $paymentMethod,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'bank_tpa' => $method,
                'online' => $online,
                'cash' => $cash,
                'customer_code' => $customerCode,
                'user_id' => $userId,
                'created_at' => now(),
                'warehouse_code' => $warehouseCode,
            ];
    
            if ($method === 'bank') {
                $paymentsData['is_payment_approved'] = 0;
            }
    
            $orderPaymentId = DB::table('order_payments')->insertGetId($paymentsData);
    
            // Insert into order_payment_sub
            DB::table('order_payment_sub')->insert([
                'order_payment_id' => $orderPaymentId,
                'customer_code' => $customerCode,
                'invoice_id' => $invoiceId,
                'amount' => $amount,
                'created_at' => now(),
            ]);
    
            $currentPaidAmount = DB::table('order_payment_sub')
                ->where('invoice_id', $invoiceId)
                ->sum('amount');
    
            DB::table('invoices')
                ->where('id', $invoiceId)
                ->update(['paid_amount' => $currentPaidAmount]);
    
            // Check if invoice is fully paid and update status
            $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
            if ($invoice && $currentPaidAmount >= $invoice->total) {
                DB::table('invoices')->where('id', $invoiceId)->update(['status' => 1]);
            }
    
            DB::commit();
    
            return response()->json(['message' => 'Payment successfully submitted'], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    public function showPayments(Request $request, $invoiceNo)
{
    $user = auth()->user();

    // Fetch invoice details
    $invoice = DB::table('invoices')
        ->where('id', $invoiceNo)
        ->first();

    if ($invoice) {
        $invoiceItems = DB::table('invoice_items')
            ->where('invoice_id', $invoice->id)
            ->get();

        $customer = DB::table('customers')
            ->where('card_code', $invoice->customer_id)
            ->first();

        $userFromInvoice = DB::table('users')
            ->where('id', $invoice->user_id)
            ->first();

        
        $payments = DB::table('order_payment_sub as ops')
            ->leftjoin('order_payments as op', 'op.id', '=', 'ops.order_payment_id')
            ->select('ops.*', 'op.payment_method as payment_method')
            ->where('ops.invoice_id', $invoice->id)
            ->get();


        $showPayments = in_array($user->role, [1, 2, 4]);

        $total = 0;
        $gstSummary = [];

        foreach ($invoiceItems as $item) {
            $price = $item->price;
            $quantity = $item->quantity;
            $disc_percent = $item->disc_percent;
            $disc_value = $price * $disc_percent / 100;
            $price_af_disc = $price - $disc_value;
            $price_total_af_disc = $quantity * $price_af_disc;

            // GST Calculation
            $gstRate = $item->gst_rate;
            $gstValue = $item->gst_value;

            $subtotal = $price_total_af_disc + ($quantity * $gstValue);
            $total += $subtotal;

            // Add to GST summary
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
        }

        ksort($gstSummary);

    } else {
        $orderItems = $customer = $payments = null;
        $total = 0;
        $gstSummary = [];
    }
    

    return view('quotation.invoice_payments', compact('invoice', 'invoiceItems', 'customer', 'payments', 'userFromInvoice', 'showPayments','gstSummary' ));
}

public function invoiceBatchList($invoiceId , $itemCode){
    $batches = DB::table('invoice_batches')
    ->where('invoice_id', $invoiceId)
    ->where('item_code', $itemCode)
    ->get();

    return response()->json($batches);
}



    //public function batchQuantity($itemId , $quantity)
    //{
    //    $warehouse = auth()->user()->ware_house;
    //    $batches = DB::table('warehouse_codebatch_details as ws')
    //                ->where('ws.item_code', '=', $itemId)
    //                ->where('ws.whs_code', '=', $warehouse)
    //                ->select('ws.*')
    //                ->orderBy('exp_date' , 'ASC')
    //                ->get();
    //    //echo "<pre>"; print_r($batches); exit;
    //    $check_batch = DB::table("warehouse_codebatch_details")
    //        ->select(DB::raw("SUM(quantity) as total_qty"),DB::raw("SUM(invoice_qty) as inv_qty"),DB::raw("SUM(credit_qty) as credit_qty"))
    //        ->where('item_code', '=', $itemId)
    //        ->where('whs_code', '=', $warehouse)
    //        ->first();

    //    $total_credit_de = ($check_batch->total_qty - $check_batch->inv_qty) + $check_batch->credit_qty;
    //    if($total_credit_de < $quantity)
    //    {
    //        return response()->json(["Item is Outof Stock - Available Qty -".$total_credit_de]);
    //    }else{
    //        $batch_addition = 0;
    //        $my_array = array();
    //        $remaining_quantity = $quantity;



    //    foreach ($batches as $key => $batch) {

    //         $available_batch = ($batch->quantity - $batch->invoice_qty) + $batch->credit_qty;
    //         if($available_batch > 0)
    //         {

    //            if ($remaining_quantity <= $batch->quantity) {
    //                    $batch_addition = $remaining_quantity;
    //                    $arrayName = array('batch' => $batch->batch_num, 'qty' => $batch_addition,'inv_quantity'=>$check_batch->inv_qty,'exp'=>date('Y-m-d', strtotime($batch->exp_date)));
    //                    $my_array[] = $arrayName;
    //                    return $my_array;  // Return as soon as batch fulfills the quantity
    //                } else {
    //                    // Use the entire batch and reduce the remaining quantity
    //                    $batch_addition = $batch->quantity;
    //                    $remaining_quantity -= $batch->quantity;

    //                    // Add this batch to the result array
    //                    $arrayName = array('batch' => $batch->batch_num, 'qty' => $batch_addition,'inv_quantity'=>$check_batch->inv_qty , 'exp'=>date('Y-m-d', strtotime($batch->exp_date)));
    //                    $my_array[] = $arrayName;
    //                }                


    //         }else{
    //            continue;
    //         }


    //        }
    //          return response()->json($my_array);
    //    }
    //}
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


    public function salesEmployee(Request $request)
    {
        try {
            $salesEmp = DB::table('sales_employees')
                            ->where('name', 'like', '%' . $request->search . '%')
                            ->select('*')
                            ->get();


            // Format the data for select2
            $formattedData = $salesEmp->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'text' => $emp->name . ' - ' . $emp->memo,
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



    

    //function generateInvoicePDF($invoiceNo)
    //{
    //    $warehouseCode = Auth::user()->ware_house;

    //    // Fetch the invoice details
    //    $invoice = DB::table('invoices')
    //        ->join('customers as c', 'invoices.customer_id', '=', 'c.card_code')
    //        ->select('invoices.*', 'c.card_name as customer_name', 'c.nif_no')
    //        ->where('invoices.id', $invoiceNo)
    //        ->first();

    //    if (!$invoice) {
    //        throw new \Exception("Invoice number $invoiceNo not found.");
    //    }

    //    // Fetch the invoice items
    //    $items = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();

    //    $invoiceBatches = DB::table('invoice_batches')->where('invoice_id', $invoice->id)->get();

    //    $total = 0;
    //    $gstSummary = [];

    //    foreach ($items as $item) {
    //        $price = $item->price;
    //        $quantity = $item->quantity;
    //        $disc_percent = $item->disc_percent;
    //        $disc_value = $price * $disc_percent / 100;
    //        $price_af_disc = $price - $disc_value;
    //        $price_total_af_disc = $quantity * $price_af_disc;

    //        $gstRate = $item->gst_rate;
    //        $gstValue = $item->gst_value;

    //        $subtotal = $price_total_af_disc + ($quantity * $gstValue);
    //        $total += $subtotal;

    //        if (!isset($gstSummary[$gstRate])) {
    //            $gstSummary[$gstRate] = [
    //                'count' => 0,
    //                'sum' => 0,
    //                'subTotal' => 0,
    //            ];
    //        }
    //        $gstSummary[$gstRate]['count'] += $quantity;
    //        $gstSummary[$gstRate]['subTotal'] += $price_total_af_disc;
    //        $gstSummary[$gstRate]['sum'] += ($quantity * $gstValue);

            
    //        $item->batches = collect($invoiceBatches)
    //        ->where('item_code', $item->item_no)
    //        ->map(function($batch) {
    //            return [
    //                'quantity' => $batch->quantity,
    //                'expiry_date' => $batch->expiry_date
    //            ];
    //        })
    //        ->values()
    //        ->all();
    //    }

    //    ksort($gstSummary);

    //    // Generate the PDF
    //    $pdf = PDF::loadView('pdf.new-format-pdf', compact('invoice', 'items', 'warehouseCode', 'gstSummary'));

    //    // Define folder and file paths
    //    $folderPath = 'pdf/invoices';
    //    $pdfDirectory = public_path($folderPath);

    //    // Create the directory if it doesn't exist
    //    if (!file_exists($pdfDirectory)) {
    //        mkdir($pdfDirectory, 0755, true);
    //    }

    //    // Define the PDF file name and path
    //    $pdfName = 'invoice_copy_' . $invoice->id . '.pdf';
    //    $pdfFilePath = $pdfDirectory . '/' . $pdfName;

    //    // Save the PDF to the specified path
    //    $pdf->save($pdfFilePath);

    //    if (!file_exists($pdfFilePath)) {
    //        throw new \Exception("PDF file was not created successfully at $pdfFilePath.");
    //    }

    //    return url($folderPath . '/' . $pdfName);
    //}

    public function generateInvoicePDF11($invoiceNo)
    {
        $warehouseCode = Auth::user()->ware_house;

        $invoice = DB::table('invoices')
            ->join('customers as c', 'invoices.customer_id', '=', 'c.card_code')
            ->join('sales_employees as se', 'se.id', '=', 'invoices.sales_emp_id')
            ->select('invoices.*', 'c.card_name as customer_name', 'c.nif_no', 'se.memo')
            ->where('invoices.id', $invoiceNo)
            ->first();

        if (!$invoice) {
            throw new \Exception("Invoice number $invoiceNo not found.");
        }

        $items = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
        $invoiceBatches = DB::table('invoice_batches')->where('invoice_id', $invoice->id)->get();
        $custAddress = DB::table('customer_addresses')->where('id', $invoice->address)->select('address')->first();
        $ware_house_data = DB::table('ware_houses')->where('whs_code', $warehouseCode)->first();

        $total = 0;
        $gstSummary = [];

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

            $item->batches = collect($invoiceBatches)
                ->where('item_code', $item->item_no)
                ->map(fn($batch) => [
                    'quantity' => $batch->quantity,
                    'expiry_date' => $batch->expiry_date
                ])
                ->values()
                ->all();
        }

        ksort($gstSummary);

        $itemsPerPage = 11;
        $totalPages = ceil(count($items) / $itemsPerPage);

        // --- Step 1: Render Blade view ---
        $html = view('pdf.new-format-pdf', compact(
            'invoice', 'items', 'warehouseCode', 'gstSummary', 'totalPages', 'custAddress', 'ware_house_data'
        ))->render();

        // --- Step 2: Generate PNG using Browsershot ---
        $tempImage = storage_path('app/public/invoice_' . time() . '.png');

        Browsershot::html($html)
            ->windowSize(1240, 1754)  // roughly A4 @150 DPI
            ->deviceScaleFactor(2)    // High-resolution screenshot
            ->fullPage()
            ->waitUntilNetworkIdle()
            ->save($tempImage);

        // --- Step 3: Convert image to protected PDF ---
        $mpdf = new Mpdf(['format' => 'A4']);
        $mpdf->AddPage();
        $mpdf->Image($tempImage, 0, 0, 210, 297, 'png', '', true, false);
        $mpdf->SetProtection(['print']); // only allow printing, no edit or copy

        // --- Step 4: Save PDF file ---
        $folderPath = 'pdf/invoices';
        $pdfDirectory = public_path($folderPath);

        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        $pdfName = 'invoice_copy_' . $invoice->id . '.pdf';
        $pdfFilePath = $pdfDirectory . '/' . $pdfName;

        $mpdf->Output($pdfFilePath, 'F'); // Save file to disk

        if (!file_exists($pdfFilePath)) {
            throw new \Exception("PDF file was not created successfully at $pdfFilePath.");
        }

        return url($folderPath . '/' . $pdfName);
    }

    public function generateInvoicePDF($invoiceNo)
{
    $warehouseCode = Auth::user()->ware_house;

    $invoice = DB::table('invoices')
        ->join('customers as c', 'invoices.customer_id', '=', 'c.card_code')
        ->join('sales_employees as se', 'se.id', '=', 'invoices.sales_emp_id')
        ->select('invoices.*', 'c.card_name as customer_name', 'c.nif_no', 'se.memo')
        ->where('invoices.id', $invoiceNo)
        ->first();

    if (!$invoice) {
        throw new \Exception("Invoice number $invoiceNo not found.");
    }

    $items = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
    $invoiceBatches = DB::table('invoice_batches')->where('invoice_id', $invoice->id)->get();
    $custAddress = DB::table('customer_addresses')->where('id', $invoice->address)->select('address')->first();
    $ware_house_data = DB::table('ware_houses')->where('whs_code', $warehouseCode)->first();

    // --- GST Summary ---
    $gstSummary = [];
    foreach ($items as $item) {
        $price = $item->price;
        $quantity = $item->quantity;
        $disc_percent = $item->disc_percent;
        $price_af_disc = $price - ($price * $disc_percent / 100);

        $gstRate = $item->gst_rate;
        if (!isset($gstSummary[$gstRate])) {
            $gstSummary[$gstRate] = ['count' => 0, 'sum' => 0, 'subTotal' => 0];
        }
        $gstSummary[$gstRate]['count'] += $quantity;
        $gstSummary[$gstRate]['subTotal'] += $price_af_disc * $quantity;
        $gstSummary[$gstRate]['sum'] += $item->gst_value * $quantity;

        $item->batches = collect($invoiceBatches)
            ->where('item_code', $item->item_no)
            ->map(fn($batch) => [
                'quantity' => $batch->quantity,
                'expiry_date' => $batch->expiry_date
            ])
            ->values()
            ->all();
    }

    ksort($gstSummary);

    // --- Step 1: Render Blade HTML ---
    $html = view('pdf.new-format-pdf', compact(
        'invoice', 'items', 'warehouseCode', 'gstSummary', 'custAddress', 'ware_house_data'
    ))->render();

    // --- Step 2: Use Browsershot to capture each full A4 page as PNG ---
    $tempFolder = storage_path('app/public/invoice_pages_' . $invoice->id);
    if (!file_exists($tempFolder)) mkdir($tempFolder, 0755, true);

    // Browsershot auto handles multi-page content if height set to A4
    $pageImages = [];
    $pageHeight = 1754; // A4 at 150 DPI height in px (approx)
    $pageWidth = 1240; // A4 width at 150 DPI
    $pageIndex = 1;
    $scrollOffset = 0;
    $maxScrolls = 10; // limit for safety

    do {
        $imagePath = "{$tempFolder}/page_{$pageIndex}.png";

        Browsershot::html($html)
            ->windowSize($pageWidth, $pageHeight)
            ->deviceScaleFactor(2)
            ->clip(0, $scrollOffset, $pageWidth, $pageHeight)
            ->save($imagePath);

        if (!file_exists($imagePath)) break;

        $pageImages[] = $imagePath;
        $pageIndex++;
        $scrollOffset += $pageHeight;
    } while ($pageIndex <= $maxScrolls);

    // --- Step 3: Combine PNGs into a PDF using mPDF ---
    $mpdf = new Mpdf(['format' => 'A4']);
    foreach ($pageImages as $index => $pageImage) {
        if ($index > 0) $mpdf->AddPage();
        $mpdf->Image($pageImage, 0, 0, 210, 297, 'png', '', true, false);
    }

    // Optional: restrict editing/printing
    $mpdf->SetProtection(['print']);

    // --- Step 4: Save final PDF ---
    $folderPath = 'pdf/invoices';
    $pdfDirectory = public_path($folderPath);
    if (!file_exists($pdfDirectory)) mkdir($pdfDirectory, 0755, true);

    $pdfName = 'invoice_copy_' . $invoice->id . '.pdf';
    $pdfFilePath = $pdfDirectory . '/' . $pdfName;
    $mpdf->Output($pdfFilePath, 'F');

    // --- Step 5: Cleanup temp PNGs ---
    foreach ($pageImages as $page) @unlink($page);
    @rmdir($tempFolder);

    return url($folderPath . '/' . $pdfName);
}
    

    function test1(){

        echo "<pre>";
    }

    //function test(){

    //       echo "<pre>";
    //         print_r( geoip_netspeedcell_by_name());
    //         exit;
    //}

    //public function test()
    //{
    //    echo "testing";
    //    $location = geoip_netspeedcell_by_name('www.google.com');
    //   // $location = geoip_netspeedcell_by_name();

    //    echo "<pre>";
    //    print_r( $location);
    //    return response()->json($location);
    //}


    public function test()
    {
        $url = 'https://google.com';

        $startTime = microtime(true);

        $client = new Client();
        try {
            $response = $client->get($url, ['stream' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Speed test failed: ' . $e->getMessage()], 500);
        }

        $endTime = microtime(true);

        $timeTaken = $endTime - $startTime;

        $fileSizeInBytes = 10 * 1024 * 1024;

        $downloadSpeed = ($fileSizeInBytes * 8) / ($timeTaken * 1024 * 1024);

        echo "<pre>";
        print_r($downloadSpeed);
        return response()->json([
            'download_speed' => round($downloadSpeed, 2) . ' Mbps',
            'time_taken' => round($timeTaken, 2) . ' seconds',
        ]);
    }
    public function HashkeyGenerationInvoice($inv_details,$warehouseCode)
        {
           // $warehouseCode = Auth::user()->ware_house;

            $privateKeyFile = url('/').'/public/haskey/b1-saft-private.pem';
        // $arrContextOptions=array(
        //         "ssl"=>array(
        //             "verify_peer"=>false,
        //             "verify_peer_name"=>false,
        //         ),
        //     );  

        // $privateKey = file_get_contents($privateKeyFile, false, stream_context_create($arrContextOptions));
        


            // Load the private key from the file
           $privateKey = file_get_contents($privateKeyFile);
            if (!$privateKey) {
                return "Error reading private key file.";
            }

            // Create a private key resource
            $privateKeyResource = openssl_pkey_get_private($privateKey);
            if (!$privateKeyResource) {
                return "Invalid private key.";
            }

                $formateDate = date("d-m-Y", strtotime($inv_details['date']));
                $otherDate = date("ymd", strtotime($inv_details['due_date']));


               // $data ='07-11-2024;07-11-2024;FT-Prima/12/241107/123/123/123';
                $data =  $formateDate.';'.$formateDate.';FT-Prima'.'/'.$warehouseCode.'/'.$otherDate.'/'.$inv_details['web_id'].'/'.$inv_details['web_id'].'/'.$inv_details['doc_total'];

                // Hash and sign the data with the private key
                $signature = '';
                if (openssl_sign($data, $signature, $privateKeyResource, OPENSSL_ALGO_SHA1)) {
                    // Return the signature in Base64 format
                    $generated_code = base64_encode($signature);

                $result = substr($generated_code, 0, 1).substr($generated_code, 10, 1).substr($generated_code, 20, 1).substr($generated_code, 30, 1);

                //echo $result;

                //exit;
                return ([
                    'generated_code' => $generated_code,
                    'hash_key' => $result,
                ]);
                            } else {
                return "Error generating signature.";
            }
        }

// Usage
// $data = "data to be signed";
// $privateKeyFile = "path/to/private_key.pem";  // Replace with the path to your private key file
// $signature = computeSignatureSHA1withRSA($data, $privateKeyFile);
// echo "Signature: " . $signature;


    public function SapOrderPost()
        {
            $orderId = 7;
            $order = DB::table('orders as q')
                        ->join('customers as c', 'q.customer_id', '=', 'c.card_code')
                        ->select('q.id', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id', 'q.order_date', 'q.status', 'q.total', 'q.sales_invoice_no', 'q.bill_to', 'c.card_name')
                        ->where('q.id', $orderId)
                        ->whereNotNull('q.sales_invoice_no')
                        ->orderBy('q.id', 'desc')
                        ->first();

            if (!$order) {
                return response()->json(['error' => 'Order not found or sales invoice number missing'], 404);
            }

            $uniqueNumber = $order->id . "_" . date('YmdHis', strtotime($order->order_date)) . "_" . md5(time() + rand(1, 999999));
            $warehouseCode = auth()->user()->ware_house;

            $sapJson = [
                'Series' => 161,
                'CardCode' => $order->customer_id,
                'CardName' => $order->card_name,
                'DocType' => "I",
                'DocDate' => date('Y-m-d', strtotime($order->order_date)),
                'DocDueDate' => "2024-09-11",
                'TaxDate' => date('Y-m-d', strtotime($order->order_date)),
                'NumAtCard' => "TEST SO",
                'Project' => "AAR-BIE",
                'PaymentGroupCode' => "1",
                //'PayToCode' => $order->card_name,
                //'ShipToCode' => $order->card_name,
                'U_TRPGUID' => $uniqueNumber,
                'SalesPersonCode' => "-1",
                'DiscountPercent' => 0.0,
                'Comments' => $order->remarks ?? '',
                'U_VSPCUSTTAXID' => "123456789",
                'U_TRPSALETYPE' => "CASH",
                'U_TKRWHS' => $warehouseCode,
                'BPL_IDAssignedToInvoice' => 22,
            ];

            $orderItems = DB::table('order_items as or')
                            ->join('orders as os', 'or.order_id', '=', 'os.id')
                            ->join('warehouse_codebatch_details as wcbd', 'or.item_no', '=', 'wcbd.item_code')
                            ->select('or.*', 'os.customer_id', 'os.order_date', 'wcbd.whs_code')
                            ->where('or.order_id', $orderId)
                            ->get();

                            // echo "test orderItesms";
                            // echo json_encode($orderItems, JSON_PRETTY_PRINT);
                            // exit;


            $documentLinesAll = [];
            foreach ($orderItems as $orderItem) {
                $documentLinesAll[] = [
                    'ItemCode' => $orderItem->item_no,
                    'ItemDescription' => $orderItem->item_desc,
                    'ItemDetails' => "null",
                    'WarehouseCode' => $orderItem->whs_code,
                    'Project' => $orderItem->whs_code,
                    'Quantity' => $orderItem->quantity,
                    'UnitPrice' => $orderItem->price,
                    'Price' => $orderItem->subtotal,
                    'TaxCode' => "IVA0"
                ];
            }

            $sapJson['DocumentLines'] = $documentLinesAll;
                //echo "test documents lines";
                // echo json_encode($sapJson, JSON_PRETTY_PRINT);
                // exit;

            $url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateSalesOrder.xsjs?DBName=TEST_AARNEXT_2909";

            $response = Http::withHeaders([
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg=='
            ])
            ->withOptions(['verify' => false])
            ->post($url, $sapJson);

            //if ($response->successful()) {
            //    return response()->json(['message' => 'Sales order posted successfully', 'data' => $response->json() , 'order' => $order,
            //    'orderItems' => $orderItems] ,);
            //} else {
            //    return response()->json(['error' => 'Failed to create sales order', 'details' => $response->body()], $response->status());
            //}
            if ($response->successful()) {
                $sapResponseData = $response->json();

                // Assuming 'DocEntry' and 'DocNum' are returned by SAP to indicate success
                $sapDocEntry = $sapResponseData['DocEntry'] ?? null;
                $sapDocNum = $sapResponseData['DocNum'] ?? null;

                // Update order with SAP response details
                DB::table('orders')
                    ->where('id', $orderId)
                    ->update([
                        'sap_doc_entry' => $sapDocEntry,
                        'sap_doc_num' => $sapDocNum,
                        'sap_status' => 'Posted'
                    ]);

                return response()->json([
                    'message' => 'Sales order posted successfully',
                    'sapResponse' => $sapResponseData,
                    'order' => $order,
                    'orderItems' => $orderItems
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to create sales order',
                    'details' => $response->body(),
                    'order' => $order,
                    'orderItems' => $orderItems
                ], $response->status());
            }
    }

    //public function SapInvoicePost()
    //{
    //    $invoiceId = 8;
    //    $invoice = DB::table('invoices')
    //                ->where('id', $invoiceId)
    //                ->first();

    //    if (!$invoice) {
    //        return response()->json(['error' => 'Order not found or sales invoice number missing'], 404);
    //    }

    //    //$uniqueNumber = $order->id . "_" . date('YmdHis', strtotime($order->order_date)) . "_" . md5(time() + rand(1, 999999));

    //    $sapJson = [
    //        'Series' => 161,
    //        'DocDate' => "2024-10-03",
    //        'DocDueDate' => "2024-10-03",
    //        'TaxDate' => "2024-10-03",
    //        'BPL_IDAssignedToInvoice' => '1',

    //    ];

    //    $invoiceItems = DB::table('invoice_items')
    //    ->where('id', $invoiceId)
    //    ->get();
    //    $order = DB::table('orders')
    //    ->where('id', $invoiceItems->order_id)
    //    ->first();

    //    $documentLinesAll = [];
    //    foreach ($invoiceItems as $invoiceItem) {
    //        $documentLinesAll[] = [
    //            'ItemCode' => $invoiceItem->item_no,
    //            'Quantity' => $invoiceItem->quantity,
    //            'BaseType' => $order->sap_doc_num,
    //            'BaseEntry' =>$order->sap_doc_entry,
    //            'BaseLine' => 0,
    //        ];
    //    }

    //    $sapJson['DocumentLines'] = $documentLinesAll;
    //        //echo "test documents lines";
    //        // echo json_encode($sapJson, JSON_PRETTY_PRINT);
    //        // exit;

    //    //$url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateSalesOrder.xsjs?DBName=TEST_AARNEXT_2909";

    //    $url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateInvoiceWithBatch.xsjs?&DBName=TEST_AARNEXT_2909"
    //    //$response = Http::withHeaders([
    //    //    'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
    //    //    'client_secret' => 'qt+h23rs9botqp//AsAYEg=='
    //    //])
    //    ->withOptions(['verify' => false])
    //    ->post($url, $sapJson);


    //    if ($response->successful()) {
    //        $sapResponseData = $response->json();

    //        return response()->json([
    //            'message' => 'Invoice order posted successfully',
    //            'sapResponse' => $sapResponseData,
    //            'order' => $order,
    //            'invoiceItems' => $invoiceItems
    //        ]);
    //    } else {
    //        return response()->json([
    //            'error' => 'Failed to create Invoice order',
    //            'details' => $response->body(),
    //            'order' => $order,
    //            'invoiceItems' => $invoiceItems
    //        ], $response->status());
    //    }
    //}
    public function SapInvoicePost()
{
    try {
        $invoiceId = 22;
        // Fetch the invoice
        $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoiceId)->get();
        if ($invoiceItems->isEmpty()) {
            return response()->json(['error' => 'No items found for this invoice'], 404);
        }

        $orderId = $invoiceItems->first()->order_id;
        $order = DB::table('orders')->where('id', $orderId)->first();
        //$orderItems = DB::table('order_items')->where('order_id', $orderId)->first();

        // Build the SAP JSON payload
        $sapJson = [
            'Series' => 94,
            'DocDate' => "2024-10-03",
            'DocDueDate' => "2024-10-03",
            'TaxDate' => "2024-10-03",
            'BPL_IDAssignedToInvoice' => '1',
        ];

        // Prepare document lines
        $documentLinesAll = [];
        foreach ($invoiceItems as $invoiceItem) {

            $orderItems = DB::table('order_items')
            ->where('order_id', $invoiceItem->order_id)
            ->where('item_no', $invoiceItem->item_no)->first();
            //dd( $orderItems);
            $documentLinesAll[] = [
                'ItemCode' => $invoiceItem->item_no,
                'Quantity' => $invoiceItem->quantity,
                'BaseType' => 17,
                'BaseEntry' => $order->sap_doc_entry,
                'BaseLine' => $orderItems->base_line,
                'BatchNumbers' => [
                    [
                        'ItemCode' => $invoiceItem->item_no,
                        'BatchNumber' => $invoiceItem->batch_number ?? 'Null',
                        'Quantity' => $invoiceItem->batch_qty,
                    ]
                ]
            ];
        }


        $sapJson['DocumentLines'] = $documentLinesAll;

        //echo "test documents lines";
        //echo json_encode($sapJson, JSON_PRETTY_PRINT);
        //exit;
        // Define the SAP API URL
        $url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateInvoiceWithBatch.xsjs?DBName=TEST_AARNEXT_2909";

        // Send the request to the SAP API
        $response = Http::withOptions(['verify' => false])
            ->post($url, $sapJson);

        // Handle the response
        if ($response->successful()) {
            $sapResponseData = $response->json();
            return response()->json([
                'message' => 'Invoice successfully posted to SAP',
                'sapResponse' => $sapResponseData,
                'order' => $order,
                'invoiceItems' => $invoiceItems
            ], 200);
        } else {
            return response()->json([
                'error' => 'Failed to create Invoice in SAP',
                'details' => $response->body()
            ], $response->status());
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'An error occurred while processing the request',
            'details' => $e->getMessage()
        ], 500);
    }
}

public function SapDirectInvoicePost($invoiceId)
    {
        try {
            $invoice = DB::table('invoices')->where('id', $invoiceId)->first();
            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoiceId)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }
            $uniqueNumber = $invoice->id . "_" . date('YmdHis', strtotime($invoice->order_date)) . "_" . md5(time() + rand(1, 999999));

            $dueDate = date("ymd", strtotime($invoice->due_date));
            $OrderDate = date("ymd", strtotime($invoice->order_date));
            $sapJson = [
                'Series' => 335,
                "CardCode" => $invoice->customer_id,
                "NumAtCard" => "TestAPI",
                'DocDate' => $OrderDate,
                'DocDueDate' => $dueDate,
                'TaxDate' => $OrderDate,
                'BPL_IDAssignedToInvoice' => '22',
            ];

            // Prepare document lines
            $documentLinesAll = [];
            foreach ($invoiceItems as $invoiceItem) {

                //dd( $orderItems);
                $documentLinesAll[] = [
                    'ItemCode' => $invoiceItem->item_no,
                    'ItemDescription' => $invoiceItem->item_desc,
                    'Quantity' => $invoiceItem->quantity,
                    'WarehouseCode' => $invoice->store_code,
                    'PriceBefDi'=> $invoiceItem->price,
                    'Price' => $invoiceItem->subtotal,
                    'TaxCode'=> "IVA0",
                    'BatchNumbers' => [
                        [
                            //'ItemCode' => $invoiceItem->item_no,
                            'BatchNumber' => $invoiceItem->batch_number,
                            'Quantity' => $invoiceItem->quantity,
                        ]
                    ]
                ];
            }


            $sapJson['DocumentLines'] = $documentLinesAll;

            //echo "test documents lines";
           // echo json_encode($sapJson, JSON_PRETTY_PRINT);
           // exit;
            // Define the SAP API URL
            $url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateInvoiceWithBatch.xsjs?&DBName=ZZZ_AARNEXT_051224";

            // Send the request to the SAP API
            $response = Http::withOptions(['verify' => false])
                ->post($url, $sapJson);

            // Handle the response
            if ($response->successful()) {
                $sapResponseData = $response->json();
                return response()->json([
                    'message' => 'Invoice successfully posted to SAP',
                    'sapResponse' => $sapResponseData,
                    'invoice' => $invoice,
                    'invoiceItems' => $invoiceItems
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Failed to create Invoice in SAP',
                    'details' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage()
            ], 500);
        }
    }

 public function SapInvoicePostByCron()
    {

        try {
            $invoice = DB::table('invoices')->where('sap_updated',0)->first();
            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoiceId)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }
            $uniqueNumber = $invoice->id . "_" . date('YmdHis', strtotime($invoice->order_date)) . "_" . md5(time() + rand(1, 999999));

            $dueDate = date("ymd", strtotime($invoice->due_date));
            $OrderDate = date("ymd", strtotime($invoice->order_date));
            $sapJson = [
                'Series' => 335,
                "CardCode" => $invoice->customer_id,
                "NumAtCard" => "TestAPI",
                'DocDate' => $OrderDate,
                'DocDueDate' => $dueDate,
                'TaxDate' => $OrderDate,
                'BPL_IDAssignedToInvoice' => '22',
            ];

            // Prepare document lines
            $documentLinesAll = [];
            foreach ($invoiceItems as $invoiceItem) {

                //dd( $orderItems);
                $documentLinesAll[] = [
                    'ItemCode' => $invoiceItem->item_no,
                    'ItemDescription' => $invoiceItem->item_desc,
                    'Quantity' => $invoiceItem->quantity,
                    'WarehouseCode' => $invoice->store_code,
                    'PriceBefDi'=> $invoiceItem->price,
                    'Price' => $invoiceItem->subtotal,
                    'TaxCode'=> "IVA0",
                    'BatchNumbers' => [
                        [
                            //'ItemCode' => $invoiceItem->item_no,
                            'BatchNumber' => $invoiceItem->batch_number,
                            'Quantity' => $invoiceItem->quantity,
                        ]
                    ]
                ];
            }


            $sapJson['DocumentLines'] = $documentLinesAll;

            //echo "test documents lines";
           // echo json_encode($sapJson, JSON_PRETTY_PRINT);
           // exit;
            // Define the SAP API URL
            $url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateInvoiceWithBatch.xsjs?&DBName=ZZZ_AARNEXT_051224";

            // Send the request to the SAP API
            $response = Http::withOptions(['verify' => false])
                ->post($url, $sapJson);

            // Handle the response
            if ($response->successful()) {
                $sapResponseData = $response->json();
                echo "<pre>"; print_r($sapResponseData); exit;

                 Log::error('successfully Created  : ' . $sapResponseData);
                // return response()->json([
                //     'message' => 'Invoice successfully posted to SAP',
                //     'sapResponse' => $sapResponseData,
                //     'invoice' => $invoice,
                //     'invoiceItems' => $invoiceItems
                // ], 200);
            } else {
                Log::error('Error while Cretae Error Code : ' . $response->status());
                // return response()->json([
                //     'error' => 'Failed to create Invoice in SAP',
                //     'details' => $response->body()
                // ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error while Cretae Invoices : ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function getCustomerItems(Request $request , $itemCode)
    {
        $data = $request->all();

        if (!isset($data['card_code']) || empty($data['card_code'])) {
            return response()->json(['error' => 'Customer card_code is required'], 400);
        }

        $warehouseCode = auth()->user()->ware_house;

            $customer = DB::table('customers')
            ->select('product_list','list_num','factor')
            ->where('card_code', $data['card_code'])
            ->first();

        $factor = $customer->factor ?? 1;
        
        if (!$customer || empty($customer->product_list)) {
            return response()->json(['error' => 'No product list found for this customer'], 404);
        }

        $query = DB::table('items as i')
        ->select(
            'i.id',
            'i.item_code',
            'i.item_name',
            'i.others_iva',
            'i.cabinda_iva',
            DB::raw("ROUND((i.item_price) * {$factor}) as price")
            
            //  DB::raw("ROUND(COALESCE(SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty), 0), 0) as available_quantity")
            )
            ->where('i.item_code', $itemCode)
            ->first();
        $warehouseCode = Auth::user()->ware_house;
        $iva_value = 0;
        if($warehouseCode == 'AAR-CAB')
        {
            $iva_value = $query->cabinda_iva;  
        }else{
            $iva_value = $query->others_iva;
        }

        $str = preg_replace("/[^0-9]/","",$iva_value);
        if($str){
        $tax = $str;
        }else{
        $tax = 0;
        }

        $query->tax = $tax;

        return response()->json($query);
    }

}
