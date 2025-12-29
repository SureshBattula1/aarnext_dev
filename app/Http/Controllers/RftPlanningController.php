<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;


class RftPlanningController extends Controller
{
    function index()
    {
        return view('rft-planning.planning');
    }



    // public function RftStockJson(Request $request)
    // {
    //     try {
    //         $startDate   = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
    //         $endDate     = $request->input('end_date', Carbon::now()->format('Y-m-d'));
    //         $search      = $request->input('search');
    //         $itemGroup   = $request->input('item_group');
    //         $manufacturer= $request->input('manufacturer');

    //         $warehouseStocks = DB::table('warehouse_codebatch_details as wc')
    //             ->join('items as i', 'wc.item_code', '=', 'i.item_code')
    //             ->select(
    //                 'wc.item_code',
    //                 'i.item_name',
    //                 'wc.whs_code',
    //                 'i.manufacturer',
    //                 DB::raw("IFNULL(SUM(wc.quantity) - SUM(wc.invoice_qty) + SUM(wc.credit_qty), 0) AS store_qty")
    //             )
    //             ->groupBy('wc.item_code', 'i.item_name', 'wc.whs_code')
    //             ->get()
    //             ->groupBy('item_code');


    //         $salesQuery = DB::table('invoices as inv')
    //             ->leftJoin('invoice_items as ii', 'ii.invoice_id', '=', 'inv.id')
    //             ->leftJoin('items as i', 'i.item_code', '=', 'ii.item_no')
    //             ->select(
    //                 'ii.item_no as item_code',
    //                 'ii.item_desc as item_name',
    //                 'inv.store_code as whs_code',

    //                 DB::raw("
    //                     NULLIF(
    //                         SUM(CASE WHEN inv.sales_type = 'hospital' THEN ii.quantity ELSE 0 END),
    //                         0
    //                     ) as tot_hospital_qty
    //                 "),

    //                 DB::raw("
    //                     NULLIF(
    //                         SUM(CASE WHEN inv.sales_type != 'hospital' THEN ii.quantity ELSE 0 END),
    //                         0
    //                     ) as tot_general_qty
    //                 ")
    //             )
    //             ->whereBetween(DB::raw('DATE(inv.created_at)'), [$startDate, $endDate]);

    //         if (!empty($search)) {
    //             $salesQuery->where(function ($q) use ($search) {
    //                 $q->where('ii.item_no', 'like', "%{$search}%")
    //                 ->orWhere('ii.item_desc', 'like', "%{$search}%");
    //             });
    //         }

    //         if (!empty($itemGroup)) {
    //             $salesQuery->where('inv.customer_sub_grp', $itemGroup);
    //         }

    //         if (!empty($manufacturer)) {
    //             $salesQuery->where('i.manufacturer', $manufacturer);
    //         }

    //         $salesItems = $salesQuery
    //             ->groupBy('ii.item_no', 'ii.item_desc', 'inv.store_code')
    //             ->get()
    //             ->groupBy('item_code');


    //         $planningMap = DB::table('rft_planning')
    //             ->get()
    //             ->keyBy('item_code');


    //         $result = [];

    //         foreach ($warehouseStocks as $itemCode => $stockData) {

    //             $itemName = $stockData->first()->item_name ?? '';
    //             $planningData = $planningMap[$itemCode] ?? null;

    //             $warehouseStock = $stockData
    //                 ->where('whs_code', 'AAR-WHS')
    //                 ->sum('store_qty');

    //             $stores = [];

    //             foreach ($stockData as $whStock) {

    //                 $salesForItem = $salesItems[$itemCode] ?? collect();
    //                 $salesData   = $salesForItem->firstWhere('whs_code', $whStock->whs_code);

    //                 $stores[] = [
    //                     'whs_code' => $whStock->whs_code,

    //                     'store_qty' => $whStock->store_qty !== null
    //                         ? (int) $whStock->store_qty
    //                         : 0,

    //                     'previous_month_day_es' => $planningData->previous_month_day_es ?? 0,
    //                     'zero_stock_days'       => $planningData->zero_stock_days ?? 0,

    //                     'tot_hospital_qty' => $salesData->tot_hospital_qty ?? 0,
    //                     'tot_general_qty'  => $salesData->tot_general_qty ?? 0,
    //                 ];
    //             }

    //             $result[] = [
    //                 'item_code'        => $itemCode,
    //                 'item_name'        => $itemName,
    //                 'warehouse_stock'  => (int) $warehouseStock,
    //                 'stores'           => $stores,
    //             ];
    //         }

    //         return response()->json($result);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error'   => true,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

 
    public function RftStockJson(Request $request)
    {
        try {
            $startDate    = $request->input('start_date');
            $endDate      = $request->input('end_date');
            $search       = $request->input('search');
            $itemGroup    = $request->input('item_group');  
            $manufacturer = $request->input('manufacturer');


            $warehouseStocksQuery = DB::table('warehouse_codebatch_details as wc')
                ->join('items as i', 'wc.item_code', '=', 'i.item_code')
                ->select(
                    'wc.item_code',
                    'i.item_name',
                    'i.manufacturer',
                    'wc.whs_code',
                    DB::raw("
                        IFNULL(
                            SUM(wc.quantity)
                            - SUM(wc.invoice_qty)
                            + SUM(wc.credit_qty),
                            0
                        ) AS store_qty
                    ")
                );

            if (!empty($search)) {
                $warehouseStocksQuery->where(function ($q) use ($search) {
                    $q->where('wc.item_code', 'like', "%{$search}%")
                    ->orWhere('i.item_name', 'like', "%{$search}%");
                });
            }

            if (!empty($manufacturer)) {
                $warehouseStocksQuery->where('i.manufacturer', $manufacturer);
            }

            $warehouseStocks = $warehouseStocksQuery
                ->groupBy(
                    'wc.item_code',
                    'i.item_name',
                    'i.manufacturer',
                    'wc.whs_code'
                )
                ->get()
                ->groupBy('item_code');

          
            $hospitalQtyExpr = "
                SUM(
                    CASE
                        WHEN inv.sales_type = 'hospital'
                        THEN ii.quantity
                        ELSE 0
                    END
                )
            ";

            $generalQtyExpr = "
                SUM(
                    CASE
                        WHEN inv.sales_type != 'hospital'
                        THEN ii.quantity
                        ELSE 0
                    END
                )
            ";

            if ($itemGroup === 'HOSPITAL') {
                $generalQtyExpr = "0";
            }

            if ($itemGroup === 'GENERAL') {
                $hospitalQtyExpr = "0";
            }

            $salesQuery = DB::table('invoices as inv')
                ->leftJoin('invoice_items as ii', 'ii.invoice_id', '=', 'inv.id')
                ->select(
                    'ii.item_no as item_code',
                    'ii.item_desc as item_name',
                    'inv.store_code as whs_code',
                    DB::raw("$hospitalQtyExpr AS tot_hospital_qty"),
                    DB::raw("$generalQtyExpr AS tot_general_qty")
                )
                ->whereBetween(
                    DB::raw('DATE(inv.created_at)'),
                    [$startDate, $endDate]
                );

            if ($itemGroup === 'HOSPITAL') {
                $salesQuery->where('inv.sales_type', 'hospital');
            }

            if ($itemGroup === 'GENERAL') {
                $salesQuery->where('inv.sales_type', '!=', 'hospital');
            }

            $salesItems = $salesQuery
                ->groupBy('ii.item_no', 'ii.item_desc', 'inv.store_code')
                ->get()
                ->groupBy('item_code');

            $planningMap = DB::table('rft_planning')
                ->get()
                ->keyBy('item_code');

            $result = [];

            foreach ($warehouseStocks as $itemCode => $stockData) {

                $itemName     = $stockData->first()->item_name ?? '';
                $planningData = $planningMap[$itemCode] ?? null;

                $warehouseStock = $stockData
                    ->where('whs_code', 'AAR-WHS')
                    ->sum('store_qty');

                $stores = [];

                foreach ($stockData as $whStock) {

                    $salesForItem = $salesItems[$itemCode] ?? collect();
                    $salesData    = $salesForItem->firstWhere(
                        'whs_code',
                        $whStock->whs_code
                    );

                    $stores[] = [
                        'whs_code' => $whStock->whs_code,

                        'store_qty' => (int) ($whStock->store_qty ?? 0),

                        'previous_month_day_es' => $planningData->previous_month_day_es ?? 0,
                        'zero_stock_days'       => $planningData->zero_stock_days ?? 0,

                        'tot_hospital_qty' => (int) ($salesData->tot_hospital_qty ?? 0),
                        'tot_general_qty'  => (int) ($salesData->tot_general_qty ?? 0),
                    ];
                }

                $result[] = [
                    'item_code'       => $itemCode,
                    'item_name'       => $itemName,
                    'warehouse_stock' => (int) $warehouseStock,
                    'stores'          => $stores,
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkStockData()
    {
        try {
            $items = DB::table('items')
                ->select('item_code', 'item_name')
                ->get();
    
            $result = [];
    
            foreach ($items as $item) {
    
                $stockQty = DB::table('warehouse_codebatch_details')
                    ->where('item_code', $item->item_code)
                    ->sum('quantity');
    
                if ($stockQty > 0) {
                    DB::table('rft_planning')
                        ->where('item_code', $item->item_code)
                        ->increment('previous_month_day_es', 1);
                }
                $result[] = [
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'stock_qty' => $stockQty
                ];
            }
    
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function zeroStockDays()
    {
        try {
    
            $items = DB::table('items')
                ->select('item_code', 'item_name')
                ->get();
    
            $result = [];

            foreach ($items as $item) {
    
                $stockQty = DB::table('invoice_items')
                    ->where('item_code', $item->item_code)
                    ->sum('quantity');
    
                if ($stockQty > 0) {
                    DB::table('rft_planning')
                        ->where('item_code', $item->item_code)
                        ->increment('zero_stock_days', 1);
                }
                $result[] = [
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'stock_qty' => $stockQty
                ];
            }
    
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    

}
