<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\SalesReportExport;
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

    public function creditNote()
    {
        return view('reports.credit-note-reports');
    }


    public function getPaidInvoiceJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = ['q.id', 'q.date', 'q.customer_code', 'q.total', 'q.quotation_pdf_no', 'q.user_id'];

            try {
                $userId = Auth::user()->id;

                $query = DB::table('invoices as q')
                ->join('users as u', 'u.id', '=', 'q.user_id')
                ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                ->join('sales_employees as s', 's.id', '=', 'q.sales_emp_id')
                    ->select(['q.id','q.sales_type', 'q.customer_id', 'q.ref_id', 'q.ship_to', 'q.user_id', 'q.day_series',
                        'q.order_date', 'q.status', 'q.total', 'q.invoice_no', 'q.bill_to', 'c.card_name','q.store_code','q.due_date','q.pymnt_grp','q.paid_amount', 'q.sap_created_id' , 'q.sap_error_msg' , 'q.sap_updated_at', 'q.sap_updated','c.grp_name','u.code', 's.name', 'c.sub_grp'])
                    ->orderBy('q.id', 'DESC');

                if (!empty($request->sales_type)) {
                    $query->where('q.sales_type', $request->sales_type);
                }

                if (!empty($request->sub_grp_search)) {
                    $query->where('c.sub_grp', $request->sub_grp_search);
                }

                if ($request->status_search != '') {
                    $query->where('q.status', '=', $request->status_search);
                }

                if (!empty($request->start_date) && !empty($request->end_date)) {
                    try {
                        $query->whereBetween('q.order_date', [$request->start_date, $request->end_date]);
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Invalid date format'], 400);
                    }
                }

                // Search filter
                if (!empty($data['search'])) {
                    $searchTerm = '%' . $data['search'] . '%';
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('q.customer_id', 'LIKE', $searchTerm)
                            ->orWhere('q.day_series', 'LIKE', $searchTerm);
                    });
                }

                // Total records before pagination
                $totalRecords = $query->count();

                // Calculate total sum before pagination
                $totalSum = $query->sum('q.total');

                // Apply ordering
                if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                    $query->orderBy(
                        $columnArray[$data['order'][0]['column']],
                        $data['order'][0]['dir']
                    );
                }
                // Pagination
                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                // Get the filtered data
                $order = $query->get();

            } catch (\Exception $e) {
                // Handle exceptions
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

    public function getPaidSalesOrderJson(Request $request)
    {
        $data = $request->all();


        //echo "<pre>"; print_r($data); exit;


        if (!isset($data['draw'])) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $columnArray = ['q.id', 'q.order_date', 'q.customer_id', 'q.total', 'q.user_id'];

        try {
            $userId = Auth::user()->id;

            $query = DB::table('orders as q')
                ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                ->select([
                    'q.id',
                    'q.sales_type',
                    'q.customer_id',
                    'q.order_date',
                    'q.status',
                    'q.total',
                    'c.card_name',
                    'c.sub_grp'
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
                ->select([
                    'q.id',
                    'q.sales_type',
                    'q.customer_code',
                    'q.date',
                    'q.status',
                    'q.total',
                    'c.card_name',
                    'c.sub_grp'
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


    public function exportInvoiceReport(Request $request)
    {
        try {
            $salesDataQuery = DB::table('invoices as q')
                ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                ->select([
                    'q.id',
                    'q.day_series',
                    'q.customer_id',
                    'q.sales_type',
                    'q.order_date',
                    'q.total',
                    'q.status',
                    'c.card_name',
                    'c.sub_grp'
                ]);

            if (!empty($request->sales_type)) {
                $salesDataQuery->where('q.sales_type', $request->sales_type);
            }

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

            return Excel::download(new SalesReportExport($salesData, $totalAmount), 'invoice_report.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }



    public function exportSalesOrderReport(Request $request)
    {
         $salesData =  DB::table('orders as q')
                    ->join('customers as c', 'c.card_code', '=', 'q.customer_id')
                            ->select([
                                'q.id',
                                'q.customer_id',
                                'q.sales_type',
                                'q.order_date',
                                'q.total',
                                'q.status',
                                'c.card_name',
                                'c.sub_grp',
                                'q.status',
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

            return Excel::download(new SalesReportExport($salesData, $totalAmount), 'sales_report.xlsx');
    }

        public function exportQuotationReport(Request $request)
        {
             $salesData =  DB::table('order_quotations as q')
                        ->join('customers as c', 'c.card_code', '=', 'q.customer_code')
                                ->select([
                                    'q.id',
                                    'q.customer_code as customer_id',
                                    'q.sales_type',
                                    'q.date as order_date',
                                    'q.total',
                                    'q.status',
                                    'c.card_name',
                                    'c.sub_grp',
                                    'q.status',
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

                return Excel::download(new SalesReportExport($salesData, $totalAmount), 'quotation_report.xlsx');
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

                // echo "test";print_r($totalRecords);

                // Calculate total sum before pagination
                $totalSum = $query->sum('q.total');


                // Apply ordering
                // if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                //     $query->orderBy(
                //         $columnArray[$data['order'][0]['column']],
                //         $data['order'][0]['dir']
                //     );
                // }
                // Pagination
                // if ($data['length'] != -1) {
                //     $query->skip($data['start'])->take($data['length']);
                // }

                // Get the filtered data
                $order = $query->get();

                // dd($order);exit;

                // echo "test-2";print_r($order);exit;

            } catch (\Exception $e) {
                // Handle exceptions
                echo "hi";
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
