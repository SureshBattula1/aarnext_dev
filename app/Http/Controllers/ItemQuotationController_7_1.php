<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;


class ItemQuotationController extends Controller
{
    public function index()
    {
        //return view('pdf.items-quotation-pdf');
        return view('quotation.items-quotation');
    }

    function newQuotation()
    {
        return view('quotation.items-quotation.new');
    }

    public function quotationlist()
    {
        return view('quotation.quotation_list');
    }

    function getItemQuotationsJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = ['q.id', 'q.date', 'q.customer_code', 'q.total', 'q.quotation_pdf_no','q.user_id','q.quotation_ref_no','c.card_name','q.sales_type','q.total','q.status' ,'c.grp_name','u.code','q.sales_emp_id','s.name'];

            try {
                $userId = Auth::user()->id;
                $query = DB::table('order_quotations as q')
                ->leftjoin('customers as c', 'q.customer_code', '=', 'c.card_code')
                ->leftjoin('users as u', 'q.user_id', '=', 'u.id')
                 ->leftjoin('sales_employees as s', 'q.sales_emp_id', '=', 's.id')
                ->select('q.id', 'q.date', 'q.customer_code', 'q.total', 'q.quotation_pdf_no','q.user_id','q.quotation_ref_no','q.sales_type','c.card_name','q.total','q.status' , 'c.grp_name','u.code','s.name',
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
                    DB::raw("(SELECT ROUND( SUM(price_total_af_disc), 2 ) as total_price FROM `order_quotation_items` WHERE `quotation_id` = q.id) as total_price"),
                    DB::raw("(SELECT ROUND(SUM(gst_value * quantity), 2) as total_tax FROM `order_quotation_items` WHERE `quotation_id` = q.id) as total_tax"),
                    DB::raw("(SELECT ROUND(SUM(subtotal), 2) FROM `order_quotation_items`  WHERE `quotation_id` = q.id) as total_with_tax_sum")
                    )

                    ->orderBy('id', 'DESC');

                // Filter by date range
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    try {
                        $query->whereBetween('q.date', [$request->start_date, $request->end_date]);
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Invalid date format'], 400);
                    }
                }

                if (!empty($data['search_user'])) {
                    $searchTerm = '%' . $data['search_user'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('q.customer_code', 'LIKE', $searchTerm)
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
    public function CustomerSearch(Request $request)
    {
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
            "),
                'customers.product_list'
            )
            ->selectRaw("(SELECT id FROM order_quotations ORDER BY created_at DESC LIMIT 1) as latest_quotation_no");
        // Add search filter
        if (!empty($data['search'])) {
            $query->where(function ($query) use ($data) {
                $query->where('customers.card_name', 'like', '%' . $data['search'] . '%')
                    ->orWhere('customers.card_code', 'like', '%' . $data['search'] . '%');
            });
        }

        $res = $query->get();

        return response()->json($res);
    }

    //public function getItemSearch($type = null, Request $request)
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


    //    $query = DB::table('items as i')
    //    ->join('item_priceses as ip', 'i.item_code', '=', 'ip.item_code')
    //    ->join('warehouse_stocks as ws', 'i.item_code', '=', 'ws.item_code')
    //    ->join('users as u', 'ws.warehouse_code', '=', 'u.ware_house')
    //    ->select(
    //        'i.id',
    //        DB::raw("
    //                CONCAT(
    //                    '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', i.item_code, '</span>',
    //                    ' - ',
    //                    '<span style=\"color:black; font-weight:bold; font-size:11px;\">', i.item_name, '</span>',
    //                    ' - ',
    //                    '<span style=\"color:orange; font-weight:bold; font-size:11px;\">', ROUND((ws.on_hand),0), '</span>'
    //                ) as text
    //            "),
    //        'i.item_name',
    //        'ip.price',
    //        'ip.id',
    //        'ws.on_hand'
    //    )
    //    ->where('ip.list_name', '=', $customer->product_list)
    //    ->where(function ($query) use ($data) {
    //        if (!empty($data['search'])) {
    //            $query->where('i.item_name', 'like', '%' . $data['search'] . '%')
    //                ->orWhere('i.item_code', 'like', '%' . $data['search'] . '%');
    //        }
    //    })
    //    ->distinct()
    //    ->orderBy('i.item_code')
    //    ->get();

    //    return response()->json($query);
    //}
    public function getItemSearch($type = null, Request $request)
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
            ->select(
                'i.id',
                DB::raw("
                        CONCAT(
                            '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', i.item_code, '</span>',
                            ' - ',
                            '<span style=\"color:black; font-weight:bold; font-size:11px;\">', i.item_name, '</span>',
                            ' - ',
                            '<span style=\"color:gray; font-weight:bold; font-size:11px;\">', ip.price, '</span>'

                        ) as text
                    "),
                'i.item_name',
                'ip.price',
                'ip.id',
                //'ws.on_hand'
            )
            // ->addSelect(DB::raw('(SELECT * FROM `warehouse_stocks` WHERE `warehouse_code`="'.$warehouseCode.'" and `item_code`=i.item_code) as lastPost'));

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

    //function getItem($itemId)
    //{
    //    try {
    //        $item = DB::table('item_priceses')
    //            ->where('id', $itemId)
    //            ->first();

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

    function getItem($itemId)
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
    function getCustomer($customerId){

    }
    public function storeQuotation(Request $request)
    {
        try {
            if (!Auth::check()) {
                Log::error('User not authenticated.');
                return response()->json([
                    'success' => 0,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $userId = Auth::user()->id;
            $quotationStrCode = Auth::user()->ware_house;

            DB::beginTransaction();

            $quotationNumber = DB::table('order_quotations')
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->first();

            if ($quotationNumber) {
                $quotationParts = explode('-', $quotationNumber->quotation_pdf_no);
                $quotationNumericPart = isset($quotationParts[2]) ? intval($quotationParts[2]) : 0;
                $nextQuotationNo = sprintf('%04d', $quotationNumericPart + 1);
            } else {
                $nextQuotationNo = '0001';
            }

            $QuotationNumber = $quotationStrCode . '-' . $nextQuotationNo;

            $orderId = DB::table('order_quotations')->insertGetId([
                'customer_code' => $request->customer_id,
                'user_id' => $userId,
                'status' => 0,
                'date' => date('Y-m-d'),
                'sales_emp_id'=> $request->sales_emp_id,
                'address_id' => $request->cust_address_id,
                'remarks' => $request->remarks,
                'sales_type' => $request->sales_type,
                'quotation_ref_no' => $request->ref_no,
                'quotation_pdf_no' => $QuotationNumber,
                'store_code' =>  $quotationStrCode,
            ]);
                Log::info('orderId: ' .$request->customer_id );

            $total = 0;
            $gstSummary = [];
            //$hasDiscount = false;
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
                $disc_percent = $request->row_item_disc_percent[$index];
                $disc_value = $price * $disc_percent / 100;
                $price_af_disc = $price - $disc_value;
                $price_total_af_disc = $quantity * $price_af_disc;

                // GST Calculation

                $gstRate =  $request->row_item_gst_rate[$index];
                $gstValue =  $request->row_item_gst_rate_value[$index];

                $subtotal = $price_total_af_disc + ($quantity * $gstValue);
                $total += $subtotal;

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

               $orderItems = DB::table('order_quotation_items')->insert([
                    'quotation_id' => $orderId,
                    'item_id' => $itemId,
                    'item_no' => $itemInfo->item_code,
                    'item_desc' => $itemInfo->item_name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'disc_value' => $disc_value,
                    'disc_percent' => $disc_percent,
                    'price_af_disc' => $price_af_disc,
                    'price_total_af_disc' => $price_total_af_disc,
                    'gst_rate' => $gstRate,
                    'gst_value' => $gstValue,
                    'subtotal' => $subtotal,
                ]);
            }

            // Update the order_quotations table
            DB::table('order_quotations')->where('id', $orderId)->update([
                'total' => $total,
                //'discount_status' => $hasDiscount ? 1 : 0,
            ]);

            DB::commit();
            $QuotationpdfUrl = $this->generateQuotationPDF($orderId , $gstSummary);
            $response = [
                'success' => true,
                'message' => 'Quotation created successfully.',
                'QuotationpdfUrl' => $QuotationpdfUrl,
            ];
            return response()->json($response, 201);

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error creating Quotation: ' . $e->getMessage());

            return response()->json([
                'success' => 0,
                'message' => 'Error creating Quotation: ' . $e->getMessage(),
            ], 500);
        }
    }


    function generateQuotationPDF($QuotationNumber , $gstSummary)
    {
        //echo"QuotationNumber"; print_r(); exit;
        $warehouseCode = Auth::user()->ware_house;
        $quotation = DB::table('order_quotations')
                        ->join('customers as c','order_quotations.customer_code', '=', 'c.card_code')
                        ->select('order_quotations.*','c.card_name as customer_name','c.nif_no')
                        ->where('order_quotations.id', $QuotationNumber)->first();
        //$quotation = DB::table('order_quotations')->where('quotation_pdf_no', $QuotationNumber)->first();
        $QuotationNumber_pdf = $quotation->id;
        if (!$quotation) {
            throw new \Exception("Quotation with number" .$QuotationNumber_pdf."not found.");
        }

        $quotationItems = DB::table('order_quotation_items')->where('quotation_id', $quotation->id)->get();

        $pdf = PDF::loadView('pdf.items-quotation-pdf', compact('quotation', 'quotationItems','warehouseCode' , 'gstSummary'));

        $folderPath = 'pdf/quotations';
        $pdfDirectory = public_path($folderPath);

        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        $pdfName = 'quotation_' . $QuotationNumber_pdf . '.pdf';
        $pdfFilePath = $pdfDirectory . '/' . $pdfName;

        $pdf->save($pdfFilePath);

        if (!file_exists($pdfFilePath)) {
            throw new \Exception("PDF file was not created successfully at $pdfFilePath.");
        }

        return url($folderPath . "/" . $pdfName);
    }

    //public function quotationPdf($quotationId){
    //    $pdfId = DB::table('order_quotations')
    //    ->select('quotation_pdf_no')
    //    ->where('id', $quotationId)
    //    ->first();
    //    return $this->generateQuotationPDF($pdfId->QuotationNumber);
    //}

    function batchQuantity(Request $request, $itemId)
    {
        $batches = DB::table('warehouse_codebatch_details as ws')
            ->leftJoin('item_priceses as ip', 'ws.item_code', '=', 'ip.item_code')
            ->where('ip.id', '=', $itemId)
            ->select('ws.*')
            ->get();
        return $batches;
    }

    public function getCustomerAddresses($cardCode, Request $request)
    {
        $search = $request->input('search', '');

        $addresses = DB::table('customer_addresses')

            ->where('customer_addresses.card_code', $cardCode)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('customer_addresses.street', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.city', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.state', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.country', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.zip_code', 'LIKE', "%$search%");
                });
            })
            ->groupBy('customer_addresses.card_code')
            ->get();

        return response()->json([
            'addresses' => $addresses
        ]);
    }

    public function getCustomerAddressesList(Request $request, $customerID)
        {
            $cardCode = $customerID; // Customer ID from route parameter
            $search = $request->input('search', ''); // Search term (optional)

            // Fetch the customer details
            $customer = DB::table('customers')
                ->where('card_code', $cardCode)
                ->select('card_code', 'sal_emp_name', 'store_location', 'pymnt_grp')
                ->first();

            // Fetch the customer addresses, applying the search filter if needed
            $addresses = DB::table('customer_addresses')
                ->where('card_code', $cardCode)
                ->when($search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('street', 'LIKE', "%$search%")
                        ->orWhere('city', 'LIKE', "%$search%")
                        ->orWhere('state', 'LIKE', "%$search%")
                        ->orWhere('country', 'LIKE', "%$search%")
                        ->orWhere('zip_code', 'LIKE', "%$search%");
                    });
                })
                ->get();

            // Return the response with addresses and customer details
            return response()->json([
                'addresses' => $addresses,
                'customer' => $customer,
            ]);
        }

        public function calculateDueDate(Request $request)
        {
            $pymnt_grp = $request->input('pymnt_grp');
            $str = preg_replace("/[^0-9]/","",$pymnt_grp);

            if($str) {
                $Date = date('Y-m-d');
                $due_date = date('Y-m-d', strtotime($Date. ' + '.$str.'days'));
                return response()->json(['due_date' => $due_date]);
            } else {
                return response()->json(['due_date' => date('Y-m-d')]);
            }

    }

    //public function showQuotationPayments(Request $request, $id)
    //{
    //    $order = DB::table('order_quotations')
    //                    ->where('id', $id)
    //                    ->first();

    //    if ($order) {
    //        $orderItems = DB::table('order_quotation_items')
    //            ->where('quotation_id', $order->id)
    //            ->get();

    //        $customer = DB::table('customers')
    //            ->where('card_code', $order->customer_code)
    //            ->first();

    //        $payments = DB::table('order_payments')
    //            ->where('order_id', $order->id)
    //            ->get();

    //    } else {
    //        $orderItems = $customer = $payments = null;
    //    }

    //    return view('quotation.quotation_items_payments', compact('order', 'orderItems', 'customer', 'payments'));
    //}
    public function showQuotationPayments(Request $request, $id)
    {
        $order = DB::table('order_quotations')
                    ->where('id', $id)
                    ->first();

        if ($order) {
            $orderItems = DB::table('order_quotation_items')
                ->where('quotation_id', $order->id)
                ->get();

            $customer = DB::table('customers')
                ->where('card_code', $order->customer_code)
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

        return view('quotation.quotation_items_payments', compact('order', 'orderItems', 'customer', 'payments', 'total', 'gstSummary'));
    }
}
