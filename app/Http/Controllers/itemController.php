<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use Auth;

class itemController extends Controller
{
    public function index()
    {
        return view('items-list');
    }


    // public function getItemsJson(Request $request)
    // {
    //     $data = $request->all();

    //     if (isset($data['draw'])) {
    //         $columnArray = [
    //             'id',
    //             'item_code',
    //             'item_name',
    //             'frgn_name',
    //             'items_grp_code',
    //             'items_grp_name',
    //             'frim_code',
    //             'manufacturer',
    //             'code',
    //             'sale_pack',
    //             'cabinda_iva',
    //             'others_iva',
    //             'valid_for',
    //             'frozen_for',
    //             'create_date',
    //             'create_ts',
    //             'update_date',
    //             'update_ts'
    //         ];

    //         try {
    //             $query = DB::table('items')
    //                 ->select(
    //                     'id',
    //                     'item_code',
    //                     'item_name',
    //                     'frgn_name',
    //                     'items_grp_code',
    //                     'frim_code',
    //                     'manufacturer',
    //                     'code',
    //                     'sale_pack',
    //                     'cabinda_iva',
    //                     'others_iva',
    //                     'valid_for',
    //                     'frozen_for',
    //                     'create_date',
    //                     'create_ts',
    //                     'update_date',
    //                     'update_ts'
    //                 )
    //                 ->orderBy('id', 'DESC');

    //             // Apply search filter if exists
    //             if (!empty($data['search'])) {
    //                 $query->where('item_name', 'LIKE', '%' . $data['search'] . '%');
    //             }

    //             // Get total records before applying pagination
    //             $totalRecords = $query->count();

    //             // Apply sorting if requested
    //             if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
    //                 $query->orderBy($columnArray[$data['order'][0]['column']], $data['order'][0]['dir']);
    //             }

    //             // Apply pagination
    //             $count = $data['length'] ?? 10;
    //             if ($data['length'] != -1) {
    //                 $query->skip($data['start'])->take($data['length']);
    //             }

    //             // Get paginated result
    //             $items = $query->get();

    //         } catch (\Exception $e) {
    //             $items = [];
    //             $totalRecords = 0;
    //         }

    //         return response()->json([
    //             'draw' => intval($data['draw']),
    //             'recordsTotal' => $totalRecords,
    //             'recordsFiltered' => $totalRecords,
    //             'data' => $items
    //         ]);
    //     }
    // }

 public function getItemsJson(Request $request)
    {
        $data = $request->all();
        $warehouseCode = Auth::user()->ware_house;


        if (isset($data['draw'])) {
            $columnArray = [
                'i.id',
                'i.item_code',
                'i.item_name',
                'i.frgn_name',
                'i.items_grp_code',
                'i.items_grp_name',
                'i.items_grp_name',
                'i.frim_code',
                'i.manufacturer',
                'i.code',
                'i.sale_pack',
                'i.cabinda_iva',
                'i.others_iva',
                'i.valid_for',
                'i.frozen_for',
                'i.create_date',
                'i.create_ts',
                'w.on_hand',
                'i.update_date',
                'i.update_ts'
            ];

            try {
                $query = DB::table('items as i')
                 // ->leftJoin('warehouse_stocks as w', function($join) use ($warehouseCode) {
                 //        $join->on('w.item_code', '=', 'i.item_code')
                 //             ->where('w.warehouse_code', '=', $warehouseCode);
                 //    })
                    //->leftjoin('warehouse_codebatch_details as wc','wc.item_code','=','i.item_code')
                    ->leftJoin('warehouse_codebatch_details as wc', function($join) use ($warehouseCode) {
                        $join->on('wc.item_code', '=', 'i.item_code')
                             ->where('wc.whs_code', '=', $warehouseCode);  // Adding where condition inside join
                    })
                    // ->leftJoin('item_priceses as ip', function($join) use ($warehouseCode) {
                    //     $join->on('ip.item_code', '=', 'i.item_code')
                    //          ->where('ip.price_list', '=', 1);  // Adding where condition inside join
                    // })
                    ->select(
                        'i.id',
                        'i.item_code',
                        'i.item_name',
                        'i.frgn_name',
                        'i.items_grp_code',
                        'i.items_grp_name',
                        'i.frim_code',
                        'i.manufacturer',
                        'i.code',
                        'i.sale_pack',
                        'i.cabinda_iva',
                        'i.others_iva',
                        'i.valid_for',
                        'i.frozen_for',
                        'i.create_date',
                        'i.create_ts',
                        'i.update_date',
                        'i.item_price as price',
                        'i.update_ts',
                        DB::raw("IFNULL(SUM(wc.quantity) - SUM(wc.invoice_qty) + SUM(wc.credit_qty), 0) AS on_hand"),
                        //DB::raw('FORMAT((i.item_price * on_hand),2) as stock_value'),
                    )
                   // ->where('w.warehouse_code', '=', $warehouseCode)
                    ->groupBy('i.id')
                    ->orderBy('i.id', 'DESC');

               

                    //if (!empty($data['search'])) {
                    //    $searchTerm = $data['search'];
                    //    $query->where(function ($q) use ($searchTerm) {
                    //        $q->where('i.item_code', 'LIKE', '%' . $searchTerm . '%')
                    //          ->orWhere('i.item_name', 'LIKE', '%' . $searchTerm . '%');
                    //    });

                    //}
                    if (!empty($data['search'])) {
                        $searchTerm = '%' . $data['search'] . '%';
                        $query->where(function($q) use ($searchTerm) {
                            $q->where('i.item_code', 'LIKE', $searchTerm)
                              ->orWhere('i.item_name', 'LIKE', $searchTerm);
                        });
                    }

                 $totalRecords = $query->count();

                // Apply sorting if requested
                if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                    $query->orderBy($columnArray[$data['order'][0]['column']], $data['order'][0]['dir']);
                }

                // Apply pagination
                $count = $data['length'] ?? 10;
                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                // Get paginated result
               //  $items = $query->toSql();
              // print_r($items);exit;
                $items = $query->get();
                // echo 'items'->$items->toSql();
                // exit;


            } catch (\Exception $e) {

                echo "hello"; echo $e;
                $items = [];
                $totalRecords = 0;
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $items
            ]);
        }
    }

    function GetbatchDetails(Request $request, $itemId)
    {

        $warehouseCode = Auth::user()->ware_house;
        try{
            $batches = DB::table('warehouse_codebatch_details as ws')
                            ->where('ws.item_code', '=', $itemId)
                            ->where('ws.whs_code', '=', $warehouseCode)
                            ->select('ws.*')
                            ->get();
                 return response()->json([
                    'data'=> $batches,
                    'message' => 'Batch details'
                 ]);
        }catch(\Exception $e) {
            # code...
            return response()->json([
                'error' => 'Failed to fetch sales employees',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
