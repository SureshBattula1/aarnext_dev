<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class RequestForTenderController extends Controller
{
    //
    function index()
    {
        return view('requestfortender.list');
    }

    function itemsIndex($id){

        return view('requestfortender.items-list' , ['rft_id' => $id]);
    }

    function getRentForTenderJson(Request $request)
    {
        $data = $request->all();
        $Balanceamount = 0;

        if (isset($data['draw'])) {
            $columnArray = [
                'rft.id', 'rft.rft_number', 'rft.req_qty',
                'rft.from_store', 'rft.to_store', 'rft.req_date', 
                'rft.received_date',
            ];

            try {

                $warehouseCode = Auth::user()->ware_house;

                $query = DB::table('request_for_tender as rft')
                        ->where(function ($q) use ($warehouseCode) {
                            $q->where('rft.from_store', $warehouseCode)
                            ->orWhere('rft.to_store', $warehouseCode);
                        })
                    ->select(
                        'rft.id', 'rft.rft_number', 'rft.from_store', 'rft.to_store',
                        'rft.req_date', 'rft.received_date', 'rft.rft_entry', 'rft.status',
                        'rft.from_sap_update', 'rft.to_sap_update', 'rft.from_sap_created_id', 'rft.to_sap_created_id',
                         'rft.sap_error_msg', 'rft.sap_error_msg_from', 'rft.from_status', 'rft.to_status', 'rft.cancelled_status', 

                        DB::raw("(CASE
                            WHEN rft.status = '0' THEN '<span class=\"text-warning\"><b>Pending</b></span>'
                            WHEN rft.status = '1' THEN '<span class=\"text-success\"><b>Completed</b></span>'
                            ELSE '<span class=\"text-muted\"><b>Unknown</b></span>'
                        END) as status_display")
                    );

                // Apply date filtering if provided
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    try {
                        $query->whereBetween('rft.created_at', [$request->start_date, $request->end_date]);
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Invalid date format'], 400);
                    }
                }


                if (!empty($data['search_user'])) {
                    $searchTerm = '%' . $data['search_user'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('rft.rft_number', 'LIKE', $searchTerm)
                          ->orWhere('rft.rft_entry', 'LIKE', $searchTerm)
                          ->orWhere('rft.from_sap_created_id', 'LIKE', $searchTerm)
                          ->orWhere('rft.to_sap_created_id', 'LIKE', $searchTerm);
                    });
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

                $totalRecords = $query->count();

                // Apply sorting
                if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                    $column = $columnArray[$data['order'][0]['column']];
                    $direction = strtolower($data['order'][0]['dir']);

                    if (in_array($direction, ['asc', 'desc'])) {
                        $query->orderBy($column, $direction);
                    } else {
                        $query->orderBy($column, 'asc');
                    }
                } else {
                    $query->orderBy('rft.id', 'DESC');
                }

                // Apply pagination
                if (isset($data['start']) && $data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                $orders = $query->get();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Query error', 'details' => $e->getMessage()], 500);
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'store_code' => $warehouseCode,
                'data' => $orders
            ]);
        }
    }

    public function ShowRFTDetails(Request $request, $rftNumber)
    {
        $users = auth()->user();
        
        $rft = DB::table('request_for_tender')
            ->select(['request_for_tender.*'])
            ->where('request_for_tender.id', $rftNumber)
            ->first();

        if (!$rft) {
            return back()->withErrors('Request for Tender not found.');
        }

        $rftItemsQuery = DB::table('request_tender_items as rti')
            ->leftJoin('request_for_tender as rt', 'rt.id', '=', 'rti.request_for_tender_id')
            ->leftJoin('items as i', 'i.item_code', '=', 'rti.item_code')
            ->leftJoin('warehouse_codebatch_details as wcd', function ($join) use ($users) {
                $join->on('rti.item_code', '=', 'wcd.item_code')
                    ->where('wcd.whs_code', $users->ware_house);
            })
            ->where('rti.request_for_tender_id', $rftNumber)
            ->orderBy('base_line', 'ASC');

        if ($users->ware_house == $rft->from_store) {
            $rftItemsQuery->select([
                'rti.request_for_tender_id as id',
                'rti.id as rti_id',
                'rti.item_code',
                'i.item_name',
                'rti.requested_qty',
                'rti.rft_required_qty',
                'rti.rft_received_qty',
                "rt.from_store",
                "rt.to_store",
                "rt.rft_number",
                "wcd.whs_code",
                DB::raw("IFNULL(SUM(wcd.quantity) - SUM(wcd.invoice_qty) + SUM(wcd.credit_qty), 0) AS available_qty"),
                'rti.from_status',
                'rti.to_status'
            ]);
        } else {
            $rftItemsQuery->where('rti.from_status', 1)
                ->select([
                    'rti.request_for_tender_id as id',
                    'rti.id as rti_id',
                    'rti.item_code',
                    'i.item_name',
                    'rti.requested_qty',
                    'rti.rft_required_qty',
                    'rti.rft_received_qty',
                    "rt.from_store",
                    "rt.to_store",
                    "rt.rft_number",
                    "wcd.whs_code",
                    DB::raw("IFNULL(SUM(wcd.quantity) - SUM(wcd.invoice_qty) + SUM(wcd.credit_qty), 0) AS available_qty"),
                    'rti.from_status',
                    'rti.to_status'
                ]);
        }

        $rftItems = $rftItemsQuery
            ->groupBy(
                'rti.item_code', 'rti.request_for_tender_id', 'i.item_name', 'rti.requested_qty',
                'rti.rft_required_qty', 'rti.rft_received_qty', "rt.from_store", "rt.to_store",
                "rt.rft_number", "wcd.whs_code", 'rti.from_status', 'rti.to_status'
            )
            ->get();

        return view('requestfortender.details', compact('rft', 'rftItems'));
    }


    public function storeRFTDetails(Request $request)
    {
        Log::info('START Time: ' . now()->toDateTimeString());

        $existingInvoice = DB::table('request_for_tender')
            ->where('from_gu_id', $request->guid)
            ->where('to_gu_id', $request->guid)
            ->first();

        if ($existingInvoice) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate submission detected.',
            ], 400);
        }

        try {
            //echo "storeRFTDetails"; print_r($request->all()); exit;
            $warehouseCode = auth()->user()->ware_house;
            $userId = auth()->user()->id;

            //if($warehouseCode == )
            if (!$request->has('item_code') || !is_array($request->item_code)) {
                return redirect()->back()->withErrors(['error' => 'Invalid input data.']);
            }
    
            $fromTotalItems = 0;
            $toTotalItems = 0;
            //$warehouseCode = auth()->user()->ware_house;
            foreach ($request->item_code as $key => $itemCode) {
    
                $itemId = $request->rft_items_id[$key];
                $item_code = $request->item_code[$key];
                $rftReqQty = $request->rft_required_qty[$key] ?? 0;
                $rftreceivedQty = $request->rft_received_qty[$key] ?? 0;
                $rftNumber = $request->rft_number[$key] ?? 0;
                $rftId    = $request->rft_id[$key];
                $issuedQty  = $request->issued_qty[$key] ?? 0;
    
                if (empty($rftReqQty) && empty($rftreceivedQty)) {
                    continue;
                }
    
                if ($rftReqQty > 0) {
                    $fromTotalItems++;
                    $batches_data = $this->rftBatchQuantity($item_code, $rftReqQty);
    
                    $requestItems =  DB::table('request_tender_items')
                        ->where('request_for_tender_id', $rftId)
                        ->where('item_code', $itemCode)
                        ->where('rft_number', $rftNumber)
                        ->update([
                            'rft_required_qty' =>  $rftReqQty,
                            'required_date' => now(),
                            'from_status' => 1,

                        ]);
                    
                    $rftUpdate = DB::table('request_for_tender')
                                ->where('id', $rftId)
                                ->update([
                                    'from_status' => 1,
                                    'req_date' => now(),
                                    'remarks' => $request->remarks,
                                    'from_gu_id' => $request->guid,
                                    'from_user_id' => $userId,
                                    ]);
                    
                    foreach ($batches_data as $batchData) {
    
                        $batchNumber = $batchData['batch'];
                        $quantity = $batchData['qty'];
                        $expDate = $batchData['exp'];
                        $insert_b_array = [
                            'rft_item_id' => $itemId,
                            'rft_number' => $rftNumber,
                            'item_code' => $itemCode,
                            'batch_num' => $batchNumber,
                            'batch_qty' => $quantity,
                            'expiry_date' => $expDate,
                        ];
    
                        $insert_b_array['from_store'] = $warehouseCode ?? '';
                        $insert_b_array['from_store_qty'] = $quantity ?? 0;
                        $insert_b_array['req_date'] = now();

                        $exists = DB::table('rft_batches')
                            ->where('rft_item_id', $itemId)
                            ->where('rft_number', $rftNumber)
                            ->where('item_code', $itemCode)
                            ->where('batch_num', $batchNumber)
                            ->where('from_store_qty' ,'=', $quantity)
                            ->exists();

                        if(!($exists)){
                        DB::table('rft_batches')->insert($insert_b_array);
                        DB::table('warehouse_codebatch_details')
                            ->where('whs_code', $warehouseCode)
                            ->where('item_code', $itemCode)
                            ->where('batch_num', $batchNumber)
                            ->update(['quantity' => DB::raw("quantity - $quantity")]);
                        }

                    }
                }
    
                if ($rftreceivedQty > 0) {
                $toTotalItems++;
                $remainingQty = $rftreceivedQty;
    
                $rftBatches = DB::table('rft_batches')
                    ->where('item_code', $item_code)
                    ->where('rft_number', $rftNumber)
                    ->orderBy('expiry_date', 'ASC')
                    ->get();
    
                foreach ($rftBatches as $batch) {
                    if ($remainingQty <= 0) {
                        break;
                    }
    
                    $batchAvailableQty = $batch->from_store_qty;
    
                    $allocatedQty = min($remainingQty, $batchAvailableQty);

                    $exists = DB::table('rft_batches')
                            ->where('rft_item_id', $itemId)
                            ->where('rft_number', $rftNumber)
                            ->where('item_code', $itemCode)
                            ->where('batch_num', $batchNumber)
                            ->where('to_store_qty' ,'=', $quantity)
                            ->exists();
                    
                    if(!($exists)) {
                        DB::table('rft_batches')->insert([
                            'rft_item_id' => $itemId,
                            'rft_number' => $rftNumber,
                            'item_code' => $item_code,
                            'batch_num' => $batch->batch_num,
                            'expiry_date' => $batch->expiry_date,
                            'to_store' => $warehouseCode,
                            'to_store_qty' => $allocatedQty,
                            'received_date' => now(),
                        ]);
        
                        $batch_exist = DB::table('warehouse_codebatch_details')
                            ->where('whs_code', $warehouseCode)
                            ->where('item_code', $item_code)
                            ->where('batch_num', $batch->batch_num)
                            ->first();
        
                        if ($batch_exist) {
                            DB::table('warehouse_codebatch_details')
                                ->where('whs_code', $warehouseCode)
                                ->where('item_code', $item_code)
                                ->where('batch_num', $batch->batch_num)
                                ->update(['quantity' => DB::raw("quantity + $allocatedQty")]);
                        } else {
                            DB::table('warehouse_codebatch_details')->insert([
                                'item_code' => $item_code,
                                'whs_code'  => $warehouseCode,
                                'quantity'  => $allocatedQty,
                                'batch_num' => $batch->batch_num,
                                'in_date'   => now(),
                                'exp_date'  => $batch->expiry_date ?? '0000',
                            ]);
                        }
                    }
                    
                    // Deduct allocated quantity from remaining
                    $remainingQty -= $allocatedQty;
                }
    
                // Update request_tender_items and request_for_tender
                DB::table('request_tender_items')
                    ->where('request_for_tender_id', $rftId)
                    ->where('item_code', $item_code)
                    ->where('rft_number', $rftNumber)
                    ->update([
                        'rft_received_qty' => $rftreceivedQty,
                        'received_date' => now(),
                        'to_status' => 1,
                    ]);

                    DB::table('request_for_tender')
                    ->where('id', $rftId)
                    ->update([
                        'to_status' => 1,
                        'received_date' => now(),
                        'remarks' => $request->remarks,
                        'to_gu_id' => $request->guid,
                        'to_user_id' => $userId,
                    ]);
                }
            }
            if (isset($rftId) && $fromTotalItems > 0) {
                DB::table('request_for_tender')
                    ->where('id', $rftId)
                    ->update([
                        'from_total_items' => $fromTotalItems,
                    ]);
            } else if (isset($rftId) && $toTotalItems > 0) { 
                DB::table('request_for_tender')
                ->where('id', $rftId)
                ->update([
                    'to_total_items' => $toTotalItems,
                ]);
            }
            return redirect('/rft-list')->with('success', 'RFT details saved successfully.');
        } catch (\Exception $e) {
            Log::error('Error while SAP Status Update: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function exportItemsJson(Request $request)
    {
        try {

            $columnArray = [
                'rft.id', 'rft.rft_number', 'rft.req_qty',
                'rft.from_store', 'rft.to_store', 'rft.req_date',
                'rft.received_date',
            ];

            $id = $request->rft_id;

            //echo "<pre>"; print_r($id); echo "</pre>"; exit;

            $users = auth()->user();
            $rft = DB::table('request_for_tender')->where('id', $id)->first();

            if (!$rft) {
                return response()->json(['error' => 'Request for Tender not found.'], 404);
            }

            // Query for RFT items
            $rftItemsQuery = DB::table('request_tender_items as rti')
                ->leftJoin('request_for_tender as rt', 'rt.id', '=', 'rti.request_for_tender_id')
                ->leftJoin('items as i', 'i.item_code', '=', 'rti.item_code')
                ->leftJoin('warehouse_codebatch_details as wcd', function ($join) use ($users) {
                    $join->on('rti.item_code', '=', 'wcd.item_code')
                        ->where('wcd.whs_code', $users->ware_house);
                })
                ->where('rti.request_for_tender_id', $id);

            if ($users->ware_house == $rft->from_store) {
                $rftItemsQuery->select([
                    'rt.id', 'rti.id as rti_id', 'rti.item_code', 'i.item_name', 'rti.requested_qty',
                    'rti.rft_required_qty', 'rti.rft_received_qty', 'rt.from_store', 'rt.to_store',
                    'rt.rft_number', 'wcd.whs_code',
                    DB::raw('IFNULL(SUM(wcd.quantity) - SUM(wcd.invoice_qty) + SUM(wcd.credit_qty), 0) AS available_qty'),
                    'rti.from_status', 'rti.to_status'
                ]);
            } else {
                $rftItemsQuery->where('rti.from_status', 1)
                    ->select([
                        'rt.id','rti.id as rti_id', 'rti.item_code', 'i.item_name', 'rti.requested_qty',
                        'rti.rft_required_qty', 'rti.rft_received_qty', 'rt.from_store', 'rt.to_store',
                        'rt.rft_number', 'wcd.whs_code',
                        DB::raw('IFNULL(SUM(wcd.quantity) - SUM(wcd.invoice_qty) + SUM(wcd.credit_qty), 0) AS available_qty'),
                        'rti.from_status', 'rti.to_status'
                    ]);
            }

            $rftItemsQuery->groupBy('rti.id', 'rti.item_code', 'i.item_name', 'rti.requested_qty',
                'rti.rft_required_qty', 'rti.rft_received_qty', 'rt.from_store', 'rt.to_store',
                'rt.rft_number', 'wcd.whs_code', 'rti.from_status', 'rti.to_status');

            // if ($request->has('start') && $request->input('length') != -1) {
            //     $rftItemsQuery->skip($request->input('start'))->take($request->input('length'));
            // }
            $remarks = $rft->remarks;
            $rftItems = $rftItemsQuery->get();

            
            return response()->json(["data" => $rftItems,
                                    "remarks" => $remarks           
                                    ]);

        } catch (\Exception $e) {
            Log::error('Error fetching Export: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while exporting the data.'], 500);
        }
    }

    public function rftBatchDetails(Request $request, $rftId)
    {
        try {
            $data = $request->only(['id', 'ware_house']);
            $id = $data['id'] ?? null;
            $storeCode = $data['ware_house'] ?? null;
    
            if (!$id) {
                return response()->json(['error' => 'Invalid ID provided'], 400);
            }
    
            $rft = DB::table('request_for_tender')->where('id', $id)->first();
    
            if (!$rft) {
                return response()->json(['error' => 'RFT not found'], 404);
            }
    
            $column = auth()->user()->ware_house == $rft->from_store ? 'from_store_qty' : 'to_store_qty';
    
            $itemBatches = DB::table('rft_batches')
                ->select( 'item_code', 'rft_item_id', 'batch_num', "$column as qty", 'expiry_date')
                ->whereIn('rft_item_id', (array)$rftId)
                ->whereNotNull($column)
                ->get();
    
            if ($itemBatches->isEmpty()) {
                return response()->json(['message' => 'No batches found'], 404);
            }
    
            $exportData = $itemBatches->map(function ($batch) {
                return [
                    'item_code' => $batch->item_code,
                    'batch_num' => $batch->batch_num,
                    'expiry_date' => $batch->expiry_date,
                    'qty' => $batch->qty,
                ];
            });
    
            return response()->json(['data' => $exportData]);
        } catch (\Exception $e) {
            Log::error('Error fetching RFT Batches: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching RFT Items.'], 500);
        }
    }
    

    public function rftBatchQuantity($itemId, $rftQty)
    {
        $warehouse = auth()->user()->ware_house;


        //$item_code = DB::table('items')
        $batches = DB::table('warehouse_codebatch_details as ws')
                        ->where('ws.item_code', '=', $itemId)
                        ->where('ws.whs_code', '=', $warehouse)
                        ->orderBy('exp_date', 'ASC')
                        ->get();

        // Calculate total available quantity
        $check_batch = DB::table("warehouse_codebatch_details")
                            ->select(DB::raw("SUM(quantity) as total_qty"), DB::raw("SUM(invoice_qty) as inv_qty"), DB::raw("SUM(credit_qty) as credit_qty"))
                            ->where('item_code', '=', $itemId)
                            ->where('whs_code', '=', $warehouse)
                            ->first();

        $total_credit_de = ($check_batch->total_qty - $check_batch->inv_qty) + $check_batch->credit_qty;

        // Check if the available quantity is less than the required quantity
        if ($total_credit_de < $rftQty) {
            return response()->json(["error" => "Item is out of stock. Available Qty: $total_credit_de"]);
        }

        $remaining_quantity = $rftQty;
        $my_array = [];

        // Loop through available batches and distribute quantity
        foreach ($batches as $batch) {
            $available_batch = ($batch->quantity - $batch->invoice_qty) + $batch->credit_qty;

            if ($available_batch > 0) {
                if ($remaining_quantity <= $available_batch) {
                    // If remaining quantity fits in this batch
                    $batch_addition = $remaining_quantity;
                    $my_array[] = [
                        'batch' => $batch->batch_num,
                        'qty' => $batch_addition,
                        'exp' => date('Y-m-d', strtotime($batch->exp_date))
                    ];
                    break; // No need to check further batches
                } else {
                    // If remaining quantity exceeds available batch quantity
                    $batch_addition = $available_batch;
                    $remaining_quantity -= $available_batch;
                    $my_array[] = [
                        'batch' => $batch->batch_num,
                        'qty' => $batch_addition,
                        'exp' => date('Y-m-d', strtotime($batch->exp_date))
                    ];
                }
            }
        }
        return $my_array;
    }

    public function toRftBatches($rftId, $itemId, $rftQty)
    {
        $warehouse = auth()->user()->ware_house;

        try {
            $batches = DB::table('rft_batches')
                ->where('rft_number', $rftId)
                ->where('item_code', $itemId)
                ->whereNotNull('from_store')
                ->get();

            $remainingQty = $rftQty;
            $allocatedBatches = [];
            // echo "<pre>"; print_r($batches); exit;

            foreach ($batches as $batch) {
                $usedQty = $remainingQty;

                $my_array[] = [
                    'batch' => $batch->batch_num,
                    'qty' => $usedQty,
                    'exp' => $batch->expiry_date,
                ];
                $remainingQty -= $usedQty;
            }

        return $my_array;

        } catch (\Exception $e) {
            echo "<pre>"; print_r($e->getmessage()); exit;
            Log::error('Error fetching RFT Batches: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching RFT Items.'
            ], 500);
        }
    }

    public function rftBlock($rftNum)
    {
        try {
            $update = DB::table('request_for_tender')
                ->where('rft_number', $rftNum)
                ->update([
                    'cancelled_status' => 0
                ]);

            if ($update) {
                return response()->json(['message' => 'RFT cancelled successfully.'], 200);
            } else {
                return response()->json(['message' => 'RFT not found or already cancelled.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.', 'details' => $e->getMessage()], 500);
        }
    }
}
