<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Firebase\JWT\JWT; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class StockEmailController extends Controller
{
    public $groupIds;
    public $projectName;
    public $sapDatabase;
    public $url;
    public $tekroiGroupId;
    public $RftIssuesGroup;

    public function __construct()
    {
        $this->url = config('sapdata.SAP_BASE_URL');
        $this->sapDatabase = config('sapdata.SAP_DATABASE');
        $this->projectName = config('sapdata.PROJECT_NAME');
        $this->tekroiGroupId = config('sapdata.TEKROI_GROUP_ID', 6302568368);
        $this->RftIssuesGroup = '120363402439631697@g.us';

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
                'body' => $content
            ];
            $this->sendWhatsappGroup($params);
    }
    
    function sendWhatsappGroup($params)
    {
       
        try {

                // echo "Test"; print_r($params); exit;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ultramsg.com/instance102294/messages/chat",
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

                $body = "*🔴 WEB to SAP Pending Sync Documents*\n\n";
                $body .= "*🏬 Project:* {$content['Project']}\n";
                $body .= "*🕒 Date:* {$content['Date']}\n";
                $body .= "*📄 Invoices:* {$content['invoices']}\n";
                $body .= "*📄 Credits:* {$content['credits']}\n";
                $body .= "*📄 Payments:* {$content['payments']}\n\n";
              
                $params = [
                    'token' => '9ffziqpgcrcf6emm',
                    'to' => $groupId,
                    'body' => $body
                ];
                $this->sendWhatsappGroup($params);
    }

    public function RFTpostSapError($errorData){
        try {
    
                $body = "*🔴 WEB to SAP Sync Error - RFT Posting Failed*\n\n";
                $body .= "*🏬 Project Name:* {$this->projectName}\n";
                $body .= "*🏭 Store Type:* {$errorData['store_type']}\n";
                $body .= "*🏪 Store Code:* {$errorData['store_code']}\n";
                $body .= "*📄 RFT Number:* {$errorData['rft_number']}\n";
                $body .= "*🕒 Date:* " . now()->format('d-m-Y') . "\n";
                $body .= "*❗ SAP Error:* {$errorData['sap_error_msg']}\n";
    
                $params = [
                    'token' => '9ffziqpgcrcf6emm',
                    'to' => $this->RftIssuesGroup, // RFT Issues Group
                    // 'to' => '6302568368',
                    'body' => $body
                ];
                // echo "Test"; print_r($params); exit;
                $this->sendWhatsappGroup($params);
            } catch(\Exception $e){
                Log::error($e->getMessage());
            }
        }

        // public function sendWhatsAppMessage(Request $request)
        // {
        //     // --- Tenant settings (keep these!) ---
        //     $WA_APP_ID   = 'a_171755451399910270';      // your WhatsApp App Id
        //     $SERVICE_KEY = '45b77fcd-18d4-11ef-9c6a-02ecbb28a0a9';   // Service Key (shared secret)
        //     $BASE        = ' https://api.imiconnect.io/resources/v1/messaging';            // EXACT region from “Know your endpoint”
        //     $toWaid      = '916302568368';
        //     $message     = 'Hello 👋 from Laravel test via Webex Connect!';
        
        //     // --- Build JWT (HS256). Keep the token short-lived (<= 5 min) ---
        //     $now = time();
        //     $claims = [
        //         'iss'   => $WA_APP_ID,            // issuer = your app id
        //         'sub'   => $WA_APP_ID,            // subject = your app id
        //         'iat'   => $now,                   // issued at (epoch)
        //         'exp'   => $now + 300,             // expires in 5 minutes
        //         'jti'   => bin2hex(random_bytes(8)),
        //         'scope' => 'messaging.send',       // required scope
        //     ];
        //     $jwt = JWT::encode($claims, $SERVICE_KEY, 'HS256');
        
        //     // --- Correct payload for WhatsApp Text Message ---
        //     $payload = [
        //         "deliverychannel" => "whatsapp",
        //         "appid"           => $WA_APP_ID,
        //         "destination"     => [
        //             [ "waid" => $toWaid ]          // STRING (E.164 without +)
        //         ],
        //         "channels"        => [
        //             "whatsapp" => [
        //                 "type" => "text",
        //                 "text" => [ "body" => $message ]
        //             ]
        //         ]
        //     ];
        
        //     // --- Headers ---
        //     $headers = [
        //         'Content-Type'  => 'application/json',
        //         'Accept'        => 'application/json',
        //         'key'           => $SERVICE_KEY,         // still required
        //         'Authorization' => "Bearer {$jwt}",      // JWT required by your tenant
        //     ];
        
        //     $client = new Client([
        //         'base_uri'    => $BASE,
        //         'timeout'     => 30,
        //         'http_errors' => false,
        //     ]);
        
        //     $res = $client->post('/resources/v1/messaging/', [
        //         'headers' => $headers,
        //         'json'    => $payload,
        //     ]);
        
        //     return response()->json([
        //         'status'   => $res->getStatusCode() < 300 ? 'success' : 'error',
        //         'httpCode' => $res->getStatusCode(),
        //         'body'     => json_decode((string)$res->getBody(), true),
        //     ], $res->getStatusCode());
        // }

//         public function sendWhatsAppMessage(Request $request)
// {
//     $SERVICE_KEY = '45b77fcd-18d4-11ef-9c6a-02ecbb28a0a9'; // sandbox key
//     $toWaid = '916302568368'; // recipient number
//     $message = 'Hello 👋 from Laravel Sandbox!';

//     $payload = [
//         "from" => '9392678496',
//         "to" => $toWaid,
//         "type" => "text",
//         "content" => [
//             "text" => $message
//         ]
//     ];

//     $headers = [
//         'Content-Type' => 'application/json',
//         'key'          => $SERVICE_KEY,
//     ];

//     $client = new Client([
//         'base_uri' => 'https://api-sandbox.imiconnect.io',
//         'timeout' => 30,
//         'http_errors' => false,
//     ]);

//     try {
//         $resp = $client->post('/v1/whatsapp/messages', [
//             'headers' => $headers,
//             'json'    => $payload,
//         ]);

//         return response()->json([
//             'status' => $resp->getStatusCode() < 300 ? 'success' : 'error',
//             'httpCode' => $resp->getStatusCode(),
//             'response' => json_decode($resp->getBody(), true),
//         ]);
//     } catch (RequestException $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => $e->getMessage(),
//         ], 500);
//     }
// }

// public function sendWhatsAppMessage(Request $request)
// {
//     $APP_ID      = 'a_171755451399910270';
//     $SERVICE_KEY = 'OTgwMThkODctMTMwOC00ZWQwLTg0YmMtMTkxYmYxZTMzODc3';
//     $BASE        = 'https://in.webexconnect.io/resources/v1/messaging"';
//     $TO_WAID     = '916302568368';

//     $templateId  = '602076559564625';

//     $payload = [
//         "appid" => $APP_ID,
//         "deliverychannel" => "whatsapp",
//         "message" => [
//             "template" => $templateId,
//             "parameters" => [
//                 "variable1" => "Test User",
//                 "variable2" => "SRN9492294"
//             ]
//         ],
//         "destination" => [
//             ["waid" => [$TO_WAID]]
//         ]
//     ];

//     $headers = [
//         "Content-Type"  => "application/json",
//         "Accept"        => "application/json",
//         "key"           => $SERVICE_KEY,
//     ];

//     $client = new \GuzzleHttp\Client(['base_uri' => $BASE]);

//     $res = $client->post('/resources/v1/messaging', [
//         'headers' => $headers,
//         'json'    => $payload,
//     ]);

//     return response()->json([
//         'status'   => $res->getStatusCode() < 300 ? 'success' : 'error',
//         'httpCode' => $res->getStatusCode(),
//         'body'     => json_decode((string)$res->getBody(), true),
//         'sent'     => $payload,
//     ], $res->getStatusCode());
// }

public function sendWhatsAppMessage(Request $request)
{
    $APP_ID      = 'a_171755451399910270';        
    $SERVICE_KEY = '45b77fcd-18d4-11ef-9c6a-02ecbb28a0a9';   
    $TEMPLATE_ID = '6020765595x64625';  
    $TO_WAID     = '+916302568368';
    $BASE_URL    = 'https://api.imiconnect.in';

    // === Build JSON payload ===
    $payload = [
        "appid" => $APP_ID,
        "deliverychannel" => "whatsapp",
        "message" => [
            "template" => $TEMPLATE_ID,
            "parameters" => [
                "variable1" => "Test User",
                "variable2" => "SRN9492294"
            ]
        ],
        "destination" => [
            [
                "waid" => [$TO_WAID]
            ]
        ]
    ];

    // === Prepare HTTP headers ===
    $headers = [
        "Content-Type"  => "application/json",
        "key"           => $SERVICE_KEY,
    ];

    // === Send request using Guzzle ===
    try {
        $client = new Client([
            'base_uri' => $BASE_URL,
            'timeout'  => 30,
        ]);

        $response = $client->post('/resources/v1/messaging', [
            'headers' => $headers,
            'json'    => $payload,
        ]);

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);

        return response()->json([
            'status'   => $statusCode < 300 ? 'success' : 'error',
            'httpCode' => $statusCode,
            'response' => $body,
            'sentData' => $payload,
        ], $statusCode);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}
}




