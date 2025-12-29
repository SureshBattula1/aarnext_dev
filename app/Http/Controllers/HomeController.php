<?php

namespace App\Http\Controllers;

use Response;
use Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function index(){
        return view('home');
    }

    public function comingSoon(){
        return view('layouts.coming-soon');
    }

    public function getDashboardStats()
    {
        try {

            $wareHouse = Auth::user()->ware_house;
            $today = Carbon::today();
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $numberOfCustomers = DB::table('customers')->count();

            $numberOfItems = DB::table('items')->count();

            $paymentReportsAmount = DB::table('order_payments')
                            ->where('warehouse_code' , $wareHouse)
                            ->whereDate('created_at', $today)
                            ->sum('amount');

            $paymentReportsAmountMonth = DB::table('order_payments')
                            ->where('warehouse_code' , $wareHouse)
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('amount');

            $cashAmount = DB::table('order_payments')
                            ->where('warehouse_code' , $wareHouse)
                            ->whereDate('created_at', $today)
                            ->whereNotNull('cash')
                            ->sum('cash');

            $cashAmountMonth = DB::table('order_payments')
                            ->where('warehouse_code' , $wareHouse)
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->whereNotNull('cash')
                            ->sum('cash');

            $tpaAmount = DB::table('order_payments')
                            ->where('warehouse_code' , $wareHouse)
                            ->whereDate('created_at', $today)
                            ->whereNotNull('online')
                            ->sum('online');

            $tpaAmountMonth = DB::table('order_payments')
                            ->where('warehouse_code' , $wareHouse)
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->whereNotNull('online')
                            ->sum('online');

            
            $salesOrdersAmount = floor(DB::table('orders')
                                ->whereDate('created_at' , $today)
                                ->sum('total'));

            $salesOrdersAmountMonth = floor(DB::table('orders')
                                ->whereMonth('created_at', $currentMonth)
                                ->whereYear('created_at', $currentYear)
                                ->sum('total'));

            $invoiceAmount = floor(DB::table('invoices')
                                ->whereDate('created_at' , $today)
                                ->sum('total'));

            $invoiceAmountMonth = floor(DB::table('invoices')
                                ->whereMonth('created_at', $currentMonth)
                                ->whereYear('created_at', $currentYear)
                                ->sum('total'));
            
            $creditAmount = floor(DB::table('credit_memos')
                                ->whereDate('created_at' , $today)
                                ->sum('total'));

            $creditAmountMonth = floor(DB::table('credit_memos')
                                ->whereMonth('created_at', $currentMonth)
                                ->whereYear('created_at', $currentYear)
                                ->sum('total'));

            // Prepare response
            $data = [

                'customers_count' => $numberOfCustomers,
                'items_count' => $numberOfItems,
                'order_day_amount' => $salesOrdersAmount,
                'order_month_amount' => $salesOrdersAmountMonth,
                'invoice_day_amount' => $invoiceAmount,
                'invoice_month_amount' => $invoiceAmountMonth,
                'credit_day_amount' => $creditAmount,
                'credit_month_amount' => $creditAmountMonth,
                'payment_reports_day_amount' => $paymentReportsAmount,
                'payment_reports_month_amount' => $paymentReportsAmountMonth,
                'cash_amount' => $cashAmount,
                'cash_amount_month' => $cashAmountMonth,
                'tpa_amount' => $tpaAmount,
                'tpa_amount_month' => $tpaAmountMonth,
                
            ];

            return response()->json($data, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve dashboard data', 'message' => $e->getMessage()], 500);
        }
    }
    public function updateWarehouse(Request $request)
    {
        $users = auth()->user();
        DB::table('users')
            ->whereIn('role', [1, 5])            ->where('id' , $users->id)
            ->update(['ware_house' => $request->warehouse]);

        return redirect()->back()->with('success', 'Warehouse updated successfully for users with role 1.');
    }
}
