<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class SalesEmployeeController extends Controller
{
    function getSalesEmps()
    {
        return view('sales-employee.sales_employees');
    }


    function getSalesEmpsJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            try {
                DB::enableQueryLog();

                // Start building the query
                $query = DB::table('sales_employees');

                // Apply search filter if search_user is present
                if (!empty($data['search_user'])) {
                    $search = $data['search_user'];
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('memo', 'like', "%{$search}%");
                    });
                }

                // Get filtered count before applying pagination
                $filteredCount = $query->count();

                // Apply ordering
                $query->orderBy('id', 'ASC');

                // Apply pagination
                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                // Get the paginated results
                $users = $query->get();

                // Get total count (without filters)
                $userCount = DB::table('sales_employees')->count();

            } catch (\Exception $e) {
                $users = [];
                $userCount = 0;
                $filteredCount = 0;
            }

            return response()->json([
                'draw' => $data['draw'],
                'recordsTotal' => $userCount,
                'recordsFiltered' => $filteredCount,
                'data' => $users
            ]);
        }
    }

    


    public function addEmployees(Request $request)
    {
        
        DB::beginTransaction();
        
        try {
            
            $params = [
                'name'          => $request->input('employee_name'),
                'code'         => $request->input('employee_code'),
                'mobile'         => $request->input('phone_num'),
                'email'         => $request->input('employee_email'),
                'memo'         => $request->input('memo'),
            ];
    
        
            $userId = DB::table('sales_employees')->insert($params);
    
            DB::commit();
    
            return response()->json(['success' => 1, 'message' => 'Employee Details Added Successfully.']);
        } catch (\Exception $e) {
            // Rollback on failure
            DB::rollBack();
            return response()->json(['success' => 0, 'message' => 'Employee Details Not Added.', 'error' => $e->getMessage()]);
        }
    }
    


    public function getUsers($user_id)
    {
        $user = DB::table('sales_employees')->where("id", $user_id)->first();

        return response()->json(array(
            'success' => 1,
            'user'    => $user,
            'message' => 'OK'
        ));
    }




    public function updateUser(Request $request)
    {
        try {
            // Update the record
            $updated = DB::table('sales_employees')
                ->where('id', $request->input('update_id'))
                ->update([
                    'name' => $request->input('update_name'),
                    'code' => $request->input('update_code'),
                    'mobile'  => $request->input('update_num'),
                    'email'         => $request->input('update_email'),
                    'memo' => $request->input('update_memo'),
                ]);
    
            if ($updated) {
                return Response::json(['success' => 1, 'message' => 'Employee Details Updated.']);
            } else {
                return Response::json(['success' => 0, 'message' => 'Employee Details Not Updated.']);
            }
        } catch (\Exception $e) {
            return Response::json(['success' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function deleteDomain($id)
    {
      
        try {
            // Update the record
            $deleted = DB::table('sales_employees')->where('id',$id)->delete();
                
    
            if ($deleted) {
                return Response::json(['success' => 1, 'message' => 'Employee Deleted Successfully.']);
            } else {
                return Response::json(['success' => 0, 'message' => 'Employee Details Not Deleted.']);
            }
        } catch (\Exception $e) {
            return Response::json(['success' => 0, 'message' => 'Error: ' . $e->getMessage()]);
        }
  
    }


    

}
