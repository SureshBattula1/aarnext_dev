<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\SalesReportExport;
use App\Exports\SalesItemsReportExport;
use App\Exports\QuotationsReportExport;
use App\Exports\PaymentsReportExport;

use PDF;
use Carbon\Carbon;
use Excel;

class ReportsController extends Controller
{
    public function indexInvoice(){
        return view('reports.invoice-reports');
    }
     public function indexSalesOrder(){
        return view('reports.sales-order-reports');
    }
     public function indexQuotation(){
        return view('reports.quotation-reports');
    }

    public function indexRftReport(){
        return view('reports.rft-reports');
    }

    public function creditNote()
    {
        return view('reports.credit-note-reports');
    }

    public function indexsalesItems()
    {
        return view('reports.sales-item-reports');
    }

    public function indexOrderItems(){
        return view('reports.sales-order-items-report');
    }

    public function getPaymentsIndex(){
        return view('reports.payments-reports');
    }

    public function indexRftWithBatchesReport(){
        return view('reports.rft-with-batches-reports');
    }

    public function invoiceBatchesIndex(){
        return view('reports.invoice-batches-reports');
    }
    public function getPaidInvoiceJson(Request $request)
    {
        $data = $request->all();
     
        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    
        $columnArray = ['q.id', 'q.order_date', 'q.customer_id', 'q.sales_type', 'q.total', 'q.status'];
        try {
            $userId = Auth::user()->id;
            $wareHouse = Auth()->user()->ware_house;

            if ($request->sales_report === "invoices") {
                //echo "invoices";
                $query = DB::table('invoices as q')
                    ->join('users as u', 'u.id', '=', 'q.user_id')
                    ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                    ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
                    ->where('q.store_code' , $wareHouse)
                    ->select([
                        'q.id', 'q.sales_type', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id', 'q.day_series',
                        'q.order_date', 'q.status', 'q.total', 'q.invoice_no as doc_num', 'q.bill_to', 'c.card_name', 'q.store_code',
                        'q.due_date', 'q.pymnt_grp', 'q.paid_amount', 'q.sap_created_id', 'q.sap_error_msg',
                        'q.sap_updated_at', 'q.sap_updated', 'c.grp_name', 'u.code','u.name as user_name', 's.name', 'c.sub_grp', 's.memo'
                    ])
                    ->orderBy('q.id', 'DESC');
            } elseif ($request->sales_report === "credits") {
                
                $query = DB::table('credit_memos as cm')
                ->join('users as u', 'u.id', '=', 'cm.user_id')
                ->join('invoices as i', 'i.id', '=', 'cm.invoice_id') 
                ->join('customers as c', 'c.card_code', '=', 'cm.customer_id')
                ->join('sales_employees as s', 's.id', '=', 'cm.sales_emp_id')
                ->where('cm.store_code', '=', $wareHouse) 
                ->select([
                    'cm.id',  
                    'cm.customer_id', 
                    'cm.ref_id', 
                    'cm.ship_to', 
                    'cm.user_id',
                    'cm.order_date', 
                    'cm.status', 
                    'cm.total', 
                    'cm.credit_no', 
                    'c.card_name',
                    'cm.store_code', 
                    'cm.paid_amount', 
                    'cm.remarks', 
                    'cm.discount_status', 
                    'i.sales_type',
                    'cm.credit_no as doc_num', 
                    'c.grp_name', 
                    'u.code', 
                    's.name as sales_employee_name', 
                    'c.sub_grp', 
                    'u.name as user_name', 
                    's.memo'
                ])
                ->orderBy('cm.id', 'DESC');

                    //->get();
                    //echo"suresh"; print_r($query); exit;
            } else {
                return response()->json(['error' => 'Invalid sales report type'], 400);
            }
    
            if (!empty($request->sales_type)) {
                $query->where($request->sales_report === "invoices" ? 'q.sales_type' : '');
            }
    
            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }
    
            if ($request->status_search !== 'all') {

                $query->where($request->sales_report === "invoices" ? 'q.status' : 'cm.status', '=', $request->status_search);
            }
    
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $query->whereBetween(
                    $request->sales_report === "invoices" ? 'q.order_date' : 'cm.order_date',
                    [$request->start_date, $request->end_date]
                );
            }
    
            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where(function ($q) use ($searchTerm, $request) {
                    $tableAlias = $request->sales_report === "invoices" ? 'q' : 'cm';
                    $q->where("$tableAlias.customer_id", 'LIKE', $searchTerm)
                        //->orWhere("$tableAlias.day_series", 'LIKE', $searchTerm)
                        ->orWhere('c.card_name', 'LIKE', $searchTerm);
                });
            }
    
            $totalRecords = $query->count();
            $totalSum = $query->sum($request->sales_report === "invoices" ? 'q.total' : 'cm.total');
            $totalPaid = round($query->sum($request->sales_report === "invoices" ? 'q.paid_amount' : 'cm.paid_amount'), 2);
            if($request->sales_report !== "credits"){
                $totalBalance = $totalSum - $totalPaid;
            }


    
            
    
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }
    
            //$order = $query->tosql();
            //echo"suresh" ; print_r($order) ; exit;

            $order = $query->get();

        } catch (\Exception $e) {
            $order = [];
            $totalRecords = 0;
            $totalSum = 0;
            $totalPaid = 0;
            $totalBalance = 0;
        }
    
        return response()->json([
            'draw' => intval($data['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $order,
            'total_amount' => $totalSum,
            'total_paid_amount' => $totalPaid,
            'total_balance_amount' => $totalBalance ?? 0,
        ]);
    }
    
    
    public function invoiceItemsWithBatches(Request $request)
    {
        $draw = intval($request->input('draw'));
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));
        $searchValue = $request->input('search.value');
    
        $docNumSearch = $request->input('doc_num_search');
        $sapEntrySearch = $request->input('sap_entry_search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        $wareHouse = auth()->user()->ware_house;
    
        try {
            $baseQuery = DB::table('invoices as q')
                ->leftJoin('invoice_items as ii', 'ii.invoice_id', '=', 'q.id')
                ->where('q.store_code', $wareHouse)
                ->select([
                    'q.id', 'q.customer_id', 'q.user_id',
                    'q.created_at', 'q.invoice_no as doc_num',
                    'q.store_code', 'q.sap_created_id',
                    'q.hash_key', 'ii.item_no'
                ])
                ->limit($length);
    
            if ($startDate && $endDate) {
                    $startDate = $startDate . ' 00:00:00';
                    $endDate = $endDate . ' 23:59:59';
                    $baseQuery->whereBetween('q.created_at', [$startDate, $endDate]);
                }
    

            if ($searchValue) {
                $baseQuery->where(function ($q) use ($searchValue) {
                    $q->where('q.customer_id', 'like', "%$searchValue%")
                        ->orWhere('ii.item_no', 'like', "%$searchValue%");
                });
            }
    
            if ($docNumSearch) {
                $baseQuery->where('q.invoice_no', 'LIKE', "%$docNumSearch%");
            }
    
            if ($sapEntrySearch) {
                $baseQuery->where('q.sap_created_id', 'LIKE', "%$sapEntrySearch%");
            }
    
            $recordsFiltered = $baseQuery->count();
    
            $records = $baseQuery->offset($start)->get();
    
            $finalData = [];
            foreach ($records as $row) {
                $batches = DB::table('invoice_batches')
                    ->where('invoice_id', $row->id)
                    ->where('item_code', $row->item_no)
                    ->select('batch_num', 'quantity', 'expiry_date')
                    ->groupBy('batch_num', 'quantity', 'expiry_date')
                    ->get();
    
                $row->batches = $batches->map(function ($b) {
                    return [
                        'batch_num' => $b->batch_num,
                        'batch_qty' => $b->quantity,
                        'expiry_date' => $b->expiry_date,
                    ];
                });
    
                $finalData[] = $row;
            }
    
            $recordsTotal = DB::table('invoices')
                ->where('store_code', $wareHouse)
                ->count();

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $finalData,
            ]);
        } catch (\Exception $e) {
            \Log::error('DataTables Error: '.$e->getMessage());
    
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Server Error: '.$e->getMessage()
            ]);
        }
    }
   


    public function getPaidSalesOrderJson(Request $request)
    {
        $data = $request->all();


        $wareHouse = Auth()->user()->ware_house;

        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $columnArray = ['q.id', 'q.order_date', 'q.customer_id', 'q.total', 'q.user_id'];

        try {
            $userId = Auth::user()->id;

            $query = DB::table('orders as q')
                ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                ->join('users as u', 'u.id', '=', 'q.user_id')
                ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
                ->where('q.ware_house_code' , $wareHouse)
                ->select([
                    'q.id',
                    'q.sales_invoice_no as doc_num',
                    'q.sales_type',
                    'q.customer_id',
                    'q.order_date',
                    'q.status',
                    'q.total',
                    'c.card_name as customer_name',
                    'c.sub_grp',
                    'u.name as user_name',
                    's.name as sales_emp_name'
                ])
                ->orderBy('q.id', 'DESC');

            if (!empty($request->sales_type)) {
                $query->where('q.sales_type', $request->sales_type);
            }

            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }

            if ($request->status_search !== null) {
                $query->where('q.status', $request->status_search);
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                try {
                    $query->whereBetween('q.order_date', [$request->start_date, $request->end_date]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }

            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('q.customer_id', 'LIKE', $searchTerm)
                    ->orWhere('c.card_name', 'LIKE', $searchTerm);
                });
            }

            $totalRecords = $query->count();

            $totalSum = $query->sum('q.total');

            if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                $query->orderBy(
                    $columnArray[$data['order'][0]['column']],
                    $data['order'][0]['dir']
                );
            }

            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }


            // Get the filtered data
            $orders = $query->get();
            //echo "test";


            //echo "print"; print_r($query->tosql()); exit;
            //dd($orders);
        } catch (\Exception $e) {
            echo "suresh";
            print_r($e);
            // Handle exceptions
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'total_amount' => 0,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'draw' => intval($data['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $orders,
            'total_amount' => $totalSum,
        ]);
    }

    
    public function getSalesOrderItemsJson(Request $request)
    {
        $data = $request->all();
    
        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    
        try {
            $userId = Auth::user()->id;
            $wareHouse = Auth()->user()->ware_house;

    
            $query = DB::table('order_items as oi')
                ->join('orders as o', 'o.id', '=', 'oi.order_id')
                ->join('customers as c', 'c.card_code', '=', 'o.customer_id')
                ->join('users as u', 'u.id', '=', 'o.user_id')
                ->join('sales_employees as s', 's.id', '=', 'o.sales_emp_id')
                ->select([
                    'oi.id',
                    'o.sales_invoice_no as doc_num',
                    'o.sales_type',
                    'o.customer_id',
                    'o.order_date',
                    'o.status',
                    'oi.item_no',
                    'oi.item_desc',
                    'oi.quantity',
                    'oi.price',
                    'oi.disc_percent',
                    'oi.gst_rate as tax_percent',
                    'oi.subtotal as item_total',
                    'c.card_name as customer_name',
                    'c.sub_grp',
                    'u.name as user_name',
                    's.name as sales_emp_name'
                ]);
            
            // Apply filters based on request parameters
            if (!empty($request->sales_type)) {
                $query->where('o.sales_type', $request->sales_type);
            }
    
            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }
    
            if ($request->status_search !== null) {
                $query->where('o.status', $request->status_search);
            }
    
            // Date filtering: Check if both start_date and end_date are provided
            if (!empty($request->start_date) && !empty($request->end_date)) {
                try {
                    $query->whereBetween('o.order_date', [$request->start_date, $request->end_date]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }
    
            // Search functionality
            if (!empty($data['search']['value'])) {
                $searchTerm = '%' . $data['search']['value'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('o.customer_id', 'LIKE', $searchTerm)
                        ->orWhere('c.card_name', 'LIKE', $searchTerm);
                });
            }
    
            // Total records before filtering
            $totalRecords = $query->count();
    
            // Apply ordering if specified
            if (isset($data['order']) && isset($data['columns'])) {
                $columnIndex = $data['order'][0]['column']; // Column index
                $columnName = $data['columns'][$columnIndex]['data']; // Column name
                $columnSortOrder = $data['order'][0]['dir']; // asc or desc
    
                if ($columnName) {
                    $query->orderBy($columnName, $columnSortOrder);
                }
            }
            
            // Apply pagination
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }
    
            // Get the filtered data
            $orders = $query->get();
    
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    
        return response()->json([
            'draw' => intval($data['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $orders,
            //'totals' => [
            //    'sub_total_item' => round($totals->sub_total_item ?? 0, 2),
            //    'total_price' => round($totals->total_price ?? 0, 2),
            //    'total_quantity' => round($totals->total_quantity ?? 0, 2)
            //],
        ]);
    }
    
    public function getRftItemsJson(Request $request)
    {
        $data = $request->all();

        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        try {
            $userId = Auth::id();
            $wareHouse = Auth::user()->ware_house;

            $query = DB::table('request_for_tender as rft')
                ->join('request_tender_items as rfti', 'rft.id', '=', 'rfti.request_for_tender_id')
                ->select([
                    'rft.id',
                    'rft.rft_entry',
                    'rft.rft_number as rft_doc_num',
                    'rft.from_store',
                    'rft.to_store',
                    'rft.created_at',
                    'rfti.item_code',
                    'rfti.requested_qty',
                    'rfti.rft_required_qty',
                    'rfti.required_date',
                    'rfti.rft_received_qty',
                    'rfti.received_date',
                ])
                ->where(function ($q) use ($wareHouse) {
                    $q->where('rft.from_store', $wareHouse)
                    ->orWhere('rft.to_store', $wareHouse);
                });

            // Date filtering
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $query->whereBetween(DB::raw('DATE(rft.created_at)'), [$request->start_date, $request->end_date]);
            }

            // Search functionality
            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where('rfti.item_code', 'LIKE', $searchTerm);
            }

            if (!empty($data['doc_num_search'])) {
                $searchTerm = '%' . $data['doc_num_search'] . '%';
                $query->where('rft.rft_number', 'LIKE', $searchTerm);
            }

            if (!empty($data['rft_entry_search'])) {
                $searchTerm = '%' . $data['rft_entry_search'] . '%';
                $query->where('rft.rft_entry', 'LIKE', $searchTerm);
            }

            // Get total records before filtering
            $totalRecords = $query->count();

            // Apply ordering
            if (isset($data['order'][0]['column']) && isset($data['columns'])) {
                $columnIndex = $data['order'][0]['column'];
                $columnName = $data['columns'][$columnIndex]['data'];
                $columnSortOrder = $data['order'][0]['dir'];

                if ($columnName) {
                    $query->orderBy($columnName, $columnSortOrder);
                }
            }

            // Apply pagination
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }

            // Get the filtered data
            $orders = $query->get();

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $orders,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function getRftItemsWithBatches(Request $request)
    {
        $data = $request->all();

        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        try {
            $userId = Auth::id();
            $wareHouse = Auth::user()->ware_house;

            $query = DB::table('request_for_tender as rft')
                ->join('request_tender_items as rfti', 'rft.id', '=', 'rfti.request_for_tender_id')
                ->select([
                    'rft.id',
                    'rft.rft_entry',
                    'rft.rft_number as rft_doc_num',
                    'rft.from_store',
                    'rft.to_store',
                    'rft.from_posted_at',
                    'rft.to_posted_at',
                    'rfti.item_code',
                    'rfti.requested_qty',
                    'rfti.rft_required_qty',
                    'rfti.rft_received_qty',
                    'rfti.id as rfti_id',
                ])
                ->where(function ($q) use ($wareHouse) {
                    $q->where('rft.from_store', $wareHouse)
                    ->orWhere('rft.to_store', $wareHouse);
                });

            // Date filtering
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $query->whereBetween(DB::raw('DATE(rft.created_at)'), [$request->start_date, $request->end_date]);
            }

            // Search functionality
            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where('rfti.item_code', 'LIKE', $searchTerm);
            }

            if (!empty($data['doc_num_search'])) {
                $searchTerm = '%' . $data['doc_num_search'] . '%';
                $query->where('rft.rft_number', 'LIKE', $searchTerm);
            }

            if (!empty($data['rft_entry_search'])) {
                $searchTerm = '%' . $data['rft_entry_search'] . '%';
                $query->where('rft.rft_entry', 'LIKE', $searchTerm);
            }

            // Get total records before filtering
            $totalRecords = $query->count();

            // Apply ordering
            if (isset($data['order'][0]['column']) && isset($data['columns'])) {
                $columnIndex = $data['order'][0]['column'];
                $columnName = $data['columns'][$columnIndex]['data'];
                $columnSortOrder = $data['order'][0]['dir'];

                if ($columnName) {
                    $query->orderBy($columnName, $columnSortOrder);
                }
            }

            // Apply pagination
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }

            // Get the filtered data
            $orders = $query->get();

            $finalData = [];
            foreach ($orders as $order) {
                // Get all unique batches for this item
                $batchRecords = DB::table('rft_batches')
                    ->where('rft_number', $order->rft_doc_num)
                    ->where('item_code', $order->item_code)
                    ->select('batch_num',
                            DB::raw('MAX(from_store_qty) as from_store_qty'),
                            DB::raw('MAX(to_store_qty) as to_store_qty'))
                    ->groupBy('batch_num')
                    ->get();
                foreach ($batchRecords as $batch) {
                    $row = clone $order;

                    $row->batches = [[
                        'batch_num' => $batch->batch_num,
                        'from_store_qty' => $batch->from_store_qty,
                        'to_store_qty' => $batch->to_store_qty
                    ]];

                    $finalData[] = $row;
                }
            }


            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $finalData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }


    public function getPaidQuotationsJson(Request $request)
    {
        $data = $request->all();


        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $columnArray = ['q.id', 'q.order_date', 'q.customer_id', 'q.total', 'q.user_id'];

        try {
            $userId = Auth::user()->id;

            $query = DB::table('order_quotations as q')
                ->join('customers as c', 'c.card_code', '=', 'q.customer_code')
                ->join('users as u', 'u.id', '=', 'q.user_id')
                ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
                ->select([
                    'q.id',
                    'q.quotation_pdf_no as doc_num',
                    'q.sales_type',
                    'q.customer_code',
                    'q.date',
                    'q.status',
                    'q.total',
                    'c.card_name as customer_name',
                    'c.sub_grp',
                    'u.name as user_name',
                    's.name as sales_emp_name'
                ])
                ->orderBy('q.id', 'DESC');


            if (!empty($request->sales_type)) {
                $query->where('q.sales_type', $request->sales_type);
            }

            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }

            if ($request->status_search !== null) {
                $query->where('q.status', $request->status_search);
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                try {
                    $query->whereBetween('q.date', [$request->start_date, $request->end_date]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }

            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('q.customer_code', 'LIKE', $searchTerm)
                    ->orWhere('c.card_name', 'LIKE', $searchTerm);
                });
            }

            $totalRecords = $query->count();

            $totalSum = $query->sum('q.total');

            if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                $query->orderBy(
                    $columnArray[$data['order'][0]['column']],
                    $data['order'][0]['dir']
                );
            }

            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }


            $orders = $query->get();
            //echo "test";

            //echo "print"; print_r($query->tosql()); exit;
            //dd($orders);
        } catch (\Exception $e) {
            //echo "suresh";
            //print_r($e);
            // Handle exceptions
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'total_amount' => 0,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'draw' => intval($data['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $orders,
            'total_amount' => $totalSum,
        ]);
    }

   

    public function paymentsJson(Request $request){
        $data = $request->all();
    
        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    
        $columnArray = ['op.id', 'op.customer_code', 'op.payment_method', 'op.payment_type', 'op.amount as total_amount',
                        'op.excess_amount', 'op.cash', 'op.online', 'ops.created_at', 'op.bank_tpa', 
                        'c.card_name', 'c.sub_grp', 'u.name as user_name', 'u.ware_house'];
        try {
            $userId = Auth::user()->id;
            $wareHouse = Auth()->user()->ware_house;

            $query = DB::table('order_payment_sub as ops')
                ->join('order_payments as op', 'op.id', '=', 'ops.order_payment_id')
                ->join('customers as c', 'c.card_code', '=', 'op.customer_code')
                ->join('users as u', 'u.id', '=', 'op.user_id')
                ->where('op.warehouse_code' , $wareHouse)
                ->select([
                    'op.id', 'op.customer_code', 'op.payment_method', 'op.payment_type', 
                    'op.amount as total_amount', 'op.excess_amount', 'op.cash', 'op.online', 
                    'ops.created_at', 'op.bank_tpa', 'c.card_name', 'c.sub_grp', 
                    'u.name as user_name', 'u.ware_house'
                ])
                ->orderBy('op.id', 'DESC');
    
            // Apply filters
            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }
    
            if (!empty($request->start_date) && !empty($request->end_date)) {
                try {
                    $query->whereBetween(\DB::raw('DATE(ops.created_at)'), [$request->start_date, $request->end_date]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }
            if (!empty($request->pay_method)) {
                if ($request->pay_method == 'cash') {
                    $query->where('cash', '>', 0);
                } else {
                    $query->where('bank_tpa', $request->pay_method);
                }
                
            }


            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search']. '%';
            
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('op.customer_code', 'LIKE', $searchTerm)
                      ->orWhere('c.card_name', 'LIKE', $searchTerm);
                });
            }
            
    
            // Count total records before filtering
            $totalRecords = $query->count();
            $totalFiltered = $totalRecords;
    
            // Apply pagination
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }
    
            // Get the results
            $orders = $query->get();
    
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $orders
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function exportPaymentsReport(Request $request){
        try {
            $userId = Auth::user()->id;
            $wareHouse = Auth()->user()->ware_house;
            $data = $request->all(); 
    
            $query = DB::table('order_payment_sub as ops')
                ->join('order_payments as op', 'op.id', '=', 'ops.order_payment_id')
                ->join('customers as c', 'c.card_code', '=', 'op.customer_code')
                ->join('users as u', 'u.id', '=', 'op.user_id')
                ->where('op.warehouse_code' , $wareHouse)
                ->select([
                    'op.id', 'op.customer_code', 'op.payment_method', 'op.payment_type', 
                    'op.amount as total_amount', 'op.excess_amount', 'op.cash', 'op.online', 
                    'ops.created_at', 'op.bank_tpa', 'c.card_name', 'c.sub_grp', 
                    'u.name as user_name', 'u.ware_house'
                ])
                ->orderBy('op.id', 'DESC');
    
            // Apply filters
            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }
    
            if (!empty($request->start_date) && !empty($request->end_date)) {
                try {
                    $query->whereBetween(\DB::raw('DATE(ops.created_at)'), [$request->start_date, $request->end_date]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }
            
            if (!empty($request->pay_method)) {
                if ($request->pay_method == 'cash') {
                    $query->where('cash', '>', 0);
                } else {
                    $query->where('bank_tpa', $request->pay_method);
                }
            }
    
            // Apply search filter
            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('op.customer_code', 'LIKE', $searchTerm)
                      ->orWhere('c.card_name', 'LIKE', $searchTerm);
                });
            }
    
            $totalRecords = $query->count();
            $totalFiltered = $totalRecords;
    
            
            $salesData = $query->get()->toArray();
    
            $totalAmount = array_sum(array_column($salesData, 'total_amount'));
            $cashAmount = array_sum(array_column($salesData, 'cash'));
            $onlineAmount = array_sum(array_column($salesData, 'online'));
            $bankAmount = 0;
            $tpaAmount = 0;

            // Calculate totals based on 'bank_tpa'
            foreach ($salesData as $payment) {
                if ($payment->bank_tpa == 'bank') {
                    $bankAmount += $payment->online;
                } elseif ($payment->bank_tpa == 'tpa') {
                    $tpaAmount += $payment->online;
                }
            }


    
            // Export to Excel (assuming SalesReportExport handles this)
            return Excel::download(new PaymentsReportExport($salesData, $totalAmount, $cashAmount, $bankAmount , $tpaAmount), "sales_report.xlsx");
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }
    
    public function exportInvoiceReport(Request $request)
    {
        try {
            $salesReportType = $request->sales_report;
            $wareHouse = Auth()->user()->ware_house;

            if ($salesReportType === "invoices") {
                $salesDataQuery = DB::table('invoices as q')
                    ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                    ->join('users as u' , 'u.id', '=', 'q.user_id')
                    ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
                    ->where('q.store_code' , $wareHouse)
                    ->select([
                        'q.invoice_no as doc_num',
                        'q.customer_id',
                        'c.card_name',
                        DB::raw('LOWER(q.sales_type) as sales_type'),  
                        'q.order_date',
                        'q.total',
                        'q.paid_amount',
                        DB::raw('q.total - q.paid_amount AS balance_amount'),
                        'q.status',
                        's.memo',
                        'u.name as user_name',
                    ]);
            } elseif ($salesReportType === "credits") {
                $salesDataQuery = DB::table('credit_memos as cm')
                    ->join('customers as c', 'c.card_code', '=', 'cm.customer_id')
                    ->join('sales_employees as s', 's.id', '=', 'cm.sales_emp_id')
                    ->join('users as u' , 'u.id', '=', 'cm.user_id')
                    ->where('cm.store_id' , $wareHouse)
                    ->select([
                        'cm.credit_no as doc_num',
                        'cm.customer_id',
                        'c.card_name',
                        DB::raw("'' as sales_type"), 
                        'cm.order_date', 
                        'cm.total',
                        'cm.paid_amount',
                        DB::raw("0 as balance_amount"),
                        'cm.status',
                        's.memo',
                        'u.name as user_name',
                    ]);
            } else {
                return response()->json(['error' => 'Invalid sales report type'], 400);
            }

            if (!empty($request->sales_type)) {
                $salesDataQuery->where(
                    $salesReportType === "invoices" ? 'q.sales_type' : 'cm.sales_type',
                    $request->sales_type
                );
            }

            if (!empty($request->sub_grp_search)) {
                $salesDataQuery->where('c.sub_grp', $request->sub_grp_search);
            }

            if ($request->status_search !== 'all') {
                $salesDataQuery->where(
                    $salesReportType === "invoices" ? 'q.status' : 'cm.status',
                    $request->status_search
                );
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $salesDataQuery->whereBetween(
                    $salesReportType === "invoices" ? 'q.order_date' : 'cm.order_date',
                    [$request->start_date, $request->end_date]
                );
            }

            $salesData = $salesDataQuery->get()->toArray();
            $totalAmount = array_sum(array_column($salesData, 'total'));
            $paidAmountTotal = array_sum(array_column($salesData, 'paid_amount'));
            $balanceAmountTotal = array_sum(array_column($salesData, 'balance_amount'));

            return Excel::download(new SalesReportExport($salesData, $totalAmount, $paidAmountTotal, $balanceAmountTotal), "{$salesReportType}_report.xlsx");

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    public function salesReportItmesJson(Request $request)
    {
        $data = $request->all();
        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        try {
            $userId = Auth::user()->id;
            $wareHouse = Auth()->user()->ware_house;


            if ($request->sales_report === "invoices") {
                $query = DB::table('invoice_items as ii')
                    ->join('invoices as q', 'q.id', '=', 'ii.invoice_id')
                    ->join('users as u', 'u.id', '=', 'q.user_id')
                    ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                    ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
                    ->where('q.store_code' , $wareHouse)
                    ->select([
                        'ii.id as item_id', // Item ID
                        'ii.item_no', // Item Code
                        'ii.item_desc as item_desc', // Item Description
                        'ii.quantity', // Quantity
                        'ii.price', // Unit Price
                        'ii.disc_percent', // Discount Precent
                        'ii.gst_rate', // Tax Precent
                        'ii.subtotal as item_total', // Item Total
                        'q.id as invoice_id', // Invoice ID
                        'q.invoice_no as doc_number', // Document Number
                        'q.order_date', // Order Date
                        'q.sales_type', // Sales Type
                        'q.customer_id', // Customer ID
                        'c.card_name as customer_name', // Customer Name
                        'c.sub_grp', // Sub Group
                        'q.status', // Status
                        'u.name as user_name', // User Name
                        's.name as sales_employee' // Sales Employee Name
                    ]);
                    //->orderBy('q.id', 'DESC');
            } elseif ($request->sales_report === "credits") {
                $query = DB::table('credit_memo_items as cmi')
                    ->join('credit_memos as cm', 'cm.id', '=', 'cmi.credit_id')
                    ->join('users as u', 'u.id', '=', 'cm.user_id')
                    ->join('customers as c', 'c.card_code', '=', 'cm.customer_id')
                    ->join('sales_employees as s', 's.id', '=', 'cm.sales_emp_id')
                    ->where('cm.store_id' , $wareHouse)
                    ->select([
                        'cmi.id as item_id', // Item ID
                        'cm.credit_no as doc_num', // Document Number
                        'cmi.item_no', // Item Code
                        'cmi.item_desc as item_desc', // Item Description
                        'cmi.quantity', // Quantity
                        'cmi.price', // Unit Price
                        'cmi.disc_percent', // Discount Precent
                        'cmi.gst_rate', // Tax Precent
                        'cmi.subtotal as item_total', // Item Total
                        'cm.id as credit_id', // Credit Memo ID
                        'cm.order_date', // Order Date
                        'cm.customer_id', // Customer ID
                        'c.card_name as customer_name', // Customer Name
                        'c.sub_grp', // Sub Group
                        //'cm.status', // Status
                        'cm.total as credit_total', // Credit Total
                        'u.name as user_name', // User Name
                        's.name as sales_employee' // Sales Employee Name
                    ]);
                    //->orderBy('cm.id', 'DESC');
            } else {
                return response()->json(['error' => 'Invalid sales report type'], 400);
            }

            // Apply filters
            if (!empty($request->sales_type)) {
                $query->where($request->sales_report === "invoices" ? 'q.sales_type' : '');
            }

            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }

            if ($request->status_search !== 'all') {
                $query->where($request->sales_report === "invoices" ? 'q.status' : 'cm.status', '=', $request->status_search);
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $query->whereBetween(
                    $request->sales_report === "invoices" ? 'q.order_date' : 'cm.order_date',
                    [$request->start_date, $request->end_date]
                );
            }

          

            if (!empty($data['search_items'])) {
                $searchTerm = '%' . $data['search_items'] . '%';
                $query->where(function ($q) use ($searchTerm, $request) {
                    if ($request->sales_report === "invoices") {
                        $q->where('ii.item_no', 'LIKE', $searchTerm)
                            ->orWhere('ii.item_desc', 'LIKE', $searchTerm)
                            ->orWhere('q.invoice_no', 'LIKE', $searchTerm)
                            ->orWhere('q.customer_id', 'LIKE', $searchTerm)
                            ->orWhere('c.card_name', 'LIKE', $searchTerm);
                    } elseif ($request->sales_report === "credits") {
                        $q->where('cmi.item_no', 'LIKE', $searchTerm)
                            ->orWhere('cmi.item_desc', 'LIKE', $searchTerm)
                            ->orWhere('cm.credit_no', 'LIKE', $searchTerm)
                            ->orWhere('cm.customer_id', 'LIKE', $searchTerm)
                            ->orWhere('c.card_name', 'LIKE', $searchTerm);
                    }
                });
            }
            $totalsQuery = clone $query;

            // Calculate totals
            $totals = $totalsQuery->selectRaw('SUM(ii.price) as total_price, SUM(ii.quantity) as total_quantity, SUM(ii.subtotal) as total_item_total')->first();

            // Get total records before pagination
            $totalRecords = $query->count();

            // Apply pagination
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }

           
            // Execute query
            $order = $query->get();

            // Return response
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $order ,
                'totals' => [
                    'total_price' => $totals->total_price ?? 0,
                    'total_quantity' => $totals->total_quantity ?? 0,
                    'total_item_total' => $totals->total_item_total ?? 0,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function exportSalesItemsReport(Request $request)
    {
        try {

            //dd($request->all());
            $salesReportType = $request->sales_report;
            $wareHouse = Auth()->user()->ware_house;

            if ($salesReportType === "invoices") {
                $salesDataQuery = DB::table('invoice_items as ii')
                    ->join('invoices as q', 'q.id', '=', 'ii.invoice_id')
                    ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                    ->join('users as u', 'u.id', '=', 'q.user_id')
                    ->where('q.store_code' , $wareHouse)
                    ->select([
                        'ii.item_no', // Item Code
                        'ii.item_desc', // Item Description
                        'ii.quantity', // Quantity
                        'ii.price', // Unit Price
                        'ii.disc_percent', // Discount Percent
                        'ii.gst_rate', // Tax Percent
                        'ii.subtotal as item_total', // Item Total
                        'q.invoice_no as doc_number', // Invoice Number
                        'q.order_date', // Order Date
                        'c.card_name as customer_name', // Customer Name
                        'c.sub_grp', // Sub Group
                        'q.status', // Status
                        'u.name as user_name' // User Name
                    ]);
            } elseif ($salesReportType === "credits") {
                $salesDataQuery = DB::table('credit_memo_items as cmi')
                    ->join('credit_memos as cm', 'cm.id', '=', 'cmi.credit_id')
                    ->join('customers as c', 'c.card_code', '=', 'cm.customer_id')
                    ->join('users as u', 'u.id', '=', 'cm.user_id')
                    ->where('cm.store_id' , $wareHouse)
                    ->select([
                        'cmi.item_no', // Item Code
                        'cmi.item_desc', // Item Description
                        'cmi.quantity', // Quantity
                        'cmi.price', // Unit Price
                        'cmi.disc_percent', // Discount Percent
                        'cmi.gst_rate', // Tax Percent
                        'cmi.subtotal as item_total', // Item Total
                        'cm.credit_no as doc_number', // Credit Memo Number
                        'cm.order_date', // Order Date
                        'c.card_name as customer_name', // Customer Name
                        'c.sub_grp', // Sub Group
                        'cm.status', // Status
                        'u.name as user_name' // User Name
                    ]);
            } else {
                return response()->json(['error' => 'Invalid sales report type'], 400);
            }

            // Apply filters
            if (!empty($request->sales_type)) {
                $salesDataQuery->where(
                    //$salesReportType === "invoices" ? 'q.sales_type' : 'cm.sales_type',
                    //$request->sales_type
                    $salesReportType === "invoices" ? 'q.sales_type' : '',

                );
            }

            if (!empty($request->sub_grp_search)) {
                $salesDataQuery->where('c.sub_grp', $request->sub_grp_search);
            }

            if ($request->status_search !== 'all') {
                $salesDataQuery->where(
                    $salesReportType === "invoices" ? 'q.status' : 'cm.status',
                    $request->status_search
                );
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $salesDataQuery->whereBetween(
                    $salesReportType === "invoices" ? 'q.order_date' : 'cm.order_date',
                    [$request->start_date, $request->end_date]
                );
            }

            if (!empty($request->search)) {
                $searchTerm = '%' . $request->search . '%';
                $salesDataQuery->where(function ($query) use ($searchTerm, $salesReportType) {
                    if ($salesReportType === "invoices") {
                        $query->where('ii.item_no', 'LIKE', $searchTerm)
                            ->orWhere('ii.item_desc', 'LIKE', $searchTerm)
                            ->orWhere('q.invoice_no', 'LIKE', $searchTerm)
                            ->orWhere('c.card_name', 'LIKE', $searchTerm);
                    } elseif ($salesReportType === "credits") {
                        $query->where('cmi.item_no', 'LIKE', $searchTerm)
                            ->orWhere('cmi.item_desc', 'LIKE', $searchTerm)
                            ->orWhere('cm.credit_no', 'LIKE', $searchTerm)
                            ->orWhere('c.card_name', 'LIKE', $searchTerm);
                    }
                });
            } 

            $salesData = $salesDataQuery->get()->toArray();

            //echo "suresh"; print_r($salesData); exit;

            // Generate the Excel file
            return Excel::download(new SalesItemsReportExport($salesData), "{$salesReportType}_items_report.xlsx");
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }


    public function exportSalesOrderReport(Request $request)
    {
        $wareHouse = Auth()->user()->ware_house;

         $salesData =  DB::table('orders as q')
            ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
            ->join('users as u', 'u.id', '=', 'q.user_id')
            ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
            ->where('q.ware_house_code' , $wareHouse)

            ->select([
                'q.id',
                'q.sales_invoice_no as doc_num',
                'q.sales_type',
                'q.customer_id as customer_code',
                'q.order_date as date',
                'q.status',
                'q.total',
                'c.card_name as customer_name',
                'c.sub_grp',
                'u.name as user_name',
                's.name as sales_emp_name'
            ]);

            if ($request->sales_type) {
                $salesData->where('sales_type' , $request->sales_type);
            }
            if (!empty($request->sub_grp_search)) {
                $salesData->where('c.sub_grp', $request->sub_grp_search);
            }

            if ($request->status_search != '') {
                $salesData->where('q.status', '=', $request->status_search);
            }
                $salesData = $salesData->whereBetween('order_date', [$request->start_date, $request->end_date])
                ->get(['id', 'customer_id', 'sales_type', 'order_date', 'total', 'status'])
                ->toArray();

            $totalAmount = array_sum(array_column($salesData, 'total'));

            return Excel::download(new QuotationsReportExport($salesData, $totalAmount), 'sales_report.xlsx');
    }

    public function exportQuotationReport(Request $request)
    {

        $salesData =  DB::table('order_quotations as q')
        ->join('customers as c', 'c.card_code', '=', 'q.customer_code')
        ->join('users as u', 'u.id', '=', 'q.user_id')
        ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
        ->select([
            'q.id',
            'q.quotation_pdf_no as doc_num',
            'q.sales_type',
            'q.customer_code',
            'q.date',
            'q.status',
            'q.total',
            'c.card_name as customer_name',
            'c.sub_grp',
            'u.name as user_name',
            's.name as sales_emp_name'
        ]);

        if ($request->sales_type) {
            $salesData->where('sales_type' , $request->sales_type);
        }
        if (!empty($request->sub_grp_search)) {
            $salesData->where('c.sub_grp', $request->sub_grp_search);
        }

        if ($request->status_search != '') {
            $salesData->where('q.status', '=', $request->status_search);
        }
            $salesData = $salesData->whereBetween('date', [$request->start_date, $request->end_date])
            ->get(['id', ' customer_id', 'sales_type', 'order_date', 'total', 'status'])
            ->toArray();

        $totalAmount = array_sum(array_column($salesData, 'total'));

        return Excel::download(new QuotationsReportExport($salesData, $totalAmount), 'quotation_report.xlsx');
    }

    public function IndexExpenses(){
        return view('reports.expenses-reports');
    }

    public function getPaidExpensesJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = [
                'ec.id', 'ec.user_id', 'ec.total_amount', 'ec.created_at','c.name'
            ];

            try {
                $userId = Auth::user()->id;

                $query = DB::table('expenses as ec')
                    ->join('users as c', 'ec.user_id', '=', 'c.id')
                    ->select(
                        'ec.id',
                        'c.name',
                        'ec.user_id',
                        'ec.total_amount',
                        'ec.created_at'
                    )
                    ->where('ec.user_id', '=', $userId)
                    ->orderBy('ec.id', 'DESC');

                    if (!empty($request->start_date) && !empty($request->end_date)) {
                        try {
                            $query->whereBetween('ec.created_at', [$request->start_date, $request->end_date]);
                        } catch (\Exception $e) {
                            return response()->json(['error' => 'Invalid date format'], 400);
                        }
                    }

                if (!empty($data['search_user'])) {
                    $query->where('c.id', 'LIKE', '%' . $data['search_user'] . '%');
                }

                $totalRecords = $query->count();

                if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                    $query->orderBy(
                        $columnArray[$data['order'][0]['column']],
                        $data['order'][0]['dir']
                    );
                }



                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                $expenses = $query->get();

            } catch (\Exception $e) {
                $expenses = [];
                $totalRecords = 0;
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $expenses
            ]);
        }
    }

    public function CreditNoteJson(Request $request)
    {
        $data = $request->all();


        // echo "<pre>";print_r($data);exit;

        if (isset($data['draw'])) {
            $columnArray = ['q.id', 'q.order_date', 'q.customer_id', 'q.total', 'q.sales_invoice_no', 'q.user_id','q.credit_no'];

            try {
                $userId = Auth::user()->id;

                $query = DB::table('credit_memos as q')
                // ->join('users as u', 'u.id', '=', 'q.user_id')
                ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                    ->select(['q.id','q.customer_id', 'q.ref_id','q.user_id',
                        'q.order_date', 'q.status', 'q.total', 'q.sales_invoice_no','c.card_name','q.paid_amount','c.grp_name','c.sub_grp','q.credit_no'])
                    ->orderBy('q.id', 'DESC');

                    if (!empty($request->start_date) && !empty($request->end_date)) {
                        try {
                            $query->whereBetween('q.order_date', [$request->start_date, $request->end_date]);
                        } catch (\Exception $e) {
                            return response()->json(['error' => 'Invalid date format'], 400);
                        }
                    }

                if (!empty($request->sub_grp_search)) {
                    $query->where('c.sub_grp', $request->sub_grp_search);
                }

                if ($request->status_search != '') {
                    $query->where('q.status', '=', $request->status_search);
                }

                // Search filter
                if (!empty($data['search'])) {
                    $searchTerm = '%' . $data['search'] . '%';
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('q.customer_id', 'LIKE', $searchTerm)
                            ->orWhere('q.card_name', 'LIKE', $searchTerm);
                    });
                }

                // Total records before pagination
                $totalRecords = $query->count();

                
                $totalSum = $query->sum('q.total');


                
                $order = $query->get();

                

            } catch (\Exception $e) {
                $order = [];
                $totalRecords = 0;
                $totalSum = 0;
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $order,
                'total_amount' => $totalSum,
            ]);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function ExportCreditNoteReport(Request $request)
    {
        try {
            $salesDataQuery = DB::table('credit_memos as q')
                ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                ->select([
                    'q.id',
                    'q.sales_invoice_no',
                    'q.customer_id',
                    'q.credit_no',
                    'q.order_date',
                    'q.total',
                    'q.status',
                    'c.card_name',
                    'c.sub_grp'
                ]);


            if (!empty($request->sub_grp_search)) {

                $salesDataQuery->where('c.sub_grp', $request->sub_grp_search);
                //echo "suresh"; print_r($salesDataQuery); exit;
            }

            if ($request->status_search != '') {
                $salesDataQuery->where('q.status', '=', $request->status_search);
            }

            if (!empty($request->start_date) && !empty($request->end_date)) {
                try {
                    $salesDataQuery->whereBetween('q.order_date', [$request->start_date, $request->end_date]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
            }

            $salesData = $salesDataQuery->get()->toArray();


            $totalAmount = array_sum(array_column($salesData, 'total'));

            return Excel::download(new SalesReportExport($salesData, $totalAmount), 'credit_note.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }


}
