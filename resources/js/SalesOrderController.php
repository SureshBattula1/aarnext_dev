<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use GuzzleHttp\Client;
use Carbon\Carbon;


class SalesOrderController extends Controller
{

    function Salesindex()
    {
        return view('salesorder.list');
    } 

    function SalesOrderJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = ['q.id','q.sales_ref_no', 'q.sales_type','q.sales_invoice_no','q.order_date', 'q.customer_id', 'q.total', 'q.status', 'q.sales_invoice_no', 'q.ref_id', 'q.user_id', 'q.bill_to', 'q.ship_to','c.card_name','c.grp_name','u.code','s.name','q.sales_emp_id'];

            try {
                $userId = Auth::user()->id;
                $wareHouse = Auth()->user()->ware_house;

                $query = DB::table('orders as q')
                ->leftjoin('customers as c', 'q.customer_id', '=', 'c.card_code')
                ->leftjoin('users as u', 'q.user_id', '=', 'u.id')
                ->leftjoin('sales_employees as s', 'q.sales_emp_id', '=', 's.id')
                    ->select('q.id','q.sales_ref_no', 'q.sales_invoice_no', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id', 'q.order_date', 'q.status', 'q.total', 'q.sales_invoice_no', 'q.bill_to',
                    'q.sales_type','c.card_name','c.grp_name','u.code','s.name','q.sales_emp_id', 'q.is_sales_approved', 'q.approval_remarks', 'q.approved_by', 'q.approved_at',
                    DB::raw("(CASE
                        WHEN q.status = '0' THEN CONCAT('<span class=\"text-warning\"><b>', 'Pending', '</b></span>')
                        WHEN q.status = '1' THEN CONCAT('<span class=\"text-success\"><b>', 'Completed', '</b></span>')
                        ELSE CONCAT('<span class=\"text-muted\"><b>', 'Unknown', '</b></span>')
                    END) as status_display"),
                    DB::raw("(CASE
                    WHEN q.status = '0' THEN CONCAT('<span class=\"text-warning\">', c.card_code, '</span>')  -- Pending status
                    WHEN q.status = '1' THEN CONCAT('<span class=\"text-success\">', c.card_code, '</span>')  -- Completed status
                    ELSE CONCAT('<span class=\"text-muted\">', c.card_code, '</span>') -- Unknown status
                END) as customer_code_display"),
                    DB::raw("(SELECT SUM(price_total_af_disc) as total_price FROM `order_items` WHERE `order_id` = q.id) as total_price"),
                    DB::raw("(SELECT SUM(gst_value * quantity) as total_tax FROM `order_items` WHERE `order_id` = q.id) as total_tax"),
                    DB::raw("(SELECT SUM(subtotal) FROM `order_items`  WHERE `order_id` = q.id) as total_with_tax_sum")
                    )
                    ->where('q.ware_house_code', $wareHouse)
                    //->where('user_id', '=', $userId)
                    ->whereNotNull('q.sales_invoice_no')

                    ->orderBy('id', 'DESC');
                  
                    if (!empty($request->start_date) && !empty($request->end_date)) {
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
                    }

            

                if (!empty($data['search_user'])) {
                    $searchTerm = '%' . $data['search_user'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('q.customer_id', 'LIKE', $searchTerm)
                          ->orWhere('c.card_name', 'LIKE', $searchTerm)
                          ->orWhere('q.sales_invoice_no', 'LIKE', $searchTerm);
                    });
                }

                $totalRecords = $query->count();

                if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                    $query->orderBy(
                        $columnArray[$data['order'][0]['column']],
                        $data['order'][0]['dir']
                    );
                }

                $count = $data['branch_count'] ?? 10;
                if ($data['length'] != -1) {
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
                'data' => $order
            ]);
        }
    }

    function OrderGeneratingPage()
    {
        return view('salesorder.new');
    }

    public function searchSalesCustomer(Request $request)
{
    // Retrieve all request data
   // echo "GOPI";
    $data = $request->all();
    $warehouseCode = auth()->user()->ware_house;
    // Build the query to fetch customers
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
            "), // Corrected DB::raw for concatenating styled card_code and card_name
            'customers.product_list'
        )
        ->where('customers.status' , 1);
        // if($warehouseCode == 'AAR-CAB')
        // {
        //     $query->whereIn('customers.grp_code',[108,109]);
        // }

         if($warehouseCode == 'AAR-CAB')
        {
            $query->whereIn('customers.grp_code',[108,109]);
        }else{
            $query->whereNotIn('customers.grp_code',[108,109]);
        }       


    // Fetch the latest sales invoice number
    $latestSalesNo = DB::table('orders')
        ->select('sales_invoice_no')
        ->orderBy('created_at', 'DESC')
        ->limit(1)
        ->pluck('sales_invoice_no')
        ->first();

    // Check if the latest sales number is available
    if ($latestSalesNo) {
        // Use preg_match to separate the prefix and number
        if (preg_match('/^([a-zA-Z]+)([0-9]+)$/', $latestSalesNo, $matches)) {
            $prefix = $matches[1];  // Extract the alphabetical prefix
            $number = $matches[2];  // Extract the numerical part

            // Increment the numeric part and pad it with zeros
            $nextNumber = str_pad((int)$number + 1, strlen($number), '0', STR_PAD_LEFT);

            // Concatenate the prefix and the new incremented number
            $newSalesNo = $prefix . $nextNumber;

        }
    }

    // Apply search filter if 'search' term is present in the request
    if (!empty($data['search'])) {
        $query->where(function ($query) use ($data) {
            $query->where('customers.card_name', 'like', '%' . $data['search'] . '%')
                ->orWhere('customers.card_code', 'like', '%' . $data['search'] . '%');
        });
    }

    // Execute the query and fetch the results
    $res = $query->get();

    // Return the results as JSON
    return response()->json($res);
}


    public function customerQuotations(Request $request){

        $customer_id = $request->customer_id;
        $customerQuotation = DB::table('order_quotations as oq')
        ->select('oq.id', 'oq.customer_code', 'oq.quotation_pdf_no')
       // ->leftJoin('order_quotation_items as oqt', 'oq.id', '=', 'oqt.quotation_id')
        ->where('oq.customer_code', $customer_id)
        ->where('oq.status', 0)
        ->get();

        return response()->json($customerQuotation);
    }


    public function getQuotationData($quotationId) {
        $warehouseCode = auth()->user()->ware_house;
        $quotation = DB::table('order_quotation_items')
                        ->leftJoin('warehouse_codebatch_details as ws', function ($join) use ($warehouseCode) {
                            $join->on('order_quotation_items.item_no', '=', 'ws.item_code')
                                ->where('ws.whs_code', '=', $warehouseCode);
                        })
                        ->where('quotation_id', $quotationId)
                        ->select('order_quotation_items.*',
                         DB::raw(' ROUND (SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty), 0) as ws_qty'))
                        ->orderBy('id', 'DESC')
                        ->groupBy('order_quotation_items.id')
                        ->get();

        $remarks = DB::table('order_quotations as o')
                        ->join('sales_employees as s', 's.id', '=', 'o.sales_emp_id')
                        ->where('o.id', $quotationId)
                        ->select('o.*', 's.name as sales_employee_name')
                        ->orderBy('id', 'DESC')
                        ->first();
                
                       $response = [
                           'sales_employee_name' => $remarks->sales_employee_name,
                           'remarks'=> $remarks->remarks,
                           'sales_type'=> $remarks->sales_type,
                           'quotation_ref_no' => $remarks->quotation_ref_no,
                            'quotation' => $quotation
                       ];

                   return response()->json($response);
    }


    public function getSalesOrderData($orderId) {

        $order = DB::table('orders')
        ->where('id', $orderId)
        ->select('id', 'sales_invoice_no', 'created_at')
        ->first();

        if ($order) {
                $createdDate = Carbon::parse($order->created_at)->toDateString();
                $today = Carbon::today()->toDateString();
                $yesterday = Carbon::yesterday()->toDateString();

                if ($createdDate !== $today && $createdDate !== $yesterday) {
                    return response()->json(['error' => 'This Sales Order Expired'], 404);
                }
           
        } else {
            return response()->json(['error' => 'Order not found'], 500);
        }
        $warehouseCode = auth()->user()->ware_house;
        $order = DB::table('order_items as oi')
            ->leftJoin('warehouse_codebatch_details as ws', function ($join) use ($warehouseCode) {
                $join->on('oi.item_no', '=', 'ws.item_code')
                    ->where('ws.whs_code', '=', $warehouseCode);
            })
            ->where('oi.order_id', $orderId)
            ->where('oi.status', 1)

            //->select('wc.*')
            ->select('oi.id','oi.item_desc','oi.item_id','oi.item_no','oi.order_id','oi.price','oi.price_af_disc','oi.price_total_af_disc','oi.quantity','oi.gst_value','oi.gst_rate','oi.disc_value','oi.disc_percent','ws.whs_code','ws.whs_name',
            //   DB::raw('(SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty) , 0) as ws_qty'))
            DB::raw('ROUND(SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty), 0) as ws_qty'))
            ->orderBy('id','DESC')
            ->groupBy('oi.id')
            ->get();

            
            $remarks = DB::table('orders as o')
                            ->join('sales_employees as s', 's.id', '=', 'o.sales_emp_id')
                            ->where('o.id', $orderId)
                            ->select('o.*', 's.name as sales_employee_name')
                            ->orderBy('id','DESC')
                            ->first();


            $response = [
                'sales_employee_name' => $remarks->sales_employee_name,
                'remarks'=> $remarks->remarks,
                'sales_type' => $remarks->sales_type,
                'sales_ref_no' => $remarks->sales_ref_no,
                'order' => $order
            ];

        return response()->json($response);
    }
    

    public function customerSalesOrder(Request $request){

        $customer_id = $request->customer_id;
        $customerSalesOrder = DB::table('orders')
            ->select('orders.id', 'orders.customer_id', 'orders.sales_invoice_no', 'orders.created_at')
            ->where('orders.customer_id', $customer_id)
            ->where('orders.status', 0)
            ->where('orders.is_sales_approved', 1)
            ->get()
            ->map(function ($order) {
                $createdDate = Carbon::parse($order->created_at)->toDateString();
                $today = Carbon::today()->toDateString();
                $yesterday = Carbon::yesterday()->toDateString();

                if ($createdDate !== $today && $createdDate !== $yesterday) {
                    $order->sales_invoice_no .= ' - expiry';
                }
                return $order;
            });
        return response()->json($customerSalesOrder);
    }

    

    public function getSalesItemSearch($type = null, Request $request)
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
        $query = DB::table('items as i')
            //->join('item_priceses as ip', 'i.item_code', '=', 'ip.item_code')
            ->leftJoin('warehouse_codebatch_details as ws', function ($join) use ($warehouseCode) {
                $join->on('i.item_code', '=', 'ws.item_code')
                    ->where('ws.whs_code', '=', $warehouseCode);
            })
            ->select(
                'i.id',
                DB::raw("
                    CONCAT(
                        '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', i.item_code, '</span>',
                        ' - ',
                        '<span style=\"color:black; font-weight:bold; font-size:11px;\">', i.item_name, '</span>',
                        ' - ',
                        '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', COALESCE(SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty), 0), '</span>',
                        ' - ',
                        '<span style=\"color:black; font-weight:bold; font-size:11px;\">kz ', i.item_price, '</span>'
                    ) AS text
                "),
                'i.item_name',
                'i.item_code',
                'i.item_price as price',
                DB::raw("COALESCE(SUM(ws.quantity) - SUM(ws.invoice_qty) + SUM(ws.credit_qty), 0) AS quantity")
            )
           // ->where('ip.list_name', '=', $customer->product_list)
            ->where(function ($query) use ($data) {
                if (!empty($data['search'])) {
                    $query->where('i.item_name', 'like', '%' . $data['search'] . '%')
                        ->orWhere('i.item_code', 'like', '%' . $data['search'] . '%');
                }
            })
            ->groupBy('i.id', 'i.item_code', 'i.item_name') 
            ->orderBy('i.item_code')
            ->get();
        return response()->json($query);
    }

    function getOrderItem($itemId)
    {
        try {
            $warehouseCode = auth()->user()->ware_house;
            $item = DB::table('item_priceses as ip')
            ->join('items as i', 'ip.item_code', '=', 'i.item_code')
            ->leftJoin('warehouse_stocks as ws', function ($join) use ($warehouseCode) {
                $join->on('i.item_code', '=', 'ws.item_code')
                    ->where('ws.warehouse_code', '=', $warehouseCode);
            })
            ->select(
                'ip.id as id',
                'i.item_code',
                'i.item_name',
                'ip.price',
                DB::raw("ROUND(ws.on_hand, 0) as quantity")
            )
            ->where('ip.id', $itemId)
            ->first();

                //DD($item);
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

    public function storeOrderQuotation(Request $request)
    {
        try {
            DB::beginTransaction();
            $userId = Auth::user()->id;
            $roleId = Auth::user()->role;
            $warehouseCode = Auth::user()->ware_house;
            $custSubGrp = $request->customer_sub_grp ?? 'GENERAL';

            // $request = $request->all();
            // exit;


            $lastCode = DB::table('ware_houses')->select('str_so_hosp_series','str_so_gen_series')
            ->where('whs_code', $warehouseCode)
            ->first();

            if($custSubGrp == 'HOSPITAL')
            {
            $last_series =  $lastCode->str_so_hosp_series;
            }else{
            $last_series =  $lastCode->str_so_gen_series; 
            }


            $numericPart = substr($last_series, strrpos($last_series, '/') + 1);
            $newNumericPart = str_pad((int)$numericPart + 1, 5, '0', STR_PAD_LEFT);
            $stringPart = strtok($last_series, '/');
            $unique_number = $stringPart.'/'.$newNumericPart;
            if($custSubGrp == 'HOSPITAL')
            {
                $update = DB::table('ware_houses')
                                ->where('whs_code',$warehouseCode)
                                ->update(['str_so_hosp_series'=>$unique_number]);          

            }else{
                $update = DB::table('ware_houses')
                                ->where('whs_code',$warehouseCode)
                                ->update(['str_so_gen_series'=>$unique_number]);           
            }

           
            $invoiceNo = $unique_number;


            $salesEmp = DB::table('sales_employees')
                        ->where('name', $request->sales_emp_id)
                        ->orWhere('id', $request->sales_emp_id)
                        ->select('id')
                        ->first();
            $salesEmpId = $salesEmp ? $salesEmp->id : null;
            $isZipCustomer = ($request->cust_default_discount > 0) ? 'Yes' : 'No';


            $systemName = gethostname();
            $systemIP = $this->getLaptopIp();
            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => $request->customer_id,
                'ref_id' => 1,
                'sales_ref_no' => $request->ref_no,
                'user_id' => Auth::user()->id,
                'sales_emp_id'=> $salesEmpId,
                'address' => $request->Cust_address,
                'sales_type' => $request->sales_type,
                'remarks' => $request->remarks,
                'order_date' => date('Y-m-d'),
                'discount_status' => 0,
                'sales_invoice_no' => $invoiceNo,
                'system_name' => $systemName,
                'system_ip' => $systemIP,
                'ware_house_code' =>$warehouseCode,
                'customer_sub_grp' => $custSubGrp,
                'is_zip_customer' => $isZipCustomer, 
            ]);

            $total = 0;
            $hasDiscount = false;
            foreach ($request->row_item_no as $index => $itemId) {
                if (empty($itemId)) {
                    continue;
                }

                $itemInfo = DB::table('items')->select('item_code','item_name')->where('item_code', $itemId)->first();


                if (!$itemInfo) {
                    throw new \Exception('Item not found for ID: ' . $itemId);
                }

                $price = $request->row_item_price[$index];
                $quantity = $request->row_item_qty[$index];
                $quotationId = $request->row_quotation_id[$index] ?? null;
                $disc_percent = $request->row_item_disc_percent[$index];
                $disc_value = $price * $disc_percent / 100;
                $price_af_disc = $price - $disc_value;
                $price_total_af_disc = $quantity * $price_af_disc;
                // GST Calculation
                $gstRate =  $request->row_item_gst_rate[$index];
                $gstValue =  $request->row_item_gst_rate_value[$index];
                $subtotal = $price_total_af_disc + ($quantity * $gstValue);
                $total += $subtotal;

                $baseLine = $index;

                if ($disc_value > 0) {
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
                $gstSummary[$gstRate]['subTotal'] += $price_total_af_disc;
                $gstSummary[$gstRate]['sum'] += ($quantity * $gstValue);
                ksort($gstSummary);


                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'quotation_id' => $quotationId ,
                    'item_id' => $itemId,
                    'item_no' => $itemInfo->item_code,
                    'item_desc' => $itemInfo->item_name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'disc_value' => $disc_value,
                    'disc_percent' => $disc_percent,
                    'price_af_disc' => $price_af_disc,
                    'price_total_af_disc' => $price_total_af_disc,
                    'gst_rate' => $gstRate ?? 0,
                    'gst_value' => $gstValue ?? 0,
                    'subtotal' => $subtotal,
                    'base_line' => $baseLine,
                ]);
            }
            
            $orderUpdate = [
                    'total' => $total,
                    'discount_status' => $hasDiscount ? 1 : 0,
            ];
            if($roleId === 5){
                $orderUpdate = [
                    'is_sales_approved' => 0,
                ];
            }
            DB::table('orders')->where('id', $orderId)->update($orderUpdate);

            if($quotationId){
                DB::table('order_quotations')->where('id', $quotationId)->update(['status' => 1]);

            }

            DB::commit();
            //$invoicePdfUrl = $this->generateSalesInvoicePDF($orderId , $gstSummary);

            $invoicePdfUrl = $this->generateSalesInvoicePDF($orderId);


            $response = [
                'success' => true,
                'message' => ' Sales Order Invoice created successfully.',
                 'sales_order_url' => $invoicePdfUrl,
            ];
            return response()->json($response, 201);

        } catch (\Exception $e) {
            DB::rollback();

            // Log the error for debugging
            Log::error('Error creating Order: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'success' => 0,
                'message' => 'Error creating Order: ' . $e->getMessage(),
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


   

    function generateSalesInvoicePDF($orderId)
    {
        $warehouseCode = Auth::user()->ware_house;

        //$order = DB::table('orders')->where('sales_invoice_no', $invoiceNo)->first();
        $order = DB::table('orders')
                        ->join('customers as c','orders.customer_id', '=', 'c.card_code')
                        ->join('sales_employees as se', 'se.id' , '=' , 'orders.sales_emp_id')
                        ->select('orders.*','c.card_name as customer_name','c.nif_no', 'se.memo')
                        ->where('orders.id', $orderId)->first();
        $items = DB::table('order_items')->where('order_id', $order->id)->get();

        $custAddress = DB::table('customer_addresses')
                            ->where('id', $order->address)
                            ->select('address')
                            ->first();

        $ware_house_data = DB::table('ware_houses')->where('whs_code',$warehouseCode)->first();

        $total = 0;
        $gstSummary = [];

        foreach ($items as $item) {
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

        if (!$order) {
            throw new \Exception("Order with invoice number $invoiceNo not found.");
        }

        $itemsPerPage = 11; // Number of items per page
        $totalPages = ceil(count($items) / $itemsPerPage);
        // Load the invoice view
        $pdf = PDF::loadView('pdf.sales-order-pdf', compact('order', 'items','warehouseCode' , 'gstSummary','totalPages','custAddress','ware_house_data'))
        ->setPaper('a4')
        ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        // Define the directory and file name
        $folderPath = 'pdf/sales';
        $pdfDirectory = public_path($folderPath);

        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        $pdfName = 'sales_copy_' . $order->id . '.pdf';
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

    public function storeOrderPayments()
    {
        try {
            request()->validate([
                'order_id' => 'required',
                'amount' => 'required|numeric|min:1',
                'payment_method' => 'required',
                'payment_type' => 'required',
            ]);

            $orderId = request('order_id');
            $amount = request('amount');
            $paymentMethod = request('payment_method');
            $paymentType = request('payment_type');

            $orderPayments = DB::table('order_payments')->insert([
                'order_id' => $orderId,
                'payment_method' => $paymentMethod,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $currentPaidAmount = DB::table('order_payments')
                ->where('order_id', $orderId)
                ->sum('amount');

            DB::table('orders')->where('id', $orderId)->update([
                'paid_amount' => $currentPaidAmount,
            ]);

            //return response()->json(['success', 'Payment Successful']);
            return response()->json(['message' => 'Order Payment successfully submitted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }

    }

    public function showOrderPayments(Request $request, $id)
    {
        $order = DB::table('orders')
            ->where('id', $id)
            ->first();

        if ($order) {
            $orderItems = DB::table('order_items')
                ->where('order_id', $order->id)
                ->get();

            $customer = DB::table('customers')
                ->where('card_code', $order->customer_id)
                ->first();

            $payments = DB::table('order_payments')
                ->where('order_id', $order->id)
                ->get();

                $total = 0;
                $gstSummary = [];

                foreach ($orderItems as $item) {
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
        return view('salesorder.sales_payments_page', compact('order', 'orderItems', 'customer', 'payments','gstSummary'));
    }


    public function editOrder($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            abort(404, 'Order not found');
        }

        $orderItems = DB::table('order_items')->where('order_id', $id)->get();

        $customer = DB::table('customers')->where('card_code', $order->customer_id)->first();

        return view('salesorder.edit', compact('order', 'orderItems', 'customer'));
    }

    public function updateSalesOrder(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            // dd($request->all());
    
            // Validate that item data exists and is an array
            if (!is_array($request->row_item_no) || count($request->row_item_no) === 0) {
                return response()->json(['success' => false, 'message' => 'No items provided.'], 422);
            }
    
            // Fetch the existing order
            $order = DB::table('orders')->where('id', $id)->first();
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }
    
            $total = 0;
            $hasDiscount = false;
            $gstSummary = [];
    
            // Safely extract all input arrays
            $itemNos           = $request->row_item_no ?? [];
            $itemIds           = $request->row_item_id ?? [];
            $quantities        = $request->row_item_qty ?? [];
            $prices            = $request->row_item_price ?? [];
            $discPercents      = $request->row_item_disc_percent ?? [];
            $gstRates          = $request->row_item_gst_rate ?? [];
            $gstValues         = $request->row_item_gst_rate_value ?? [];
            $quotationIds      = $request->row_quotation_id ?? [];
            // echo "Items::."; print_r($itemNos); exit;

            $removedCodesRaw = $request->input('removed_item_codes');
            if (!empty($removedCodesRaw)) {
                $removedCodes = explode(',', $removedCodesRaw);
                
                DB::table('order_items')
                    ->where('order_id', $id)
                    ->whereIn('item_id', $removedCodes)
                    ->delete();
            }

            foreach ($itemNos as $index => $itemNo) {
                if (empty($itemNo)) {
                    continue;
                }
    
                $itemInfo = DB::table('items')
                    ->select('item_code', 'item_name')
                    ->where('item_code', $itemNo)
                    ->first();
    
                if (!$itemInfo) {
                    throw new \Exception("Item not found for ID: {$itemNo}");
                }
    
                $price        = (float) ($prices[$index] ?? 0);
                $quantity     = (int) ($quantities[$index] ?? 0);
                $discPercent  = (float) ($discPercents[$index] ?? 0);
                $gstRate      = (float) ($gstRates[$index] ?? 0);
                $gstValue     = (float) ($gstValues[$index] ?? 0);
                $quotationId  = $quotationIds[$index] ?? null;
    
                $discValue           = $price * $discPercent / 100;
                $priceAfterDiscount  = $price - $discValue;
                $totalAfterDiscount  = $priceAfterDiscount * $quantity;
                $gstTotal            = $totalAfterDiscount * ($gstRate / 100);
                $subtotal            = $totalAfterDiscount + $gstTotal;
                $total              += $subtotal;
    
                Log::debug("Item #$index", [
                    'price' => $price,
                    'quantity' => $quantity,
                    'discPercent' => $discPercent,
                    'gstRate' => $gstRate,
                    'gstValue' => $gstValue,
                    'discValue' => $discValue,
                    'priceAfterDiscount' => $priceAfterDiscount,
                    'totalAfterDiscount' => $totalAfterDiscount,
                    'subtotal' => $subtotal,
                ]);
                
                if ($discValue > 0) {
                    $hasDiscount = true;
                }
    
                // GST summary
                if (!isset($gstSummary[$gstRate])) {
                    $gstSummary[$gstRate] = [
                        'count' => 0,
                        'sum' => 0,
                        'subTotal' => 0,
                    ];
                }
                $baseLine = $index;
                $gstSummary[$gstRate]['count']     += $quantity;
                $gstSummary[$gstRate]['subTotal'] += $totalAfterDiscount;
                $gstSummary[$gstRate]['sum']      += ($quantity * $gstValue);
    
                // Check if item already exists in order
                $existingOrderItem = DB::table('order_items')
                    ->where('order_id', $id)
                    ->where('item_id', $itemInfo->item_code)
                    ->first();
                $orderItemData = [
                    'price' => $price,
                    'quantity' => $quantity,
                    'gst_rate' => $gstRate,
                    'gst_value' => $gstValue,
                    'disc_percent' => $discPercent,
                    'disc_value' => $discValue,
                    'price_af_disc' => $priceAfterDiscount,
                    'price_total_af_disc' => $totalAfterDiscount,
                    'subtotal' => $subtotal,
                ];
    
                if ($existingOrderItem) {
                    DB::table('order_items')
                        ->where('order_id', $id)
                        ->where('item_id', $itemInfo->item_code)
                        ->update($orderItemData);
                } else {
                    DB::table('order_items')->insert(array_merge($orderItemData, [
                        'order_id' => $id,
                        'quotation_id' => $quotationId,
                        'item_id' => $itemNo,
                        'item_no' => $itemInfo->item_code,
                        'item_desc' => $itemInfo->item_name,
                        'base_line' => $baseLine,
                    ]));
                }
            }
            Log::debug("Item #$total");
            // Final update to order
            DB::table('orders')->where('id', $id)->update([
                'total' => $total,
                'discount_status' => $hasDiscount ? 1 : 0,
                'updated_user_id' => Auth::user()->id,
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Sales Order updated successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating order: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }

}
