<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\CreateInvoiceController;
use Exception;
use PDF;
use Carbon\Carbon;

class BalaxiimportController extends Controller
{
    public $sapDatabase;

    function __construct()
    {
        //$this->sapDatabase = env('BASE_API_URL_DELIVERYSOLUTION_MASTERSETUP');
          $this->url = env("SAP_BASE_URL", "https://10.10.12.10:4300/SAPWebApp_Live/");
          $this->sapDatabase = env("SAP_DATABASE", "ZZZ_AARNEXT_11022025");
    }

    //
    public function fetchPdfData1()
    {
        try {
            $url_server = $this->url."Masters_Setup/Users.xsjs?DBName=TEST_AARNEXT_0809";

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            // $response = Http::withHeaders($headers)->get($url_server);
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            // return ($response->body());
            Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

            $processedUsers = [];

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Decoded JSON Data:', ['data' => $responseData]);

                if (is_array($responseData)) {
                    foreach ($responseData as $user) {
                        $userData = [
                            'user_code' => $user['USER_CODE'] ?? null,
                            'u_name' => $user['U_NAME'] ?? null,
                            'e_email' => $user['E_Mail'] ?? null,
                            'mobile' => $user['Mobile'] ?? null,
                            'defaults' => $user['Defaults'] ?? null,
                            'defaults_nm' => $user['DefaultsNm'] ?? null,
                            'where_house' => $user['Warehouse'] ?? null,
                            'sale_person' => $user['SalePerson'] ?? null,
                            'cash_acct' => $user['CashAcct'] ?? null,
                        ];

                        Log::info('Data to be inserted into balaxi_users:', $userData);

                        DB::table('balaxi_users')->updateOrInsert(
                            ['user_code' => $userData['user_code']],
                            $userData
                        );

                        Log::info('User data inserted or updated:', $userData);

                        $processedUsers[] = $userData;
                    }
                } else {
                    Log::warning('Expected array but received non-array response', ['data' => $responseData]);
                }
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return response()->json([
                'success' => 1,
                'processedUsers' => $processedUsers,
                'message' => 'Users processed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }

    public function warehousedata()
    {
        try {
            $url_server = "https://10.10.12.10:4300/SAPWebApp/Masters_Setup/Warehouses.xsjs?DBName=".$this->sapDatabase ;

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            //  return ($response->body());
            Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

            $processedWarehouse = [];

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Decoded JSON Data:', ['data' => $responseData]);

                if (is_array($responseData)) {
                    foreach ($responseData as $user) {
                        $warehouseData = [
                            'whs_code' => $user['WhsCode'] ?? null,
                            'whs_name' => $user['WhsName'] ?? null,
                            'code' => $user['Code'] ?? null,
                            'location' => $user['Location'] ?? null,
                        ];

                        Log::info('Data to be inserted into balaxi_users:', $warehouseData);

                        DB::table('ware_houses')->updateOrInsert($warehouseData);

                        Log::info('User data inserted or updated:', $warehouseData);

                        $processedWarehouse[] = $warehouseData;
                    }
                } else {
                    Log::warning('Expected array but received non-array response', ['data' => $responseData]);
                }
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return response()->json([
                'success' => 1,
                'processedWarehouse' => $processedWarehouse,
                'message' => 'Users processed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }




public function warehouseBatch()
{
    $get_warehouses = DB::table('ware_houses')->select('*')->where('insert_status', 0)->get();
    
    foreach ($get_warehouses as $key => $warehouse) {

        try {
            $url_server = $this->url . "Masters/GetBatchesWithWhsCode.xsjs?DBName=" . $this->sapDatabase . "&WhsCode=" . $warehouse->whs_code;
            $headers = [];
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $responseData = $response->json();

                foreach ($responseData as $key => $item_qty) {

                    // $params = [
                    //     'item_code' => $item_qty['ItemCode'],
                    //     'item_name' => $item_qty['ItemName'],
                    //     'warehouse_code' => $item_qty['WhsCode'],
                    //     'warehouse_name' => $item_qty['WhsName'],
                    //     'on_hand' => $item_qty['Quantity']
                    // ];

                    // // Check for existing warehouse stock and update/insert accordingly
                    // $check_for_update = DB::table('warehouse_stocks')
                    //     ->where('item_code', $item_qty['ItemCode'])
                    //     ->where('warehouse_code', $item_qty['WhsCode'])
                    //     ->exists();

                    // if ($check_for_update) {
                    //     DB::table('warehouse_stocks')
                    //         ->where('warehouse_code', $item_qty['WhsCode'])
                    //         ->where('item_code', $item_qty['ItemCode'])
                    //         ->update($params);
                    // } else {
                    //     DB::table('warehouse_stocks')->insert($params);
                    // }

            if(isset($item_qty['ItemBatchDetails'])){

                    // Process batches
                    foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {
                        $param_items = [
                            'item_code' => $item_qty['ItemCode'],
                            'whs_code' => $item_qty['WhsCode'],
                            'batch_num' => $batches['BatchNum'],
                            'in_date' => $batches['InDate'],
                            'prd_date' => $batches['PrdDate'],
                            'quantity' => $batches['Quantity'],
                            'exp_date' => $batches['ExpDate'],
                            'status' => $batches['Status']
                        ];

                        // Check for existing batch and update/insert accordingly
                        $check_for_update_batch = DB::table('warehouse_codebatch_details')
                            ->where('item_code', $item_qty['ItemCode'])
                            ->where('whs_code', $item_qty['WhsCode'])
                            ->where('batch_num', $batches['BatchNum'])
                            ->exists();

                        if ($check_for_update_batch) {
                            $param_items['updated_at'] = date('Y-m-d H:i:s');
                            DB::table('warehouse_codebatch_details')
                                ->where('whs_code', $item_qty['WhsCode'])
                                ->where('item_code', $item_qty['ItemCode'])
                                ->where('batch_num', $batches['BatchNum'])
                                ->update($param_items);
                        } else {
                            $param_items['created_at'] = date('Y-m-d H:i:s');
                            DB::table('warehouse_codebatch_details')->insert($param_items);
                        }
                    }
                }


                }
            }
        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            echo $e->getMessage();

            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }
}

public function warehouseBatchdata2()
{
    $get_warehouses = DB::table('ware_houses')->select('*')->where('insert_status', 2)->get();
    
    foreach ($get_warehouses as $key => $warehouse) {

        try {
            $url_server = $this->url . "Masters/GetBatchesWithWhsCode.xsjs?DBName=" . $this->sapDatabase . "&WhsCode=" . $warehouse->whs_code;


            $headers = [];
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $responseData = $response->json();

                foreach ($responseData as $key => $item_qty) {
                    $params = [
                        'item_code' => $item_qty['ItemCode'],
                        'item_name' => $item_qty['ItemName'],
                        'warehouse_code' => $item_qty['WhsCode'],
                        'warehouse_name' => $item_qty['WhsName'],
                        'on_hand' => $item_qty['Quantity']
                    ];

                    // Check for existing warehouse stock and update/insert accordingly
                    $check_for_update = DB::table('warehouse_stocks')
                        ->where('item_code', $item_qty['ItemCode'])
                        ->where('warehouse_code', $item_qty['WhsCode'])
                        ->exists();

                    if ($check_for_update) {
                        DB::table('warehouse_stocks')
                            ->where('warehouse_code', $item_qty['WhsCode'])
                            ->where('item_code', $item_qty['ItemCode'])
                            ->update($params);
                    } else {
                        DB::table('warehouse_stocks')->insert($params);
                    }

            if(isset($item_qty['ItemBatchDetails'])){

                    // Process batches
                    foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {
                        $param_items = [
                            'item_code' => $item_qty['ItemCode'],
                            'whs_code' => $item_qty['WhsCode'],
                            'batch_num' => $batches['BatchNum'],
                            'in_date' => $batches['InDate'],
                            'prd_date' => $batches['PrdDate'],
                            'quantity' => $batches['Quantity'],
                            'exp_date' => $batches['ExpDate'],
                            'status' => $batches['Status']
                        ];

                        // Check for existing batch and update/insert accordingly
                        $check_for_update_batch = DB::table('warehouse_codebatch_details')
                            ->where('item_code', $item_qty['ItemCode'])
                            ->where('whs_code', $item_qty['WhsCode'])
                            ->where('batch_num', $batches['BatchNum'])
                            ->exists();

                        if ($check_for_update_batch) {
                            DB::table('warehouse_codebatch_details')
                                ->where('whs_code', $item_qty['WhsCode'])
                                ->where('item_code', $item_qty['ItemCode'])
                                ->where('batch_num', $batches['BatchNum'])
                                ->update($param_items);
                        } else {
                            DB::table('warehouse_codebatch_details')->insert($param_items);
                        }
                    }
                }


                }
            }
        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            echo $e->getMessage();

            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }
}

public function warehouseBatchdata3()
{
    $get_warehouses = DB::table('ware_houses')->select('*')->where('insert_status', 3)->get();
    
    foreach ($get_warehouses as $key => $warehouse) {

        try {
            $url_server = $this->url . "Masters/GetBatchesWithWhsCode.xsjs?DBName=" . $this->sapDatabase . "&WhsCode=" . $warehouse->whs_code;


            $headers = [];
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $responseData = $response->json();

                foreach ($responseData as $key => $item_qty) {
                    $params = [
                        'item_code' => $item_qty['ItemCode'],
                        'item_name' => $item_qty['ItemName'],
                        'warehouse_code' => $item_qty['WhsCode'],
                        'warehouse_name' => $item_qty['WhsName'],
                        'on_hand' => $item_qty['Quantity']
                    ];

                    // Check for existing warehouse stock and update/insert accordingly
                    $check_for_update = DB::table('warehouse_stocks')
                        ->where('item_code', $item_qty['ItemCode'])
                        ->where('warehouse_code', $item_qty['WhsCode'])
                        ->exists();

                    if ($check_for_update) {
                        DB::table('warehouse_stocks')
                            ->where('warehouse_code', $item_qty['WhsCode'])
                            ->where('item_code', $item_qty['ItemCode'])
                            ->update($params);
                    } else {
                        DB::table('warehouse_stocks')->insert($params);
                    }

            if(isset($item_qty['ItemBatchDetails'])){

                    // Process batches
                    foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {
                        $param_items = [
                            'item_code' => $item_qty['ItemCode'],
                            'whs_code' => $item_qty['WhsCode'],
                            'batch_num' => $batches['BatchNum'],
                            'in_date' => $batches['InDate'],
                            'prd_date' => $batches['PrdDate'],
                            'quantity' => $batches['Quantity'],
                            'exp_date' => $batches['ExpDate'],
                            'status' => $batches['Status']
                        ];

                        // Check for existing batch and update/insert accordingly
                        $check_for_update_batch = DB::table('warehouse_codebatch_details')
                            ->where('item_code', $item_qty['ItemCode'])
                            ->where('whs_code', $item_qty['WhsCode'])
                            ->where('batch_num', $batches['BatchNum'])
                            ->exists();

                        if ($check_for_update_batch) {
                            DB::table('warehouse_codebatch_details')
                                ->where('whs_code', $item_qty['WhsCode'])
                                ->where('item_code', $item_qty['ItemCode'])
                                ->where('batch_num', $batches['BatchNum'])
                                ->update($param_items);
                        } else {
                            DB::table('warehouse_codebatch_details')->insert($param_items);
                        }
                    }
                }


                }
            }
        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            echo $e->getMessage();

            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }
}
public function warehouseBatchdata4()
{
    $get_warehouses = DB::table('ware_houses')->select('*')->where('insert_status', 4)->get();
    
    foreach ($get_warehouses as $key => $warehouse) {

        try {
            $url_server = $this->url . "Masters/GetBatchesWithWhsCode.xsjs?DBName=" . $this->sapDatabase . "&WhsCode=" . $warehouse->whs_code;


            $headers = [];
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $responseData = $response->json();

                foreach ($responseData as $key => $item_qty) {
                    $params = [
                        'item_code' => $item_qty['ItemCode'],
                        'item_name' => $item_qty['ItemName'],
                        'warehouse_code' => $item_qty['WhsCode'],
                        'warehouse_name' => $item_qty['WhsName'],
                        'on_hand' => $item_qty['Quantity']
                    ];

                    // Check for existing warehouse stock and update/insert accordingly
                    $check_for_update = DB::table('warehouse_stocks')
                        ->where('item_code', $item_qty['ItemCode'])
                        ->where('warehouse_code', $item_qty['WhsCode'])
                        ->exists();

                    if ($check_for_update) {
                        DB::table('warehouse_stocks')
                            ->where('warehouse_code', $item_qty['WhsCode'])
                            ->where('item_code', $item_qty['ItemCode'])
                            ->update($params);
                    } else {
                        DB::table('warehouse_stocks')->insert($params);
                    }

            if(isset($item_qty['ItemBatchDetails'])){

                    // Process batches
                    foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {
                        $param_items = [
                            'item_code' => $item_qty['ItemCode'],
                            'whs_code' => $item_qty['WhsCode'],
                            'batch_num' => $batches['BatchNum'],
                            'in_date' => $batches['InDate'],
                            'prd_date' => $batches['PrdDate'],
                            'quantity' => $batches['Quantity'],
                            'exp_date' => $batches['ExpDate'],
                            'status' => $batches['Status']
                        ];

                        // Check for existing batch and update/insert accordingly
                        $check_for_update_batch = DB::table('warehouse_codebatch_details')
                            ->where('item_code', $item_qty['ItemCode'])
                            ->where('whs_code', $item_qty['WhsCode'])
                            ->where('batch_num', $batches['BatchNum'])
                            ->exists();

                        if ($check_for_update_batch) {
                            DB::table('warehouse_codebatch_details')
                                ->where('whs_code', $item_qty['WhsCode'])
                                ->where('item_code', $item_qty['ItemCode'])
                                ->where('batch_num', $batches['BatchNum'])
                                ->update($param_items);
                        } else {
                            DB::table('warehouse_codebatch_details')->insert($param_items);
                        }
                    }
                }


                }
            }
        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            echo $e->getMessage();

            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }
}
public function warehouseBatchdata5()
{
    $get_warehouses = DB::table('ware_houses')->select('*')->where('insert_status', 5)->get();
    
    foreach ($get_warehouses as $key => $warehouse) {

        try {
            $url_server = $this->url . "Masters/GetBatchesWithWhsCode.xsjs?DBName=" . $this->sapDatabase . "&WhsCode=" . $warehouse->whs_code;


            $headers = [];
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $responseData = $response->json();

                foreach ($responseData as $key => $item_qty) {
                    $params = [
                        'item_code' => $item_qty['ItemCode'],
                        'item_name' => $item_qty['ItemName'],
                        'warehouse_code' => $item_qty['WhsCode'],
                        'warehouse_name' => $item_qty['WhsName'],
                        'on_hand' => $item_qty['Quantity']
                    ];

                    // Check for existing warehouse stock and update/insert accordingly
                    $check_for_update = DB::table('warehouse_stocks')
                        ->where('item_code', $item_qty['ItemCode'])
                        ->where('warehouse_code', $item_qty['WhsCode'])
                        ->exists();

                    if ($check_for_update) {
                        DB::table('warehouse_stocks')
                            ->where('warehouse_code', $item_qty['WhsCode'])
                            ->where('item_code', $item_qty['ItemCode'])
                            ->update($params);
                    } else {
                        DB::table('warehouse_stocks')->insert($params);
                    }

            if(isset($item_qty['ItemBatchDetails'])){

                    // Process batches
                    foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {
                        $param_items = [
                            'item_code' => $item_qty['ItemCode'],
                            'whs_code' => $item_qty['WhsCode'],
                            'batch_num' => $batches['BatchNum'],
                            'in_date' => $batches['InDate'],
                            'prd_date' => $batches['PrdDate'],
                            'quantity' => $batches['Quantity'],
                            'exp_date' => $batches['ExpDate'],
                            'status' => $batches['Status']
                        ];

                        // Check for existing batch and update/insert accordingly
                        $check_for_update_batch = DB::table('warehouse_codebatch_details')
                            ->where('item_code', $item_qty['ItemCode'])
                            ->where('whs_code', $item_qty['WhsCode'])
                            ->where('batch_num', $batches['BatchNum'])
                            ->exists();

                        if ($check_for_update_batch) {
                            DB::table('warehouse_codebatch_details')
                                ->where('whs_code', $item_qty['WhsCode'])
                                ->where('item_code', $item_qty['ItemCode'])
                                ->where('batch_num', $batches['BatchNum'])
                                ->update($param_items);
                        } else {
                            DB::table('warehouse_codebatch_details')->insert($param_items);
                        }
                    }
                }


                }
            }
        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            echo $e->getMessage();

            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }
}

// public function warehouseBatch()
// {
//     $get_warehouses = DB::table('ware_houses')->select('*')->where('insert_status', 0)->get();
    
//     foreach ($get_warehouses as $key => $warehouse) {

//         try {
//             $url_server = $this->url . "Masters/GetBatchesWithWhsCode.xsjs?DBName=" . $this->sapDatabase . "&WhsCode=" . $warehouse->whs_code;


//             $headers = [];
//             $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

//             if ($response->successful()) {
//                 $responseData = $response->json();

//                 foreach ($responseData as $key => $item_qty) {
//                     $params = [
//                         'item_code' => $item_qty['ItemCode'],
//                         'item_name' => $item_qty['ItemName'],
//                         'warehouse_code' => $item_qty['WhsCode'],
//                         'warehouse_name' => $item_qty['WhsName'],
//                         'on_hand' => $item_qty['Quantity']
//                     ];

//                     // Check for existing warehouse stock and update/insert accordingly
//                     $check_for_update = DB::table('warehouse_stocks')
//                         ->where('item_code', $item_qty['ItemCode'])
//                         ->where('warehouse_code', $item_qty['WhsCode'])
//                         ->exists();

//                     if ($check_for_update) {
//                         DB::table('warehouse_stocks')
//                             ->where('warehouse_code', $item_qty['WhsCode'])
//                             ->where('item_code', $item_qty['ItemCode'])
//                             ->update($params);
//                     } else {
//                         DB::table('warehouse_stocks')->insert($params);
//                     }

//                     // Process batches
//                     foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {
//                         $param_items = [
//                             'item_code' => $item_qty['ItemCode'],
//                             'whs_code' => $item_qty['WhsCode'],
//                             'batch_num' => $batches['BatchNum'],
//                             'in_date' => $batches['InDate'],
//                             'prd_date' => $batches['PrdDate'],
//                             'quantity' => $batches['Quantity'],
//                             'exp_date' => $batches['ExpDate'],
//                             'status' => $batches['Status']
//                         ];

//                         // Check for existing batch and update/insert accordingly
//                         $check_for_update_batch = DB::table('warehouse_codebatch_details')
//                             ->where('item_code', $item_qty['ItemCode'])
//                             ->where('whs_code', $item_qty['WhsCode'])
//                             ->where('batch_num', $batches['BatchNum'])
//                             ->exists();

//                         if ($check_for_update_batch) {
//                             DB::table('warehouse_codebatch_details')
//                                 ->where('whs_code', $item_qty['WhsCode'])
//                                 ->where('item_code', $item_qty['ItemCode'])
//                                 ->where('batch_num', $batches['BatchNum'])
//                                 ->update($param_items);
//                         } else {
//                             DB::table('warehouse_codebatch_details')->insert($param_items);
//                         }
//                     }
//                 }
//             }
//         } catch (\Exception $e) {
//             Log::error('HTTP request failed: ' . $e->getMessage());
//             return response()->json([
//                 'success' => 0,
//                 'message' => 'Failed to connect to the server!'
//             ], 500);
//         }
//     }
// }

public function warehouseBatchExec2()
{
    $get_warehouses = DB::table('ware_houses')->select('*')->where('insert_status', 2)->get();
    
    foreach ($get_warehouses as $key => $warehouse) {

        try {
            $url_server = $this->url . "Masters/GetBatchesWithWhsCode.xsjs?DBName=" . $this->sapDatabase . "&WhsCode=" . $warehouse->whs_code;


            $headers = [];
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $responseData = $response->json();

                foreach ($responseData as $key => $item_qty) {
                    $params = [
                        'item_code' => $item_qty['ItemCode'],
                        'item_name' => $item_qty['ItemName'],
                        'warehouse_code' => $item_qty['WhsCode'],
                        'warehouse_name' => $item_qty['WhsName'],
                        'on_hand' => $item_qty['Quantity']
                    ];

                    // Check for existing warehouse stock and update/insert accordingly
                    $check_for_update = DB::table('warehouse_stocks')
                        ->where('item_code', $item_qty['ItemCode'])
                        ->where('warehouse_code', $item_qty['WhsCode'])
                        ->exists();

                    if ($check_for_update) {
                        DB::table('warehouse_stocks')
                            ->where('warehouse_code', $item_qty['WhsCode'])
                            ->where('item_code', $item_qty['ItemCode'])
                            ->update($params);
                    } else {
                        DB::table('warehouse_stocks')->insert($params);
                    }

                    // Process batches
                    foreach ($item_qty['ItemBatchDetails'] as $key => $batches) {
                        $param_items = [
                            'item_code' => $item_qty['ItemCode'],
                            'whs_code' => $item_qty['WhsCode'],
                            'batch_num' => $batches['BatchNum'],
                            'in_date' => $batches['InDate'],
                            'prd_date' => $batches['PrdDate'],
                            'quantity' => $batches['Quantity'],
                            'exp_date' => $batches['ExpDate'],
                            'status' => $batches['Status']
                        ];

                        // Check for existing batch and update/insert accordingly
                        $check_for_update_batch = DB::table('warehouse_codebatch_details')
                            ->where('item_code', $item_qty['ItemCode'])
                            ->where('whs_code', $item_qty['WhsCode'])
                            ->where('batch_num', $batches['BatchNum'])
                            ->exists();

                        if ($check_for_update_batch) {
                            DB::table('warehouse_codebatch_details')
                                ->where('whs_code', $item_qty['WhsCode'])
                                ->where('item_code', $item_qty['ItemCode'])
                                ->where('batch_num', $batches['BatchNum'])
                                ->update($param_items);
                        } else {
                            DB::table('warehouse_codebatch_details')->insert($param_items);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }
}




        // public function warehouseBatch(){
        //     try {
        //         echo"Hello";
        //         $items_data = DB::table('items')
        //                             ->select('*')
        //                             ->get();
        //     foreach ($items_data as $key => $item) {
        //            $item_code = $item->item_code;
        //            $url_server = "https://10.10.12.10:4300/SAPWebApp/Masters/GetBatchDetailsWithItem.xsjs?DBName=ZTEST_AARNEXT090125&ItemCode=".$item_code;
        //                 $headers = []; 

        //                 $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

        //                 Log::info('Raw HTTP Response for Warehouse Data:', ['response' => $response->body()]);

        //                 $processedWarehouse = [];

        //     if ($response->successful()) {
        //             $responseData = $response->json();
        //            // echo "<pre>"; print_r($responseData); exit;
                  
        //           //  echo "TEst".sizeof($responseData); exit;

        //             for ($i=0; $i < 5; $i++) {
                            
        //                 echo "suresh".$i;
        //                 $params = array(
        //                     'item_code' => $responseData[$i]['ItemCode'],
        //                     'item_name' => $responseData[$i]['ItemName'],
        //                     'warehouse_code' => $responseData[$i]['WhsCode'],
        //                     'warehouse_name' => $responseData[$i]['WhsName'],
        //                     'on_hand' => $responseData[$i]['Quantity']
        //                 );
        //                 echo "<pre>"; print_r($params);
               
        //           //  $db_data = DB::table('warehouse_stocks')->insert($params);
                   
        //             for ($j=0; $j < $responseData[$i]['ItemBatchDetails']; $j++) { 

        //               //  $responseData[$i]['ItemBatchDetails'][$j]['Quantity'];

        //                 $param_items = array(
        //                     'item_code' => $responseData[$i]['ItemCode'],
        //                     'whs_code'  => $responseData[$i]['WhsCode'],
        //                     'batch_num' => $responseData[$i]['ItemBatchDetails'][$j]['BatchNum'],
        //                     'in_date' => $responseData[$i]['ItemBatchDetails'][$j]['InDate'],
        //                     'prd_date' => $responseData[$i]['ItemBatchDetails'][$j]['PrdDate'],
        //                     'quantity' => $responseData[$i]['ItemBatchDetails'][$j]['Quantity'],
        //                     'exp_date' => $responseData[$i]['ItemBatchDetails'][$j]['ExpDate'],
        //                     'status'   => $responseData[$i]['ItemBatchDetails'][$j]['Status']  
        //                 );
        //                 echo "<pre>"; print_r($param_items);
        //           //  $insert_item_batches = DB::table('warehouse_codebatch_details')->insert($param_items);
                       
        //             }
        //                 }
                            
                            
        //                 } 
        //                // echo "<pre>"; print_r($responseData); exit;
        //         }

        //     } catch (\Exception $e) {
        //         Log::error('HTTP request failed: ' . $e->getMessage());
        //         print_r($e->getMessage());
        //         return response()->json([
        //             'success' => 0,
        //             'message' => 'Failed to connect to the server!'
        //         ], 500);
        //     }
        // }

      
    //    public function warehouseBatchDetails()
    //{
    //    try {
    //        $url_server = "https://10.10.12.10:4300/SAPWebApp/Masters/GetBatchDetails.xsjs?DBName=TEST_AARNEXT_2909";

    //        //$headers = [
    //        //    'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
    //        //    'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
    //        //];

    //        $response = Http::withoutVerifying()->get($url_server);

    //        Log::info('Raw HTTP Response for Batch Data:', ['response' => $response->body()]);

    //        $processedWarehouse = [];

    //        if ($response->successful()) {
    //            $responseData = $response->json();

    //            Log::info('Decoded JSON Data:', ['data' => $responseData]);

    //            if (is_array($responseData)) {
    //                foreach ($responseData as $item) {
    //                    $commonData = [
    //                        'item_code' => $item['ItemCode'] ?? null,
    //                        'item_name' => $item['ItemName'] ?? null,
    //                        'whs_code' => $item['WhsCode'] ?? null,
    //                        'whs_name' => $item['WhsName'] ?? null,
    //                        'bpl_name' => $item['BPLName'] ?? null,
    //                        'quantity' => $item['Quantity'] ?? null,
    //                        'invntry_uom' => $item['InvntryUom'] ?? null,
    //                    ];

    //                    if (!empty($item['ItemBatchDetails']) && is_array($item['ItemBatchDetails'])) {
    //                        foreach ($item['ItemBatchDetails'] as $batchDetail) {
    //                            $batchData = [
    //                                'batch_num' => $batchDetail['BatchNum'] ?? null,
    //                                'in_date' => $batchDetail['InDate'] ?? null,
    //                                'prd_date' => $batchDetail['PrdDate'] ?? null,
    //                                'batch_quantity' => $batchDetail['Quantity'] ?? null,
    //                                'exp_date' => $batchDetail['ExpDate'] ?? null,
    //                                'status' => $batchDetail['Status'] ?? null,
    //                            ];

    //                            // Combine common data with batch-specific data
    //                            $warehouseData = array_merge($commonData, $batchData);

    //                            Log::info('Data to be inserted into warehouse_codebatch_details:', $warehouseData);

    //                            DB::table('warehouse_codebatch_details')->updateOrInsert(
    //                                ['item_code' => $warehouseData['item_code'], 'batch_num' => $warehouseData['batch_num']], // Unique keys for update
    //                                $warehouseData // Data to insert or update
    //                            );

    //                            Log::info('Data inserted or updated:', $warehouseData);

    //                            $processedWarehouse[] = $warehouseData;
    //                        }
    //                    } else {
    //                        Log::warning('ItemBatchDetails missing or not an array', ['item' => $item]);
    //                    }
    //                }
    //            } else {
    //                Log::warning('Expected array but received non-array response', ['data' => $responseData]);
    //            }
    //        } else {
    //            Log::error('Failed to fetch data from the server', [
    //                'status' => $response->status(),
    //                'body' => $response->body()
    //            ]);
    //        }

    //        return response()->json([
    //            'success' => 1,
    //            'processedWarehouse' => $processedWarehouse,
    //            'message' => 'Warehouse batch details processed successfully!'
    //        ]);

    //    } catch (\Exception $e) {
    //        Log::error('HTTP request failed: ' . $e->getMessage());
    //        return response()->json([
    //            'success' => 0,
    //            'message' => 'Failed to connect to the server!'
    //        ], 500);
    //    }
    //}


    public function bpGroupdata()
    {
        try {
            $url_server = "https://10.10.12.10:4300/SAPWebApp/Masters_Setup/BPGroup.xsjs?DBName=TEST_AARNEXT_0809";

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            // return ($response->body());
            Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

            $processedgroupData = [];

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Decoded JSON Data:', ['data' => $responseData]);

                if (is_array($responseData)) {
                    foreach ($responseData as $user) {
                        $bpGroupData = [
                            'code' => $user['GroupCode'] ?? null,
                            'name' => $user['GroupName'] ?? null,
                            'type' => $user['GroupType'] ?? null,
                            'type_nm' => $user['GroupTypNm'] ?? null,
                        ];

                        Log::info('Data to be inserted into balaxi_users:', $bpGroupData);

                        DB::table('bp_groups')->updateOrInsert($bpGroupData);

                        Log::info('User data inserted or updated:', $bpGroupData);

                        $processedgroupData[] = $bpGroupData;
                    }
                } else {
                    Log::warning('Expected array but received non-array response', ['data' => $responseData]);
                }
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return response()->json([
                'success' => 1,
                'processedgroupData' => $processedgroupData,
                'message' => 'BPGroupData posted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }

    public function Itemgroupdata()
    {
        try {
            $url_server = "https://10.10.12.10:4300/SAPWebApp/Masters_Setup/ItemGroups.xsjs?DBName=".$this->sapDatabase ;

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            //  return ($response->body());
            Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

            $ItemgroupData = [];

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Decoded JSON Data:', ['data' => $responseData]);

                if (is_array($responseData)) {
                    foreach ($responseData as $user) {
                        $ItemgroupData = [
                            'code' => $user['ItmsGrpCod'] ?? null,
                            'name' => $user['ItmsGrpNam'] ?? null,
                        ];

                        Log::info('Data to be inserted into balaxi_users:', $ItemgroupData);

                        DB::table('item_groups')->updateOrInsert($ItemgroupData);

                        Log::info('User data inserted or updated:', $ItemgroupData);

                        $ItemgroupData[] = $ItemgroupData;
                    }
                } else {
                    Log::warning('Expected array but received non-array response', ['data' => $responseData]);
                }
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return response()->json([
                'success' => 1,
                'ItemgroupData' => $ItemgroupData,
                'message' => 'ItemgroupData posted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }

    public function ItemsData()
    {
        try {
           // echo "AGN Item Test";

          // $my_url = "https://uat-ibdms.advantagetvs.com/erp/api/CheckReachability";

          // //$response = Http::withHeaders($headers)->withoutVerifying()->get($my_url);
          // $response = Http::withoutVerifying()->get($my_url);

          // echo "<pre>"; print_r($response);

            $url_server = $this->url."/Masters/GetItems.xsjs?DBName=".$this->sapDatabase."&UpdateDate=20180101";

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            // $response = Http::withHeaders($headers)->get($url_server);
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            // return ($response->body());
           // Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

            $ItemData = [];

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Decoded JSON Data:', ['data' => $responseData]);

                if (is_array($responseData)) {
                    foreach ($responseData as $user) {
                        $ItemsData = [
                            'item_code' => $user['ItemCode'] ?? null,
                            'item_name' => $user['ItemName'] ?? null,
                            'frgn_name' => $user['FrgnName'] ?? null,
                            'items_grp_code' => $user['ItmsGrpCod'] ?? null,
                            'items_grp_name' => $user['ItmsGrpNam'] ?? null,
                            'frim_code' => $user['FirmCode'] ?? null,
                            'manufacturer' => $user['Manufacturer'] ?? null,
                            'code' => $user['Code'] ?? null,
                            'sale_pack' => $user['SalePack'] ?? null,
                            'cabinda_iva' => $user['Cabinda_iVA'] ?? null,
                            'others_iva' => $user['Others_iVA'] ?? null,
                            'valid_for' => $user['validFor'] ?? null,
                            'frozen_for' => $user['frozenFor'] ?? null,
                            'create_date' => $user['CreateDate'] ?? null,
                            'create_ts' => $user['CreateTS'] ?? null,
                            'update_date' => $user['UpdateDate'] ?? null,
                            'update_ts' => $user['UpdateTS'] ?? null,
                            'ManBtchNum'  => $user['ManBtchNum'] ?? null
                        ];

                    //    Log::info('Data to be inserted into balaxi_users:', $ItemsData);



                    $insert_update = DB::table('items')->updateOrInsert(
                                ['item_code' => $user['ItemCode']],  // Condition to check
                                $ItemsData  // Data to insert or update
                            );

                     //   Log::info('User data inserted or updated:', $ItemsData);

                       // $ItemData[] = $ItemsData;
                    }
                } else {
                    Log::warning('Expected array but received non-array response', ['data' => $responseData]);
                }
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return response()->json([
                'success' => 1,
              //  'ItemsData' => $ItemsData,
                'message' => 'Users processed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            print_r($e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }

    public function PaymentTerms()
    {
        try {
            $url_server = "https://10.10.12.10:4300/SAPWebApp/Masters_Setup/PaymentTerms.xsjs?DBName=ZZZ_AARNEXT_N0901";

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            // $response = Http::withHeaders($headers)->get($url_server);
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            // return ($response->body());
            Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

            $processedPayments = [];

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Decoded JSON Data:', ['data' => $responseData]);

                if (is_array($responseData)) {
                    foreach ($responseData as $user) {
                        $paymentData = [
                            'group_num' => $user['GroupNum'] ?? null,
                            'payment_group' => $user['PymntGroup'] ?? null,
                            'extra_month' => $user['ExtraMonth'] ?? null,
                            'extra_days' => $user['ExtraDays'] ?? null,
                        ];

                        Log::info('Data to be inserted into balaxi_users:', $paymentData);

                        DB::table('payment_terms')->updateOrInsert($paymentData);

                        Log::info('User data inserted or updated:', $paymentData);

                        $processedPayments[] = $paymentData;
                    }
                } else {
                    Log::warning('Expected array but received non-array response', ['data' => $responseData]);
                }
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return response()->json([
                'success' => 1,
                'processedPayments' => $processedPayments,
                'message' => 'Users processed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }

    public function salesEmployee()
    {
        try {
            $url_server = "https://10.10.12.10:4300/SAPWebApp/Masters_Setup/SalesEmployee.xsjs?DBName=ZZZ_AARNEXT_N0901";

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            // $response = Http::withHeaders($headers)->get($url_server);
            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            //  return ($response->body());
            Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

            $salesemployeeData = [];

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Decoded JSON Data:', ['data' => $responseData]);

                if (is_array($responseData)) {
                    foreach ($responseData as $user) {
                        $salesData = [
                            'code' => $user['SlpCode'] ?? null,
                            'name' => $user['SlpName'] ?? null,
                            'telephone' => $user['Telephone'] ?? null,
                            'mobile' => $user['Mobil'] ?? null,
                            'email' => $user['Email'] ?? null,
                            'memo' => $user['Memo'] ?? null,
                            'active' => $user['Active'] ?? null,

                        ];

                        Log::info('Data to be inserted into balaxi_users:', $salesData);

                        DB::table('sales_employees')->updateOrInsert($salesData);

                        Log::info('User data inserted or updated:', $salesData);

                        $salesemployeeData[] = $salesData;
                    }
                } else {
                    Log::warning('Expected array but received non-array response', ['data' => $responseData]);
                }
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return response()->json([
                'success' => 1,
                'salesemployeeData' => $salesemployeeData,
                'message' => 'Users processed successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }

    public function customers()
    {
        try {

            $url_server = $this->url."Masters/GetCustomers.xsjs?DBName=".$this->sapDatabase."&UpdateDate=20250210";

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

         //   Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

           // $customersData = [];

            if ($response->successful()) {
                $responseData = $response->json();

                //Log::info('Decoded JSON Data:', ['data' => $responseData]);
            //   echo "<pre>"; print_r($responseData); exit;

                if (is_array($responseData)) {
                    foreach ($responseData as $customer) {
                        $customerimport = [
                            
                            'series' => $customer['Series'] ?? null,       
                            'card_code' => $customer['CardCode'] ?? null,
                            'card_name' => $customer['CardName'] ?? null,
                            'card_fname' => $customer['CardFName'] ?? null,
                            'alias_name' => $customer['AliasName'] ?? null,
                            'card_typ_nm' => $customer['CardTypNm'] ?? null,
                            'currency' => $customer['Currency'] ?? null,
                            'grp_code' => $customer['GroupCode'] ?? null,
                            'grp_name' => $customer['GrpName'] ?? null,
                            'phone1' => $customer['Phone1'] ?? null,
                            'phone2' => $customer['Phone2'] ?? null,
                            'cellular' => $customer['Cellular'] ?? null,
                            'email' => $customer['E_Mail'] ?? null,
                            'grp_num' => $customer['GroupNum'] ?? null,
                            'credit_line' => $customer['CreditLine'] ?? null,
                            'list_num' => $customer['ListNum'] ?? null,
                            'product_list' => $customer['PriceList'] ?? null,
                            'slp_code' => $customer['SlpCode'] ?? null,
                            'sal_emp_name' => $customer['SalEmpNam'] ?? null,
                            'pymnt_grp' => $customer['PymntGroup'] ?? null,
                            'deb_pay_acct' => $customer['DebPayAcct'] ?? null,
                            'deb_pay_name' => $customer['DebPayNam'] ?? null,
                            'valid_for' => $customer['validFor'] ?? null,
                            'frozen_for' => $customer['frozenFor'] ?? null,
                            'sub_grp' => $customer['SubGroup'] ?? null,
                            'nif_no' => $customer['NIFNo'] ?? null,
                            'store_location' => $customer['StoreLocation'] ?? null,
                            'sales_commission' => $customer['SalesCommission'] ?? null,
                            'hike_percent' => $customer['HikePercent'] ?? null,
                            'create_date' => $customer['CreateDate'] ?? null,
                            'create_ts' => $customer['CreateTS'] ?? null,
                            'update_date' => $customer['UpdateDate'] ?? null,
                            'update_ts' => $customer['UpdateTS'] ?? null,
                        ];

                       // Log::info('Data to be inserted into sales_employees:', $customerimport);


                    $insert_update = DB::table('customers')->updateOrInsert(
                                ['card_code' => $customer['CardCode']],  // Condition to check
                                $customerimport  // Data to insert or update
                            );



                       // Log::info('Sales employee data inserted or updated:', $customerimport);

                       // $customersData[] = $customerimport;
                    }
                } else {
                    Log::warning('Expected array but received non-array response', ['data' => $responseData]);
                }
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }

            return response()->json([
                'success' => 1,
               // 'customersData' => $customersData,
                'message' => ' processed successfully!'
            ]);

        } catch (\Exception $e) {
           // Log::error('HTTP request failed: ' . $e->getMessage());

            echo "<pre>"; print_r($e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }

    public function CustomerAddresses()
    {

        $url_server = $this->url."Masters/GetCustomerAddress.xsjs?DBName=".$this->sapDatabase;

        $headers = [
            'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
            'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
        ];

        try{

        $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {

                $responseData = $response->json();
                foreach ($responseData as $key => $address) {

                        $params = array(
                            'address_type'      => $address['AdresType'],
                            'address'           => $address['Address'],
                            'card_code'         => $address['CardCode'],
                            'building'          => $address['Building'],
                            'block'             => $address['Block'],
                            'zip_code'          => $address['ZipCode'],
                            'city'              => $address['City'],
                            'country'           => $address['Country'],
                            'street'            => $address['Street'],
                            'landmark'          => $address['LandMark'],
                            'country_code'      => $address['CountryCode'],
                            'state'             => $address['State'],
                            'state_code'        => $address['StateCode'],
                            'gst_type'          => $address['GSTType'],
                            'gst_regn_no'       => $address['GSTRegnNo'],
                            'sap_created_date'  => $address['CreateDate'],
                            'CreateTS'          => $address['CreateTS'],
                            'created_at'        => date('Y-m-d H:i:s')  // or $address->CreateDate if this is also the same
                        );

                        $insert_address = DB::table('customer_addresses')->insertGetId($params);                   

                    
                }

        }

    }catch (\Exception $e) {
         echo "<pre>"; print_r($e->getMessage());
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }


  }
    public function ItemPriceses()
    {
        try {
            $url_server = $this->url . "Masters/GetItemPrices.xsjs?DBName=" . $this->sapDatabase . "&UpdatedDate=20250221";
            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $responseData = $response->json();
                $batchData = [];
                $chunkSize = 1000; // Number of records per batch

                DB::transaction(function () use ($responseData, $chunkSize) {
                    if (is_array($responseData)) {
                        foreach ($responseData as $user) {
                            $batchData[] = [
                                'item_code'   => $user['ItemCode'] ?? null,
                                'item_name'   => $user['ItemName'] ?? null,
                                'price_list'  => $user['PriceList'] ?? null,
                                'list_name'   => $user['ListName'] ?? null,
                                'currency'    => $user['Currency'] ?? null,
                                'price'       => $user['Price'] ?? null,
                            ];

                            if (count($batchData) >= $chunkSize) {
                                DB::table('item_priceses')->upsert($batchData, ['item_code', 'price_list', 'list_name'], ['item_name', 'currency', 'price']);
                                $batchData = []; // Clear after batch insert
                            }
                        }

                        // Insert remaining data
                        if (!empty($batchData)) {
                            DB::table('item_priceses')->upsert($batchData, ['item_code', 'price_list', 'list_name'], ['item_name', 'currency', 'price']);
                        }
                    }
                });

                return response()->json([
                    'success' => 1,
                    'message' => 'Item prices processed successfully!'
                ]);
            } else {
                Log::error('Failed to fetch data from the server', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Failed to connect to the server!'
            ], 500);
        }
    }

//     public function ItemPriceses()
//     {
//     try {


//         $url_server = $this->url."Masters/GetItemPrices.xsjs?DBName=".$this->sapDatabase."&UpdatedDate=20250221";

//         $headers = [
//             'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
//             'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
//         ];

//         $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

//         //  return ($response->body());
//        // Log::info('Raw HTTP Response for User Data:', ['response' => $response->body()]);

//         //$ItempriceseData = [];

//         if ($response->successful()) {
//             $responseData = $response->json();

//           //  Log::info('Decoded JSON Data:', ['data' => $responseData]);

//             if (is_array($responseData)) {
//                 foreach ($responseData as $user) {
//                     $pricesesData = [
//                         'item_code'     => $user['ItemCode'] ?? null,
//                         'item_name'        => $user['ItemName'] ?? null,
//                         'price_list'       => $user['PriceList'] ?? null,
//                         'list_name'        => $user['ListName'] ?? null,
//                         'currency'     => $user['Currency'] ?? null,
//                         'price'        => $user['Price'] ?? null,
//                     ];

//                    // Log::info('Data to be inserted into balaxi_users:', $pricesesData);
//             $insert_update = DB::table('item_priceses')->updateOrInsert(
//                         ['item_code' => $user['ItemCode'],'price_list' =>$user['PriceList'],'list_name' =>$user['ListName']],  // Condition to check
//                         $pricesesData  // Data to insert or update
//                     );
     

//                     //DB::table('item_priceses')->updateOrInsert($pricesesData);

//                  //   Log::info('User data inserted or updated:', $pricesesData);

//                     //$ItempriceseData[] = $pricesesData;
//                 }
//             } else {
//                 Log::warning('Expected array but received non-array response', ['data' => $responseData]);
//             }
//         } else {
//             Log::error('Failed to fetch data from the server', [
//                 'status' => $response->status(),
//                 'body' => $response->body()
//             ]);
//         }

//         return response()->json([
//             'success' => 1,
//             //'ItempriceseData' => $ItempriceseData,
//             'message' => 'Users processed successfully!'
//         ]);

//     } catch (\Exception $e) {
//         Log::error('HTTP request failed: ' . $e->getMessage());
//         return response()->json([
//             'success' => 0,
//             'message' => 'Failed to connect to the server!'
//         ], 500);
//     } 
// }
//  public function SapInvoicePostByCron()
//     {

//         try {
//             $invoice = DB::table('invoices')
//                         ->where('sap_updated',0)
//                         ->where('id',70)
//                         ->first();

//         //   echo "<pre>"; print_r($invoice); exit;
//            // echo "JAKA".$invoice->id;
//             if (!$invoice) {
//                 return response()->json(['error' => 'Invoice not found'], 404);
//             }
    
//             $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
//             if ($invoiceItems->isEmpty()) {
//                 return response()->json(['error' => 'No items found for this invoice'], 404);
//             }
//             $uniqueNumber = $invoice->id . "_" . date('YmdHis', strtotime($invoice->order_date)) . "_" . md5(time() + rand(1, 999999));

//            if(!$invoice->due_date){
//               $dueDate = date("ymd", strtotime($invoice->due_date));
//            }else{
//               $dueDate = date("ymd", strtotime($invoice->order_date));
//            }
            
//             $OrderDate = date("ymd", strtotime($invoice->order_date));

//             $user_data = DB::table('users')->select('*')->where('id',$invoice->user_id)->first();
//             $sales_person = DB::table('sales_employees')->select('code')->where('id',$invoice->sales_emp_id)->first();

//             $sequnce = '';
//             if($invoice->sales_type == 'Hospital')
//             {
//               $sequnce = $user_data->inv_hospital_seq;
//             }else{
//              $sequnce = $user_data->inv_otehr_seq;
//             }

// //'Cash' is not a valid value for property 'U_TRPSALETYPE'. The valid values are: 'CASH' - 'CASH', 

//             $sapJson = [
//                 'Series' => $user_data->inv_series,
//                 'CardCode' => $invoice->customer_id,
//                 'U_TRPGUID'=> $uniqueNumber,
//                 'U_TRPSALETYPE' => strtoupper($invoice->sales_type),
//                 'U_ITG_SignDigest' => $invoice->hash_key_code,
//                 'U_ITG_KeyVersion' => 11,
//                 'U_ITG_CertifNum'  => '1.1',
//                 'U_VSPSRN'   => $user_data->inv_series,
//                 'U_VSPSRNO'  => $invoice->id,
//                 'NumAtCard' => $invoice->ref_id,
//                 'DocDate' => $OrderDate,
//                 'DocDueDate' => $dueDate,
//                 'TaxDate' => $OrderDate,
//                 'SequenceCode' => $sequnce,
//                 'BPL_IDAssignedToInvoice' => $user_data->branch,
//                 'SalesPersonCode' => $sales_person->code
//             ];
    
//             // Prepare document lines
//             $documentLinesAll = [];
//             foreach ($invoiceItems as $invoiceItem) {

//             $batches = DB::table('invoice_batches')->select('*')
//                             ->where('invoice_id',$invoice->id)
//                             ->where('item_code',$invoiceItem->item_no)
//                             ->get();

//             $itemBatches = [];
//             foreach ($batches as $batch) {
//                 $itemBatches[] = [
//                     'BatchNumber' => $batch->batch_num, 
//                     'Quantity' => $batch->quantity,
//                 ];
//             }         
              
//                 //dd( $orderItems);
//                 $documentLinesAll[] = [
//                     'ItemCode' => $invoiceItem->item_no,
//                     'ItemDescription' => $invoiceItem->item_desc,
//                     'Quantity' => $invoiceItem->quantity,
//                     'WarehouseCode' => $invoice->store_code,
//                     'PriceBefDi'=> $invoiceItem->price,
//                     'Price' => $invoiceItem->price_af_disc,
//                     'TaxCode'=> "IVA".$invoiceItem->gst_rate,
//                     'BatchNumbers' => $itemBatches
//                 ];

//                 //$sapJson['BatchNumbers'] = $batches;    
                
//             }
           
//             $sapJson['DocumentLines'] = $documentLinesAll;
//            // echo "test documents lines";
//         echo json_encode($sapJson, JSON_PRETTY_PRINT);
//           // exit; 
//             // Define the SAP API URL
//             echo "URL".$url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateInvoiceWithBatch.xsjs?&DBName=".$this->sapDatabase;
    
//             // Send the request to the SAP API
//             $response = Http::withOptions(['verify' => false])
//                 ->post($url, $sapJson);
//        echo "ARARRAA"; exit;
//       // echo "<pre>"; print_r($response);
//             // Handle the response
//             if ($response->successful()) {
//                 $sapResponseData = $response->json();
//                 echo "Data";
//               echo "<pre>"; print_r($sapResponseData);  exit;
//                 //Log::error('successfully Created  : ' . $sapResponseData);
//                if($sapResponseData['status'] != 'Error')
//                {

//                 $sap_doc_entry = $sapResponseData['data']['DocEntry'];
//                 $update_params = array('sap_updated'    => 1,
//                                        'sap_updated_at' => date('Y-m-d H:i:s'),
//                                        'sap_created_id' => $sap_doc_entry
//                                       );

//                 $update_data = DB::table('invoices')
//                                         ->where('id',$invoice->id)
//                                         ->update($update_params);

//                 // Log::error('successfully Created  : ' . $sapResponseData);

//                }else{



//                 $update_params = array('sap_updated'    => 1,
//                                        'sap_updated_at' => date('Y-m-d H:i:s'),
//                                        'sap_error_msg'  => $sapResponseData['data']['error']['message']['value']
//                                       );

//                 $update_data = DB::table('invoices')
//                                         ->where('id',$invoice->id)
//                                         ->update($update_params);


//                }
//                 // return response()->json([
//                 //     'message' => 'Invoice successfully posted to SAP',
//                 //     'sapResponse' => $sapResponseData,
//                 //     'invoice' => $invoice,
//                 //     'invoiceItems' => $invoiceItems
//                 // ], 200);
//             } else {
//                 //Log::error('Error while Cretae Error Code : ' . $response->status());
//                 // return response()->json([
//                 //     'error' => 'Failed to create Invoice in SAP',
//                 //     'details' => $response->body()
//                 // ], $response->status());
//             }
//         } catch (\Exception $e) {

//             echo "<pre>"; print_r($e->getMessage()); exit;
//            Log::error('Error while Cretae Invoices : ' . $e->getMessage());
//             // return response()->json([
//             //     'error' => 'An error occurred while processing the request',
//             //     'details' => $e->getMessage()
//             // ], 500);
//         }
     


//     }

    public function SapCustomerPostByCron() 
    {
        try {
            $customer = DB::table('customers')
                            ->where('is_sap_updated', 0)
                           // ->where('id',8899)
                            ->first();
            if (!$customer) {
                Log::error('No customers found for SAP update');
                return response()->json(['error' => 'No customers found'], 404);
            }
            $addresses = DB::table('customer_addresses')->where('card_code', $customer->card_code)->get();
            $bpAddresses = [];
            foreach ($addresses as $address) {
                  $state_code = DB::table('states')->select('code')->where('id',$address->state)->first();
                if ($address->address_type == 'BillTo') {
                    $bpAddresses[] = [
                        'AddressType' => "B",
                        'AddressName' => $customer->card_name,
                        'BuildingFloorRoom'    => $address->address?? '#',
                        'City'        => $address->city ?? '#',
                        'Country'     => $address->country_code ?? '',
                        'State'       => $state_code->code ?? null
                    ];
                } else {
                    $bpAddresses[] = [
                        'AddressType' => "S",
                        'AddressName' => $customer->card_name,
                        'BuildingFloorRoom'    => $customer->card_name,
                        'City'        => $address->city ?? '#',
                        'Country'     => $address->country_code ?? '',
                        'State'       => $state_code->code ?? null
                    ];
                }
            }
            $sapJson = [
                'CardCode'        => $customer->card_code, 
                'Series'          => $customer->series ?? 1,
                'CardName'        => $customer->card_name, 
                'CardType'        => "cCustomer",
                'U_TRPSUBGRP'     => strtoupper($customer->sub_grp) ?? '',
                'GroupCode'       => $customer->grp_code,
                'Currency'        => $customer->currency ?? "##",
                'SalesPersonCode' => $customer->slp_code ?? "-1",
                'PayTermsGrpCode' => "-1",
                'Phone1'          => $customer->phone1,
                'Cellular'        => $customer->cellular ?? '##',
                'EmailAddress'    => $customer->email,
                'PriceListNum'    => $customer->list_num ?? 1,
                'U_TRPNIF'        => $customer->nif_no,
                'BPAddresses'     => $bpAddresses 
            ];
            //echo"test sapJson "; print_r($sapJson); exit;
            //echo json_encode($sapJson); exit;
            $url = $this->url."/XSJS_SAP/BPMaster.xsjs?DBName=". $this->sapDatabase;
        
            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);
        

            $sapResponseData = json_decode($response->body(), true);

            if (!is_array($sapResponseData)) {


                Log::error('Invalid response from SAP API: ' . $response->body());
                return response()->json(['error' => 'Invalid response from SAP', 'details' => $response->body()], 500);
            }

            if ($response->successful()) {

                DB::table('customers')->where('id', $customer->id)->update([
                    'is_sap_updated' => 1,
                    'sap_updated_at' => now(),
                ]);
                return response()->json(['message' => 'Customer updated successfully in SAP', 'data' =>
                [
                    'sap_response' => $sapResponseData,
                    'customer' => $customer,
                    ]], 200);
            } else {

                DB::table('customers')->where('id', $customer->id)->update([
                    'sap_updated_at' => now(),
                    'sap_error_msg'  => $sapResponseData['data']['error']['message']['value'] ?? 'Unknown error'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error while creating SAP customer: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the request', 'details' => $e->getMessage()], 500);
        } 
    }

    public function SapInvoicePostByWeb($id)
    {

        try {
            // Fetch the invoice
            $invoice = DB::table('invoices')
                        //->where('sap_updated', 0)
                        ->where('id', $id)
                        ->whereNotNull('hash_key')
                        ->first();

            if (!$invoice) {
                Log::error('Invoice not found for SAP update');
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            // Fetch the invoice items
            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                Log::error('No items found for invoice ID ' . $invoice->id);
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the unique number and dates
            $uniqueNumber = $invoice->id . "_" . date('YmdHis', strtotime($invoice->order_date)) . "_" . md5(time() + rand(1, 999999));
            $dueDate = $invoice->due_date ? date("Ymd", strtotime($invoice->due_date)) : date("Ymd", strtotime($invoice->order_date));
            $orderDate = date("Ymd", strtotime($invoice->order_date));

            // Fetch the user and sales person details
            $user_data = DB::table('users')->select('*')->where('id', $invoice->user_id)->first();
            $sales_person = DB::table('sales_employees')->select('code')->where('id', $invoice->sales_emp_id)->first();

            // Prepare sequence code
            $sequence = ($invoice->sales_type == 'Hospital') ? $user_data->inv_hospital_seq : $user_data->inv_otehr_seq;

            // Prepare SAP JSON payload
            $sapJson = [
                'Series' => $user_data->inv_series,
                'CardCode' => $invoice->customer_id,
                'U_TRPGUID'=> $uniqueNumber,
                'U_TRPSALETYPE' => strtoupper($invoice->sales_type),
                'U_ITG_SignDigest' => $invoice->hash_key_code,
                'U_ITG_KeyVersion' => 11,
                'U_ITG_CertifNum'  => '1.1',
                'U_VSPHSKY'        => $invoice->invoice_no,
                'Comments'         => $invoice->remarks,
                'U_VSPSRN'   => $user_data->inv_series,
                'U_VSPSRNO'  => $invoice->id,
                'NumAtCard' => $invoice->ref_id,
                'DocDate' => $orderDate,
                'DocDueDate' => $dueDate,
                'TaxDate' => $orderDate,
                'SequenceCode' => $sequence,
                'BPL_IDAssignedToInvoice' => $user_data->branch,
                'SalesPersonCode' => $sales_person->code
            ];

            // Prepare document lines
            $documentLinesAll = [];
            foreach ($invoiceItems as $invoiceItem) {
                $batches = DB::table('invoice_batches')->where('invoice_id', $invoice->id)->where('item_code', $invoiceItem->item_no)->get();
                $itemBatches = [];

                foreach ($batches as $batch) {
                    $itemBatches[] = [
                        'BatchNumber' => $batch->batch_num, 
                        'Quantity' => $batch->quantity,
                    ];
                }

                $documentLinesAll[] = [
                    'ItemCode' => $invoiceItem->item_no,
                    'ItemDescription' => $invoiceItem->item_desc,
                    'Quantity' => $invoiceItem->quantity,
                    'WarehouseCode' => $invoice->store_code,
                    'PriceBefDi' => $invoiceItem->price,
                    'Price' => $invoiceItem->price_af_disc,
                    'TaxCode' => "IVA" . $invoiceItem->gst_rate,
                    'BatchNumbers' => $itemBatches
                ];
            }

            $sapJson['DocumentLines'] = $documentLinesAll;
            echo json_encode($sapJson, JSON_PRETTY_PRINT);

            // Define the SAP API URL
            $url = $this->url."Sales_Transactions/CreateInvoiceWithBatch.xsjs?&DBName=" . $this->sapDatabase;

            // Send the request to the SAP API
            $response = Http::withOptions(['verify' => false])
                ->post($url, $sapJson);

            // Handle the response
            if ($response->successful()) {
                echo $sapResponseData = $response->json();
                if ($sapResponseData['status'] != 'Error') {
                    $sap_doc_entry = $sapResponseData['data']['DocEntry'];

                    $update_params = [
                        'sap_updated'    => 1,
                        'sap_updated_at' => now(),
                        'sap_created_id' => $sap_doc_entry,
                        'sap_error_msg' => '',
                        'is_updated_err_msg' => 0,
                    ];

                    DB::table('invoices')->where('id', $invoice->id)->update($update_params);

                } else {
                    $update_params = [
                        'sap_updated'    => 1,
                        'sap_updated_at' => now(),
                        'sap_error_msg'  => $sapResponseData['data']['error']['message']['value']
                    ];

                    DB::table('invoices')->where('id', $invoice->id)->update($update_params);
                }
            } else {
                Log::error('Failed to post invoice to SAP. Status Code: ' . $response->status() . ', Response: ' . $response->body());
                return response()->json(['error' => 'Failed to create invoice in SAP', 'details' => $response->body()], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error while creating SAP invoice: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the request', 'details' => $e->getMessage()], 500);
        }
    }

    public function InvoiceSampleJsonPrepare($id)
    {

        try {
            // Fetch the invoice
            $invoice = DB::table('invoices')
                        //->where('sap_updated', 0)
                        ->where('id', $id)
                       // ->whereNotNull('hash_key')
                        ->first();

            if (!$invoice) {
                Log::error('Invoice not found for SAP update');
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            // Fetch the invoice items
            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                Log::error('No items found for invoice ID ' . $invoice->id);
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the unique number and dates
            $uniqueNumber = $invoice->id . "_" . date('YmdHis', strtotime($invoice->order_date)) . "_" . md5(time() + rand(1, 999999));
            $dueDate = $invoice->due_date ? date("Ymd", strtotime($invoice->due_date)) : date("Ymd", strtotime($invoice->order_date));
            $orderDate = date("Ymd", strtotime($invoice->order_date));

            // Fetch the user and sales person details
            $user_data = DB::table('users')->select('*')->where('id', $invoice->user_id)->first();
            $sales_person = DB::table('sales_employees')->select('code')->where('id', $invoice->sales_emp_id)->first();

            // Prepare sequence code
            $sequence = ($invoice->sales_type == 'Hospital') ? $user_data->inv_hospital_seq : $user_data->inv_otehr_seq;

            // Prepare SAP JSON payload
            $sapJson = [
                'Series' => $user_data->inv_series,
                'CardCode' => $invoice->customer_id,
                'U_TRPGUID'=> $uniqueNumber,
                'U_TRPSALETYPE' => strtoupper($invoice->sales_type),
                'U_ITG_SignDigest' => $invoice->hash_key_code,
                'U_ITG_KeyVersion' => 11,
                'U_ITG_CertifNum'  => '1.1',
                'U_VSPHSKY'        => $invoice->invoice_no,
                'Comments'         => $invoice->remarks,
                'U_VSPSRN'   => $user_data->inv_series,
                'U_VSPSRNO'  => $invoice->id,
                'NumAtCard' => $invoice->ref_id,
                'DocDate' => $orderDate,
                'DocDueDate' => $dueDate,
                'TaxDate' => $orderDate,
                'SequenceCode' => $sequence,
                'BPL_IDAssignedToInvoice' => $user_data->branch,
                'SalesPersonCode' => $sales_person->code
            ];

            // Prepare document lines
            $documentLinesAll = [];
            foreach ($invoiceItems as $invoiceItem) {
                $batches = DB::table('invoice_batches')->where('invoice_id', $invoice->id)->where('item_code', $invoiceItem->item_no)->get();
                $itemBatches = [];

                foreach ($batches as $batch) {
                    $itemBatches[] = [
                        'BatchNumber' => $batch->batch_num, 
                        'Quantity' => $batch->quantity,
                    ];
                }

                $documentLinesAll[] = [
                    'ItemCode' => $invoiceItem->item_no,
                    'ItemDescription' => $invoiceItem->item_desc,
                    'Quantity' => $invoiceItem->quantity,
                    'WarehouseCode' => $invoice->store_code,
                    'PriceBefDi' => $invoiceItem->price,
                    'Price' => $invoiceItem->price_af_disc,
                    'TaxCode' => "IVA" . $invoiceItem->gst_rate,
                    'BatchNumbers' => $itemBatches
                ];
            }

            $sapJson['DocumentLines'] = $documentLinesAll;
            echo json_encode($sapJson, JSON_PRETTY_PRINT);


        } catch (\Exception $e) {
            Log::error('Error while creating SAP invoice: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the request', 'details' => $e->getMessage()], 500);
        }
    }


    public function SapInvoicePostByCron()
    {

        try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->first();
        //   echo "<pre>"; print_r($invoice);

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the necessary data for the API request
            $sapJson = $this->prepareSapJson($invoice, $invoiceItems);
            echo json_encode($sapJson, JSON_PRETTY_PRINT);
            $url = $this->url."Sales_Transactions/CreateInvoiceWithBatch.xsjs?&DBName=" . $this->sapDatabase;

        //   echo "<pre>"; print_r($sapJson);

            // Make the API call using HTTP client
            $response = Http::withOptions(['verify' => false])
                            ->post($url, $sapJson);

            // If the request was successful
            if ($response->successful()) {
                $sapResponseData = $response->json();
                // If no errors in SAP response
                if ($sapResponseData['status'] !== 'Error') {
                    $sap_doc_entry = $sapResponseData['data']['DocEntry'];
                    $update_params = [
                        'sap_updated'    => 1,
                        'sap_updated_at' => now(),
                        'sap_created_id' => $sap_doc_entry,
                        'sap_error_msg' => '',
                        'is_updated_err_msg' => 0,


                    ];
                    DB::table('invoices')->where('id', $invoice->id)->update($update_params);
                } else {
                    // Handle SAP response with an error
                    $update_params = [
                        'sap_updated'    => 1,
                        'is_updated_err_msg' => 1,
                        'sap_updated_at' => now(),
                        'sap_error_msg'  => $sapResponseData['data']['error']['message']['value']
                    ];
                    DB::table('invoices')->where('id', $invoice->id)->update($update_params);
                }
            } else {
                // Handle HTTP status codes such as 500 or 404
                //echo"error"; print_r($response); exit;

                $statusCode = $response->status();
                if ($statusCode == 500 || $statusCode == 404) {
                    $update_params = [
                        'sap_updated'    => 1,
                        'sap_updated_at' => now(),
                        'is_updated_err_msg' => 1,
                        'sap_error_msg'  => "HTTP Error: $statusCode - $error. Details: $details"
                    ];
                    DB::table('invoices')->where('id', $invoice->id)->update($update_params);
                }
            }

        } catch (\Exception $e) {
            // In case of exceptions, update the record with error details
            $update_params = [
                'sap_updated'    => 1,
                'is_updated_err_msg' => 1,
                'sap_updated_at' => now(),
                'sap_error_msg'  => $e->getMessage()
            ];
            DB::table('invoices')->where('id', $invoice->id)->update($update_params);
            
            Log::error('Error while creating Invoice in SAP: ' . $e->getMessage());
        }
    }


    private function prepareSapJson($invoice, $invoiceItems)
    {
        $uniqueNumber = $invoice->id . "_" . date('YmdHis', strtotime($invoice->order_date)) . "_" . md5(time() + rand(1, 999999));
        
        $dueDate = $invoice->due_date ? date("Ymd", strtotime($invoice->due_date)) : date("Ymd", strtotime($invoice->order_date));
        $OrderDate = date("Ymd", strtotime($invoice->order_date));

        $user_data = DB::table('users')->select('*')->where('id', $invoice->user_id)->first();
        $sales_person = DB::table('sales_employees')->select('code')->where('id', $invoice->sales_emp_id)->first();
        $sequence = $invoice->sales_type === 'Hospital' ? $user_data->inv_hospital_seq : $user_data->inv_otehr_seq;

        $sapJson = [
            'Series' => $user_data->inv_series,
            'CardCode' => $invoice->customer_id,
            'U_TRPGUID' => $uniqueNumber,
            'U_TRPSALETYPE' => strtoupper($invoice->sales_type),
            'U_ITG_SignDigest' => $invoice->hash_key_code,
            'U_ITG_KeyVersion' => 11,
            'U_ITG_CertifNum' => '1.1',
            'U_VSPSRN' => $user_data->inv_series,
            'U_VSPSRNO' => $invoice->id,
            'U_VSPHSKY'        => $invoice->invoice_no,
            'Comments'         => $invoice->remarks,            
            'NumAtCard' => $invoice->ref_id,
            'DocDate' => $OrderDate,
            'DocDueDate' => $dueDate,
            'TaxDate' => $OrderDate,
            'SequenceCode' => $sequence,
            'BPL_IDAssignedToInvoice' => $user_data->branch,
            'SalesPersonCode' => $sales_person->code,
            'DocumentLines' => $this->prepareDocumentLines($invoice, $invoiceItems),
        ];

        return $sapJson;
    }

        private function prepareDocumentLines($invoice, $invoiceItems)
        {
            $documentLinesAll = [];

            foreach ($invoiceItems as $invoiceItem) {
                $batches = DB::table('invoice_batches')
                             ->where('invoice_id', $invoice->id)
                             ->where('item_code', $invoiceItem->item_no)
                             ->get();

                $itemBatches = [];
                foreach ($batches as $batch) {
                    $itemBatches[] = [
                        'BatchNumber' => $batch->batch_num,
                        'Quantity' => $batch->quantity
                    ];
                }

                $documentLinesAll[] = [
                    'ItemCode' => $invoiceItem->item_no,
                    'ItemDescription' => $invoiceItem->item_desc,
                    'Quantity' => $invoiceItem->quantity,
                    'WarehouseCode' => $invoice->store_code,
                    'PriceBefDi' => $invoiceItem->price,
                    'Price' => $invoiceItem->price_af_disc,
                    'TaxCode' => "IVA" . $invoiceItem->gst_rate,
                    'BatchNumbers' => $itemBatches
                ];
            }

            return $documentLinesAll;
        }
    
    //public function CashPaymentPostByWeb($id)
    //{
    //    try {
    //        $cash_payment = DB::table('order_payments')->where('payment_method','cash')
    //                    ->where('id' ,$id)
    //                    ->first();

    //        if (!$cash_payment) {
    //            return response()->json(['error' => 'Payments not found'], 404);
    //        }
    //        $sub_payments = DB::table('order_payment_sub')->where('order_payment_id', $cash_payment->id)->get();
    //        // / echo "<pre>"; print_r($sub_payments);
    //        if ($sub_payments->isEmpty()) {

    //            return response()->json(['error' => 'No Sub Payments found for this Payments'], 404);
    //        }
    //        $uniqueNumber = $cash_payment->id . "_" . date('YmdHis', strtotime($cash_payment->created_at)) . "_" . md5(time() + rand(1, 999999));
    //            $dueDate = date("Ymd", strtotime($cash_payment->created_at));
    //            $OrderDate = date("ymd", strtotime($cash_payment->created_at));


    //        $cash_account = DB::table('users')->select('cash_acct','ware_house','branch','incoming_payment_series')
    //                        ->where('id',$cash_payment->user_id)
    //                        ->first();

    //        $sapJson = [
    //            'Series' => $cash_account->incoming_payment_series,
    //            'U_TRPGUID' => $uniqueNumber,
    //            'CardCode' => $cash_payment->customer_code,
    //            'DocDate' => $dueDate,
    //            'TaxDate' => $dueDate,
    //            'Remarks'   => $cash_account->ware_house,
    //            'ProjectCode' => $cash_account->ware_house,
    //            'BPLID'  => $cash_account->branch,
    //            'DocType' => "C",
    //            'CashSum'  => $cash_payment->amount,
    //            'CashAccount' => $cash_account->cash_acct
    //        ]; 

    //        $documentLinesAll = [];
    //        $i=0;
    //        foreach ($sub_payments as $spayment) {

    //            $doc_number = DB::table('invoices')->select('sap_created_id')
    //            ->where('id',$spayment->invoice_id)
    //            ->first();

    //            if($doc_number)
    //            {

    //            $documentLinesAll[] = [
    //                'InvoiceType' => "13",
    //                'LineNum' => $i,
    //                'DocEntry' => $doc_number->sap_created_id,
    //                'SumApplied' => $spayment->amount
    //            ];
    //            }
                
    //            $i++;

    //        }
    
    //        $sapJson['PaymentInvoices'] = $documentLinesAll;


    //        echo json_encode($sapJson, JSON_PRETTY_PRINT);
        
    //        $url = "https://10.10.12.10:4300/SAPWebApp/Payments/PostIP_Invoice_BankTransfer.xsjs?DBName=" . $this->sapDatabase;
    
    //        $response = Http::withOptions(['verify' => false])
    //            ->post($url, $sapJson);
    
    //        // Handle the response
    //        if ($response->successful()) {
    //            $sapResponseData = $response->json();
                
    //            echo "<pre>"; print_r($sapResponseData);

    //            if($sapResponseData['status'] != 'Error')
    //            {

    //            $sap_doc_number = $sapResponseData['data']['DocNum'];
    //            $update_params = array('is_sap_updated'    => 1,
    //                                    'sap_updated_at' => date('Y-m-d H:i:s'),
    //                                    'sap_created_id' => $sap_doc_number
    //                                    );


    //            //$cash_payment = DB::table('order_payments')->where('payment_method','cash')->where('is_sap_updated',0)
    //            $update_data = DB::table('order_payments')
    //                                    ->where('id',$cash_payment->id)
    //                                    ->update($update_params);

    //                Log::error('successfully Created  : ' . $sapResponseData);

    //            }else{

    //            $update_params = array('sap_updated'    => 1,
    //                                    'sap_updated_at' => date('Y-m-d H:i:s'),
    //                                    'sap_error_msg'  => $sapResponseData['data']['error']['message']['value']
    //                                    );

    //            $update_data = DB::table('')
    //                                     ->where('id',)
    //                                     ->update($update_params);


    //            }
    //             return response()->json([
    //                 'message' => ' posted to SAP',
    //                 'sapResponse' => $sapResponseData,
                    
    //             ], 200);
    //        } else {
    //            Log::error('Error while Cretae Error Code : ' . $response->status());
    //             return response()->json([
    //                 'error' => 'Failed to create Invoice in SAP',
    //                 'details' => $response->body()
    //             ], $response->status());
    //        }
    //    } catch (\Exception $e) {
    //        // echo "<pre>"; print_r($e->getMessage());
    //        Log::error('Error while Cash Payment : ' . $e->getMessage());
    //         return response()->json([
    //             'error' => 'An error occurred while processing the request',
    //             'details' => $e->getMessage()
    //         ], 500);
    //    }        
    //}

    public function CashPaymentPostByWeb($id)
    {
        try {
            $cash_payment = DB::table('order_payments')
                ->where('payment_method', 'cash')
                ->where('id', $id)
                ->first();

            if (!$cash_payment) {
                return response()->json(['error' => 'Payments not found'], 404);
            }

            $sub_payments = DB::table('order_payment_sub')->where('order_payment_id', $cash_payment->id)->get();

            if ($sub_payments->isEmpty()) {
                return response()->json(['error' => 'No Sub Payments found for this Payment'], 404);
            }

            $uniqueNumber = $cash_payment->id . "_" . date('YmdHis', strtotime($cash_payment->created_at)) . "_" . md5(time() + rand(1, 999999));
            $dueDate = date("Ymd", strtotime($cash_payment->created_at));
            $OrderDate = date("Ymd", strtotime($cash_payment->created_at));

    
                $cash_account = DB::table('users')
                    ->select('cash_acct','tpa_account', 'ware_house', 'branch', 'incoming_payment_series')
                    ->where('ware_house', $cash_payment->warehouse_code)
                    ->first();
              //  echo "<pre>"; print_r($cash_account); exit;

            $sapJson = [
                'Series' => $cash_account->incoming_payment_series,
                'U_TRPGUID' => $uniqueNumber,
                'CardCode' => $cash_payment->customer_code,
                'DocDate' => $dueDate,
                'TaxDate' => $dueDate,
                'Remarks' => $cash_account->ware_house,
                'ProjectCode' => $cash_account->ware_house,
                'BPLID' => $cash_account->branch,
                'DocType' => "C",
                'CashSum' => $cash_payment->amount,
                'CashAccount' => $cash_account->cash_acct
            ];

            $documentLinesAll = [];
            $i = 0;
            foreach ($sub_payments as $spayment) {
                $doc_number = DB::table('invoices')->select('sap_created_id')
                    ->where('id', $spayment->invoice_id)
                    ->first();

                if ($doc_number) {
                    $documentLinesAll[] = [
                        'InvoiceType' => "13",
                        'LineNum' => $i,
                        'DocEntry' => $doc_number->sap_created_id,
                        'SumApplied' => $spayment->amount
                    ];
                }
                $i++;
            }

            $sapJson['PaymentInvoices'] = $documentLinesAll;

            echo json_encode($sapJson, JSON_PRETTY_PRINT);


            $url = $this->url."Payments/PostIP_Invoice_BankTransfer.xsjs?DBName=" . $this->sapDatabase;

            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);

            if ($response->successful()) {
                $sapResponseData = $response->json();

                if ($sapResponseData['status'] !== 'Error') {
                    $sap_doc_number = $sapResponseData['data']['DocNum'];
                    DB::table('order_payments')
                        ->where('id', $cash_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_created_id' => $sap_doc_number,
                            'sap_error_msg' => '',
                        ]);

                    return response()->json([
                        'message' => 'Successfully posted to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'success'
                    ], 200);
                } else {
                    DB::table('order_payments')
                        ->where('id', $cash_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg' => $sapResponseData['data']['error']['message']['value']
                        ]);

                    return response()->json([
                        'error' => 'Failed to post to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'error'
                    ], 500);
                }
            } 
        } catch (\Exception $e) {
            Log::error('Error while processing Cash Payment: ' . $e->getMessage());
            DB::table('order_payments')
            ->where('id', $cash_payment->id)
            ->update([
                'is_sap_updated' => 1,
                'sap_updated_at' => now(),
                'sap_error_msg' => $e->getMessage()
                ]);
            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }


    public function CashPaymentPost()
    {
        try {
            $cash_payment = DB::table('order_payments')
                ->where('payment_method', 'cash')
               // ->where('id', $id)
                ->where('is_sap_updated',0)
                ->orderBy('id','DESC')
                ->first();

            if (!$cash_payment) {
                return response()->json(['error' => 'Payments not found'], 404);
            }

            $sub_payments = DB::table('order_payment_sub')->where('order_payment_id', $cash_payment->id)->get();

            if ($sub_payments->isEmpty()) {

                DB::table('order_payments')
                        ->where('id', $cash_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg' => 'Sub Payments Not Found'
                        ]);

                return response()->json(['error' => 'No Sub Payments found for this Payment'], 404);

            }

            $uniqueNumber = $cash_payment->id . "_" . date('YmdHis', strtotime($cash_payment->created_at)) . "_" . md5(time() + rand(1, 999999));
            $dueDate = date("Ymd", strtotime($cash_payment->created_at));
            $OrderDate = date("Ymd", strtotime($cash_payment->created_at));

    
                $cash_account = DB::table('users')
                    ->select('cash_acct','tpa_account', 'ware_house', 'branch', 'incoming_payment_series')
                    ->where('ware_house', $cash_payment->warehouse_code)
                    ->first();
              //  echo "<pre>"; print_r($cash_account); exit;

            $sapJson = [
                'Series' => $cash_account->incoming_payment_series,
                'U_TRPGUID' => $uniqueNumber,
                'CardCode' => $cash_payment->customer_code,
                'DocDate' => $dueDate,
                'TaxDate' => $dueDate,
                'Remarks' => $cash_account->ware_house,
                'ProjectCode' => $cash_account->ware_house,
                'BPLID' => $cash_account->branch,
                'DocType' => "C",
                'CashSum' => $cash_payment->amount,
                'CashAccount' => $cash_account->cash_acct
            ];

            $documentLinesAll = [];
            $i = 0;
            foreach ($sub_payments as $spayment) {
                $doc_number = DB::table('invoices')->select('sap_created_id')
                    ->where('id', $spayment->invoice_id)
                    ->first();

                if ($doc_number) {
                    $documentLinesAll[] = [
                        'InvoiceType' => "13",
                        'LineNum' => $i,
                        'DocEntry' => $doc_number->sap_created_id,
                        'SumApplied' => $spayment->amount
                    ];
                }
                $i++;
            }

            $sapJson['PaymentInvoices'] = $documentLinesAll;

            echo json_encode($sapJson, JSON_PRETTY_PRINT);


            $url = $this->url."Payments/PostIP_Invoice_BankTransfer.xsjs?DBName=" . $this->sapDatabase;

            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);

            if ($response->successful()) {
                $sapResponseData = $response->json();

                if ($sapResponseData['status'] !== 'Error') {
                    $sap_doc_number = $sapResponseData['data']['DocNum'];
                    DB::table('order_payments')
                        ->where('id', $cash_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_created_id' => $sap_doc_number,
                            'sap_error_msg' => '',
                        ]);

                    return response()->json([
                        'message' => 'Successfully posted to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'success'
                    ], 200);
                } else {
                    DB::table('order_payments')
                        ->where('id', $cash_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg' => $sapResponseData['data']['error']['message']['value']
                        ]);

                    return response()->json([
                        'error' => 'Failed to post to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'error'
                    ], 500);
                }
            } 
        } catch (\Exception $e) {
            Log::error('Error while processing Cash Payment: ' . $e->getMessage());
            DB::table('order_payments')
            ->where('id', $cash_payment->id)
            ->update([
                'is_sap_updated' => 1,
                'sap_updated_at' => now(),
                'sap_error_msg' => $e->getMessage()
                ]);
            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function tpaPaymentPostByWeb($id)
    {
        try {
            $tpa_payment = DB::table('order_payments')
                ->where('payment_method', 'tpa')
                //->orWhere('payment_method', 'online')
                ->where('id', $id)
                ->first();

            if (!$tpa_payment) {
                return response()->json(['error' => 'TPA Payment not found'], 404);
            }

            $sub_payments = DB::table('order_payment_sub')
                ->where('order_payment_id', $tpa_payment->id)
                ->get();

            if ($sub_payments->isEmpty()) {
                return response()->json(['error' => 'No Sub Payments found for this Payments'], 404);
            }

            $uniqueNumber = $tpa_payment->id . "_" . date('YmdHis', strtotime($tpa_payment->created_at)) . "_" . md5(time() + rand(1, 999999));

           // $dueDate = date('Ymd');
            $dueDate = date("Ymd", strtotime($tpa_payment->created_at));
    
                $cash_account = DB::table('users')
                    ->select('tpa_account', 'ware_house', 'branch', 'incoming_payment_series')
                    ->where('ware_house', $tpa_payment->warehouse_code)
                    ->orderBy('id','ASC')
                    ->first();

            $sapJson = [
                'Series' => $cash_account->incoming_payment_series,
                'U_TRPGUID' => $uniqueNumber,
                'CardCode' => $tpa_payment->customer_code,
                'DocDate' => $dueDate,
                'TaxDate' => $dueDate,
                'Remarks' => $cash_account->ware_house,
                'ProjectCode' => $cash_account->ware_house,
                'BPLID' => $cash_account->branch,
                'DocType' => "C", // Customer Payment
                'TransferSum' => $tpa_payment->amount,
                'TransferAccount' => $cash_account->tpa_account,
                'TransferReference' => "WEB_ID_" . $tpa_payment->id
            ];

            
            $documentLinesAll = [];
            $i = 0;
            foreach ($sub_payments as $spayment) {
                $doc_number = DB::table('invoices')
                    ->select('sap_created_id')
                    ->where('id', $spayment->invoice_id)
                    ->first();

                if ($doc_number) {
                    $documentLinesAll[] = [
                        'InvoiceType' => "13",
                        //'LineNum' => $i,
                        'DocEntry' => $doc_number->sap_created_id,
                        'SumApplied' => $spayment->amount
                    ];
                }
                $i++;
            }

            $sapJson['PaymentInvoices'] = $documentLinesAll;

            echo json_encode($sapJson, JSON_PRETTY_PRINT);


            $url = $this->url."Payments/PostIP_Invoice_BankTransfer.xsjs?DBName=" . $this->sapDatabase;

            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);

            if ($response->successful()) {
                $sapResponseData = $response->json();

                if ($sapResponseData['status'] != 'Error') {
                    $sap_doc_number = $sapResponseData['data']['DocNum'];

                    DB::table('order_payments')
                        ->where('id', $tpa_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_created_id' => $sap_doc_number,
                            'sap_error_msg' => ''
                        ]);

                    return response()->json([
                        'message' => 'Successfully posted to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'success'
                    ], 200);
                } else {
                    DB::table('order_payments')
                        ->where('id', $tpa_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg' => $sapResponseData['data']['error']['message']['value']
                        ]);

                    return response()->json([
                        'error' => 'Failed to post to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'error'
                    ], 500);
                }
            } else {
                Log::error('Error while posting to SAP: ' . $response->body());

                return response()->json([
                    'error' => 'Failed to post to SAP',
                    'status' => 'error'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error while processing TPA Payment: ' . $e->getMessage());
            DB::table('order_payments')
                ->where('id', $tpa_payment->id)
                ->update([
                    'is_sap_updated' => 1,
                    'sap_updated_at' => now(),
                    'sap_error_msg' => $e->getMessage()
                ]);

            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }



    public function TPAPaymentPost()
    {
        try {
            $tpa_payment = DB::table('order_payments')
                ->where('payment_method', 'tpa')
                ->where('is_sap_updated',0)
                ->orderBy('id','DESC')
                ->first();

            if (!$tpa_payment) {
                return response()->json(['error' => 'TPA Payment not found'], 404);
            }

            $sub_payments = DB::table('order_payment_sub')
                ->where('order_payment_id', $tpa_payment->id)
                ->get();

            if ($sub_payments->isEmpty()) {
                return response()->json(['error' => 'No Sub Payments found for this Payments'], 404);
            }

            $uniqueNumber = $tpa_payment->id . "_" . date('YmdHis', strtotime($tpa_payment->created_at)) . "_" . md5(time() + rand(1, 999999));

           // $dueDate = date('Ymd');
            $dueDate = date("Ymd", strtotime($tpa_payment->created_at));
    
                $cash_account = DB::table('users')
                    ->select('tpa_account', 'ware_house', 'branch', 'incoming_payment_series')
                    ->where('ware_house', $tpa_payment->warehouse_code)
                    ->orderBy('id','ASC')
                    ->first();

            $sapJson = [
                'Series' => $cash_account->incoming_payment_series,
                'U_TRPGUID' => $uniqueNumber,
                'CardCode' => $tpa_payment->customer_code,
                'DocDate' => $dueDate,
                'TaxDate' => $dueDate,
                'Remarks' => $cash_account->ware_house,
                'ProjectCode' => $cash_account->ware_house,
                'BPLID' => $cash_account->branch,
                'DocType' => "C", // Customer Payment
                'TransferSum' => $tpa_payment->amount,
                'TransferAccount' => $cash_account->tpa_account,
                'TransferReference' => "WEB_ID_" . $tpa_payment->id
            ];

            
            $documentLinesAll = [];
            $i = 0;
            foreach ($sub_payments as $spayment) {
                $doc_number = DB::table('invoices')
                    ->select('sap_created_id')
                    ->where('id', $spayment->invoice_id)
                    ->first();

                if ($doc_number) {
                    $documentLinesAll[] = [
                        'InvoiceType' => "13",
                        //'LineNum' => $i,
                        'DocEntry' => $doc_number->sap_created_id,
                        'SumApplied' => $spayment->amount
                    ];
                }
                $i++;
            }

            $sapJson['PaymentInvoices'] = $documentLinesAll;

            echo json_encode($sapJson, JSON_PRETTY_PRINT);


            $url = $this->url."Payments/PostIP_Invoice_BankTransfer.xsjs?DBName=" . $this->sapDatabase;

            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);

            if ($response->successful()) {
                $sapResponseData = $response->json();

                if ($sapResponseData['status'] != 'Error') {
                    $sap_doc_number = $sapResponseData['data']['DocNum'];

                    DB::table('order_payments')
                        ->where('id', $tpa_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_created_id' => $sap_doc_number,
                            'sap_error_msg' => ''
                        ]);

                    return response()->json([
                        'message' => 'Successfully posted to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'success'
                    ], 200);
                } else {
                    DB::table('order_payments')
                        ->where('id', $tpa_payment->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg' => $sapResponseData['data']['error']['message']['value']
                        ]);

                    return response()->json([
                        'error' => 'Failed to post to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'error'
                    ], 500);
                }
            } else {
                Log::error('Error while posting to SAP: ' . $response->body());

                return response()->json([
                    'error' => 'Failed to post to SAP',
                    'status' => 'error'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error while processing TPA Payment: ' . $e->getMessage());
            DB::table('order_payments')
                ->where('id', $tpa_payment->id)
                ->update([
                    'is_sap_updated' => 1,
                    'sap_updated_at' => now(),
                    'sap_error_msg' => $e->getMessage()
                ]);

            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

   

    public function fromRftPost()
    {
        try {
            $rftData = DB::table('request_for_tender')
                ->where('from_sap_update', 0)
                ->where('from_status', 1)
                ->first(); 

            if (!$rftData) {
                return response()->json(['error' => 'No pending RFT data found'], 404);
            }
            $rftItems = DB::table('request_tender_items')
                            ->where('request_for_tender_id', $rftData->id)
                            ->where('from_status' , 1)
                            ->get();
            if ($rftItems->isEmpty()) {
                return response()->json(['error' => 'No RFT items found'], 404);
            }

            $stockTransferLines = [];

            foreach ($rftItems as $rftItem) {

               // echo "SU".$rftItem->id;
               // echo "WA".$rftData->from_store;
                $rftBatches = DB::table('rft_batches')
                    ->where('rft_item_id', $rftItem->id)
                    ->where('from_store' , $rftData->from_store)
                    ->get();
                    //echo "<pre>"; print_r($rftBatches); exit;

                $batchNumbers = [];
                foreach ($rftBatches as $batch) {
                    $batchNumbers[] = [
                        'ItemCode' => $rftItem->item_code,
                        'BatchNumber' => $batch->batch_num,
                        'Quantity' => $batch->from_store_qty
                    ];
                }
                //echo "<pre>"; print_r($batchNumbers); exit;

                $stockTransferLines[] = [
                    'ItemCode' => $rftItem->item_code,
                    'Quantity' => $rftItem->rft_required_qty,
                    'FromWarehouseCode' => $rftData->from_store,
                    'BatchNumbers' => $batchNumbers
                ];
            }

            $sapJson = [
                'U_TRPRFT' => $rftData->rft_number,
                'U_TRPIDS' => $rftData->rft_entry,
                'DocDate' => $rftItem->required_date,
                'DueDate' => $rftItem->required_date,
                'FromWarehouse' => $rftData->from_store,
                'ToWarehouse' => $rftData->to_store,
                'StockTransferLines' => $stockTransferLines
            ];

            //echo"suresh"; print_r($sapJson); 
            //echo json_encode($sapJson, JSON_PRETTY_PRINT); 


           $url = $this->url."Sales_Transactions/CreateStockTransferStore.xsjs?DBName=". $this->sapDatabase;
            
            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);
            //echo json_encode($response, JSON_PRETTY_PRINT); exit;

            if ($response->successful()) {
                $sapResponseData = $response->json();

                if ($sapResponseData['status'] != 'Error') {
                    echo"successful"; print_r($sapResponseData); 
                    $sap_doc_number = $sapResponseData['data']['DocNum'];

                    DB::table('request_for_tender')
                        ->where('id', $rftData->id)
                        ->update([
                            'from_sap_update' => 1,
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg_from' => '',
                            'from_sap_created_id' => $sap_doc_number
                        ]);
                        
                    return response()->json([
                        'message' => 'Successfully posted to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'success'
                    ], 200);
                } else {
                    echo"Error"; print_r($sapResponseData); 

                    DB::table('request_for_tender')
                        ->where('id', $rftData->id)
                        ->update([
                            'from_sap_update' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg_from' => $sapResponseData['data']['error']['message']['value']
                        ]);

                    return response()->json([
                        'error' => 'Failed to post to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'error'
                    ], 500);
                }
            } else {
                Log::error('Error while posting to SAP: ' . $response->body());
                return response()->json([
                    'error' => 'Failed to post to SAP',
                    'status' => 'error'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error while processing RFT: ' . $e->getMessage());
            DB::table('request_for_tender')
                ->where('id', $rftData->id)
                ->update([
                    'is_sap_updated' => 1,
                    'from_sap_update' => 1,
                    'sap_updated_at' => now(),
                    'sap_error_msg_from' => $e->getMessage()
                ]);

            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function fromRftPostByWeb($id)
    {
        try {
            $rftData = DB::table('request_for_tender')
                ->where('id', $id)
                ->first(); 

            if (!$rftData) {
                return response()->json(['error' => 'No pending RFT data found'], 404);
            }
            $rftItems = DB::table('request_tender_items')
                            ->where('request_for_tender_id', $rftData->id)
                            ->where('from_status' , 1)
                            ->get();
            if ($rftItems->isEmpty()) {
                return response()->json(['error' => 'No RFT items found'], 404);
            }

            $stockTransferLines = [];

            foreach ($rftItems as $rftItem) {

               // echo "SU".$rftItem->id;
               // echo "WA".$rftData->from_store;
                $rftBatches = DB::table('rft_batches')
                    ->where('rft_item_id', $rftItem->id)
                    ->where('from_store' , $rftData->from_store)
                    ->get();
                    //echo "<pre>"; print_r($rftBatches); exit;

                $batchNumbers = [];
                foreach ($rftBatches as $batch) {
                    $batchNumbers[] = [
                        'ItemCode' => $rftItem->item_code,
                        'BatchNumber' => $batch->batch_num,
                        'Quantity' => $batch->from_store_qty
                    ];
                }
                //echo "<pre>"; print_r($batchNumbers); exit;

                $stockTransferLines[] = [
                    'ItemCode' => $rftItem->item_code,
                    'Quantity' => $rftItem->rft_required_qty,
                    'FromWarehouseCode' => $rftData->from_store,
                    'BatchNumbers' => $batchNumbers
                ];
            }

            $sapJson = [
                'U_TRPRFT' => $rftData->rft_number,
                'U_TRPIDS' => $rftData->rft_entry,
                'DocDate' => $rftItem->required_date,
                'DueDate' => $rftItem->required_date,
                'FromWarehouse' => $rftData->from_store,
                'ToWarehouse' => $rftData->to_store,
                'StockTransferLines' => $stockTransferLines
            ];

            //echo"suresh"; print_r($sapJson); 
            //echo json_encode($sapJson, JSON_PRETTY_PRINT); 


           $url = $this->url."Sales_Transactions/CreateStockTransferStore.xsjs?DBName=". $this->sapDatabase;
            
            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);
            //echo json_encode($response, JSON_PRETTY_PRINT); exit;

            if ($response->successful()) {
                $sapResponseData = $response->json();

                if ($sapResponseData['status'] != 'Error') {
                    echo"successful"; print_r($sapResponseData); 
                    $sap_doc_number = $sapResponseData['data']['DocNum'];

                    DB::table('request_for_tender')
                        ->where('id', $rftData->id)
                        ->update([
                            'from_sap_update' => 1,
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg_from' => '',
                            'from_sap_created_id' => $sap_doc_number
                        ]);
                        
                    return response()->json([
                        'message' => 'Successfully posted to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'success'
                    ], 200);
                } else {
                    echo"Error"; print_r($sapResponseData); 

                    DB::table('request_for_tender')
                        ->where('id', $rftData->id)
                        ->update([
                            'from_sap_update' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg_from' => $sapResponseData['data']['error']['message']['value']
                        ]);

                    return response()->json([
                        'error' => 'Failed to post to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'error'
                    ], 500);
                }
            } else {
                Log::error('Error while posting to SAP: ' . $response->body());
                return response()->json([
                    'error' => 'Failed to post to SAP',
                    'status' => 'error'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error while processing RFT: ' . $e->getMessage());
            DB::table('request_for_tender')
                ->where('id', $rftData->id)
                ->update([
                    'is_sap_updated' => 1,
                    'from_sap_update' => 1,
                    'sap_updated_at' => now(),
                    'sap_error_msg_from' => $e->getMessage()
                ]);

            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }


    public function toRftPost()
    {

            $rftData = DB::table('request_for_tender')
                ->where('to_sap_update', 0)
                ->where('from_sap_update', 1)
                ->where('to_status' , 1)
                ->first();
        try {
            if (!$rftData) {
                return response()->json(['error' => 'No pending RFT data found'], 404);
            }
    
            $rftItems = DB::table('request_tender_items')
                ->where('request_for_tender_id', $rftData->id)
                ->where('to_status', 1) 
                ->get();
    
            if ($rftItems->isEmpty()) {
                return response()->json(['error' => 'No RFT items found'], 404);
            }
            //echo "Generated SAP JSON: ";
            //print_r($rftItems);
            $stockTransferLines = [];
    
            foreach ($rftItems as $rftItem) {
                $rftBatches = DB::table('rft_batches')
                    ->where('rft_item_id' , $rftItem->id)
                    ->where('to_store', $rftData->to_store)
                    ->get();
    
                $batchNumbers = [];
                foreach ($rftBatches as $batch) {
                    $batchNumbers[] = [
                        'ItemCode' => $rftItem->item_code,
                        'BatchNumber' => $batch->batch_num,
                        'Quantity' => $rftItem->rft_required_qty
                    ];
                }
    
                $stockTransferLines[] = [
                    'ItemCode' => $rftItem->item_code,
                    'Quantity' => $rftItem->rft_required_qty,
                    'BatchNumbers' => $batchNumbers
                ];
            }
    
            // Prepare SAP JSON request
            $sapJson = [
                'U_TRPRFT' => $rftData->rft_number,
                'U_TRPIDS' => $rftData->rft_entry,
                'DocDate' => $rftItem->received_date,
                'DueDate' => $rftItem->received_date,
                'FromWarehouse' => $rftData->from_store, 
                'ToWarehouse' => $rftData->to_store,     
                'StockTransferLines' => $stockTransferLines
            ];
    
            //echo "Generated SAP JSON: ";
            //print_r($sapJson);
    
            // Define SAP API URL
           $url = $this->url."Sales_Transactions/CreateStockTransferWH.xsjs?DBName=" . $this->sapDatabase;
    
            // Send request to SAP
            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);
    
            echo json_encode($sapJson, JSON_PRETTY_PRINT); 
            if ($response->successful()) {
                $sapResponseData = $response->json();
    
                if ($sapResponseData['status'] != 'Error') {
                    echo "SAP Response: ";
                    print_r($sapResponseData);
    
                    // Update RFT record as successfully posted
                    DB::table('request_for_tender')
                        ->where('id', $rftData->id)
                        ->update([
                            'to_sap_update' => 1,
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg' => '',
                            'to_sap_created_id'=> $sapResponseData['data']['DocNum'],
                            'status' => 1,
                        ]);
    
                    return response()->json([
                        'message' => 'Successfully posted to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'success'
                    ], 200);
                } else {
                    echo "SAP Error: ";
                    print_r($sapResponseData);
    
                    // Log SAP error
                    DB::table('request_for_tender')
                        ->where('id', $rftData->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'to_sap_update' => 1,
                            'sap_error_msg' => $sapResponseData['data']['error']['message']['value']
                        ]);
    
                    return response()->json([
                        'error' => 'Failed to post to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'error'
                    ], 500);
                }
            } else {
                Log::error('Error while posting to SAP: ' . $response->body());
                return response()->json([
                    'error' => 'Failed to post to SAP',
                    'status' => 'error'
                ], 500);
            }
        } catch (\Exception $e) {

          
            Log::error('Error while processing toRFT: ' . $e->getMessage());
    
            DB::table('request_for_tender')
                ->where('id', $rftData->id)
                ->update([
                    'is_sap_updated' => 1,
                    'to_sap_update' => 1,
                    'sap_updated_at' => now(),
                    'sap_error_msg' => $e->getMessage()
                ]);
    
            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function toRftPostByWeb($id)
    {
            $rftData = DB::table('request_for_tender')
                ->where('id' , 0)
                ->first();
        try {
            if (!$rftData) {
                return response()->json(['error' => 'No pending RFT data found'], 404);
            }
    
            $rftItems = DB::table('request_tender_items')
                ->where('request_for_tender_id', $rftData->id)
                ->where('to_status', 1) 
                ->get();
    
            if ($rftItems->isEmpty()) {
                return response()->json(['error' => 'No RFT items found'], 404);
            }
            //echo "Generated SAP JSON: ";
            //print_r($rftItems);
            $stockTransferLines = [];
    
            foreach ($rftItems as $rftItem) {
                $rftBatches = DB::table('rft_batches')
                    ->where('rft_item_id' , $rftItem->id)
                    ->where('to_store', $rftData->to_store)
                    ->get();
    
                $batchNumbers = [];
                foreach ($rftBatches as $batch) {
                    $batchNumbers[] = [
                        'ItemCode' => $rftItem->item_code,
                        'BatchNumber' => $batch->batch_num,
                        'Quantity' => $rftItem->rft_required_qty
                    ];
                }
    
                $stockTransferLines[] = [
                    'ItemCode' => $rftItem->item_code,
                    'Quantity' => $rftItem->rft_required_qty,
                    'BatchNumbers' => $batchNumbers
                ];
            }
    
            // Prepare SAP JSON request
            $sapJson = [
                'U_TRPRFT' => $rftData->rft_number,
                'U_TRPIDS' => $rftData->rft_entry,
                'DocDate' => $rftItem->received_date,
                'DueDate' => $rftItem->received_date,
                'FromWarehouse' => $rftData->from_store, 
                'ToWarehouse' => $rftData->to_store,     
                'StockTransferLines' => $stockTransferLines
            ];
    
            //echo "Generated SAP JSON: ";
            //print_r($sapJson);
    
            // Define SAP API URL
           $url = $this->url."Sales_Transactions/CreateStockTransferWH.xsjs?DBName=" . $this->sapDatabase;
    
            // Send request to SAP
            $response = Http::withOptions(['verify' => false])->post($url, $sapJson);
    
            echo json_encode($sapJson, JSON_PRETTY_PRINT); 
            if ($response->successful()) {
                $sapResponseData = $response->json();
    
                if ($sapResponseData['status'] != 'Error') {
                    echo "SAP Response: ";
                    print_r($sapResponseData);
    
                    // Update RFT record as successfully posted
                    DB::table('request_for_tender')
                        ->where('id', $rftData->id)
                        ->update([
                            'to_sap_update' => 1,
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg' => '',
                            'to_sap_created_id'=> $sapResponseData['data']['DocNum'],
                            'status' => 1,
                        ]);
    
                    return response()->json([
                        'message' => 'Successfully posted to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'success'
                    ], 200);
                } else {
                    echo "SAP Error: ";
                    print_r($sapResponseData);
    
                    // Log SAP error
                    DB::table('request_for_tender')
                        ->where('id', $rftData->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'to_sap_update' => 1,
                            'sap_error_msg' => $sapResponseData['data']['error']['message']['value']
                        ]);
    
                    return response()->json([
                        'error' => 'Failed to post to SAP',
                        'sapResponse' => $sapResponseData,
                        'status' => 'error'
                    ], 500);
                }
            } else {
                Log::error('Error while posting to SAP: ' . $response->body());
                return response()->json([
                    'error' => 'Failed to post to SAP',
                    'status' => 'error'
                ], 500);
            }
        } catch (\Exception $e) {

          
            Log::error('Error while processing toRFT: ' . $e->getMessage());
    
            DB::table('request_for_tender')
                ->where('id', $rftData->id)
                ->update([
                    'is_sap_updated' => 1,
                    'to_sap_update' => 1,
                    'sap_updated_at' => now(),
                    'sap_error_msg' => $e->getMessage()
                ]);
    
            return response()->json([
                'error' => 'An error occurred while processing the request',
                'details' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
    

     public function CashTpaWebPost($id){
           
            try {
                $orderPayments = DB::table('order_payments')
                                    ->where('payment_method','tpa, cash')
                                    ->where('id',$id)
                                    //->where('is_sap_updated',0)
                                    ->first();

                    if (!$orderPayments) {
                        return response()->json(['error' => 'CASH TPA Payment not found'], 404);
                    }
        
                    $sub_payments = DB::table('order_payment_sub')
                        ->where('order_payment_id', $orderPayments->id) 
                        ->get();
        
                    if ($sub_payments->isEmpty()) {
                        return response()->json(['error' => 'No Sub Payments found for this Payments'], 404);
                    }
        
                    $uniqueNumber = $orderPayments->id . "_" . date('YmdHis', strtotime($orderPayments->created_at)) . "_" . md5(time() + rand(1, 999999));
        
                  //  $dueDate = date('Ymd');
                     $dueDate = date("Ymd", strtotime($orderPayments->created_at));
                    

                    $accDetails = DB::table('users')
                                    ->select('tpa_account', 'cash_acct', 'ware_house', 'branch', 'incoming_payment_series')
                                    ->where('ware_house', $orderPayments->warehouse_code)
                                    ->first();
        
                    //echo""; print_r($accDetails); exit;
                    $sapJson = [
                        'Series' => $accDetails->incoming_payment_series,
                        'U_TRPGUID' => $uniqueNumber,
                        'CardCode' => $orderPayments->customer_code,
                        'DocDate' => $dueDate,
                        'TaxDate' => $dueDate,
                        'Remarks' => $accDetails->ware_house,
                        'ProjectCode' => $accDetails->ware_house,
                        'BPLID' => $accDetails->branch,
                        'DocType' => "C", // Customer Payment
                        'TransferSum' => $orderPayments->online,
                        'TransferAccount' => $accDetails->tpa_account,
                        'CashSum' => $orderPayments->cash,
                        'CashAccount' => $accDetails->cash_acct,
                        'TransferReference' => "WEB_ID_" . $orderPayments->id
                    ];
        
                    
                    $documentLinesAll = [];
                    $i = 0;
                    foreach ($sub_payments as $spayment) {
                        $doc_number = DB::table('invoices')
                            ->select('sap_created_id')
                            ->where('id', $spayment->invoice_id)
                            ->first();
        
                        if ($doc_number) {
                            $documentLinesAll[] = [
                                'InvoiceType' => "13",
                                //'LineNum' => $i,
                                'DocEntry' => $doc_number->sap_created_id,
                                'SumApplied' => $spayment->amount
                            ];
                        }
                        $i++;
                    }
        
                    $sapJson['PaymentInvoices'] = $documentLinesAll;
        
                    echo json_encode($sapJson, JSON_PRETTY_PRINT);
        
        
                    $url = $this->url."Payments/PostIP_Invoice_BankTransfer.xsjs?DBName=" . $this->sapDatabase;
        
                    $response = Http::withOptions(['verify' => false])->post($url, $sapJson);
        
                    if ($response->successful()) {
                        $sapResponseData = $response->json();
        
                        if ($sapResponseData['status'] != 'Error') {
                            $sap_doc_number = $sapResponseData['data']['DocNum'];
        
                            DB::table('order_payments')
                                ->where('id', $orderPayments->id)
                                ->update([
                                    'is_sap_updated' => 1,
                                    'sap_updated_at' => now(),
                                    'sap_created_id' => $sap_doc_number,
                                    'sap_error_msg' => ''
                                ]);
                            return response()->json([
                                'message' => 'Successfully posted to SAP',
                                'sapResponse' => $sapResponseData,
                                'status' => 'success'
                            ], 200);
                        } else {
                            DB::table('order_payments')
                                ->where('id', $orderPayments->id)
                                ->update([
                                    'is_sap_updated' => 1,
                                    'sap_updated_at' => now(),
                                    'sap_error_msg' => $sapResponseData['data']['error']['message']['value']
                                ]);
        
                            return response()->json([
                                'error' => 'Failed to post to SAP',
                                'sapResponse' => $sapResponseData,
                                'status' => 'error'
                            ], 500);
                        }
                    } else {
                        Log::error('Error while posting to SAP: ' . $response->body());
        
                        return response()->json([
                            'error' => 'Failed to post to SAP',
                            'status' => 'error'
                        ], 500);
                    }
                } catch (\Exception $e) {
                    Log::error('Error while processing CASH TPA Payment: ' . $e->getMessage());
                    DB::table('order_payments')
                        ->where('id', $orderPayments->id)
                        ->update([
                            'is_sap_updated' => 1,
                            'sap_updated_at' => now(),
                            'sap_error_msg' => $e->getMessage()
                        ]);
        
                    return response()->json([
                        'error' => 'An error occurred while processing the request',
                        'details' => $e->getMessage(),
                        'status' => 'error'
                    ], 500);
                }
         }    


    //public function TPAPaymentPost(){
    //    try {
    //        $cash_payment = DB::table('order_payments')->where('payment_method','tpa')->where('is_sap_updated',0)
    //                    //->where('id',3)
    //                    ->first();

    //        if (!$cash_payment) {
    //            return response()->json(['error' => 'Invoice not found'], 404);
    //        }
    //        $sub_payments = DB::table('order_payment_sub')->where('order_payment_id', $cash_payment->id)->get();
    //        // / echo "<pre>"; print_r($sub_payments);
    //        if ($sub_payments->isEmpty()) {

    //            return response()->json(['error' => 'No items found for this invoice'], 404);
    //        }
    //        $uniqueNumber = $cash_payment->id . "_" . date('YmdHis', strtotime($cash_payment->created_at)) . "_" . md5(time() + rand(1, 999999));
    //         //$dueDate = date("Ymd", strtotime($cash_payment->created_at));
    //        // $OrderDate = date("ymd", strtotime($cash_payment->created_at));

    //         $dueDate = date('Ymd');

    //        // echo "HSDHDH"; exit;
    //        $cash_account = DB::table('users')->select('tpa_account','ware_house','branch','incoming_payment_series')
    //                        ->where('id',$cash_payment->user_id)
    //                        ->first();

    //        $sapJson = [
    //            'Series' => $cash_account->incoming_payment_series,
    //            'U_TRPGUID' => $uniqueNumber,
    //            'CardCode' => $cash_payment->customer_code,
    //            'DocDate' => $dueDate,
    //            'TaxDate' => $dueDate,
    //            'Remarks'   => $cash_account->ware_house,
    //            'ProjectCode' => $cash_account->ware_house,
    //            'BPLID'  => $cash_account->branch,
    //            'DocType' => "C",
    //            'TransferSum'  => $cash_payment->amount,
    //            'TransferAccount' => $cash_account->tpa_account,
    //            'TransferReference' => "WEB_ID_".$cash_payment->id
    //        ]; 

    //        // Prepare document lines
    //        $documentLinesAll = [];
    //        $i=0;
    //        foreach ($sub_payments as $spayment) {

    //         $doc_number = DB::table('invoices')->select('sap_created_id')
    //         ->where('id',$spayment->invoice_id)
    //         ->first();

    //         if($doc_number)
    //         {

    //            $documentLinesAll[] = [
    //                'InvoiceType' => "13",
    //                'LineNum' => $i,
    //                'DocEntry' => $doc_number->sap_created_id,
    //                'SumApplied' => $spayment->amount
    //            ];
    //         }
    //            //dd( $orderItems);
    //         $i++;

    //        }
    //        $sapJson['PaymentInvoices'] = $documentLinesAll;

    //        // echo json_encode($sapJson, JSON_PRETTY_PRINT);
    //        //exit; 
    //        // Define the SAP API URL
    //        $url = "https://10.10.12.10:4300/SAPWebApp/Payments/PostIP_Invoice_BankTransfer.xsjs?DBName=" . $this->sapDatabase;
    //        //$url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateInvoiceWithBatch.xsjs?&DBName=ZTEST_AARNEXT090125";
    
    //        // Send the request to the SAP API
    //        $response = Http::withOptions(['verify' => false])
    //            ->post($url, $sapJson);
    
    //        // Handle the response
    //        if ($response->successful()) {
    //            $sapResponseData = $response->json();
    //           // echo "<pre>"; print_r($sapResponseData);
    //           // Log::error('successfully Created  : ' . $sapResponseData);
    //           if($sapResponseData['status'] != 'Error')
    //           {

    //           $sap_doc_number = $sapResponseData['data']['DocNum'];
    //            $update_params = array('is_sap_updated'    => 1,
    //                                   'sap_updated_at' => date('Y-m-d H:i:s'),
    //                                   'sap_created_id' => $sap_doc_number
    //                                  );
    //            //$cash_payment = DB::table('order_payments')->where('payment_method','cash')->where('is_sap_updated',0)
    //            $update_data = DB::table('order_payments')
    //                                    ->where('id',$cash_payment->id)
    //                                    ->update($update_params);

    //             Log::error('successfully Created  : ' . $sapResponseData);

    //           }else{

    //            $update_params = array('sap_updated'    => 1,
    //                                   'sap_updated_at' => date('Y-m-d H:i:s'),
    //                                   'sap_error_msg'  => $sapResponseData['data']['error']['message']['value']
    //                                  );

    //            // $update_data = DB::table('invoices')
    //            //                         ->where('id',$invoice->id)
    //            //                         ->update($update_params);


    //           }
    //            // return response()->json([
    //            //     'message' => 'Invoice successfully posted to SAP',
    //            //     'sapResponse' => $sapResponseData,
    //            //     'invoice' => $invoice,
    //            //     'invoiceItems' => $invoiceItems
    //            // ], 200);
    //        } else {
    //            //Log::error('Error while Cretae Error Code : ' . $response->status());
    //            // return response()->json([
    //            //     'error' => 'Failed to create Invoice in SAP',
    //            //     'details' => $response->body()
    //            // ], $response->status());
    //        }
    //        } catch (\Exception $e) {
    //            echo "<pre>"; print_r($e->getMessage());
    //            Log::error('Error while Cretae Invoices : ' . $e->getMessage());
    //            // return response()->json([
    //            //     'error' => 'An error occurred while processing the request',
    //            //     'details' => $e->getMessage()
    //            // ], 500);
    //        }
         
    // }

     public function TestSapUrl()
     {

          $my_url = "https://uat-ibdms.advantagetvs.com/erp/api/CheckReachability";

          //$response = Http::withHeaders($headers)->withoutVerifying()->get($my_url);
          $response = Http::withoutVerifying()->get($my_url);

          echo "<pre>"; print_r($response);
         
     }


   

    //public function SapCreditlinePostByCron()
    //{
    //    try {
    //        // Fetch a credit memo where sap_updated is 0
    //            $creditmemo = DB::table('credit_memos as cm')
    //            ->leftJoin('users as u', 'u.id', '=', 'cm.user_id')
    //            ->leftJoin('customers as c', 'c.id', '=', 'cm.customer_id') 
    //            ->select(['cm.*', 'u.ware_house', 'c.card_name']) 
    //            ->where('cm.sap_updated', 0)
    //            ->first();
    //        // echo "<pre>"; print_r($creditmemo); exit;

    //        if (!$creditmemo) {
    //            return response()->json(['error' => 'Credit memo not found'], 404);
    //        }

    //        // Fetch credit memo items
    //        $creditItems = DB::table('credit_memo_items')->where('credit_id', $creditmemo->id)->get();
    //        // echo "<pre>"; print_r($creditItems); exit;
    //        if ($creditItems->isEmpty()) {
    //            return response()->json(['error' => 'No items found for this credit memo'], 404);
    //        }

    //        // Prepare the unique number and date fields
    //        $uniqueNumber = $creditmemo->id . "_" . date('YmdHis', strtotime($creditmemo->created_at)) . "_" . md5(time() + rand(1, 999999));
    //        $dueDate = date("ymd", strtotime($creditmemo->order_date));
    //        $OrderDate = date("ymd", strtotime($creditmemo->order_date));

    //        // Prepare the SAP JSON payload
    //        $sapJson = [
    //            'Series' => 312,
    //            //"CardCode" => $creditmemo->customer_id,
    //            //"NumAtCard" => "TestAPI",
    //            //'DocDate' => $OrderDate,
    //            //'DocDueDate' => $dueDate,
    //            //'TaxDate' => $OrderDate,
    //            //'GUID' => $uniqueNumber,
    //            //'BPL_IDAssignedToInvoice' => '22',
    //            "CardCode" => $creditmemo->customer_id,
    //            "CardName" => $creditmemo->card_name,
    //            "DocDate" => $orderDate,
    //            "DocDueDate" => $dueDate,
    //            "NumAtCard" => "CustomerReferance1", 
    //            "U_TRPGUID" => $uniqueNumber,
    //            "DiscountPercent" => 2.867, 
    //            "BPL_IDAssignedToInvoice" => "35",
    //            "Comments" => $creditmemo->remarks,
    //            ];

    //        // Prepare document lines
    //        $documentLinesAll = [];
    //        foreach ($creditItems as $creditItem) {
    //            $batches_list = DB::table('credit_batches')
    //                ->where('credit_id', $creditmemo->id)
    //                ->where('item_code', $creditItem->item_no)
    //                ->get();

    //            $itemBatches = [];
    //            foreach ($batches_list as $batch) {
    //                $itemBatches[] = [
    //                    'ItemCode' => $creditItem->item_code,
    //                    'BatchNumber' => $batch->batch_num,
    //                    'Quantity' => $batch->quantity,
    //                ];
    //            }

    //            $documentLinesAll[] = [
    //                'ItemCode' => $creditItem->item_no,
    //                'WarehouseCode' => $creditmemo->ware_house,
    //                'Quantity' => $creditItem->quantity,
    //                'ItemDescription' => $creditItem->item_desc,
    //                'PriceBefDi' => $creditItem->price,
    //                'Price' => $creditItem->subtotal,
    //                'TaxCode' => "IVA".$creditItem->gst_rate,
    //                'BatchNumbers' => $itemBatches,
    //            ];
    //        }

    //        // Add document lines to SAP JSON
    //        $sapJson['DocumentLines'] = $documentLinesAll;
    //        //echo json_encode($sapJson['DocumentLines'], JSON_PRETTY_PRINT); exit;
    //        echo json_encode($sapJson); exit;


    //        // Define the SAP API URL
    //        $url = "https://10.10.12.10:4300/SAPWebApp/Sales_Transactions/CreateCreditMemo_Invoices.xsjs?&DBName=" . $this->sapDatabase;

    //        // Send the request to the SAP API
    //        $response = Http::withOptions(['verify' => false])
    //            ->post($url, $sapJson);

    //        // Handle the response
    //        if ($response->successful()) {
    //            $sapResponseData = $response->json();
                
    //            // Check if the response is not an error
    //            if ($sapResponseData['status'] != 'Error') {
    //                $sap_doc_entry = $sapResponseData['data']['DocEntry'];
    //                $update_params = [
    //                    'sap_updated' => 1,
    //                    'sap_updated_at' => now(),
    //                    'sap_created_id' => $sap_doc_entry,
    //                ];
    //                DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
    //                Log::info('Credit memo successfully created in SAP: ' . $sapResponseData);
    //                echo"Credit memo successfully created in SAP:"; json_encode($sapResponseData);
    //            } else {
                    
    //                $update_params = [
    //                    'sap_updated' => 1,
    //                    'sap_updated_at' => now(),
    //                    'sap_error_msg' => $sapResponseData['data']['error']['message']['value'],
    //                ];
    //                DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
    //                echo"Credit memo error:elseelse"; json_encode($sapResponseData);

    //            }
    //        } else {
    //            // Handle HTTP errors (400, 500, etc.)
    //            $statusCode = $response->status();
    //            if ($statusCode == 500 || $statusCode == 400) {
    //                $update_params = [
    //                    'sap_updated' => 1,
    //                    'sap_updated_at' => now(),
    //                    'sap_error_msg' => "HTTP Error: " . $statusCode,
    //                ];
    //                DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
    //                echo"Credit memo error:else"; json_encode(['error' => 'HTTP Error: ' .$statusCode ,]);
    //            }
    //        }
    //    } catch (\Exception $e) {
    //        // Handle any exceptions
    //        $update_params = [
    //            'sap_updated' => 1,
    //            'sap_updated_at' => now(),
    //            'sap_error_msg' => $e->getMessage(),
    //        ];
    //        DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);

    //        Log::error('Error while creating credit memo in SAP: ' . $e->getMessage());
    //        echo"Credit memo error:catch"; json_encode(['error' => $e->getMessage()]);
    //    }
    //}

    public function SapCreditPostByWeb($id)
    {
        try {

            $creditmemo = DB::table('credit_memos as cm')
                ->leftJoin('users as u', 'u.id', '=', 'cm.user_id')
                ->leftJoin('customers as c', 'c.id', '=', 'cm.customer_id') 
               // ->leftJoin('invoices as i', 'i.id', '=', 'cm.invoice_id') 
                ->select(['cm.*', 'u.ware_house', 'c.card_name']) 
                //->where('cm.sap_updated', 0)
                ->where('cm.id', $id)
                ->first();

                //echo"test inoices"; print_r($creditmemo->invoice_id); exit; 

            $invoices = DB::table('invoices')->where('id', $creditmemo->invoice_id)->first();


              //  echo"test credit"; print_r($docEntry); 
    
            if (!$creditmemo) {
                return response()->json(['error' => 'Credit memo not found'], 404);
            }
    
            // Fetch credit memo items
            $creditItems = DB::table('credit_memo_items')->where('credit_id', $creditmemo->id)->get();
            if ($creditItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this credit memo'], 404);
            }

            $user_data = DB::table('users')->select('*')->where('id', $creditmemo->user_id)->first();
            $sales_person = DB::table('sales_employees')->select('code')->where('id', $creditmemo->sales_emp_id)->first();
            $sequence = $invoices->sales_type === 'Hospital' ? $user_data->cn_sequence : $user_data->cn_other_sequence;

    
            // Prepare unique identifier
            $uniqueNumber = $creditmemo->id . "_" . date('YmdHis', strtotime($creditmemo->created_at)) . "_" . md5(time() + rand(1, 999999));
            $dueDate = now();
            $orderDate = now();
    
            // Prepare the SAP JSON payload
            $sapJson = [
                'Series' => $user_data->cn_series,
                "CardCode" => $creditmemo->customer_id,
                "CardName" => $creditmemo->card_name,
                "DocDate" => $orderDate,
                "DocDueDate" => $dueDate,
                "NumAtCard" => $creditmemo->ref_id, 
                "U_TRPGUID" => $uniqueNumber,
                "U_TRPSALETYPE" => strtoupper($invoices->sales_type),
                "U_ITG_SignDigest" => $invoices->hash_key_code,
                "U_ITG_KeyVersion" => 11,
                "U_ITG_CertifNum" => "1.1",
                "U_VSPSRN" => $user_data->cn_series,
                "U_VSPSRNO" => $creditmemo->id,
                "TaxDate" => $orderDate,
                "SequenceCode" => $sequence,
                "BPL_IDAssignedToInvoice" => $user_data->branch,
                'SalesPersonCode' => $sales_person->code,      
                "DiscountPercent" => '', 
                "Comments" => $creditmemo->remarks,
                "U_VSPHSKY" => $creditmemo->credit_no,
                "OriginalRefNo" => $invoices->sap_created_id,
                "OriginalRefDate" => $invoices->order_date,
            ];
            // Prepare document lines
            $documentLinesAll = [];
            foreach ($creditItems as $creditItem) {

             $invoice_line_num = DB::table('invoice_items')
                                        ->where('invoice_id' ,$creditmemo->invoice_id)
                                        ->where('item_no' ,$creditItem->item_no)
                                        ->first();

                $batches_list = DB::table('credit_batches')
                    ->where('credit_id', $creditmemo->id)
                    ->where('item_code', $creditItem->item_no)
                    ->get();

                $itemBatches = [];
                foreach ($batches_list as $batch) {
                    $itemBatches[] = [
                        'ItemCode' => $batch->item_code,
                        'BatchNumber' => $batch->batch_num,
                        'Quantity' => $batch->quantity,
                    ];
                }
    
                $documentLinesAll[] = [
                    'ItemCode' => $creditItem->item_no,
                    'WarehouseCode' => $creditmemo->ware_house,
                    'Quantity' => $creditItem->quantity,
                    'PriceBefDi' => $creditItem->price,
                    'Price' => $creditItem->price_af_disc,
                    'TaxCode' => "IVA".$creditItem->gst_rate,
                    // 'BaseEntry' => $invoices->sap_created_id,
                    // 'BaseType' => "13", 
                    // 'BaseLine' => $invoice_line_num->sap_base_line,
                    'BatchNumbers' => $itemBatches,
                ];
            }
    
            $sapJson['DocumentLines'] = $documentLinesAll;

            echo json_encode($sapJson, JSON_PRETTY_PRINT);
           // exit;
    
            $url = $this->url."Sales_Transactions/CreateCreditMemo_Invoices.xsjs?&DBName=" . $this->sapDatabase;
    
            // Send request to SAP API
            $response = Http::withOptions(['verify' => false])
                ->post($url, $sapJson);
    
            if ($response->successful()) {
                $sapResponseData = $response->json();
    
                if ($sapResponseData['status'] != 'Error') {
                    $sap_doc_entry = $sapResponseData['data']['DocEntry'];
                    $update_params = [
                        'sap_updated' => 1,
                        'sap_updated_at' => now(),
                        'sap_created_id' => $sap_doc_entry,
                        'sap_error_msg' => '',
                        'is_updated_err_msg' => 0,
                    ];
                    DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
                    Log::info('Credit memo successfully created in SAP: ' . json_encode($sapResponseData));
                    echo "Credit memo successfully created in SAP:" . json_encode($sapResponseData);
                } else {
                    $update_params = [
                        'sap_updated' => 1,
                        'sap_updated_at' => now(),
                        'sap_error_msg' => $sapResponseData['data']['error']['message']['value'],
                        'is_updated_err_msg' => 1,
                    ];
                    DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
                    echo "Credit memo error: " . json_encode($sapResponseData);
                }
            } else {
                // Handle HTTP errors (400, 500, etc.)
                $statusCode = $response->status();
                if ($statusCode == 500 || $statusCode == 400) {
                    $update_params = [
                        'sap_updated' => 1,
                        'sap_updated_at' => now(),
                        'sap_error_msg' => "HTTP Error: " . $statusCode,
                        'is_updated_err_msg' => 1,
                    ];
                    DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
                    echo "Credit memo error: " . json_encode(['error' => 'HTTP Error: ' . $statusCode]);
                }
            }
        } catch (\Exception $e) {
            // Handle exceptions
            $update_params = [
                'sap_updated' => 1,
                'sap_updated_at' => now(),
                'sap_error_msg' => $e->getMessage(),
                'is_updated_err_msg' => 1,
            ];
            DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
    
            Log::error('Error while creating credit memo in SAP: ' . $e->getMessage());
        }
    }

    public function SapCreditlinePostByCron()
    {
        try {
            // Fetch a credit memo where sap_updated is 0
            $creditmemo = DB::table('credit_memos as cm')
                ->leftJoin('users as u', 'u.id', '=', 'cm.user_id')
                ->leftJoin('customers as c', 'c.id', '=', 'cm.customer_id') 
                ->select(['cm.*', 'u.ware_house', 'c.card_name' ]) 
                ->where('cm.sap_updated', 0)
                ->first();
    
            if (!$creditmemo) {
                return response()->json(['error' => 'Credit memo not found'], 404);
            }
    
            $invoices = DB::table('invoices')->where('id', $creditmemo->invoice_id)->first();

            $user_data = DB::table('users')->select('*')->where('id', $creditmemo->user_id)->first();
            $sales_person = DB::table('sales_employees')->select('code')->where('id', $creditmemo->sales_emp_id)->first();
            $sequence = $invoices->sales_type === 'Hospital' ? $user_data->cn_sequence : $user_data->cn_other_sequence;

            // Fetch credit memo items
            $creditItems = DB::table('credit_memo_items')->where('credit_id', $creditmemo->id)->get();
            if ($creditItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this credit memo'], 404);
            }

            // Prepare unique identifier
            $uniqueNumber = $creditmemo->id . "_" . date('YmdHis', strtotime($creditmemo->created_at)) . "_" . md5(time() + rand(1, 999999));
            $dueDate = now();
            $orderDate = now();
    
            // Prepare the SAP JSON payload
            $sapJson = [
                'Series' => $user_data->cn_series,
                "CardCode" => $creditmemo->customer_id,
                "CardName" => $creditmemo->card_name,
                "DocDate" => $orderDate,
                "DocDueDate" => $dueDate,
                "NumAtCard" => $creditmemo->ref_id, 
                "U_TRPGUID" => $uniqueNumber,
                "U_TRPSALETYPE" => strtoupper($invoices->sales_type),
                "U_ITG_SignDigest" => $invoices->hash_key_code,
                "U_ITG_KeyVersion" => 11,
                "U_ITG_CertifNum" => "1.1",
                "U_VSPSRN" => $user_data->cn_series,
                "U_VSPSRNO" => $creditmemo->id,
                "TaxDate" => $orderDate,
                "SequenceCode" => $sequence,
                "BPL_IDAssignedToInvoice" => $user_data->branch,
                'SalesPersonCode' => $sales_person->code,      
                "DiscountPercent" => '', 
                "Comments" => $creditmemo->remarks,
                "U_VSPHSKY" => $creditmemo->credit_no,
                "OriginalRefNo" => $invoices->sap_created_id,
                "OriginalRefDate" => $invoices->order_date,

            ];
            // Prepare document lines

            $documentLinesAll = [];
            foreach ($creditItems as $creditItem) {

             $invoice_line_num = DB::table('invoice_items')
                                        ->where('invoice_id' ,$creditmemo->invoice_id)
                                        ->where('item_no' ,$creditItem->item_no)
                                        ->first();

                $batches_list = DB::table('credit_batches')
                    ->where('credit_id', $creditmemo->id)
                    ->where('item_code', $creditItem->item_no)
                    ->get();

                $itemBatches = [];
                foreach ($batches_list as $batch) {
                    $itemBatches[] = [
                        'ItemCode' => $batch->item_code,
                        'BatchNumber' => $batch->batch_num,
                        'Quantity' => $batch->quantity,
                    ];
                }
    
                $documentLinesAll[] = [
                    'ItemCode' => $creditItem->item_no,
                    'WarehouseCode' => $creditmemo->ware_house,
                    'Quantity' => $creditItem->quantity,
                    'PriceBefDi' => $creditItem->price,
                    'Price' =>  $creditItem->price_af_disc,
                    'TaxCode' => "IVA".$creditItem->gst_rate,
                    // 'BaseEntry' => $invoices->sap_created_id,
                    // 'BaseType' => "13", 
                    // 'BaseLine' => $invoice_line_num->sap_base_line,
                    'BatchNumbers' => $itemBatches,
                ];
            }
    
            $sapJson['DocumentLines'] = $documentLinesAll;
            echo json_encode($sapJson, JSON_PRETTY_PRINT);
            //exit;
    
            $url = $this->url."Sales_Transactions/CreateCreditMemo_Invoices.xsjs?&DBName=" . $this->sapDatabase;
    
            // Send request to SAP API
            $response = Http::withOptions(['verify' => false])
                ->post($url, $sapJson);
    
            if ($response->successful()) {
                $sapResponseData = $response->json();
    
                if ($sapResponseData['status'] != 'Error') {
                    $sap_doc_entry = $sapResponseData['data']['DocEntry'];
                    $update_params = [
                        'sap_updated' => 1,
                        'sap_updated_at' => now(),
                        'sap_created_id' => $sap_doc_entry,
                        'sap_error_msg' => '',
                        'is_updated_err_msg' => 0,
                    ];
                    DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
                    Log::info('Credit memo successfully created in SAP: ' . json_encode($sapResponseData));
                    echo "Credit memo successfully created in SAP:" . json_encode($sapResponseData);
                } else {
                    $update_params = [
                        'sap_updated' => 1,
                        'sap_updated_at' => now(),
                        'sap_error_msg' => $sapResponseData['data']['error']['message']['value'],
                        'is_updated_err_msg' => 1,
                    ];
                    DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
                    echo "Credit memo error: " . json_encode($sapResponseData);
                }
            } else {
                // Handle HTTP errors (400, 500, etc.)
                $statusCode = $response->status();
                if ($statusCode == 500 || $statusCode == 400) {
                    $update_params = [
                        'sap_updated' => 1,
                        'sap_updated_at' => now(),
                        'sap_error_msg' => "HTTP Error: " . $statusCode,
                        'is_updated_err_msg' => 1,
                    ];
                    DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
                    echo "Credit memo error: " . json_encode(['error' => 'HTTP Error: ' . $statusCode]);
                }
            }
        } catch (\Exception $e) {
            // Handle exceptions
            $update_params = [
                'sap_updated' => 1,
                'sap_updated_at' => now(),
                'sap_error_msg' => $e->getMessage(),
                'is_updated_err_msg' => 1,
            ];
            DB::table('credit_memos')->where('id', $creditmemo->id)->update($update_params);
    
            Log::error('Error while creating credit memo in SAP: ' . $e->getMessage());
        }
    }
    
    public function baseLine(){

   $inv_data = DB::table('invoices')
   ->where('sap_created_id' , '!=', null)
   ->select('sap_created_id','id')->get();
     foreach ($inv_data as $key => $invoice) {

       echo "Invoice_id".$invoice->id;
        try {
            $url_server = $this->url."Sales_Transactions/InvoiceLineNum.xsjs?DBName=".$this->sapDatabase."&InvDocEntry=".$invoice->sap_created_id;
                    $headers = []; 
                    $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);
                //    Log::info('Raw HTTP Response for Warehouse Data:', ['response' => $response->body()]);
                    $processedWarehouse = [];

            if ($response->successful()) {
                    $responseData = $response->json();
                //   echo "<pre>"; print_r($responseData); exit;
               foreach ($responseData as $key => $item_qty) {

                $update_in_items = DB::table('invoice_items')->where('invoice_id',$invoice->id)
                                                            ->where('item_no', $item_qty['ItemCode'])
                                                            ->update([
                                                                'sap_base_line' => $item_qty['LineNum'],
                                                            ]); 
                    }        
                } 
            }catch (\Exception $e) {
                Log::error('HTTP request failed: ' . $e->getMessage());
                print_r($e->getMessage());
                return response()->json([
                    'success' => 0,
                    'message' => 'Failed to connect to the server!'
                ], 500);
            }
     }
    }


        public function GenerateInvoiceCronPDF()
        {

            $invoice_details = DB::table('invoices')
                ->select('*')
                ->whereNull('generated_pdf')
                ->where('created_at', '>=', Carbon::today())  // Filter records created from the start of today onwards
                ->orderBy('id', 'DESC')
               // ->where('id',2838)
                ->first();
            if (!$invoice_details) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            if(isset($invoice_details->generated_pdf)){

                $pdf_url = url('').'/'.$invoice_details->generated_pdf;

                 return response()->json([
                                         'success' => 1,
                                         'message' => 'Generated PDF',
                                         'pdf_url' => $pdf_url
                                        ], 200);
            }else{
               // echo "esle"; exit;
            $warehouseCode = $invoice_details->store_code;
            $custSubGrp  = $invoice_details->customer_sub_grp;
            //$latestInvoice = $this->getLatestInvoice($warehouseCode, $custSubGrp);
           // echo "<pre>"; print_r($latestInvoice); exit;
           // $wareHouseNum = $this->getWarehouseSeries($warehouseCode);

            // Generate unique invoice number
            $invoiceNo = CreateInvoiceController::generateUniqueInvoiceNo($warehouseCode, $custSubGrp);

            $inv_details = array(
                'date' => $invoice_details->order_date,
                'warehouse_code' => $warehouseCode,
                'due_date' => $invoice_details->due_date,
                'web_id' => $invoiceNo,
                'doc_total' => $invoice_details->total
            );

            $hashKey = QuotationController::HashkeyGenerationInvoice($inv_details,$warehouseCode);


        //$pdfDirectory = public_path('pdf/invoices');
        $pdfName = 'pdf/invoices/invoice_copy_' . $invoice_details->id . '.pdf';

           // $pdfFilePath = $pdfDirectory . '/' . $pdfName;
            $invoiceUpdate = DB::table('invoices')
                ->where('id', $invoice_details->id)
                ->update([
                    'hash_key' => $hashKey['hash_key'],
                    'hash_key_code' => $hashKey['generated_code'],
                    'invoice_no'    => $invoiceNo,
                    'generated_pdf' => $pdfName

                ]);
             $invoicePdfUrl = $this->generateInvoicePDF($invoice_details->id,$warehouseCode);


                if($invoiceUpdate){
                    return response()->json([
                                             'success' => 1,
                                             'message' => 'Payment successfully submitted',
                                             'pdf_url' => $invoicePdfUrl
                                            ], 200);                    

                }
            }
            
            
        }

    function generateInvoicePDF($invoiceNo,$warehouseCode)
    {
        

        $invoice = DB::table('invoices')
            ->join('customers as c', 'invoices.customer_id', '=', 'c.card_code')
            ->join('sales_employees as se', 'se.id' , '=' , 'invoices.sales_emp_id' )
            ->select('invoices.*', 'c.card_name as customer_name', 'c.nif_no' , 'se.memo')
            ->where('invoices.id', $invoiceNo)
            ->first();

            //dd($invoice);
        if (!$invoice) {
            throw new \Exception("Invoice number $invoiceNo not found.");
        }

        // Fetch the invoice items
        $items = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
        $invoiceBatches = DB::table('invoice_batches')->where('invoice_id', $invoice->id)->get();
        $custAddress = DB::table('customer_addresses')
                            ->where('id', $invoice->address)
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
            ->map(function($batch) {
                return [
                    'quantity' => $batch->quantity,
                    'expiry_date' => $batch->expiry_date
                ];
            })
            ->values()
            ->all();
        }

        ksort($gstSummary);

        $itemsPerPage = 11; // Number of items per page
        $totalPages = ceil(count($items) / $itemsPerPage);
        // Generate the PDF
        $pdf = PDF::loadView('pdf.new-format-pdf', compact('invoice', 'items', 'warehouseCode', 'gstSummary','totalPages','custAddress','ware_house_data'))
        ->setPaper('a4')
        ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);;

        // Define folder and file paths
        $folderPath = 'pdf/invoices';
        $pdfDirectory = public_path($folderPath);

        // Create the directory if it doesn't exist
        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        // Define the PDF file name and path
        $pdfName = 'invoice_copy_' . $invoice->id . '.pdf';
        $pdfFilePath = $pdfDirectory . '/' . $pdfName;

        $pdf->render();
        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $domPdf->set_option('isHtml5ParserEnabled', true); // Ensure HTML5 is supported
        $canvas = $domPdf->get_canvas();
        $font = $domPdf->getFontMetrics()->get_font('arial', 'sans-serif');
        $canvas->page_text(500, 800, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
        // Save the PDF to the specified path
        $pdf->save($pdfFilePath);

        if (!file_exists($pdfFilePath)) {
            throw new \Exception("PDF file was not created successfully at $pdfFilePath.");
        }

        return url($folderPath . '/' . $pdfName);
    }




    public function rftFromSAP(Request $request)
    {
        $requestData = $request->all();

        //echo"req"; print_r($requestData); exit;
        try {
            // Validate required fields before proceeding
            if (empty($requestData['ITRDocNum']) || empty($requestData['ITRDocEntry'])) {
                return response()->json(['error' => 'ITRDocNum and ITRDocEntry cannot be empty'], 400);
            }

            DB::beginTransaction();

            // Insert into request_for_tender
            $tenderId = DB::table('request_for_tender')->insertGetId([
                'rft_number' => $requestData['ITRDocNum'],
                'rft_entry' => $requestData['ITRDocEntry'],
                'doc_date' => $requestData['DocDate'],
                'from_store' => $requestData['FromWarehouse'],
                'to_store' => $requestData['ToWarehouse'],
                'created_at' => now(),
            ]);

            // Insert into request_tender_items
            foreach ($requestData['StockTransferLines'] as $item) {
                DB::table('request_tender_items')->insert([
                    'request_for_tender_id' => $tenderId,
                    'rft_number' => $requestData['ITRDocNum'],
                    'item_code' => $item['ItemCode'],
                    'requested_qty' => $item['Quantity'],
                    //'from_store' => $item['FromWarehouseCode'],
                    //'to_store' => $item['WarehouseCode'],
                    //'created_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Tender data inserted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SAP Request Exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Request error: ' . $e->getMessage()], 500);
        }
    }
    public function TruncateStockTables()
    {
       // $trucated = DB::table('warehouse_codebatch_details')->truncate();
       // DB::table('warehouse_stocks')->truncate();  

       // if($trucated)
       // {
       //   return response()->json(['message' => 'Stock Removed successfully..!']);
       // }
    }   
}
