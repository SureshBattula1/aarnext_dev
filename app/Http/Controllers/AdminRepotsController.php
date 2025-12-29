<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRepotsController extends Controller
{
    public function indexInvoice(){

        $warehouses = DB::table('ware_houses')->orderby('whs_code')->get();
        return view('admin-reports.invoice-reports');
    }

     public function indexSalesOrder(){

        $warehouses = DB::table('ware_houses')->orderby('whs_code')->get();
        return view('admin-reports.sales-order-reports');
    }
    public function getRFTReportList(){
        $warehouses = DB::table('ware_houses')->orderby('whs_code')->get();
        return view('admin-reports.rft-reports');
    }

    public function getRFTDocListData(Request $request){
        $warehouses = DB::table('ware_houses')->orderby('whs_code')->get();
        return view('admin-reports.rft-doc-reports');
    }

    public function getPaymentsIndex(){

        $warehouses = DB::table('ware_houses')->orderby('whs_code')->get();
        return view('admin-reports.payments-reports');
    }

    public function getitemsIndex(){
        
        $warehouses = DB::table('ware_houses')->orderby('id')->get();
        return view('admin-reports.items-report');
    }

    public function getAdminSalesReportJson(Request $request)
    {
        $data = $request->all();
    
        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    
        try {
            $userId = Auth::user()->id;
            $wareHouse = Auth::user()->ware_house;
    
            if ($request->sales_report === "invoices") {
                $query = DB::table('invoices as q')
                    ->join('users as u', 'u.id', '=', 'q.user_id')
                    ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                    ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
                    ->select([
                        'q.id', 'q.sales_type', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id',
                        'q.order_date', 'q.status', 'q.total', 'q.invoice_no', 'q.bill_to', 'c.card_name',
                        'q.store_code', 'q.due_date', 'q.pymnt_grp', 'q.paid_amount', 'q.sap_created_id',
                        'q.sap_error_msg', 'q.sap_updated_at', 'q.sap_updated', 'c.grp_name', 'u.code',
                        'u.name as user_name',  'c.sub_grp','q.invoice_no as doc_num', 's.name as sales_employee_name', 's.memo' , 
                        DB::raw("(CASE
                            WHEN q.sap_updated = '0' THEN '<span class=\"text-warning\"><b>Pending</b></span>'
                            WHEN q.sap_updated = '1' THEN '<span class=\"text-success\"><b>Updated</b></span>'
                            ELSE '<span class=\"text-muted\"><b>Unknown</b></span>'
                        END) as sap_status"),
                    ])
                    ->orderBy('q.id', 'DESC');
            } elseif ($request->sales_report === "credits") {
                $query = DB::table('credit_memos as cm')
                    ->join('users as u', 'u.id', '=', 'cm.user_id')
                    ->join('customers as c', 'c.card_code', '=', 'cm.customer_id')
                    ->join('invoices as i', 'i.id', '=', 'cm.invoice_id')
                    ->join('sales_employees as s', 's.id', '=', 'cm.sales_emp_id')
                    ->select([
                        'cm.id', 'cm.customer_id', 'cm.ref_id', 'cm.ship_to', 'cm.user_id', 
                        'cm.order_date', 'cm.status', 'cm.total', 'cm.credit_no', 'c.card_name',
                        'cm.store_id', 'cm.paid_amount', 'cm.remarks', 'cm.discount_status',
                        'cm.credit_no as doc_num', 'c.grp_name', 'u.code', 'c.sub_grp', 'u.name as user_name',
                        'cm.sap_created_id', 'cm.sap_error_msg', 'cm.sap_updated_at',  's.name as sales_employee_name', 's.memo' , 'i.sales_type',
                        DB::raw("(CASE
                        WHEN cm.sap_updated = '0' THEN '<span class=\"text-warning\"><b>Pending</b></span>'
                        WHEN cm.sap_updated = '1' THEN '<span class=\"text-success\"><b>Updated</b></span>'
                        ELSE '<span class=\"text-muted\"><b>Unknown</b></span>'
                    END) as sap_status"),
                    ])
                    ->orderBy('cm.id', 'DESC');
                    
            } else {
                return response()->json(['error' => 'Invalid sales report type'], 400);
            }
    
            if (!empty($request->sales_type)) {
                $query->where($request->sales_report === "invoices" ? 'q.sales_type' : 'cm.sales_type', $request->sales_type);
            }
    
            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }

            if (!empty($request->doc_num_search)) {
                if ($request->doc_num_search == "2") {
                    $query->whereNull($request->sales_report === "invoices" ? 'q.sap_created_id' : 'cm.sap_created_id');
                } elseif ($request->doc_num_search == "1") {
                    $query->whereNotNull($request->sales_report === "invoices" ? 'q.sap_created_id' : 'cm.sap_created_id');
                }
            }
            
            if (!empty($request->search_ware_house)) {
                $warehouses = explode(',', $request->search_ware_house);
                $query->whereIn($request->sales_report === "invoices" ? 'q.store_code' : 'cm.store_code', $warehouses);
            }
    
            if ($request->status_search !== 'all') {
                $query->where($request->sales_report === "invoices" ? 'q.status' : 'cm.status', $request->status_search);
            }
    
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $query->whereBetween(
                    $request->sales_report === "invoices" ? 'q.order_date' : 'cm.order_date',
                    [$request->start_date, $request->end_date]
                );
            }
    
            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $tableAlias = $request->sales_report === "invoices" ? 'q' : 'cm';
                $query->where(function ($q) use ($searchTerm, $tableAlias) {
                    $q->where("$tableAlias.customer_id", 'LIKE', $searchTerm)
                        ->orWhere('c.card_name', 'LIKE', $searchTerm);
                });
            }
    
            $totalRecords = $query->count();
            $totalSum = $query->sum($request->sales_report === "invoices" ? 'q.total' : 'cm.total');
            $totalPaid = round($query->sum($request->sales_report === "invoices" ? 'q.paid_amount' : 'cm.paid_amount'), 2);
            $totalBalance = ($request->sales_report !== "credits") ? ($totalSum - $totalPaid) : 0;
    
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }
    
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
            'total_balance_amount' => $totalBalance,
        ]);
    }

    public function getAdminSalesOrderReportJson(Request $request)
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
                ->select([
                    'q.id',
                    'q.sales_invoice_no as doc_num',
                    'q.sales_type',
                    'q.customer_id',
                    'q.order_date',
                    'q.status',
                    'q.total',
                    'q.ware_house_code',
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
            if (!empty($request->search_ware_house)) {
                $warehouses = explode(',', $request->search_ware_house);
                $query->whereIn('q.ware_house_code', $warehouses);
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

    public function getAdminPaymentsReportJson(Request $request){
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
                ->select([
                    'op.id', 'op.customer_code', 'op.payment_method', 'op.payment_type', 
                    'op.amount as total_amount', 'op.excess_amount', 'op.cash', 'op.online', 
                    'ops.created_at', 'op.bank_tpa', 'c.card_name', 'c.sub_grp', 
                    'u.name as user_name', 'u.ware_house', 'op.warehouse_code' , 
                    'op.is_sap_updated', 'op.sap_created_id', 'op.sap_updated_at', 'op.sap_error_msg',
                ])
                ->orderBy('op.id', 'DESC');
    
            if (!empty($request->sub_grp_search)) {
                $query->where('c.sub_grp', $request->sub_grp_search);
            }
            if (!empty($request->search_ware_house)) {
                $warehouses = explode(',', $request->search_ware_house);
                $query->whereIn('op.warehouse_code', $warehouses);
            }
    
            if (!empty($request->doc_num_search)) {
                if ($request->doc_num_search == "2") {
                $query->whereNull('op.sap_created_id');

                } elseif ($request->doc_num_search == "1") {
                    $query->whereNotNull('op.sap_created_id');
                }
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
            
    
            $totalRecords = $query->count();
            $totalFiltered = $totalRecords;
    
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }
    
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

   
    
    public function getAdminItemsReportJson(Request $request)
    {
        $data = $request->all();

        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        try {

            $warehouses = DB::table('ware_houses')->pluck('whs_code')->toArray();

            $warehouseStockQuery = DB::table('warehouse_codebatch_details as wc')
                ->join('items as i', 'wc.item_code', '=', 'i.item_code') 
                ->select(
                    'wc.item_code', 
                    'i.item_name',
                    'wc.whs_code',
                    DB::raw("IFNULL(SUM(wc.quantity) - SUM(wc.invoice_qty) + SUM(wc.credit_qty), 0) AS whs_qty")
                )
                ->groupBy('wc.item_code', 'i.item_name', 'wc.whs_code');

            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $warehouseStockQuery->where(function ($q) use ($searchTerm) {
                    $q->where('wc.item_code', 'LIKE', $searchTerm)
                    ->orWhere('i.item_name', 'LIKE', $searchTerm);
                });
            }

            $totalRecords = $warehouseStockQuery->count();

            // Apply pagination
            if ($data['length'] != -1) {
                $warehouseStockQuery->skip($data['start'])->take($data['length']);
            }

            $items = $warehouseStockQuery->get();

            $groupedItems = $items->groupBy('item_code');
            $result = [];

            foreach ($groupedItems as $itemCode => $records) {
                $item = $records->first(); // Get first record for item details

                $row = [
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                ];

                // Map warehouse stock quantities
                foreach ($warehouses as $index => $whsCode) {
                    $warehouseData = $records->where('whs_code', $whsCode)->first();
                    $row["whs_code" . ($index + 1)] = $whsCode;
                    $row["whs_qty" . ($index + 1)] = $warehouseData->whs_qty ?? 0;
                }

                $result[] = $row;
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => count($result),
                'data' => $result
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

    public function ItemsBatches($itemCode , $store){
        try{
            $batches = DB::table('warehouse_codebatch_details as ws')
                            ->where('ws.item_code', '=', $itemCode)
                            ->where('ws.whs_code', '=', $store)
                            ->select('ws.*')
                            ->get();
                 return response()->json([
                    'data'=> $batches,
                    'message' => 'Batch details'
                 ]);
        }catch(\Exception $e) {
            
            return response()->json([
                'error' => 'Failed to fetch Item Batches',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function getAdminRftItemsJson(Request $request)
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
                    'rft.from_sap_created_id',
                    'rft.to_sap_created_id',
                    'rft.from_status',
                    'rft.to_status',
                    'rft.sap_error_msg_from',
                    'rft.sap_error_msg as sap_error_msg_to',
                ]);

                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $query->whereBetween(\DB::raw('DATE(rft.created_at)'), [$request->start_date, $request->end_date]);
                }
                
            // Search functionality
            if (!empty($data['search'])) {
                $searchTerm = '%' . $data['search'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('rfti.item_code', 'LIKE', $searchTerm)
                    ->orWhere('rft.rft_number', 'LIKE', $searchTerm)
                    ->orWhere('rft.rft_entry', 'LIKE', $searchTerm);
                });
            }

            if (!empty($request->search_ware_house)) {
                $warehouses = explode(',', $request->search_ware_house);
                $query->whereIn('rft.from_store', $warehouses)
                    ->orWhereIn('rft.to_store', $warehouses);
            }

            if (!empty($data['doc_num_search'])) {
                $searchTerm = '%' . $data['doc_num_search'] . '%';
                $query->where('rft.rft_number', 'LIKE', $searchTerm);
            }

            if (!empty($data['rft_entry_search'])) {
                $searchTerm = '%' . $data['rft_entry_search'] . '%';
                $query->where('rft.rft_entry', 'LIKE', $searchTerm);
            }

            // Total records before pagination
            $totalRecords = (clone $query)->count();

            // Total Sum (on requested quantity or required quantity)
            $totalRequestedQty = (clone $query)->sum('rfti.requested_qty');
            $totalRequiredQty = (clone $query)->sum('rfti.rft_required_qty');

            // Ordering
            if (isset($data['order'][0]['column']) && isset($data['columns'])) {
                $columnIndex = $data['order'][0]['column'];
                $columnName = $data['columns'][$columnIndex]['data'];
                $columnSortOrder = $data['order'][0]['dir'];

                if ($columnName) {
                    $query->orderBy($columnName, $columnSortOrder);
                }
            }

            // Pagination
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }

            $orders = $query->get();

            // echo "data"; print_r($orders); exit;
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $orders,
                'totals' => [
                    'total_requested_qty' => $totalRequestedQty,
                    'total_required_qty' => $totalRequiredQty,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }
    
    public function getRftDocDetailsJson(Request $request)
    {
        $data = $request->all();
    
        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    
        try {
            $userId = Auth::id();
            $wareHouse = Auth::user()->ware_house;
    
            $query = DB::table('request_for_tender as rft')
                ->select([
                    'rft.id',
                    'rft.rft_entry',
                    'rft.rft_number as rft_doc_num',
                    'rft.from_store',
                    'rft.to_store',
                    'rft.created_at',
                    'rft.from_sap_created_id',
                    'rft.to_sap_created_id',
                    'rft.from_status',
                    'rft.to_status',
                    'rft.sap_error_msg_from',
                    'rft.sap_error_msg as sap_error_msg_to',
                    'rft.cancelled_status', 

                ]);
    
            // Date filter
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $query->whereBetween(DB::raw('DATE(rft.created_at)'), [$request->start_date, $request->end_date]);
            }
    
            // Global search
            if (!empty($data['search']['value'])) {
                $searchTerm = '%' . $data['search']['value'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('rft.rft_number', 'LIKE', $searchTerm)
                      ->orWhere('rft.rft_entry', 'LIKE', $searchTerm);
                });
            }
    
            // Filter by warehouse(s)
            if (!empty($request->search_ware_house)) {
                $warehouses = explode(',', $request->search_ware_house);
                $query->where(function ($q) use ($warehouses) {
                    $q->whereIn('rft.from_store', $warehouses)
                      ->orWhereIn('rft.to_store', $warehouses);
                });
            }
    
            // RFT doc number specific search
            if (!empty($data['doc_num_search'])) {
                $searchTerm = '%' . $data['doc_num_search'] . '%';
                $query->where('rft.rft_number', 'LIKE', $searchTerm);
            }
    
            // RFT entry specific search
            if (!empty($data['rft_entry_search'])) {
                $searchTerm = '%' . $data['rft_entry_search'] . '%';
                $query->where('rft.rft_entry', 'LIKE', $searchTerm);
            }

            if (!empty($request->sap_status)) {
                if($request->sap_status == 1){
                    $query->whereNull('rft.from_sap_created_id');
                } else if($request->sap_status == 2){
                    $query->whereNotNull('rft.from_sap_created_id');

                } else if($request->sap_status == 3){
                    $query->whereNull('rft.to_sap_created_id');

                } else if ($request->sap_status == 4){
                    $query->whereNotNull('rft.to_sap_created_id');
                } else {
                    $query->whereNotNull('rft.from_sap_created_id')->whereNotNull('rft.to_sap_created_id');
                }
            }

            if (!empty($request->web_status)) {
                if($request->web_status == 1){
                    $query->where('rft.from_status' , 0);
                } else if($request->web_status == 2){
                    $query->where('rft.from_status', 1);

                } else if($request->web_status == 3){
                    $query->where('rft.to_status', 0);

                } else if ($request->web_status == 4){
                    $query->where('rft.to_status', 1);
                }
                 else {
                    $query->where('rft.from_status', 1)
                          ->where('rft.to_status', 1);
                }
            }
    
            // Total records before pagination
            $totalRecords = (clone $query)->count();
    
            // Ordering
            if (isset($data['order'][0]['column']) && isset($data['columns'])) {
                $columnIndex = $data['order'][0]['column'];
                $columnName = $data['columns'][$columnIndex]['data'];
                $columnSortOrder = $data['order'][0]['dir'];
    
                if (!empty($columnName)) {
                    $query->orderBy($columnName, $columnSortOrder);
                }
            }
    
            // Pagination
            if ($data['length'] != -1) {
                $query->skip($data['start'])->take($data['length']);
            }
    
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
                'error' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }
    
}
