<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use GuzzleHttp\Client;


class SalesOrderController extends Controller
{

    function Salesindex()
    {
        return view('salesorder.list');
    }

    //function SalesOrderJson(Request $request)
    //{
    //    $data = $request->all();

    //    if (isset($data['draw'])) {
    //        $columnArray = ['q.id', 'q.order_date', 'q.customer_id', 'q.total', 'q.status', 'q.sales_invoice_no', 'q.ref_id', 'q.user_id', 'q.bill_to', 'q.ship_to','c.card_name'];

    //        try {
    //            $userId = Auth::user()->id;
    //            $query = DB::table('orders as q')
    //            ->join('customers as c', 'q.customer_id', '=', 'c.card_code')
    //                ->select('q.id', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id', 'q.order_date', 'q.status', 'q.total', 'q.sales_invoice_no', 'q.bill_to','c.card_name',
    //                DB::raw("(CASE
    //                    WHEN q.status = '0' THEN CONCAT('<span class=\"text-warning\"><b>', 'Pending', '</b></span>')
    //                    WHEN q.status = '1' THEN CONCAT('<span class=\"text-success\"><b>', 'Completed', '</b></span>')
    //                    ELSE CONCAT('<span class=\"text-muted\"><b>', 'Unknown', '</b></span>')
    //                END) as status_display")
    //                )
    //                //->where('user_id', '=', $userId)
    //                ->whereNotNull('q.sales_invoice_no')
    //                ->orderBy('id', 'DESC');

    //            if (!empty($data['search_user'])) {
    //                $query->where('q.customer_id', 'LIKE', '%' . $data['search_user'] . '%');
    //            }

    //            $totalRecords = $query->count();

    //            if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
    //                $query->orderBy(
    //                    $columnArray[$data['order'][0]['column']],
    //                    $data['order'][0]['dir']
    //                );
    //            }

    //            $count = $data['branch_count'] ?? 10;
    //            if ($data['length'] != -1) {
    //                $query->skip($data['start'])->take($data['length']);
    //            }

    //            $order = $query->get();

    //        } catch (\Exception $e) {
    //            $order = [];
    //            $totalRecords = 0;
    //        }

    //        return response()->json([
    //            'draw' => intval($data['draw']),
    //            'recordsTotal' => $totalRecords,
    //            'recordsFiltered' => $totalRecords,
    //            'data' => $order
    //        ]);
    //    }
    //}
    function SalesOrderJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = ['q.id','q.sales_ref_no', 'q.sales_type','q.sales_invoice_no','q.order_date', 'q.customer_id', 'q.total', 'q.status', 'q.sales_invoice_no', 'q.ref_id', 'q.user_id', 'q.bill_to', 'q.ship_to','c.card_name','c.grp_name','u.code','s.name'];

            try {
                $userId = Auth::user()->id;
                $query = DB::table('orders as q')
                ->leftjoin('customers as c', 'q.customer_id', '=', 'c.card_code')
                ->leftjoin('users as u', 'q.user_id', '=', 'u.id')
                ->leftjoin('sales_employees as s', 'q.sales_emp_id', '=', 's.code')
                    ->select('q.id','q.sales_ref_no', 'q.sales_invoice_no', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id', 'q.order_date', 'q.status', 'q.total', 'q.sales_invoice_no', 'q.bill_to','q.sales_type','c.card_name','c.grp_name','u.code','s.name',
                    DB::raw("(CASE
                        WHEN q.status = '0' THEN CONCAT('<span class=\"text-warning\"><b>', 'Pending', '</b></span>')
                        WHEN q.status = '1' THEN CONCAT('<span class=\"text-success\"><b>', 'Completed', '</b></span>')
                        ELSE CONCAT('<span class=\"text-muted\"><b>', 'Unknown', '</b></span>')
                    END) as status_display"),
                    DB::raw("(SELECT ROUND(SUM(price_total_af_disc), 2) as total_price FROM `order_items` WHERE `order_id` = q.id) as total_price"),
                    DB::raw("(SELECT ROUND(SUM(gst_value * quantity), 2) as total_tax FROM `order_items` WHERE `order_id` = q.id) as total_tax"),
                    DB::raw("(SELECT ROUND(SUM(subtotal), 2) FROM `order_items`  WHERE `order_id` = q.id) as total_with_tax_sum")
                    )
                    //->where('user_id', '=', $userId)
                    ->whereNotNull('q.sales_invoice_no')

->orderBy('id', 'DESC');
                    if (!empty($request->start_date) && !empty($request->end_date)) {
                        try {
                            $query->whereBetween('q.order_date', [$request->start_date, $request->end_date]);
                        } catch (\Exception $e) {
                            return response()->json(['error' => 'Invalid date format'], 400);
                        }
                    }

                //if (!empty($data['search_user'])) {
                //    $query->where('q.customer_id', 'LIKE', '%' . $data['search_user'] . '%');
                //}

                if (!empty($data['search_user'])) {
                    $searchTerm = '%' . $data['search_user'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('q.customer_id', 'LIKE', $searchTerm)
                          ->orWhere('c.card_name', 'LIKE', $searchTerm);
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
    $data = $request->all();

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
        );

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


    //public function getQuotationData($quotationId) {
    //    $quotation = DB::table('order_quotation_items')
    //                   ->where('quotation_id', $quotationId)
    //                   ->orderBy('id', 'DESC')
    //                   ->get();

    //    $remarks = DB::table('order_quotations as o')
    //                   ->join('sales_employees as s', 's.id', '=', 'o.sales_emp_id')
    //                   ->where('o.id', $quotationId)
    //                   ->select('o.*', 's.name as sales_employee_name')
    //                   ->first();
    //            //        echo "Test HOO".$quotationId;
    //            // echo "<pre>"; print_r($remarks); exit;
    //                   $response = [
    //                       'sales_employee_name' => $remarks->sales_employee_name,
    //                       'remarks'=> $remarks->remarks,
    //                       'quotation_ref_no' => $remarks->quotation_ref_no,
    //                        'quotation' => $quotation
    //                   ];

    //               return response()->json($response);
    //}

    public function getQuotationData($quotationId) {
        $quotation = DB::table('order_quotation_items')
                        ->where('quotation_id', $quotationId)
                        ->orderBy('id', 'DESC')
                        ->get();

        $remarks = DB::table('order_quotations as o')
                        ->join('sales_employees as s', 's.id', '=', 'o.sales_emp_id')
                        ->where('o.id', $quotationId)
                        ->select('o.*', 's.name as sales_employee_name')
                        ->orderBy('id', 'DESC')
                        ->first();
                //        echo "Test HOO".$quotationId;
                // echo "<pre>"; print_r($remarks); exit;
                       $response = [
                           'sales_employee_name' => $remarks->sales_employee_name,
                           'remarks'=> $remarks->remarks,
                           'sales_type'=> $remarks->sales_type,
                           'quotation_ref_no' => $remarks->quotation_ref_no,
                            'quotation' => $quotation
                       ];

                   return response()->json($response);
    }

    //public function getSalesOrderData($orderId) {
    //    $warehouseCode = auth()->user()->ware_house;
    //    $order = DB::table('order_items as oi')
    //        ->leftJoin('warehouse_stocks as ws', function ($join) use ($warehouseCode) {
    //            $join->on('oi.item_no', '=', 'ws.item_code')
    //                ->where('ws.warehouse_code', '=', $warehouseCode);
    //        })
    //        ->where('oi.order_id', $orderId)
    //        ->where('oi.status', 1)

    //        //->select('wc.*')
    //        ->select('oi.id','oi.item_desc','oi.item_id','oi.item_no','oi.order_id','oi.price','oi.price_af_disc','oi.price_total_af_disc','oi.quantity','oi.gst_value','oi.gst_rate','oi.disc_value','oi.disc_percent','ws.warehouse_code','ws.warehouse_name',   DB::raw('ROUND(IFNULL(ws.on_hand, 0), 0) AS ws_qty'),)
    //        ->get();

    //        // $remarks = DB::table('orders as o')->where('id', $orderId)
    //        //                 ->join('sales_employees as s', 's.id', '=','o.sales_emp_id')
    //        //                 ->select('*')
    //        //                 ->get();

    //        $remarks = DB::table('orders as o')
    //                        ->join('sales_employees as s', 's.id', '=', 'o.sales_emp_id')
    //                        ->where('o.id', $orderId)
    //                        ->select('o.*', 's.name as sales_employee_name')
    //                        ->first();


    //        $response = [
    //            'sales_employee_name' => $remarks->sales_employee_name,
    //            'remarks'=> $remarks->remarks,
    //            'sales_ref_no' => $remarks->sales_ref_no,
    //            'order' => $order
    //        ];

    //    return response()->json($response);
    //}

    public function getSalesOrderData($orderId) {
        $warehouseCode = auth()->user()->ware_house;
        $order = DB::table('order_items as oi')
            ->leftJoin('warehouse_stocks as ws', function ($join) use ($warehouseCode) {
                $join->on('oi.item_no', '=', 'ws.item_code')
                    ->where('ws.warehouse_code', '=', $warehouseCode);
            })
            ->where('oi.order_id', $orderId)
            ->where('oi.status', 1)

            //->select('wc.*')
            ->select('oi.id','oi.item_desc','oi.item_id','oi.item_no','oi.order_id','oi.price','oi.price_af_disc','oi.price_total_af_disc','oi.quantity','oi.gst_value','oi.gst_rate','oi.disc_value','oi.disc_percent','ws.warehouse_code','ws.warehouse_name',   DB::raw('ROUND(IFNULL(ws.on_hand, 0), 0) AS ws_qty'),)
            ->orderBy('id','DESC')
            ->get();

            // $remarks = DB::table('orders as o')->where('id', $orderId)
            //                 ->join('sales_employees as s', 's.id', '=','o.sales_emp_id')
            //                 ->select('*')
            //                 ->get();

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
        ->select('orders.id', 'orders.customer_id', 'orders.sales_invoice_no')
        ->where('orders.customer_id', $customer_id)
        ->where('orders.status', 0)
        ->get();
        return response()->json($customerSalesOrder);
    }

    //public function getSalesItemSearch($type = null, Request $request)
    //{
    //    $data = $request->all();

    //    if (!isset($data['card_code']) || empty($data['card_code'])) {
    //        return response()->json(['error' => 'Customer card_code is required'], 400);
    //    }

    //    $customer = DB::table('customers')
    //        ->select('product_list')
    //        ->where('card_code', $data['card_code'])
    //        ->first();

    //    if (!$customer || empty($customer->product_list)) {
    //        return response()->json(['error' => 'No product list found for this customer'], 404);
    //    }

    //    $warehouseCode = Auth::user()->ware_house;

    //    $query = DB::table('items as i')
    //        ->join('item_priceses as ip', 'i.item_code', '=', 'ip.item_code')
    //        ->join('warehouse_stocks as ws', 'i.item_code', '=', 'ws.item_code')
    //        ->select(
    //            'i.id',
    //            DB::raw("
    //                    CONCAT(
    //                        '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', i.item_code, '</span>',
    //                        ' - ',
    //                        '<span style=\"color:black; font-weight:bold; font-size:11px;\">', i.item_name, '</span>',
    //                        ' - ',
    //                        '<span style=\"color:orange; font-weight:bold; font-size:11px;\">', ROUND((ws.on_hand),0), '</span>'
    //                    ) as text
    //                "),
    //            'i.item_name',
    //            'ip.price',
    //            'ip.id'
    //        )
    //        ->where('ws.warehouse_code',$warehouseCode)
    //        ->where('ip.list_name', '=', $customer->product_list)
    //        ->where(function ($query) use ($data) {
    //            if (!empty($data['search'])) {
    //                $query->where('i.item_name', 'like', '%' . $data['search'] . '%')
    //                    ->orWhere('i.item_code', 'like', '%' . $data['search'] . '%');
    //            }
    //        })
    //        ->orderBy('i.item_code')
    //        ->get();

    //    return response()->json($query);
    //}

    public function getSalesItemSearch($type = null, Request $request)
    {
        $data = $request->all();
        if (!isset($data['card_code']) || empty($data['card_code'])) {
            return response()->json(['error' => 'Customer card_code is required'], 400);
        }
        $warehouseCode = auth()->user()->ware_house;
        $customer = DB::table('customers')
            ->select('product_list')
            ->where('card_code', $data['card_code'])
            ->first();

        if (!$customer || empty($customer->product_list)) {
            return response()->json(['error' => 'No product list found for this customer'], 404);
        }
        $query = DB::table('items as i')
            ->join('item_priceses as ip', 'i.item_code', '=', 'ip.item_code')
            //->join('warehouse_stocks as ws', 'i.item_code', '=', 'ws.item_code')
            ->leftJoin('warehouse_stocks as ws', function ($join) use ($warehouseCode) {
                $join->on('i.item_code', '=', 'ws.item_code')
                     ->where('ws.warehouse_code', '=', $warehouseCode);
                    })
            ->select(
                'i.id',
                DB::raw("
                        CONCAT(
                            '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', i.item_code, '</span>',
                            ' - ',
                            '<span style=\"color:black; font-weight:bold; font-size:11px;\">', i.item_name, '</span>',
                            ' - ',
                            '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', ROUND(IFNULL(ws.on_hand, 0), 0), '</span>'
                             ' - ',
                            '<span style=\"color:black; font-weight:bold; font-size:11px;\">kz ', ip.price, '</span>'

                        ) as text
                    "),
                'i.item_name',
                'i.item_code',
                'ip.price',
                'ip.id',
                //'ws.on_hand'
                DB::raw("ROUND(ws.on_hand, 0) AS quantity")

            )

            ->where('ip.list_name', '=', $customer->product_list)
            //->where('ws.warehouse_code', '=', $warehouseCode)
            ->where(function ($query) use ($data) {
                if (!empty($data['search'])) {
                    $query->where('i.item_name', 'like', '%' . $data['search'] . '%')
                        ->orWhere('i.item_code', 'like', '%' . $data['search'] . '%');
                }
            })
            ->distinct()
            ->orderBy('i.item_code')
            ->get();
        return response()->json($query);
    }

    //function getOrderItem($itemId)
    //{
    //    try {

    //        $item = DB::table('item_priceses')
    //                    ->where('id', $itemId)
    //                    ->first();
    //        if ($item) {
    //            return $item;
    //        } else {
    //            return response()->json([
    //                'success' => false,
    //                'message' => 'Item not found'
    //            ], 404);
    //        }

    //    } catch (\Exception $e) {
    //        return response()->json([
    //            'success' => false,
    //            'message' => 'An error occurred while retrieving the item.',
    //            'error' => $e->getMessage()
    //        ], 500);
    //    }
    //}
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
            $invoiceStrCode = Auth::user()->ware_house;
            // $latestInvoice = DB::table('orders')->where('user_id', $userId, 'desc')->first();
            $latestInvoice = DB::table('orders')
                                ->where('user_id', $userId)
                                ->orderBy('sales_invoice_no', 'desc')
                                ->first();
            // $nextInvoiceNo = $latestInvoice ? sprintf('%04d', intval($latestInvoice->invoice_no) + 1) : '1000';
            if ($latestInvoice && preg_match('/\d+$/', $latestInvoice->sales_invoice_no, $matches)) {
                $nextInvoiceNo = sprintf('%04d', intval($matches[0]) + 1);
            } else {
                $nextInvoiceNo = '1000';
            }

            $invoiceNo = $invoiceStrCode. '-'. $nextInvoiceNo;

            $salesEmp = DB::table('sales_employees')
                        ->where('name', $request->sales_emp_id)
                        ->orWhere('id', $request->sales_emp_id)
                        ->select('id')
                        ->first();

                $salesEmpId = $salesEmp ? $salesEmp->id : null;


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
            ]);

            $total = 0;
            $hasDiscount = false;
            foreach ($request->row_item_id as $index => $itemId) {
                if (empty($itemId)) {
                    continue;
                }

                $itemInfo = DB::table('item_priceses')->where('id', $itemId)->first();

                if (!$itemInfo) {
                    throw new \Exception('Item not found for ID: ' . $itemId);
                }

                $price = $itemInfo->price;
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
            DB::table('orders')->where('id', $orderId)->update(['total' => $total, 'discount_status' => $hasDiscount ? 1 : 0,]);

            if($quotationId){
                DB::table('order_quotations')->where('id', $quotationId)->update(['status' => 1]);

            }

            DB::commit();
            $invoicePdfUrl = $this->generateSalesInvoicePDF($orderId , $gstSummary);

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

    function generateSalesInvoicePDF($orderId ,$gstSummary)
    {
        $warehouseCode = Auth::user()->ware_house;

        //$order = DB::table('orders')->where('sales_invoice_no', $invoiceNo)->first();
        $order = DB::table('orders')
                        ->join('customers as c','orders.customer_id', '=', 'c.card_code')
                        ->select('orders.*','c.card_name as customer_name','c.nif_no')
                        ->where('orders.id', $orderId)->first();
        $items = DB::table('order_items')->where('order_id', $order->id)->get();

        if (!$order) {
            throw new \Exception("Order with invoice number $invoiceNo not found.");
        }

        // Load the invoice view
        $pdf = PDF::loadView('pdf.sales-order-pdf', compact('order', 'items','warehouseCode' , 'gstSummary'));

        // Define the directory and file name
        $folderPath = 'pdf/sales';
        $pdfDirectory = public_path($folderPath);

        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        $pdfName = 'sales_copy_' . $order->id . '.pdf';
        $pdfFilePath = $pdfDirectory . '/' . $pdfName;

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

        } else {
            $orderItems = $customer = $payments = null;
        }

        return view('salesorder.sales_payments_page', compact('order', 'orderItems', 'customer', 'payments'));
    }



}
