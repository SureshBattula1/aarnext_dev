<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth;
use Exception;

class CreditApprovelController extends Controller
{
    public function approvelIndex()
    {
        // echo "Approvel Index"; exit;
        try {
            $storeCode = Auth::user()->ware_house;

            $creditNotes = DB::table('credit_memos')
            ->where('is_credit_approved', 1)
            ->where('store_code', $storeCode)
            ->get();
            return view('approvels.recreditnote_approvel', compact('creditNotes'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error fetching credit requests: ' . $e->getMessage());
        }
    }

    public function customerSearch(Request $request)
    {
        $search = $request->get('term', '');
        $customers = DB::table('customers')
            ->select('id', 'card_code', 'card_name')
            ->when($search, function ($query, $search) {
                $query->where('card_code', 'like', "%{$search}%")
                      ->orWhere('card_name', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();
    
        return response()->json(
            $customers->map(function ($customer) {
                return [
                    'id'   => $customer->id,
                    'customer_code' =>$customer->card_code,
                    'text' => "{$customer->card_code} - {$customer->card_name}"
                ];
            })
        );
    }

    public function getInvoicesByCustomer($customerId)
{
    try {
        $storeCode = Auth::user()->ware_house;

        $invoices = DB::table('credit_memos')
            ->select('invoice_id as id', 'sales_invoice_no as invoice_no', 'is_credit_approved', 'customer_id', 'store_code')
            ->where('is_credit_approved', 0)
            ->where('customer_id', $customerId)
            ->where('store_code', $storeCode)
            ->get();

        // echo "Invoices"; print_r($customerId); exit;
        $formatted = [];
        foreach ($invoices as $invoice) {
            $formatted[] = [
                'id' => $invoice->id,
                'text' => $invoice->invoice_no
            ];
        }

        return response()->json($formatted);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

 
    public function recreditnoteRequest(Request $request)
    {
        try {
            $userId = Auth::user()->id;

            $request->validate([
                'invoice_id'  => 'required'
            ]);

            // echo "inv"; print_r($request->invoice_id); exit;
            $invoice = DB::table('credit_memos')
                ->where('sales_invoice_no', $request->invoice_id)
                ->update([
                    'is_credit_approved'  => 1,
                    'credit_date_req'      => now(),
                    'credit_req_uid' => $userId
                ]);

            return response()->json([
                'success' => 1,
                'message' => 'Credit note approved successfully.!',
                'data' => $invoice
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Error approving credit note.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approveRecreditRequest($id, $action, Request $request)
    {
        try {
            // echo "credit note"; print_r('test'); exit;
            $userId = Auth::user()->id;
           
            $creditNote = DB::table('credit_memos')->where('id', $id)->first();

            if (!$creditNote) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Credit note not found.',
                ]);
            }
            $status = ($action == 'accept') ? 2 : 3;  
            // 1 = Request Pending
            // 2 = Approved
            // 3 = Rejected

            // echo "credit note"; print_r($action); exit;
            DB::table('credit_memos')
                ->where('id', $id)
                ->update([
                    'is_credit_approved' => $status,
                    'approval_remarks'    => $request->remarks,
                    'approved_by'         => $userId,
                    'approved_at'         => now(),
                ]);

            if($status == 2){
                $updateInvoice = DB::table('invoices')
                                ->where('id', $creditNote->invoice_id)
                                ->update(['credit_status' => 0,]);
            }
            return response()->json([
                'success' => 1,
                'message' => $status == 2 ? 'Credit note approved successfully.' : 'Credit note rejected successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Error updating credit note status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
