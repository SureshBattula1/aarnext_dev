<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PaymentsController extends Controller
{
    public function index(){
        //showPayments();
        return view('directpayments.direct-payments-list');

    }

    public function create(){
        return view('directpayments.direct-payments');
    }

    public function directPaymentsJson(Request $request)
    {
        $data = $request->all();
        if (isset($data['draw'])) {
            $columnArray = ['op.id', 'op.customer_code', 'op.user_id', 'op.payment_method', 'op.amount'];

            try {
                $user = Auth::user();
                $query = DB::table('order_payments as op')
                    ->join('customers as c', 'op.customer_code', '=', 'c.card_code')
                    ->leftJoin('users as u', 'op.user_id', '=', 'u.id')
                    ->select('op.id', 'op.user_id', 'op.payment_method', 'c.card_code' , 'c.card_name' , 'c.grp_name' ,
                     'op.amount' , 'op.created_at' , 'op.cash' , 'op.online', 'op.is_sap_updated','op.sap_created_id','op.sap_updated_at','op.sap_error_msg')
                     ->where('u.ware_house', $user->ware_house)
                     ->orderBy('op.id', 'DESC');

                if (!empty($data['search_user'])) {
                    $searchTerm = '%' . $data['search_user'] . '%';
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('c.card_code', 'LIKE', $searchTerm)
                          ->orWhere('c.card_name', 'LIKE', $searchTerm);
                    });
                }

                if (!empty($request->start_date) && !empty($request->end_date)) {
                    try {
                        $query->whereBetween('op.created_at', [$request->start_date, $request->end_date]);
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Invalid date format'], 400);
                    }
                }

                $totalRecords = $query->count();


                // Order by requested column
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

                $order = $query->get();

                //echo 'test',print_r($order);
                //    exit;

            } catch (\Exception $e) {
                $order = [];
                $totalRecords = 0;
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $order,
            ]);
        }
    }


    public function customerInvoices($customerId)
    {
    $warehouseCode = auth()->user()->ware_house;
    $invoice = DB::table('invoices as i')
                 ->select('i.id', 'i.total','i.invoice_no','i.order_date','i.due_date','i.paid_amount')
                // ->leftjoin('order_payments as p','p.invoice_id','=','i.id')
                 ->where('i.customer_id', $customerId)
                 ->where('store_code',$warehouseCode)
                 ->where('i.status', 0)
                 ->get();

        if ($invoice->isEmpty()) {
            return response()->json([
                'total' => 0,
                'invoice_no' => 'No invoices found',
                'order_date'=>'Null',
                'due_date' => 'Null',
            ]);
        } else {
            return response()->json([
                'invoices' => $invoice->map(function($invoice) {
                    return [
                        'id' => $invoice->id,
                        'total' => $invoice->total,
                        'invoice_no' => $invoice->invoice_no,
                        'order_date' => $invoice->order_date,
                        'due_date' => $invoice->due_date,
                        'paid_amount' =>$invoice->paid_amount,
                        'balance_amount' => ($invoice->total - $invoice->paid_amount)
                    ];
                })
            ]);
        }
    }

    public function storeDirectPayments(Request $request) {
        Log::info('STARTTime: ' . now()->toDateTimeString());

        $existingInvoice = DB::table('invoices')
                            ->where('gu_id',$request->guid)
                            ->first();

            if ($existingInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate submission detected',
                ], 400);
                exit;
            }
            DB::beginTransaction();

        try{

            $userId = Auth::user()->id;
            $warehouseCode = Auth::user()->ware_house;
            $invoiceId = request('invoice_id');
            $totalAmt = request('payment');
            $amount = request('amount');
            $guId = request('guid');
            $cash = $request->input('cash') ?? 0;
            $online = $request->input('online') ?? 0;
            $paymentMethod = request('payment_method');
            $paymentType = request('payment_type');
            $customerCode = request('customer_code');
            $totalAmount = $cash + $online;

            $selected_inv = request('select_invoice');

            $method = null;
            if(request('payment_method') !== 'cash'){
                $method = request('payment_method');
            }
           
                if(empty($selected_inv)){
                $paymen_status = $this->directCustomerPayments($customerCode, $totalAmount, $cash, $online, $userId , $warehouseCode , $paymentMethod , $guId);

                return response()->json([
                    'success' => 1,
                    'message' => 'Direct Payments posted successfully!',
                ]);
            } else {
                $systemName = gethostname();
                $systemIP = $this->getLaptopIp();
                $params = array(
                    'customer_code' => $customerCode,
                    // 'payment_method' => $paymentMethod,
                    //'payment_type' => $cashMethod,
                    'bank_tpa' => $method,
                    'amount' => $totalAmount,
                    'system_name' => $systemName,
                    'system_ip' => $systemIP,
                    'gu_id' => $guId,
                    'user_id' => $userId,
                    'warehouse_code' => $warehouseCode,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );

                if($cash > 0 && $online > 0){
                    $paymentMethod = $paymentMethod . ', cash';
                    $params['cash'] = $cash;
                    $params['online'] = $online;
                // exit;
                }else if($cash > 0){
                    $paymentMethod = 'cash';
                    $params['cash'] = $cash;
                }else if($online > 0){
                    $paymentMethod = 'tpa';
                    $params['online'] = $online;
                }
                $params['payment_method'] = $paymentMethod;
                $orderPaymentId = DB::table('order_payments')->insertGetId($params);

                

                $invoice_sub_data = [];
            for ($i=0; $i < sizeof($request['select_invoice'])  ; $i++) {

                    $selected_str = $request['select_invoice'][$i];
                    $get_number = substr($selected_str, strpos($selected_str, "_") + 1);


                    $invid = $request['invoice_no'][$get_number];
                    $amount = $request['payment_amount'][$get_number];
                    $balance_amount = $request['balance_amount'][$get_number] ?? 0;
                    $total_amount = $request['total'][$get_number];
                    $paid_amount = $request['paid_amount'][$get_number];
                    $totalPaidAmt = $amount + $paid_amount;

                        if($amount >= $balance_amount)
                        {
                            $params = array('status'=> 1,
                                            'paid_amount'=> $totalPaidAmt
                                        ); 

                            if($balance_amount < $amount)
                            {
                                $excess = $amount - $balance_amount;
                                $params['exceess_payment'] = $excess;
                            } 
                        }else{
                        // $my_amt = $amount + $paid_amount;
                            $params = array(

                            'paid_amount'=>  $amount + $paid_amount
                        );

                        }

                        if($amount > 0)
                        {
                        $update = DB::table('invoices')
                        ->where('invoice_no',$request['invoice_no'][$get_number])
                        ->update($params);

                        $direct_payment_sub = DB::table('order_payment_sub')->insert([
                            'order_payment_id' => $orderPaymentId,
                            'invoice_id' => $request['invoice_id'][$get_number],
                            'customer_code' => $customerCode,
                            'amount' => $amount,
                            'user_id' => $userId,
                            'created_at' => now(),
                        ]);
                    }
                }
                DB::commit();
                return response()->json([
                    'success' => 1,
                    'message' => 'Direct Payments posted successfully!',
                    'direct-payments' => $orderPaymentId,
                ]);
            }

        } catch (\Exception $e) {
            // echo"print";
            DB::rollBack();
            echo $e->getMessage();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function directCustomerPayments($customerCode, $totalAmount, $cash, $online, $userId, $warehouseCode, $paymentMethod, $guId)
    {
        try {

            $paymentData = [
                'customer_code'   => $customerCode,
                'amount'          => $totalAmount,
                'cash'            => $cash,
                'online'          => $online,
                'warehouse_code'  => $warehouseCode,
                'user_id'         => $userId,
                'created_at'      => date('Y-m-d H:i:s'),
                'gu_id'           => $guId
            ];
            
            if ($cash > 0 && $online == 0) {
                $paymentData['payment_method'] = 'cash';
            } elseif ($cash == 0 && $online > 0) {
                $paymentData['payment_method'] = $paymentMethod;
            } elseif ($cash > 0 && $online > 0) {
                $paymentData['payment_method'] = $paymentMethod . ', cash';
            }
            
            $customerPayments = DB::table('order_payments')->insertGetId($paymentData);

            DB::table('order_payment_sub')->insert([
                'order_payment_id' => $customerPayments,
                'customer_code' => $customerCode,
                'user_id' => $userId,
                'amount' => $totalAmount,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
    
            return 1;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    

    private function getLaptopIp() {

        $output = array();
        exec('ipconfig /all', $output);


        foreach ($output as $line) {
            if (strpos($line, 'IPv4 Address') !== false) {
                $parts = explode(':', $line);
                return trim(end($parts));
            }
        }


        return '';
    }

    // public function directCustomerPayments($customerCode, $totalAmt, $cash, $online , $userId)
    // {
    //     // echo"amount"; print_r($totalAmt); exit;
    //     try {
    //         echo "<pre>";print_r();exit;
    //         $customerPayments  = DB::table('order_payments')->insert([
    //             'customer_code' => $customerCode,
    //             'amount' => $totalAmt,
    //             'cash' => $cash,
    //             'online' => $online,
    //             'user_id' => $userId,
    //             'created_at' => date('Y-m-d H:i:s'),
    //         ]);
    //         return (1);
    //     } catch (\Exception $e) {
    //         throw $e;
    //     }
    // }

    public function updateOrderPrice(Request $request, $id)
    {
        try{
            //echo "<pre>";print_r($request->all());exit;
            $orderDetails = DB::table('order_items')->where('id', $id)->first();
            $quantity = $request->quantity;
            $discount = $request->discount;
            $tax = $orderDetails->gst_rate;
            $price = $request->price;

            $discountAmount = ($price * $discount) / 100;
            $priceAfDiscount = $price - $discountAmount;

            $taxAmount = ($priceAfDiscount * $tax) / 100;
            $priceAfTax = $priceAfDiscount + $taxAmount;

            $subTotal = $priceAfTax * $quantity;
            $updatePrice = DB::table('order_items')->where('id', $id)->update([
                'price' => $request->price,
                'quantity' => $quantity,
                'disc_value' => $discountAmount,
                'disc_percent' => $request->discount,
                'price_total_af_disc' => $priceAfDiscount * $quantity,
                'subtotal' => $subTotal,

                ]);
                return response()->json([
                    'success' => 1,
                    'message' => ' Item Price Updated successfully!',
                ]);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
}
 