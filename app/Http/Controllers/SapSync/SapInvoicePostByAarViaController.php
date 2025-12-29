<?php
namespace App\Http\Controllers\SapSync;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Exception;
use App\Http\Controllers\AarnextimportController;
class SapInvoicePostByAarViaController extends Controller
{

    function __construct()
    {
        //$this->sapDatabase = env('BASE_API_URL_DELIVERYSOLUTION_MASTERSETUP');
        $this->url = config("sapdata.SAP_BASE_URL");
        $this->sapDatabase = config("sapdata.SAP_DATABASE");
    }

    public function SapInvoicePostByUser()
    {
         $user_session = DB::table('users')->select('session_key')->where('id',72)->first();
              if ($user_session) {
                    $session_id = $user_session->session_key;
                try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-VIA')
                         ->where('user_id',72)
                         ->first();
         

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }
            echo "INVP".$invoice->id;

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the necessary data for the API request
            $aarnextController = new AarnextimportController();
            $sapJson = $aarnextController->prepareSapJson($invoice, $invoiceItems);
            //echo json_encode($sapJson, JSON_PRETTY_PRINT);

            $url = $this->url."Sales_Transactions/CreateInvoiceWithSesion.xsjs?&DBName=" . $this->sapDatabase .'&SessionId='.$session_id;

        //   echo "<pre>"; print_r($sapJson);

            // Make the API call using HTTP client
            $response = Http::withOptions(['verify' => false])
                            ->post($url, $sapJson);

            // If the request was successful
            if ($response->successful()) {
                $sapResponseData = $response->json();
                // If no errors in SAP response
                if ($sapResponseData['status'] !== 'Error') {
                    echo "DOCENTRY".$sap_doc_entry = $sapResponseData['data']['DocEntry'];
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

    }


    public function SapInvoicePostByUser2()
    {
         $user_session = DB::table('users')->select('session_key')->where('id',73)->first();
              if ($user_session) {
                    $session_id = $user_session->session_key;
                try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-VIA')
                         ->where('user_id',73)
                         ->first();
         

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }
            echo "INVP".$invoice->id;

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the necessary data for the API request
            $aarnextController = new AarnextimportController();
            $sapJson = $aarnextController->prepareSapJson($invoice, $invoiceItems);
            //echo json_encode($sapJson, JSON_PRETTY_PRINT);

            $url = $this->url."Sales_Transactions/CreateInvoiceWithSesion.xsjs?&DBName=" . $this->sapDatabase .'&SessionId='.$session_id;

        //   echo "<pre>"; print_r($sapJson);

            // Make the API call using HTTP client
            $response = Http::withOptions(['verify' => false])
                            ->post($url, $sapJson);

            // If the request was successful
            if ($response->successful()) {
                $sapResponseData = $response->json();
                // If no errors in SAP response
                if ($sapResponseData['status'] !== 'Error') {
                    echo "DOCENTRY".$sap_doc_entry = $sapResponseData['data']['DocEntry'];
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

    }

    public function SapInvoicePostByUser3()
    {
         $user_session = DB::table('users')->select('session_key')->where('id',74)->first();
              if ($user_session) {
                    $session_id = $user_session->session_key;
                try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-VIA')
                         ->where('user_id',74)
                         ->first();
        
            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }
             echo "INVP".$invoice->id;

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the necessary data for the API request
            $aarnextController = new AarnextimportController();
            $sapJson = $aarnextController->prepareSapJson($invoice, $invoiceItems);
            //echo json_encode($sapJson, JSON_PRETTY_PRINT);

            $url = $this->url."Sales_Transactions/CreateInvoiceWithSesion.xsjs?&DBName=" . $this->sapDatabase .'&SessionId='.$session_id;

        //   echo "<pre>"; print_r($sapJson);

            // Make the API call using HTTP client
            $response = Http::withOptions(['verify' => false])
                            ->post($url, $sapJson);

            // If the request was successful
            if ($response->successful()) {
                $sapResponseData = $response->json();
                // If no errors in SAP response
                if ($sapResponseData['status'] !== 'Error') {
                    echo "DOCENTRY".$sap_doc_entry = $sapResponseData['data']['DocEntry'];
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

    }

    public function SapInvoicePostByUser4()
    {
         $user_session = DB::table('users')->select('session_key')->where('id',75)->first();
              if ($user_session) {
                    $session_id = $user_session->session_key;
                try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-VIA')
                         ->where('user_id',75)
                         ->first();
         
            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }
            echo "INVP".$invoice->id;

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the necessary data for the API request
            $aarnextController = new AarnextimportController();
            $sapJson = $aarnextController->prepareSapJson($invoice, $invoiceItems);
            //echo json_encode($sapJson, JSON_PRETTY_PRINT);

            $url = $this->url."Sales_Transactions/CreateInvoiceWithSesion.xsjs?&DBName=" . $this->sapDatabase .'&SessionId='.$session_id;

        //   echo "<pre>"; print_r($sapJson);

            // Make the API call using HTTP client
            $response = Http::withOptions(['verify' => false])
                            ->post($url, $sapJson);

            // If the request was successful
            if ($response->successful()) {
                $sapResponseData = $response->json();
                // If no errors in SAP response
                if ($sapResponseData['status'] !== 'Error') {
                    echo "DOCENTRY".$sap_doc_entry = $sapResponseData['data']['DocEntry'];
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

    }

    public function SapInvoicePostByUser5()
    {
         $user_session = DB::table('users')->select('session_key')->where('id',76)->first();
              if ($user_session) {
                    $session_id = $user_session->session_key;
                try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-VIA')
                         ->where('user_id',76)
                         ->first();
         

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }
            echo "INVP".$invoice->id;

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the necessary data for the API request
            $aarnextController = new AarnextimportController();
            $sapJson = $aarnextController->prepareSapJson($invoice, $invoiceItems);
            //echo json_encode($sapJson, JSON_PRETTY_PRINT);

            $url = $this->url."Sales_Transactions/CreateInvoiceWithSesion.xsjs?&DBName=" . $this->sapDatabase .'&SessionId='.$session_id;

        //   echo "<pre>"; print_r($sapJson);

            // Make the API call using HTTP client
            $response = Http::withOptions(['verify' => false])
                            ->post($url, $sapJson);

            // If the request was successful
            if ($response->successful()) {
                $sapResponseData = $response->json();
                // If no errors in SAP response
                if ($sapResponseData['status'] !== 'Error') {
                    echo "DOCENTRY".$sap_doc_entry = $sapResponseData['data']['DocEntry'];
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

    }


    public function SapInvoicePostByUser6()
    {
         $user_session = DB::table('users')->select('session_key')->where('id',77)->first();
              if ($user_session) {
                    $session_id = $user_session->session_key;
                try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-VIA')
                         ->where('user_id',77)
                         ->first();
         

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }
            echo "INVP".$invoice->id;

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the necessary data for the API request
            $aarnextController = new AarnextimportController();
            $sapJson = $aarnextController->prepareSapJson($invoice, $invoiceItems);
            //echo json_encode($sapJson, JSON_PRETTY_PRINT);

            $url = $this->url."Sales_Transactions/CreateInvoiceWithSesion.xsjs?&DBName=" . $this->sapDatabase .'&SessionId='.$session_id;

        //   echo "<pre>"; print_r($sapJson);

            // Make the API call using HTTP client
            $response = Http::withOptions(['verify' => false])
                            ->post($url, $sapJson);

            // If the request was successful
            if ($response->successful()) {
                $sapResponseData = $response->json();
                // If no errors in SAP response
                if ($sapResponseData['status'] !== 'Error') {
                    echo "DOCENTRY".$sap_doc_entry = $sapResponseData['data']['DocEntry'];
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

    }

    public function SapInvoicePostByUser7()
    {
         $user_session = DB::table('users')->select('session_key')->where('id',78)->first();
              if ($user_session) {
                    $session_id = $user_session->session_key;
                try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-VIA')
                         ->where('user_id',78)
                         ->first();
         

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }
            echo "INVP".$invoice->id;

            $invoiceItems = DB::table('invoice_items')->where('invoice_id', $invoice->id)->get();
            if ($invoiceItems->isEmpty()) {
                return response()->json(['error' => 'No items found for this invoice'], 404);
            }

            // Prepare the necessary data for the API request
            $aarnextController = new AarnextimportController();
            $sapJson = $aarnextController->prepareSapJson($invoice, $invoiceItems);
            //echo json_encode($sapJson, JSON_PRETTY_PRINT);

            $url = $this->url."Sales_Transactions/CreateInvoiceWithSesion.xsjs?&DBName=" . $this->sapDatabase .'&SessionId='.$session_id;

        //   echo "<pre>"; print_r($sapJson);

            // Make the API call using HTTP client
            $response = Http::withOptions(['verify' => false])
                            ->post($url, $sapJson);

            // If the request was successful
            if ($response->successful()) {
                $sapResponseData = $response->json();
                // If no errors in SAP response
                if ($sapResponseData['status'] !== 'Error') {
                    echo "DOCENTRY".$sap_doc_entry = $sapResponseData['data']['DocEntry'];
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

    }






}
