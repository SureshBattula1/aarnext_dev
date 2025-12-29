<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class InvoicePaymentsController extends Controller
{
    public function index(){
        return view('invoice-payments.inv-payments');

    }

    public function getInvoicePayments(){
            $warehouseCode = Auth::user()->ware_house;
            $today = Carbon::today();
            $invPayments =  DB::table('invoices as i')
                ->leftJoin('credit_memos as cm' , 'cm.invoice_id', '=' ,'i.id')
                ->select('i.id','i.customer_id', 'i.invoice_no as doc_num', 'i.sales_type', 'i.total as invoice_total' , 'i.paid_amount', 'cm.total as credit_total')
                ->whereDate('i.created_at', $today)
                ->where('i.paid_amount', '=', 0)
                ->where('i.store_code', $warehouseCode)
                ->whereIn('i.sales_type', ['cash'])
                ->get();

            return response()->json([
                'data' => $invPayments 
            ]);
    }

    public function storebulkPayments(Request $request) {
        try {

            // echo'data::'; print_r($request->all()); exit;

            $userId = Auth::user()->id;
            $warehouseCode = Auth::user()->ware_house;
            $systemName = gethostname();
            $systemIP = $this->getLaptopIp();
            $paymentsData = $request->input('data');
            $totCollection = $request->input('tot_collection');
            $totTpa = $request->input('tot_tpa');
            $totCash = $request->input('tot_cash');
    
            if (!is_array($paymentsData) || empty($paymentsData)) {
                return response()->json(['error' => 'Invalid payment data'], 400);
            }

            $bulkInvPayments = DB::table('bulk_inv_payments')->insertGetId([
                'tot_collection' => $totCollection, 
                'tot_tpa' => $totTpa,
                'tot_cash' => $totCash,
                'created_at' => now(),
            ]);
            
            // Process each payment entry
            foreach ($paymentsData as $payment) {
                $invoiceId = $payment['id'];
                $customerCode = $payment['customer_id'];
                $invoiceAmt = $payment['invoice_total'];
                $credit_note_amt = $payment['credit_amt'];
                $invoiceTotal = $payment['balance_amt'];

                $cash = $payment['cash_amt'];
                $tpa = $payment['tpa_amt'];
                $creditAmt = $payment['credit_amt'];
                $balanceAmt = $payment['balance_amt'];
                $docNum = $payment['doc_num'];
                $totalAmount = $cash + $tpa;
    
                // Check if invoice exists
                $customerInvoices = DB::table('invoices')
                    ->where('id', $invoiceId)
                    ->where('customer_id', $customerCode)
                    ->where('status', 0)
                    ->count();
    
                if ($customerInvoices === 0) {
                    return response()->json(['error' => "Invoice not found for ID: $invoiceId"], 404);
                }
    
                $paymentMethod = '';
                if ($cash > 0 && $tpa > 0) {
                    $paymentMethod = 'cash, tpa';
                } elseif ($cash > 0) {
                    $paymentMethod = 'cash';
                } elseif ($tpa > 0) {
                    $paymentMethod = 'tpa';
                }

                if($tpa > 0){
                    $method = 'tpa';
                }else {
                    $method = '';
                }

                $orderPaymentId = DB::table('order_payments')->insertGetId([
                    'customer_code' => $customerCode,
                    'amount' => $totalAmount,
                    'system_name' => $systemName,
                    'system_ip' => $systemIP,
                    'user_id' => $userId,
                    'warehouse_code' => $warehouseCode,
                    'payment_method' => $paymentMethod,
                    'bank_tpa' => $method,
                    'cash' => $cash,
                    'online' => $tpa,
                    'bulk_inv_payments_id' => $bulkInvPayments,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update Invoice Status
                $paidAmount = DB::table('invoices')
                    ->where('id', $invoiceId)
                    ->value('paid_amount');

                $totalPaidAmt = $paidAmount + $cash + $tpa;
                $updateParams = ['paid_amount' => $totalPaidAmt];
                $updateParams['status'] = 1;
                if ($totalPaidAmt > $invoiceTotal) {
                    $updateParams['excess_payment'] = $totalPaidAmt - $invoiceTotal;
                }
                
                DB::table('invoices')->where('id', $invoiceId)->update($updateParams);
                // Insert into Order Payment Sub
                DB::table('order_payment_sub')->insert([
                    'order_payment_id' => $orderPaymentId,
                    'invoice_id' => $invoiceId,
                    'customer_code' => $customerCode,
                    'amount' => $cash + $tpa,
                    'user_id' => $userId,
                    'created_at' => now(),
                ]);
            }
    
            return response()->json([
                'success' => 1,
                'message' => 'Bulk Payments posted successfully!',
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
    
}
