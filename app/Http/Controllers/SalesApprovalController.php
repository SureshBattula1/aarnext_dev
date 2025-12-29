<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
use GuzzleHttp\Client;
use Carbon\Carbon;

class SalesApprovalController extends Controller
{
    public function index(){
        return view('approvels.sales_approval');

    }
    public function requestSaleslist()
{
    try {
        $storeCode = Auth::user()->ware_house;

        $salesDetails = DB::table('orders as o')
            ->join('users as u', 'o.user_id', '=', 'u.id')
            ->where('o.ware_house_code', $storeCode)
            ->where('u.role', 5)
            ->where('o.is_sales_approved', 0)
            ->select('o.id', 'o.customer_id', 'o.user_id', 'o.sales_invoice_no', 'u.code as user_code')
            ->get();

        return response()->json([
            'status' => 200,
            'salesDetails' => $salesDetails
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Error fetching sales approvals: ' . $e->getMessage()
        ], 500);
    }
}

public function approveSales($id, $action, Request $request)
{
    try {
        $userId = Auth::user()->id;
        $status = ($action == 'accept') ? 1 : 2; 

        if (empty($id)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid sales Order is empty'
            ], 400);
        }
        
            DB::table('orders')->where('id', $id)->update([
            'is_sales_approved'  => $status,
            'approval_remarks' => $request->remarks ?? '',
            'approved_by' => $userId,
            'approved_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Order #$id has been $action successfully."
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
}
