<?php
namespace App\Http\Controllers\SapSync;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Exception;
class SapMissingTransactionSyncController extends Controller
{

    function __construct()
    {
        //$this->sapDatabase = env('BASE_API_URL_DELIVERYSOLUTION_MASTERSETUP');
        $this->url = config("sapdata.SAP_BASE_URL");
        $this->sapDatabase = config("sapdata.SAP_DATABASE");
    }
    public function GetInvoiceById($id)
    {

       try {

            $url_server = $this->url ."Sales_Transactions/GetInvoiceWebID.xsjs?DBName=".$this->sapDatabase.'&WebID='.$id;
            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $sapResponseData = $response->json();
               //echo "<pre>"; print_r($sapResponseData);

                if ($sapResponseData) {
                   echo $sap_doc_number = $sapResponseData[0]['DocEntry'];

                    $update_params = [
                        'sap_updated'    => 1,
                        'sap_updated_at' => now(),
                        'sap_created_id' => $sap_doc_number,
                        'is_updated_err_msg' => 0,
                        'sap_error_msg'  => ""
                    ];
                    DB::table('invoices')->where('id', $id)->update($update_params);

                    return response()->json([
                        'message' => 'Successfully GET the Data',
                        'status' => 'success'
                    ], 200);
                } else {
                    
                }
            }


        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage());// exit;
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage(),
                            'item_no' => $data['item_no']
                        ]);
            } 

    }
    public function GetBulkInvoicesById()
    {

       try {


        $sap_created_invs = DB::table('invoices')->select('id','created_at')
                                    ->whereNull('sap_created_id')
                                    ->where('is_updated_err_msg',1)
                                    ->get();
        foreach ($sap_created_invs as $key => $created_invoice) {

            $id = $created_invoice->id;
        echo $url_server = $this->url ."Sales_Transactions/GetInvoiceWebID.xsjs?DBName=".$this->sapDatabase.'&WebID='.$id;

            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $sapResponseData = $response->json();
               //echo "<pre>"; print_r($sapResponseData);

                if ($sapResponseData) {
                   echo $sap_doc_number = $sapResponseData[0]['DocEntry'];

                    $update_params = [
                        'sap_updated'    => 1,
                        'sap_updated_at' => now(),
                        'sap_created_id' => $sap_doc_number,
                        'is_updated_err_msg' => 0,
                        'sap_error_msg'  => ""
                    ];
                    DB::table('invoices')->where('id', $id)->update($update_params);

                    // return response()->json([
                    //     'message' => 'Successfully GET the Data',
                    //     'status' => 'success'
                    // ], 200);
                } else {
                    
                }
            } 


        }


        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage());// exit;
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage(),
                            'item_no' => $data['item_no']
                        ]);
            } 



    }

    public function GetCreditById($id)
    {

       try {

            $url_server = $this->url ."Sales_Transactions/GetCreditMemoWebID.xsjs?DBName=".$this->sapDatabase.'&WebID='.$id;
            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $sapResponseData = $response->json();
               //echo "<pre>"; print_r($sapResponseData);

                if ($sapResponseData) {
                   echo $sap_doc_number = $sapResponseData[0]['DocEntry'];

                    $update_params = [
                        'sap_updated'    => 1,
                        'sap_updated_at' => now(),
                        'sap_created_id' => $sap_doc_number,
                        'is_updated_err_msg' => 0,
                        'sap_error_msg'  => ""
                    ];
                    DB::table('credit_memos')->where('id', $id)->update($update_params);

                    return response()->json([
                        'message' => 'Successfully GET the Data',
                        'status' => 'success'
                    ], 200);
                } else {
                    
                }
            }


        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage());// exit;
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage(),
                            'item_no' => $data['item_no']
                        ]);
            } 


    } 

    public function GetBulkCreditsById()
    {

       try {
                $sap_created_crds = DB::table('credit_memos')->select('id','created_at')
                                            ->whereNull('sap_created_id')
                                            ->where('is_updated_err_msg',1)
                                            ->get();

                foreach ($sap_created_crds as $key => $created_creditmemo) {

                    $id = $created_creditmemo->id;
                $url_server = $this->url ."Sales_Transactions/GetCreditMemoWebID.xsjs?DBName=".$this->sapDatabase.'&WebID='.$id;

                    $headers = [
                        'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                        'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
                    ];

                    $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

                    if ($response->successful()) {
                        $sapResponseData = $response->json();
                       //echo "<pre>"; print_r($sapResponseData);

                        if ($sapResponseData) {
                           echo $sap_doc_number = $sapResponseData[0]['DocEntry'];

                            $update_params = [
                                'sap_updated'    => 1,
                                'sap_updated_at' => now(),
                                'sap_created_id' => $sap_doc_number,
                                'is_updated_err_msg' => 0,
                                'sap_error_msg'  => ""
                            ];
                            DB::table('credit_memos')->where('id', $id)->update($update_params);

                            // return response()->json([
                            //     'message' => 'Successfully GET the Data',
                            //     'status' => 'success'
                            // ], 200);
                        } else {
                            
                        }
                    } 
                }

                } catch (\Exception $e) {
                // Handle connection errors or other exceptions
                        echo "<pre>"; print_r($e->getMessage());// exit;
                 Log::error('Error during Item WEB POST request', [
                                    'message' => $e->getMessage(),
                                    'item_no' => $data['item_no']
                                ]);
        } 
    }

   
    public function GetInvoiceIdCredit($id){
        try {
            $credit = DB::table('credit_memos as cm')
                ->leftJoin('invoices as i', 'i.id', '=', 'cm.invoice_id')
                ->select('cm.*', 'i.sap_created_id as invoice_doc_num') 
                ->where('cm.id', $id)
                ->first();
    
            if (!$credit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credit memo not found.'
                ], 404);
            }
            echo "<pre>"; print_r($credit);
    
            // return response()->json([
            //     'success' => true,
            //     'data' => $credit
            // ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the credit memo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function GetInvoicePaymentsId($id)
    {

       try {
            $url_server = $this->url ."Sales_Transactions/GetIPWebID.xsjs?DBName=".$this->sapDatabase.'&WebID='.$id;
            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            echo $response;
            if ($response->successful()) {
                $sapResponseData = $response->json();
               echo "<pre success>"; print_r($sapResponseData); exit;

                if ($sapResponseData) {
                   echo $sap_doc_number = $sapResponseData[0]['DocEntry'];

                    // $update_params = [
                    //     'sap_updated'    => 1,
                    //     'sap_updated_at' => now(),
                    //     'sap_created_id' => $sap_doc_number,
                    //     'is_updated_err_msg' => 0,
                    //     'sap_error_msg'  => ""
                    // ];
                    // DB::table('credit_memos')->where('id', $id)->update($update_params);

                    return response()->json([
                        'message' => 'Successfully GET the Data',
                        'status' => 'success'
                    ], 200);
                } else {
                    
                }
            }


        } catch (\Exception $e) {
        // Handle connection errors or other exceptions
                echo "<pre>"; print_r($e->getMessage());// exit;
         Log::error('Error during Item WEB POST request', [
                            'message' => $e->getMessage(),
                            'item_no' => $data['item_no']
                        ]);
            } 


    } 

    public function GetRFTById($id)
    {
        try {
            $url_server = $this->url . "Sales_Transactions/GetRFTWebID.xsjs?DBName=" . $this->sapDatabase . '&WebID=' . $id;
            $headers = [
                'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
            ];

            $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

            if ($response->successful()) {
                $sapResponseData = $response->json();

                if (!empty($sapResponseData)) {
                    $update_params = [];

                    if (count($sapResponseData) === 1) {
                        $from_doc_num = $sapResponseData[0]['DocNum'];

                        $update_params = [
                            'from_sap_update'    => 1,
                            'from_sap_created_id' => $from_doc_num,
                            'sap_error_msg_from'  => "",
                        ];
                    } elseif (count($sapResponseData) === 2) {
                        $from_doc_num = $sapResponseData[0]['DocNum'];
                        $to_doc_num = $sapResponseData[1]['DocNum'];

                        $update_params = [
                            'from_sap_update'    => 1,
                            'from_sap_created_id' => $from_doc_num,
                            'to_sap_update'      => 1,
                            'to_sap_created_id'   => $to_doc_num,
                            'sap_error_msg'       => "",
                        ];
                    }

                    DB::table('request_for_tender')->where('rft_number', $id)->update($update_params);

                    return response()->json([
                        'message' => 'Successfully GET the Data',
                        'status'  => 'success'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'No data found in SAP response',
                        'status'  => 'error'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'SAP server error',
                    'status'  => 'error',
                    'details' => $response->body()
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Error during RFT GET request', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Exception occurred',
                'status'  => 'error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function SyncRFTBulkFromSAP()
    {
        try {
            $sap_rfts = DB::table('request_for_tender')
                ->select('id', 'rft_number')
                ->where(function ($query) {
                    $query->whereNull('from_sap_created_id')
                        ->orWhereNull('to_sap_created_id');
                })
                ->where(function ($query) {
                    $query->where('from_sap_update', 1)
                        ->orWhere('to_sap_update', 1);
                })
                ->get();

            $success_count = 0;
            $fail_count = 0;

            foreach ($sap_rfts as $rft) {
                $id = $rft->rft_number;
                $url_server = $this->url . "Sales_Transactions/GetRFTWebID.xsjs?DBName=" . $this->sapDatabase . '&WebID=' . $id;
                $headers = [
                    'client_id' => 'lwcH6y2pT5eCYTLr93LatQ==',
                    'client_secret' => 'qt+h23rs9botqp//AsAYEg==',
                ];

                try {
                    $response = Http::withHeaders($headers)->withoutVerifying()->get($url_server);

                    if ($response->successful()) {
                        $sapResponseData = $response->json();

                        if (!empty($sapResponseData)) {
                            $update_params = [];

                            if (count($sapResponseData) === 1) {
                                $from_doc_num = $sapResponseData[0]['DocNum'];
                                $update_params = [
                                    'from_sap_update'    => 1,
                                    'from_sap_created_id' => $from_doc_num,
                                    'sap_error_msg_from'  => '',
                                ];
                            } elseif (count($sapResponseData) === 2) {
                                $from_doc_num = $sapResponseData[0]['DocNum'];
                                $to_doc_num   = $sapResponseData[1]['DocNum'];
                                $update_params = [
                                    'from_sap_update'    => 1,
                                    'from_sap_created_id' => $from_doc_num,
                                    'to_sap_update'      => 1,
                                    'to_sap_created_id'   => $to_doc_num,
                                    'sap_error_msg'       => '',
                                ];
                            }

                            DB::table('request_for_tender')
                                ->where('rft_number', $id)
                                ->update($update_params);
                            // echo "Updated: $update_params\n";
                            $success_count++;
                        } else {
                            Log::warning("SAP response empty for RFT: $id");
                            $fail_count++;
                        }
                    } else {
                        Log::warning("SAP API failed for RFT: $id", ['response' => $response->body()]);
                        $fail_count++;
                    }

                } catch (\Exception $e) {
                    Log::error("Error syncing RFT: $id", ['exception' => $e->getMessage()]);
                    $fail_count++;
                }
            }

            return response()->json([
                'message' => 'RFT Bulk Sync Completed',
                'total_synced' => $success_count,
                'total_failed' => $fail_count,
            ]);

        } catch (\Exception $e) {
            Log::error("Bulk RFT Sync Error", ['exception' => $e->getMessage()]);
            return response()->json([
                'message' => 'Bulk sync failed',
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
