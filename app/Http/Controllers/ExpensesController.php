<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;
class ExpensesController extends Controller
{

    public function index() 
    {
        return view('expenses.expenses-list');
    }

    public function ExpensesJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = [
                'ec.id', 'ec.user_id', 'ec.total_amount', 'ec.created_at','c.name'
            ];

            try {
                $userId = Auth::user()->id;

                $query = DB::table('expenses as ec')
                    ->join('users as c', 'ec.user_id', '=', 'c.id')
                    ->select(
                        'ec.id',
                        'c.name',
                        'ec.user_id',
                        'ec.total_amount',
                        'ec.created_at'
                    )
                    ->where('ec.user_id', '=', $userId)
                    ->orderBy('ec.id', 'DESC');

                    if (!empty($request->start_date) && !empty($request->end_date)) {
                        try {
                            $query->whereBetween('ec.created_at', [$request->start_date, $request->end_date]);
                        } catch (\Exception $e) {
                            return response()->json(['error' => 'Invalid date format'], 400);
                        }
                    }

                if (!empty($data['search_user'])) {
                    $query->where('c.id', 'LIKE', '%' . $data['search_user'] . '%');
                }

                $totalRecords = $query->count();

                if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                    $query->orderBy(
                        $columnArray[$data['order'][0]['column']],
                        $data['order'][0]['dir']
                    );
                }



                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                $expenses = $query->get();

            } catch (\Exception $e) {
                $expenses = [];
                $totalRecords = 0;
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $expenses
            ]);
        }
    }

    public function expensesShow($expensesId){
        $expenses = DB::table('expenses_payments')
                            ->where('expenses_id', $expensesId)
                            ->get();

        return view('expenses.list', compact('expenses'));
    }


  
    public function create(){
        $expenses = DB::table('expenses_list_of_acc')->get();
        $expenses_inter_cmpy = DB::table('expenses_inter_cmpy')->get();
        $exp_store_or_house = DB::table('expenses_store_or_house')->get();
        $expenses_employee = DB::table('expenses_employee')->get();
        $expenses_other = DB::table('expenses_others')->get();
        $expenses_vehicle = DB::table('expenses_vehicle')->get();
        return view('expenses.expenses-payments', compact('expenses', 'expenses_inter_cmpy', 'exp_store_or_house', 'expenses_employee', 'expenses_other', 'expenses_vehicle'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            $entries = [];
            $userId = Auth::user()->id;
            $systemName = gethostname();
            $systemIP = $this->getLaptopIp();
            $expenseId = DB::table('expenses')->insertGetId([
                'user_id' => $userId,
                'total_amount' => $request->total_calculated_amount,
                'system_name' => $systemName,
                'system_ip' => $systemIP,
                'created_at' => now()
            ]);

            foreach ($data['account_number'] as $index => $accountNumber) {
                if (empty($accountNumber) || $data['amount'][$index] === null) {
                    continue;
                }

                $expensesAccount = DB::table('expenses_list_of_acc')
                    ->where('account_number', $accountNumber)
                    ->first();

                $expensesInterCompany = DB::table('expenses_inter_cmpy')
                    ->where('distribution', $data['inter_company'][$index])
                    ->first();

                $expensesStoreHouse = DB::table('expenses_store_or_house')
                    ->where('distribution', $data['store_house'][$index])
                    ->first();

                $expensesEmployee = DB::table('expenses_employee')
                    ->where('distribution', $data['employee'][$index])
                    ->first();

                $expensesOther = DB::table('expenses_others')
                    ->where('distribution', $data['other'][$index])
                    ->first();

                $expensesVehicle = DB::table('expenses_vehicle')
                    ->where('distribution', $data['vehicle'][$index])
                    ->first();

                $entries[] = [
                    'expenses_id' => $expenseId, 
                    'acc_code' => $expensesAccount->account_number ?? null,
                    'acc_name' => $expensesAccount->account_name ?? null,
                    'remarks' => $data['doc_remarks'][$index] ?? null,
                    'amount' => $data['amount'][$index] ?? 0,
                    'inter_cmpy_code' => $expensesInterCompany->distribution ?? null,
                    'inter_cmpy_name' => $expensesInterCompany->distribution_rule_name ?? null,
                    'store_house_code' => $expensesStoreHouse->distribution ?? null,
                    'store_house_name' => $expensesStoreHouse->distribution_rule_name ?? null,
                    'employee_code' => $expensesEmployee->distribution ?? null,
                    'employee_name' => $expensesEmployee->distribution_rule_name ?? null,
                    'others_code' => $expensesOther->distribution ?? null,
                    'others_name' => $expensesOther->distribution_rule_name ?? null,
                    'vehicle_code' => $expensesVehicle->distribution ?? null,
                    'vehicle_name' => $expensesVehicle->distribution_rule_name ?? null,
                    'created_at' => now(),
                ];
            }

            if (empty($entries)) {
                return redirect()->back()->with('error', 'No valid data to save!');
            }

            DB::table('expenses_payments')->insert($entries);
            
            return response()->json([
                'success' => 1, 
                'message' => 'Expense created successfully!',
                'expense_id' => $expenseId,
                'total_amount' => $request->total_calculated_amount,
                'expenses_payments' => $entries
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
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
