<?php
namespace App\Http\Controllers\SapSync;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Exception;
class SapInvoicePostByUserController extends Controller
{

    function __construct()
    {
          $this->url = config("sapdata.SAP_BASE_URL");
          $this->sapDatabase = config("sapdata.SAP_DATABASE");
    }

    public function SapInvoicePostByUser()
    {
      $params = array('CompanyDB' => $this->sapDatabase,
                      'Password'  => 'Aarnext@2025',
                      'UserName'  => 'AA-PAL1'
                     );
      $session_url = $this->url."XSJS_SAP/Login.xsjs?&DBName=" . $this->sapDatabase;
      $response = Http::withOptions(['verify' => false])
                            ->post($session_url, $params);
      if ($response->successful()) {
            $sapResponseData = $response->json();
            $session_id = $sapResponseData['SessionId'];
        try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-PAL')
                         ->where('user_id',55)
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
            $sapJson = $this->prepareSapJson($invoice, $invoiceItems);
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
      $params = array('CompanyDB' => $this->sapDatabase,
                      'Password'  => 'Aarnext@2025',
                      'UserName'  => 'AA-PAL2'
                     );
      $session_url = $this->url."XSJS_SAP/Login.xsjs?&DBName=" . $this->sapDatabase;
      $response = Http::withOptions(['verify' => false])
                            ->post($session_url, $params);
      if ($response->successful()) {
            $sapResponseData = $response->json();
            $session_id = $sapResponseData['SessionId'];
        try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-PAL')
                         ->where('user_id',56)
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
            $sapJson = $this->prepareSapJson($invoice, $invoiceItems);
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
      $params = array('CompanyDB' => $this->sapDatabase,
                      'Password'  => 'Aarnext@2025',
                      'UserName'  => 'AA-PAL3'
                     );
      $session_url = $this->url."XSJS_SAP/Login.xsjs?&DBName=" . $this->sapDatabase;
      $response = Http::withOptions(['verify' => false])
                            ->post($session_url, $params);
      if ($response->successful()) {
            $sapResponseData = $response->json();
            $session_id = $sapResponseData['SessionId'];
        try {
            $invoice = DB::table('invoices')
                         ->where('sap_updated', 0)
                         ->where('is_updated_err_msg',0)
                         ->whereNotNull('hash_key')
                         ->where('store_code','AAR-PAL')
                         ->where('user_id',57)
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
            $sapJson = $this->prepareSapJson($invoice, $invoiceItems);
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



   
   






}
