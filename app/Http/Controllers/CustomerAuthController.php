<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\CustomerForgotPassword;
use DB;

class CustomerAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.customer_login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'card_code' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('card_code', 'password');
        if (Auth::guard('customer')->attempt($credentials)) {
            return redirect()->route('customer.dashboard')
                           ->with('success', 'Signed in successfully!');
        }

        return redirect()->back()
                        ->withErrors(['card_code' => 'Customer Login details are not valid']);
    }

    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        $warehouseCode = $customer->store_location;

        // Pending Orders (not approved)
        $totalOrders = DB::table('orders')
            ->where('customer_id', $customer->card_code)
            ->where('ware_house_code', $warehouseCode)
            ->where('is_sales_approved', 0)
            ->count();

        // Approved Orders
        $ApprovedOrders = DB::table('orders')
            ->where('customer_id', $customer->card_code)
            ->where('ware_house_code', $warehouseCode)
            ->where('is_sales_approved', 1)
            ->count();

        $rejectedOrders = DB::table('orders')
            ->where('customer_id', $customer->card_code)
            ->where('ware_house_code', $warehouseCode)
            ->where('is_sales_approved', 2)
            ->count();

        $completedOrders = DB::table('invoices')
            ->where('customer_id', $customer->card_code)
            ->where('store_code', $warehouseCode)
            ->where('status', 'paid')
            ->count();

        $outstandingBalance = DB::table('invoices')
            ->where('customer_id', $customer->card_code)
            ->where('store_code', $warehouseCode)
            ->where('status', '!=', 'paid') 
            ->selectRaw('SUM(total - COALESCE(paid_amount, 0)) as balance')
            ->value('balance') ?? 0;

        $recentOrders = DB::table('invoices')
            ->where('customer_id', $customer->card_code)
            ->where('store_code', $warehouseCode)
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get(['id', 'invoice_no as sales_invoice_no', 'order_date', 'total', 'status']);

        $ordersApprovals = DB::table('orders')
            ->where('customer_id', $customer->card_code)
            ->where('ware_house_code', $warehouseCode)
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get(['id', 'sales_invoice_no', 'order_date', 'total', 'is_sales_approved as status']);

        return view('customer.dashboard', compact(
            'totalOrders',
            'ApprovedOrders',
            'rejectedOrders',
            'completedOrders',
            'outstandingBalance',
            'recentOrders',
            'ordersApprovals',
        ));
    }

    public function logout()
    {
        Auth::guard('customer')->logout();
        return redirect('/customer-login');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.customer_forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $customer = DB::table('customers')->where('email', $request->email)->first();

        if (!$customer) {
            return back()->withErrors(['email' => 'We can\'t find a customer with that email address.']);
        }

        $token = Str::random(60);

        DB::table('customer_password_resets')->updateOrInsert(
            ['card_code' => $customer->card_code],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::to($request->email)->send(new CustomerForgotPassword($token, $customer));

        return back()->with('status', 'We have emailed your password reset link!');
    }

    public function showResetPasswordForm($token)
    {
        // Logout customer if authenticated to prevent layout conflicts
        if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->logout();
        }

        return view('auth.customer_reset_password', ['token' => $token, 'email' => request('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $customer = DB::table('customers')->where('email', $request->email)->first();

        if (!$customer) {
            return back()->withErrors(['email' => 'We can\'t find a customer with that email address.']);
        }

        $resetRecord = DB::table('customer_password_resets')
            ->where('card_code', $customer->card_code)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Invalid token or email.']);
        }

        // Check if token is expired (e.g., 60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            return back()->withErrors(['email' => 'Token has expired.']);
        }

        DB::table('customers')
            ->where('card_code', $customer->card_code)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('customer_password_resets')
            ->where('card_code', $customer->card_code)
            ->delete();

        return redirect()->route('customer.login')->with('status', 'Password has been reset successfully!');
    }
}
