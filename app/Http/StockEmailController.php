<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

use GuzzleHttp\Exception\RequestException;
use Firebase\JWT\JWT;

class StockEmailController extends Controller
{
    public $groupIds;
    public $projectName;
    public $sapDatabase;
    public $url;
    public $tekroiGroupId;

    public function __construct()
    {
        $this->url = config('sapdata.SAP_BASE_URL');
        $this->sapDatabase = config('sapdata.SAP_DATABASE');
        $this->projectName = config('sapdata.PROJECT_NAME');
        $this->tekroiGroupId = config('sapdata.TEKROI_GROUP_ID', 6302568368);


        $envGroupId = config('sapdata.LIVE_WHATSAPP_GROUP_ID');
        $manualGroupId = config('sapdata.TEKROI_GROUP_ID');
        
        $this->groupIds = array_filter([$envGroupId, $manualGroupId]);
    }

    public function stockView()
    {
        $sapStock = $this->url."StockReport/StockCount.xsjs?DBName=". $this->sapDatabase;
    
        try {
    
            $response = Http::withoutVerifying()->get($sapStock);
            
            // Check for success
            if ($response->successful()) {
                $sapData = collect($response->json());
                
                $webSockData = DB::table('warehouse_codebatch_details')
                    ->select('whs_code as WhsCode', DB::raw('COUNT(*) AS total_records'), DB::raw('SUM(quantity) AS QtySum'), 'created_at AS date')
                    ->groupBy('whs_code')
                    ->get()
                    ->keyBy('WhsCode');
    
                    $result = [];
                    $id = 1;
                    
                    foreach ($sapData as $sapItem) {
                        $sapWhsCode = $sapItem['WhsCode'];
                        $sapQty = $sapItem['QtySum'];
                    
                        $local = $webSockData->get($sapWhsCode);
                        $webQty = $local ? $local->QtySum : 0;
                    
                        $status = ($webQty == $sapQty) ? 'Matched' : 'Unmatched';
                    
                        $result[] = [
                            'id' => $id++,
                            'warehouse_code' => $sapWhsCode,
                            'web_quantity' => $webQty,
                            'sap_quantity' => $sapQty,
                            'status' => $status,
                        ];
                    }
                    return view('stock-comparison/stock_comparison', ['data' => $result]);
                   
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch SAP Stock data',
                    'status' => $response->status()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred: ' . $e->getMessage()
            ], 500);
        }
    }


    public function stockData()
    {
        $sapStock = $this->url."StockReport/StockCount.xsjs?DBName=". $this->sapDatabase;
        try {

            $response = Http::withoutVerifying()->get($sapStock);
            
            // Check for success
            if ($response->successful()) {
                $sapData = collect($response->json());
                
                $webSockData = DB::table('warehouse_codebatch_details')
                    ->select('whs_code as WhsCode', DB::raw('COUNT(*) AS total_records'), DB::raw('SUM(quantity) AS QtySum'), 'created_at AS date')
                    ->groupBy('whs_code')
                    ->get()
                    ->keyBy('WhsCode');

                    $result = [];
                    $id = 1;
                    
                    foreach ($sapData as $sapItem) {
                        $sapWhsCode = $sapItem['WhsCode'];
                        $sapQty = $sapItem['QtySum'];
                    
                        $local = $webSockData->get($sapWhsCode);
                        $webQty = $local ? $local->QtySum : 0;
                    
                        $status = ($webQty == $sapQty) ? 'Matched' : 'Unmatched';
                    
                        $result[] = [
                            'id' => $id++,
                            'warehouse_code' => $sapWhsCode,
                            'web_quantity' => $webQty,
                            'sap_quantity' => $sapQty,
                            'status' => $status,
                        ];
                    }
                    $viewPath = url('api/stock-comparison-data');

                    
                    $output = [];
                    foreach ($result as $my_data) {

                        $webQty = (int)$my_data['web_quantity'];
                        $sapQty = round((float)$my_data['sap_quantity']);

                        $emoji = 'ℹ️';
                        if ($my_data['status'] == 'Matched') {
                            $emoji = "✅";
                        } elseif ($my_data['status'] == 'Unmatched') {
                            $emoji = "❌";
                        } 
                        $output[] = "{$emoji} - Warehouse - {$my_data['warehouse_code']}: WEB - {$webQty} & SAP - {$sapQty} Status - {$my_data['status']}";
                    }
                    //echo "<pre>"; print_r($output); 

                    echo $finalOutput = implode("\n", $output);

                    echo "\n";
                    echo $viewPath;

                    $phoneNo = '';
                    $this->sendMessage($viewPath, $finalOutput);
                
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch SAP Stock data',
                    'status' => $response->status(),
                    'view data' => $viewPath
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred: ' . $e->getMessage()
            ], 500);
        }
    }


    public function sendMessage($viewPath, $finalOutput)
    {
        $content = [
                'Project' => $this->projectName,
                'details' => $finalOutput,
                'stock' => $viewPath,
            ];
        foreach ($this->groupIds as $groupId) {
            $params = [
                'token' => '9ffziqpgcrcf6emm',
                'to' => $groupId, 
                'body' => $content
                //  'body' => 'TEST MESSAGE FROM TEKROI'
            ];
            $this->sendWhatsappGroup($params);
        } 
    } 

    public function sendMessageToTekroi($viewPath, $finalOutput)
    {
        $content = [
                'Project' => $this->projectName,
                'details' => $finalOutput,
                'stock' => $viewPath,
            ];
            $groupId = $this->tekroiGroupId;
            $params = [
                'token' => '9ffziqpgcrcf6emm',
                'to' => $groupId, 
                // 'to' => $this->tekroiGroupId, 
                'body' => $content
            ];
            $this->sendWhatsappGroup($params);
    }

    public function CreditCustomer($whatsappData)
    {
        $projectName = $this->projectName;
        $store = $whatsappData['store_type'] ?? 'N/A';
        $customerCode = $whatsappData['customer_code'] ?? 'N/A';
        $pdfUrl = $whatsappData['pdf_url'] ?? 'N/A';
        $docNum = $whatsappData['doc_num'] ?? 'N/A';
        $orderId = $whatsappData['order_id'];

        $approvalUrl = url('sales-order-approvals') . '/' . $orderId;

        $message = "
        📢 *Credit Customer Alert*\n────────────────────────────\n\n" .
        "🏗️ *Project:*        " . str_pad($projectName, 20) . "\n" .
        "🏬 *Store:*           " . str_pad($store, 20) . "\n" .
        "👤 *Customer Code:*   " . str_pad($customerCode, 20) . "\n" .
        "📄 *Doc No:*          " . $docNum . "\n\n" .
        "⚠️ *Note:* A credit customer has generated a new Sales Order.".
        "*Approvals:* " . $approvalUrl;
            // $groupId = 6302568368; 9866894121
            // // $groupId = '120363402379926198@g.us';
            $params = [
                'token' => '9ffziqpgcrcf6emm',
                'to' => 9866894121,
                'filename' => "SalesOrder_{$docNum}.pdf",
                'caption' => $message,
                'document' => $pdfUrl,
            ];
            $this->sendWhatsappGroup($params);
    }
    
    function sendWhatsappGroup($params)
    {
       
        try {

                // echo "Test"; print_r($params); exit;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ultramsg.com/instance102294/messages/document",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($params),
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/x-www-form-urlencoded"
                ),
                ));
                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    // echo "cURL Error #:" . $err;
                    Log::info('WhatsApp Message cURL Error #: ' . $err);
                } else {
                    // echo $response;
                    Log::info('WhatsApp Message response: ' . $response);
                }
            } catch (\Exception $e) {
                Log::error('Exception while sending WhatsApp message: ' . $e->getMessage());
            }
        
    }

    public function penddingSyncDoc() {

        // echo "Test"; print_r('suresh'); exit;
        $today = Carbon::today();

        $penddingInvoices = DB::table('invoices')
                ->whereNull('sap_created_id')
                ->whereDate('created_at', $today)
                ->count();

        $penddingCredits = DB::table('credit_memos')
                ->whereNull('sap_created_id')
                ->whereDate('created_at', $today)
                ->count();

        $penddingPayments = DB::table('order_payments')
                ->whereNull('sap_created_id')
                ->whereDate('created_at', $today)
                ->count();

                $content = [
                    'Project' => $this->projectName,
                    'Date' => $today->format('Y-m-d'),
                    'invoices' => $penddingInvoices,
                    'credits' => $penddingCredits,
                    'payments' => $penddingPayments
                ];
                $groupId = $this->tekroiGroupId;
                // $groupId = 6302568368 ;

                $body = "WEB to SAP Pending Sync Documents\n\n";
                $body .= "Project: {$content['Project']}\n";
                $body .= "Date: {$content['Date']}\n";
                $body .= "Invoices: {$content['invoices']}\n";
                $body .= "Credits: {$content['credits']}\n";
                $body .= "Payments: {$content['payments']}\n\n";
              
                $params = [
                    'token' => '9ffziqpgcrcf6emm',
                    'to' => $groupId,
                    'body' => $body
                ];
                $this->sendWhatsappGroup($params);
    }

    public function sendWhatsAppMessage(Request $request)
    {
        // === YOUR SETTINGS (hardcoded) ===
        $WA_APP_ID   = 'a_171755451399910270';
        $SERVICE_KEY = '45b77fcd-18d4-11ef-9c6a-02ecbb28a0a9';
        $toWaid      = '916302568368';
        $message     = 'Hello 👋 from Laravel test via Webex Connect!';

        // === build JWT (HS256) ===
        $now = time();
        $exp = $now + 5 * 60;
        $claims = [
            'iss' => $WA_APP_ID,
            'sub' => $WA_APP_ID,
            'iat' => $now,
            'exp' => $exp,
            'jti' => bin2hex(random_bytes(8)),
            'scope' => 'messaging.send',
        ];
        $jwt = JWT::encode($claims, $SERVICE_KEY, 'HS256');

        // === payload (WhatsApp Text Message) ===
        $payload = [
            "deliverychannel" => "whatsapp",
            "appid" => $WA_APP_ID,
            "destination" => [
                ["waid" => [$toWaid]]
            ],
            "channels" => [
                "OTT-Messaging" => [
                    "wa" => [
                        "type" => "text",
                        "text" => ["body" => $message]
                    ]
                ]
            ]
        ];

        // === headers (as requested) ===
        $headers = [
            'Content-Type' => 'application/json',
            'key'          => $SERVICE_KEY,
            // include JWT as well if your tenant expects it:
            'Authorization' => "Bearer {$jwt}",
        ];

        // === 1) pick a region that actually resolves ===
        $regions = ['in','us','eu','ap']; // try these in order
        $resolved = null;
        $chosenBase = null;
        foreach ($regions as $r) {
            $host = "{$r}.webexconnect.io";
            $ip = gethostbyname($host);
            if ($ip !== $host) { // DNS resolved
                $resolved = $ip;
                $chosenBase = "https://{$host}";
                break;
            }
        }
        if (!$chosenBase) {
            return response()->json([
                'status' => 'error',
                'message' => "DNS failed: none of (".implode(',', $regions).") resolved on this machine.",
                'fix' => [
                    'Check your tenant region in the Webex Connect portal (“Know your endpoint”).',
                    'If you are in a container/VM, fix DNS (/etc/resolv.conf) or add a proxy.',
                    'Quick test on this host: nslookup in.webexconnect.io | dig +short in.webexconnect.io',
                    'If corporate proxy is required, set Guzzle proxy options.',
                ],
            ], 500);
        }

        // === 2) send ===
        $client = new Client([
            'base_uri'    => $chosenBase,
            'timeout'     => 30,
            'http_errors' => false,
            // 'proxy'     => 'http://user:pass@proxy:port', // uncomment if needed
        ]);

        try {
            $resp = $client->post('/resources/v1/messaging/', [
                'headers' => $headers,
                'json'    => $payload,
            ]);

            return response()->json([
                'status'      => $resp->getStatusCode() < 300 ? 'success' : 'error',
                'httpCode'    => $resp->getStatusCode(),
                'resolved_ip' => $resolved,
                'base_url'    => $chosenBase,
                'jwt'         => $jwt,
                'jwt_header'  => ['alg' => 'HS256', 'typ' => 'JWT'],
                'jwt_expires' => $exp,
                'request'     => ['headers' => $headers, 'payload' => $payload],
                'response'    => json_decode((string)$resp->getBody(), true),
            ], $resp->getStatusCode());

        } catch (RequestException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'hint'    => 'If this still shows “Couldn’t resolve host”, your OS/container DNS needs fixing or region is wrong.',
            ], 500);
        }
    }
}

