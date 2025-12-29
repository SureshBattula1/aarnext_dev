<?php
namespace App\Http\Controllers;

use App\Models\SapCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SapCustomerController extends Controller
{
    public function index()
    {
        return view('sap_customers');
    }

    public function indexwebid()
    {
        $role_id = Auth::user()->role;
        $userId = Auth::user()->id;
        $userName = Auth::user()->name;
        $saleEmpNames = DB::table('users')->where('manager_id', $userId)->pluck('name');

        $categoriesQuery = DB::table('sap_customers')
                        ->select('new_u_csubcat as name')
                        ->distinct();

        if ($role_id == 3)
            $categoriesQuery->where('sales_emp_name', '=', $userName);
        else if ($role_id == 2)
            $categoriesQuery->whereIn('sales_emp_name', $saleEmpNames);

        $categories = $categoriesQuery->orderBy('name')->get();



        $subcategoriesQuery = DB::table('sap_customers')
                            ->select('new_u_csubcat2 as name')
                            ->distinct();
        if ($role_id == 3)
            $subcategoriesQuery->where('sales_emp_name', '=', $userName);
        else if ($role_id == 2)
            $subcategoriesQuery->whereIn('sales_emp_name', $saleEmpNames);

        $subcategories = $subcategoriesQuery->orderBy('name')->get();

        $saleEmpNamesQuery = DB::table('sap_customers')
                            ->select('sales_emp_name')
                            ->distinct();

         if ($role_id == 3)
            $saleEmpNamesQuery->where('sales_emp_name', '=', $userName);
        else if ($role_id == 2)
            $saleEmpNamesQuery->whereIn('sales_emp_name', $saleEmpNames);

        $saleEmpNames = $saleEmpNamesQuery->orderBy('sales_emp_name')->get();

        return view('sap_customers_webid', compact('categories', 'subcategories', 'saleEmpNames'));
    }

    public function customersJson(Request $request)
    {
        $data = $request->all();
        if (isset($data['draw'])) {
            $columnArray = ['sap_customers.id', 'db_name', 'card_code', 'card_name', 'u_vsppan', 'u_trpbpnm', 'u_csubcat', 'u_csubcat2'];

            try {
                DB::enableQueryLog();

                $query = DB::table('sap_customers')
                        ->select(
                            'sap_customers.id',
                            'sap_customers.db_name',
                            'sap_customers.card_code',
                            'sap_customers.card_name',
                            'sap_customers.gst_pan as u_vsppan',
                            'sap_customers.u_trpbpnm',
                            'sap_customers.u_csubcat',
                            'sap_customers.u_csubcat2',
                            'sap_customers.cntct_prsn',
                            'sap_customers.phone1',
                            'sap_customers.phone2',
                            'sap_customers.e_mail',


                            DB::raw('COUNT(*) as total_count'),
                            DB::raw('(SELECT COUNT(DISTINCT sub_sc.gst_pan)
                                    FROM sap_customers sub_sc
                                    WHERE sub_sc.u_trpbpnm = sap_customers.u_trpbpnm ) as distinct_pan_count'),

                            DB::raw('(SELECT COUNT(DISTINCT se.slp_name)
                            FROM sap_customers sub_sc
                            JOIN sap_sales_employees se ON se.db_name = sub_sc.db_name AND se.slp_code = sub_sc.slp_code
                            WHERE sub_sc.u_trpbpnm = sap_customers.u_trpbpnm and sub_sc.slp_code != -1) as distinct_slp_count')

                            // DB::raw('(
                            //     SELECT COUNT(DISTINCT CASE
                            //         WHEN sub_sc.db_name = "-" AND sub_sc.sales_emp_name IS NOT NULL THEN sub_sc.sales_emp_name
                            //         ELSE NULL
                            //     END) +
                            //     COUNT(DISTINCT CASE
                            //         WHEN sub_sc.db_name != "-" AND sub_sc.sales_emp_name IS NOT NULL THEN sub_sc.sales_emp_name
                            //         ELSE NULL
                            //     END)
                            //     FROM sap_customers sub_sc
                            //     WHERE sub_sc.u_trpbpnm = sap_customers.u_trpbpnm AND sub_sc.slp_code != -1
                            // ) as distinct_slp_count')

                        );

                if ($data['search_user'] != '') {
                    $query->where(function($query) use ($data) {
                        $query->where('sap_customers.u_trpbpnm', 'like', $data['search_user'] . '%');
                             // ->orWhere('card_name', 'like', '%' . $data['search_user'] . '%');
                    });

                }

                if (strlen(trim($data['search_user'])) < 3)
                {
                    $query->where('sap_customers.u_trpbpnm', 'like',  $data['alphabet'] . '%');
                }

                $query->groupBy('sap_customers.u_trpbpnm');


                $count = $data['branch_count'] ?? '10';
                // $userCount = $query->count();
                $userCount = count($query->get());
                if (isset($data['order'])) {
                    $query->orderBy(
                        $columnArray[$data['order'][0]['column']],
                        $data['order'][0]['dir']
                    );
                }

                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($count);
                }

                $users = $query->get();
            } catch (\Exception $e) {
                $users = [];
                $userCount = 0;
            }

            // $sql = $query->toSql();
            // $bindings = $query->getBindings();
            // echo vsprintf(str_replace('?', "'%s'", $sql), $bindings);

            return response()->json([
                'draw' => $data['draw'],
                'recordsTotal' => $userCount,
                'recordsFiltered' => $userCount,
                // 'sql' => $query->toSql(),
                'data' => $users
            ]);
        }
    }


    public function customerCompanies($id)
    {
        $sapCust = SapCustomer::findorFail($id);
        $commonName = $sapCust->u_trpbpnm;

        $categories = DB::table('categories')->orderBy('name')->get();
        $subcategories = DB::table('sub_categories')->orderBy('name')->get();

        $saleEmpNames = DB::table('sap_sales_employees')->groupBy('slp_name')->orderBy('slp_name')->get();

        $res =  $sapCust;
        // if (!empty($res->gst_reg_no)) {
        //     $gst_no = $res->gst_reg_no;
        // } else {

        // }

        $gst_no = $this->getGst($res->u_trpbpnm);

        if (!empty($res->sales_emp_name)) {
            $saleEmpName = $res->sales_emp_name;
        } else {
            $saleEmpName = DB::table('sap_sales_employees')->where('db_name', $res->db_name)
                            ->where('slp_code', $res->slp_code)
                            ->first();

            $saleEmpName = ($saleEmpName) ? $saleEmpName->slp_name : false;
        }

        $category =  DB::table('categories')->where('name', 'LIKE', '' . $res->u_csubcat . '')->first();

        $subcategory =  DB::table('sub_categories')->where('name', 'LIKE', '' . $res->u_csubcat2 . '')->first();

        // dd($subcategory);
        // echo $res->u_csubcat2; exit;

        return view('sap_customer_companies', ['commonName' => $commonName, 'categories'=> $categories, 'subcategories' => $subcategories, 'saleEmpNames' => $saleEmpNames,
        'salesemp' => $saleEmpName,
        'gst_reg_no' => $gst_no,
        'dbcategory' => $category->name ?? null,
        'subcategory' => $subcategory->name ?? null,
        'cust' => $sapCust

        ]);
    }


    public function customerCompaniesJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = ['id', 'db_name', 'card_code', 'card_name', 'u_vsppan', 'u_trpbpnm'];

            try {
                DB::enableQueryLog();
                $query = SapCustomer::with('addresses')
                ->select(
                    'sap_customers.id',
                    'sap_customers.db_name',
                    'sap_customers.card_code',
                    'sap_customers.card_name',
                    'sap_customers.gst_pan as u_vsppan',
                    'sap_customers.u_trpbpnm',
                    'sap_customers.u_csubcat',
                    'sap_customers.u_csubcat2',
                    'sap_sales_employees.slp_name as slp_name',
                    'sap_customers.cntct_prsn',
                    'sap_customers.phone1',
                    'sap_customers.phone2',
                    'sap_customers.e_mail',
                    'sap_customers.cellular',
                    'sap_customers.sales_emp_name',
                )->leftJoin('sap_sales_employees', function ($join) {
                    $join->on('sap_customers.slp_code', '=', 'sap_sales_employees.slp_code');
                    $join->on('sap_customers.db_name', '=', 'sap_sales_employees.db_name');
                });

                if ($data['search_user'] != '') {
                    $query->Where('sap_customers.u_trpbpnm', '=', $data['search_user'] );
                }

                if (empty(trim($data['search_user'])))
                {
                    $query->where('sap_customers.id', '<', '1');

                }



                $count = $data['branch_count'] ?? '10';
                $userCount = $query->count();

                if (isset($data['order'])) {
                    $query->orderBy(
                        $columnArray[$data['order'][0]['column']],
                        $data['order'][0]['dir']
                    );
                }

                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($count);
                }

                $users = $query->get();
            } catch (\Exception $e) {
                $users = [];
                $userCount = 0;
            }

            // $sql = $query->toSql();
            // $bindings = $query->getBindings();
            // echo vsprintf(str_replace('?', "'%s'", $sql), $bindings);


            return response()->json([
                'draw' => $data['draw'],
                'recordsTotal' => $userCount,
                'recordsFiltered' => $userCount,
                'data' => $users
            ]);
        }
    }


    function updateCustomerPan(Request $request)
    {
        $data = $request->all();
        $user_id = Auth::user()->id;
        $common_name = $data['common_name'];

        try {
                $params = [
                    // 'gst_reg_no' => $data['gst_reg_no'],
                    // 'gst_pan' => $data['gst_pan'],
                    // 'card_name' => $data['card_name'],
                    // 'gst_pan' => $data['gst_pan'],
                    'u_csubcat' => $data['category'],
                    'u_csubcat2' => $data['subcategory'],
                    'sales_emp_name' => $data['sales_emp_name'],
                    // 'e_mail' => $data['e_mail'],
                    // 'cellular' => $data['cellular'],
                    // 'cntct_prsn' => $data['cntct_prsn'],
                    // 'phone1' => $data['phone1'],
                    // 'phone2' => $data['phone2'],
                    'last_updated_user_id' => $user_id,
                    'last_updated_on' => Now(),
                    'is_updated' => 1,
                ];

                $update = SapCustomer::where('u_trpbpnm','=',$common_name)->update($params);
                if ($update) {
                    return response()->json(array('success'=> 1, 'message'=> 'Customer Details Updated to All Linked Masters'));
                }
                else {
                    return response()->json(array('success'=> 0, 'message'=> 'Failed Customer Details Updated to All Linked Masters'));
                }

        }catch(\Exception $e) {
            return response()->json(array('success'=> 0, 'message'=> 'Customer Details Not Updated.'));
        }
    }

    function getCustomerDetail($id) {

        $res = SapCustomer::where('id', $id)->first();

        if (!$res) {
            return response()->json([
                'success' => 0,
                'message' => 'Invalid Customer Id or Server Error'
            ]);
        }

        if (!empty($res->gst_reg_no)) {
            $gst_no = $res->gst_reg_no;
        } else {
            $gst_no = $this->getGst($res->u_trpbpnm);
        }




        return response()->json([
            'success' => 1,
            'customer' => $res,
            'salesemp' => $res->sales_emp_name,
            'gst_reg_no' => $gst_no,
            'category' => $res->new_u_csubcat,
            'subcategory' => $res->new_u_csubcat2,

        ]);

    }

    function getAddressDetail($id)
    {

        $res = DB::table('sap_customer_addresses')->where('id', $id)->first();

        if (!$res) {
            return response()->json([
                'success' => 0,
                'message' => 'Invalid Customer Id or Server Error'
            ]);
        }

        return response()->json([
            'success' => 1,
            'address' => $res,
            'message' => 'Success'
        ]);

    }

    function getContactDetail($id)
    {

        $res = DB::table('sap_customer_contacts')->where('id', $id)->first();

        if (!$res) {
            return response()->json([
                'success' => 0,
                'message' => 'Invalid Customer Id or Server Error'
            ]);
        }

        return response()->json([
            'success' => 1,
            'contact' => $res,
            'message' => 'Success'
        ]);

    }

    public function getGst($commonName)
    {
        try {

            $data = DB::table('sap_customer_addresses')
                ->select('id', 'gst_regn_no', 'sap_customer_id')
                ->whereIn('sap_customer_id', function($query) use ($commonName) {
                    $query->select('id')
                        ->from('sap_customers')
                        ->where('u_trpbpnm', 'LIKE', $commonName);
                })
                ->get();

            foreach ($data as $key => $gst_data)
            {
                // echo $gst_data->gst_regn_no.'</br>';

                $result = substr($gst_data->gst_regn_no, 0, 2);
                $array = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20",
                            "21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","97","99");

                if (in_array($result,$array))
                {
                    return $gst_data->gst_regn_no;
                }
            }

        }catch(\Exception $e) {
            Log::info('Failed to get values'. $e->getMessage());
        }

            return '';
    }

    public function customersWEBIDJson_Slow(Request $request)
    {
        $data = $request->all();
        if (isset($data['draw'])) {
            $columnArray = ['sap_customers.id', 'web_id', 'card_name', 'u_vsppan', 'u_trpbpnm', 'u_csubcat', 'u_csubcat2'];

            try {
                DB::enableQueryLog();

                $query = DB::table('sap_customers')
    ->select(
        'sap_customers.id',
        'sap_customers.web_id',
        'sap_customers.card_name',
        'sap_customers.gst_pan',
        'sap_customers.u_trpbpnm',
        'sap_customers.u_csubcat',
        'sap_customers.u_csubcat2',
        'sap_customers.sales_emp_name',
        DB::raw('COUNT(sap_customers.web_id) as db_master'),
        DB::raw('SUM(IF(sap_customers.is_active_customer = 0, 1, 0)) as inactive_webid_count')
    )
    ->leftJoin('sap_customers as s1', 's1.web_id', '=', 'sap_customers.web_id')
    ->groupBy('sap_customers.web_id');

// Filters
if (!empty($data['sales_emp_name'])) {
    $query->where('sap_customers.sales_emp_name', $data['sales_emp_name']);
}

if (!empty($data['category'])) {
    $query->where('sap_customers.u_csubcat', $data['category']);
}

if (!empty($data['subcategory'])) {
    $query->where('sap_customers.u_csubcat2', $data['subcategory']);
}

if (!empty($data['alphabet'])) {
    $query->where('sap_customers.u_trpbpnm', 'like', $data['alphabet'] . '%');
}

if (!empty($data['created_web']) && $data['created_web'] == 1) {
    $query->where('sap_customers.is_created_in_web', 1);
}

if (!empty($data['search_user'])) {
    $query->where(function($query) use ($data) {
        $query->where('sap_customers.u_trpbpnm', 'like', $data['search_user'] . '%')
              ->orWhere('sap_customers.card_code', 'like', $data['search_user'])
              ->orWhere('sap_customers.web_id', 'like', $data['search_user']);
    });
}

// Having Clause
if (!empty($data['active_status'])) {
    if ($data['active_status'] == 'active') {
        $query->havingRaw('db_master != inactive_webid_count');
    } elseif ($data['active_status'] == 'inactive') {
        $query->havingRaw('db_master = inactive_webid_count');
    }
}

// Count and Pagination
$userCount = count($query->get());
$count = $data['branch_count'] ?? 10;


$query->orderBy('sap_customers.web_id');

if (!empty($data['length']) && $data['length'] != -1) {
    $query->skip($data['start'])->take($count);
}

// Fetch the Results
$users = $query->get();



                $results = [];
                foreach($users as $user)
                {
                    // $totalWebIdCount = $this->getWebIdCount($user->web_id);
                    // $activeWebIdCount = $this->getActiveWebIdCount($user->web_id);
                    // $inActiveWebIdCount = $this->getInactiveWebIdCount($user->web_id);

                    // if ($data['active_status'] == 'active') {

                    //     if ($totalWebIdCount == $inActiveWebIdCount) // Inactive
                    //         continue;

                    // } else if ($data['active_status'] == 'inactive') {
                    //     if ($totalWebIdCount != $inActiveWebIdCount) // Inactive
                    //         continue;
                    // }

                    $row = [];
                    $row['id'] = $user->id;
                    $row['web_id'] = $user->web_id;
                    $row['card_name'] = $user->card_name;
                    $row['u_vsppan'] = $user->gst_pan;
                    $row['u_trpbpnm'] = $user->u_trpbpnm;
                    $row['u_csubcat'] = $user->u_csubcat;
                    $row['u_csubcat2'] = $user->u_csubcat2;
                    $row['sales_emp_name'] = $user->sales_emp_name;
                    $row['db_master'] = $user->db_master;
                    // $row['db_master'] = $totalWebIdCount;
                    $addressDetails = $this->getAddressOfWebId($row['web_id']);
                    $row['street'] = $addressDetails['street'];
                    $row['city'] = $addressDetails['city'];
                    $row['state'] = $addressDetails['state'];
                    $row['gst_regn_no'] = $addressDetails['gst_regn_no'];
                    $row['gst_status'] =$addressDetails['gst_status'];
                    $contactDetails = $this->getContactsWebId($row['web_id']);
                    $row['cntct_prsn'] = $contactDetails['name'];
                    $row['cellular'] = $contactDetails['cellolar'];
                    $row['phone1'] = $contactDetails['tel1'];
                    $row['e_mail'] = $contactDetails['e_mail_l'];
                    $row['active_status'] = ($user->db_master == $user->inactive_webid_count) ? '<label class="badge badge-danger">Inactive</label>' : '<label class="badge badge-success">Active</label>';

                    $results[] = $row;
                }


            } catch (\Exception $e) {
                $users = [];
                $userCount = 0;
            }



            return response()->json([
                'draw' => $data['draw'],
                'recordsTotal' => $userCount,
                'recordsFiltered' => $userCount,
                // 'sql' => $query->toSql(),
                'data' => $results
            ]);
        }
    }

    public function customersWEBIDJson(Request $request)
    {
        $data = $request->all();
        if (isset($data['draw'])) {
            $columnArray = ['sap_customers.id', 'web_id', 'card_name', 'u_vsppan', 'u_trpbpnm', 'u_csubcat', 'u_csubcat2'];

            try {
                DB::enableQueryLog();

                $query = DB::table('sap_customers')
                ->selectRaw('
                MAX(sap_customers.id) as id,
                MAX(sap_customers.web_id) as web_id,
                MAX(sap_customers.card_name) as card_name,
                MAX(sap_customers.gst_pan) as gst_pan,
                MAX(sap_customers.u_trpbpnm) as u_trpbpnm,
                MAX(sap_customers.new_u_csubcat) as new_u_csubcat,
                MAX(sap_customers.new_u_csubcat2) as new_u_csubcat2,
                MAX(sap_customers.sales_emp_name) as sales_emp_name,
                (SELECT COUNT(web_id) FROM sap_customers s WHERE s.web_id = sap_customers.web_id) as db_master,
                (SELECT COUNT(web_id) FROM sap_customers s1 WHERE s1.web_id = sap_customers.web_id AND s1.is_active_customer = 0) as inactive_webid_count
            ');

                // $query->selectRaw('(SELECT COUNT(web_id) FROM sap_customers s WHERE s.web_id = sap_customers.web_id) as db_master');

                // $query->selectRaw('(SELECT COUNT(web_id) FROM sap_customers s1 WHERE s1.web_id = sap_customers.web_id AND s1.is_active_customer  = 0) as inactive_webid_count');


                if ($data['sales_emp_name'] != '') {
                    $query->where('sap_customers.sales_emp_name', '=', $data['sales_emp_name']);
                }
                if ($data['category'] != '') {
                    $query->where('sap_customers.new_u_csubcat', '=', $data['category']);
                }
                if ($data['subcategory'] != '') {
                    $query->where('sap_customers.new_u_csubcat2', '=', $data['subcategory']);
                }

                if ($data['alphabet'] != '') {
                    $query->where('sap_customers.u_trpbpnm', 'like',  $data['alphabet'] . '%');
                }

                if ($data['created_web'] == 1) {
                    $query->Where('sap_customers.is_created_in_web', 1);
                }

                // Rolebased Access
                $role_id = Auth::user()->role;
                $userId = Auth::user()->id;
                if ($role_id == 3) {
                    $userName = Auth::user()->name;
                    $query->where('sap_customers.sales_emp_name', '=', $userName);
                } else if ($role_id == 2) {
                    $userNames = DB::table('users')->where('manager_id', $userId)->pluck('name');
                    $query->whereIn('sap_customers.sales_emp_name', $userNames);
                }

                if ($data['search_user'] != '') {
                    $query->where(function($query) use ($data) {
                        $query->where('sap_customers.u_trpbpnm', 'like', $data['search_user'] . '%')
                             ->orWhere('card_code', 'like', '' . $data['search_user'] . '')
                             ->orWhere('web_id', 'like', '' . $data['search_user'] . '');
                    });

                }
                $query->groupBy('sap_customers.web_id');


                if ($data['active_status'] == 'active') {
                    $query->havingRaw('db_master != inactive_webid_count');
                } else if ($data['active_status'] == 'inactive') {
                    $query->havingRaw('db_master = inactive_webid_count');
                }



                $count = $data['branch_count'] ?? '10';
                $userCount = count($query->get());
                $query->orderBy('sap_customers.web_id');

                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($count);
                }

                $users = $query->get();

            // $sql = $query->toSql();
            // $bindings = $query->getBindings();
            // echo vsprintf(str_replace('?', "'%s'", $sql), $bindings);
            // exit;

                $results = [];
                foreach($users as $user)
                {

                    $row = [];
                    $row['id'] = $user->id;
                    $row['web_id'] = $user->web_id;
                    $row['card_name'] = $user->card_name;
                    $row['u_vsppan'] = $user->gst_pan;
                    $row['u_trpbpnm'] = $user->u_trpbpnm;
                    $row['new_u_csubcat'] = $user->new_u_csubcat;
                    $row['new_u_csubcat2'] = $user->new_u_csubcat2;
                    $row['sales_emp_name'] = $user->sales_emp_name;
                    $row['db_master'] = $user->db_master;

                    $addressDetails = $this->getAddressOfWebId($row['web_id']);
                    $row['street'] = $addressDetails['street'];
                    $row['city'] = $addressDetails['city'];
                    $row['state'] = $addressDetails['state'];
                    $row['gst_regn_no'] = $addressDetails['gst_regn_no'];
                    $row['gst_status'] =$addressDetails['gst_status'];
                    $contactDetails = $this->getContactsWebId($row['web_id']);
                    $row['cntct_prsn'] = $contactDetails['name'];
                    $row['cellular'] = $contactDetails['cellolar'];
                    $row['phone1'] = $contactDetails['tel1'];
                    $row['e_mail'] = $contactDetails['e_mail_l'];
                    $row['active_status'] = ($user->db_master == $user->inactive_webid_count) ? '<label class="badge badge-danger">Inactive</label>' : '<label class="badge badge-success">Active</label>';

                    $results[] = $row;
                }


            } catch (\Exception $e) {
                $users = [];
                $userCount = 0;
            }



            return response()->json([
                'draw' => $data['draw'],
                'recordsTotal' => $userCount,
                'recordsFiltered' => $userCount,
                // 'sql' => $query->toSql(),
                'data' => $results
            ]);
        }
    }


    function getAddressOfCommonName($commonName)
    {
        try {

            $subQuery = DB::table('sap_customers')
                    ->select('id')
                    ->where('u_trpbpnm', $commonName);

        $query = DB::table('sap_customer_addresses')
            ->whereIn('sap_customer_id', $subQuery)
            ->orderByRaw("
                CASE
                    WHEN gst_regn_no = '' THEN 0
                    WHEN gst_regn_no LIKE 'GST%' THEN 2
                    WHEN gst_regn_no IS NULL THEN 1
                    ELSE 3
                END DESC
            ")
            ->first();

         $res = [];
        if ($query)
        {
            $res['street'] = $query->street;
            $res['city'] = $query->city;
            $res['state'] = $query->state;
            $res['gst_regn_no'] = $query->gst_regn_no;
            $res['gst_status'] = $query->gst_status;

        } else {
            $res['street'] = Null;
            $res['city'] = Null;
            $res['state'] = Null;
            $res['gst_regn_no'] = Null;
            $res['gst_status'] = Null;
        }

        return $res;

        } catch(\Exception $e) {
            Log::info(' getAddressOfCommonName Failed to get values'. $e->getMessage());
        }
    }

    function getAddressOfWebId($web_id)
    {
        try {

            $subQuery = DB::table('sap_customers')
                    ->select('id')
                    ->where('web_id', $web_id);

        $query = DB::table('sap_customer_addresses')
            ->whereIn('sap_customer_id', $subQuery)
            ->orderByRaw("
                CASE
                    WHEN gst_regn_no = '' THEN 0
                    WHEN gst_regn_no LIKE 'GST%' THEN 2
                    WHEN gst_regn_no IS NULL THEN 1
                    ELSE 3
                END DESC
            ")
            ->first();

         $res = [];
        if ($query)
        {
            $res['street'] = $query->street;
            $res['city'] = $query->city;
            $res['state'] = $query->state;
            $res['gst_regn_no'] = $query->gst_regn_no;
            $res['gst_status'] = $query->gst_status;

        } else {
            $res['street'] = Null;
            $res['city'] = Null;
            $res['state'] = Null;
            $res['gst_regn_no'] = Null;
            $res['gst_status'] = Null;
        }

        return $res;

        } catch(\Exception $e) {
            Log::info(' getAddressOfWebId Failed to get values'. $e->getMessage());
        }
    }

    function getContactCommonName($commonName)
    {
        try {

            $subQuery = DB::table('sap_customers')
                    ->select('id')
                    ->where('u_trpbpnm', $commonName);

                $query = DB::table('sap_customer_contacts')
                    ->whereIn('sap_customer_id', $subQuery)
                    // ->orderBy('e_mail_l', 'desc')
                    ->orderBy('cellolar', 'desc')
                    ->first();

         $res = [];
        if ($query)
        {
            $res['name'] = $query->name;
            $res['cellolar'] = $query->cellolar;
            $res['tel1'] = $query->tel1;
            $res['e_mail_l'] = $query->e_mail_l;

        } else {
            $res['name'] = Null;
            $res['cellolar'] = Null;
            $res['tel1'] = Null;
            $res['e_mail_l'] = Null;
        }

        return $res;

        } catch(\Exception $e) {
            Log::info(' getAddressOfCommonName Failed to get values'. $e->getMessage());
        }
    }
    function getContactsWebId($web_id)
    {
        try {

            $subQuery = DB::table('sap_customers')
                    ->select('id')
                    ->where('web_id', $web_id);

                $query = DB::table('sap_customer_contacts')
                    ->whereIn('sap_customer_id', $subQuery)
                    // ->orderBy('e_mail_l', 'desc')
                    ->orderBy('cellolar', 'desc')
                    ->first();

         $res = [];
        if ($query)
        {
            $res['name'] = $query->name;
            $res['cellolar'] = $query->cellolar;
            $res['tel1'] = $query->tel1;
            $res['e_mail_l'] = $query->e_mail_l;

        } else {
            $res['name'] = Null;
            $res['cellolar'] = Null;
            $res['tel1'] = Null;
            $res['e_mail_l'] = Null;
        }

        return $res;

        } catch(\Exception $e) {
            Log::info(' getContactsWebId Failed to get values'. $e->getMessage());
        }
    }

    function getCommonNameCount($commonName)
    {
        $count = DB::table('sap_customers')
            ->where('u_trpbpnm', '=', $commonName)
            ->count();

        return $count;
    }

    function getWebIdCount($web_id)
    {
        return DB::table('sap_customers')
            ->where('web_id', '=', $web_id)
            ->count();
    }

    function getActiveWebIdCount($web_id)
    {
        return DB::table('sap_customers')
            ->where('web_id', '=', $web_id)
            ->where('is_active_customer', '=', 1)
            ->count();
    }
    function getInactiveWebIdCount($web_id)
    {
        return DB::table('sap_customers')
            ->where('web_id', '=', $web_id)
            ->where('is_active_customer', '=', 0)
            ->count();
    }





    public function webIDCustomerCompanies($id)
    {
        $role_id = Auth::user()->role;
        $userId = Auth::user()->id;
        $userName = Auth::user()->name;
        $saleEmpNames_users = DB::table('users')->where('manager_id', $userId)->pluck('name');

        $sapCust = SapCustomer::findorFail($id);
        $commonName = $sapCust->u_trpbpnm;
        $web_id = $sapCust->web_id;

        $categories = DB::table('sap_customers')
                        ->selectRaw('new_u_csubcat AS name')
                        ->distinct()
                        ->orderBy('new_u_csubcat')
                        ->get();

        $subcategories = DB::table('sap_customers')
                        ->selectRaw('new_u_csubcat2 AS name')
                        ->distinct()
                        ->orderBy('new_u_csubcat2')
                        ->get();

        $saleEmpNamesQuery = DB::table('sap_customers')
                        ->selectRaw('sales_emp_name AS slp_name')
                        ->distinct();

        if ($role_id == 3)
            $saleEmpNamesQuery->where('sales_emp_name', '=', $userName);
        else if ($role_id == 2)
            $saleEmpNamesQuery->whereIn('sales_emp_name', $saleEmpNames_users);


        $saleEmpNames =  $saleEmpNamesQuery->orderBy('sales_emp_name')->get();

        $res =  $sapCust;

        $gst_no = $this->getGst($res->u_trpbpnm);
        $saleEmpName = $res->sales_emp_name;
        $category =  $res->new_u_csubcat;
        $subcategory =   $res->new_u_csubcat2;

        $customerContacts = $this->getContacts($web_id);
        $customerAddresses = $this->getAddresses($web_id);

        $countries  = DB::table('countries')->orderBy('name')->get();


        return view('sap_webid_customer_companies', ['commonName' => $commonName, 'categories'=> $categories, 'subcategories' => $subcategories, 'saleEmpNames' => $saleEmpNames,
        'web_id' => $web_id,
        'salesemp' => $saleEmpName,
        'gst_reg_no' => $gst_no,
        'dbcategory' => $category ?? null,
        'subcategory' => $subcategory ?? null,
        'cust' => $sapCust,
        'customerContacts' => $customerContacts,
        'customerAddresses' => $customerAddresses,
        'countries' => $countries

        ]);
    }


    public function webIDCustomerCompaniesJson(Request $request)
    {
        $data = $request->all();

        if (isset($data['draw'])) {
            $columnArray = ['id', 'db_name', 'card_code', 'card_name', 'u_vsppan', 'u_trpbpnm'];

            try {
                DB::enableQueryLog();
                $query = SapCustomer::with('addresses')
                ->select(
                    'sap_customers.id',
                    'sap_customers.db_name',
                    'sap_customers.card_code',
                    'sap_customers.card_name',
                    'sap_customers.gst_pan as u_vsppan',
                    'sap_customers.u_trpbpnm',
                    'sap_customers.new_u_csubcat',
                    'sap_customers.new_u_csubcat2',
                    // 'sap_sales_employees.slp_name as slp_name',
                    // 'sap_customers.cntct_prsn',
                    // 'sap_customers.phone1',
                    // 'sap_customers.phone2',
                    // 'sap_customers.e_mail',
                    // 'sap_customers.cellular',
                    'sap_customers.sales_emp_name',
                );

                if ($data['search_user'] != '') {
                    $query->Where('sap_customers.web_id', '=', $data['search_user'] );
                }

                if (empty(trim($data['search_user'])))
                {
                    $query->where('sap_customers.id', '<', '1');
                }




                $count = $data['branch_count'] ?? '10';
                $userCount = $query->count();

                if (isset($data['order'])) {
                    $query->orderBy(
                        $columnArray[$data['order'][0]['column']],
                        $data['order'][0]['dir']
                    );
                }

                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($count);
                }

                $users = $query->get();
            } catch (\Exception $e) {
                $users = [];
                $userCount = 0;
            }

            // $sql = $query->toSql();
            // $bindings = $query->getBindings();
            // echo vsprintf(str_replace('?', "'%s'", $sql), $bindings);


            return response()->json([
                'draw' => $data['draw'],
                'recordsTotal' => $userCount,
                'recordsFiltered' => $userCount,
                'data' => $users
            ]);
        }
    }

    function getContacts($web_id) {
        $query = DB::table('sap_customer_contacts')
                ->select(
                    'sap_customer_contacts.id',
                    'sap_customers.db_name',
                    'sap_customers.card_code',
                    'sap_customer_contacts.name',
                    'sap_customer_contacts.first_name',
                    'sap_customer_contacts.middle_name',
                    'sap_customer_contacts.last_name',
                    'sap_customer_contacts.designation',
                    'sap_customer_contacts.tel1',
                    'sap_customer_contacts.cellolar',
                    'sap_customer_contacts.e_mail_l',
                    'sap_customer_contacts.active',
                    'sap_customer_contacts.create_date',
                    'sap_customer_contacts.create_ts',

                )->Join('sap_customers', function ($join) use($web_id) {
                    $join->on('sap_customers.id', '=', 'sap_customer_contacts.sap_customer_id');
                    $join->on('sap_customers.web_id', 'LIKE', DB::raw("'" . $web_id . "'"));
                });

                $query->orderBy('sap_customers.db_name');

                return $query->get();
    }


    function getAddresses($web_id) {
        $query = DB::table('sap_customers')
                ->select(
                    'sap_customers.db_name',
                    'sap_customer_addresses.id',
                    'sap_customer_addresses.adres_type',
                    'sap_customer_addresses.address',
                    'sap_customer_addresses.street',
                    'sap_customer_addresses.block',
                    'sap_customer_addresses.zip_code',
                    'sap_customer_addresses.city',
                    'sap_customer_addresses.county',
                    'sap_customer_addresses.country',
                    'sap_customer_addresses.state',
                    'sap_customer_addresses.gst_regn_no',
                    'sap_customer_addresses.gst_status',
                    'sap_customer_addresses.create_date',
                    'sap_customer_addresses.create_ts',
                    'sap_customer_addresses.active',

                )->Join('sap_customer_addresses', function ($join) {
                    $join->on('sap_customers.id', '=', 'sap_customer_addresses.sap_customer_id');
                });

                $query->where('sap_customers.web_id', '=', $web_id);
                $query->orderBy('sap_customers.db_name');
                return $query->get();

    }

    function getSalesEmpCategories($sales_emp_name)
    {
        $categories = DB::table('sap_customers')
                        ->select('new_u_csubcat')
                        ->distinct()
                        ->where('sales_emp_name', $sales_emp_name)
                        ->orderBy('new_u_csubcat')
                        ->get()
                        ->toArray();

        return response()->json($categories);
    }

    function getSalesEmpCatSubcategory($sales_emp_name, $category)
    {
        $sub_categories = DB::table('sap_customers')
                        ->select('new_u_csubcat2')
                        ->distinct()
                        ->where('sales_emp_name', $sales_emp_name)
                        ->where('new_u_csubcat', $category)
                        ->orderBy('new_u_csubcat2')
                        ->get()
                        ->toArray();

        return response()->json($sub_categories);
    }

    function updateCustomerAddress(Request $request)
    {
        $data = $request->all();
        try {
            DB::beginTransaction();
            $user_id = Auth::user()->id;

            $params = [
                'street' => $data['street'],
                'block' => $data['block'] ?? Null,
                'city' => $data['city'] ?? Null,
                'state' => $data['state'],
                'country' => $data['country'],
                'zip_code' => $data['zip_code'],
                'active' => $data['status'],
                'gst_regn_no' => $data['gst_regn_no'],
                'last_updated_user_id' => $user_id,
                'last_updated_on' => Now()
            ];

            if (empty($data['address_id'])) {
                // Get SapCustomerID

                $cust = DB::table('sap_customers')->where('web_id', $data['web_id'])->first();

                $params['adres_type'] =  $data['adres_type'];
                $params['sap_customer_id'] =  $cust->id;

                $cust_addresses = DB::table('sap_customer_addresses')->insert($params);
            }
            else {

                $params['adres_type'] =  $data['adres_type'];

                $cust_addresses = DB::table('sap_customer_addresses')
                ->where('id', $data['address_id'])
                ->update($params);


                // if ($data['update_option'] == 'all')
                // {
                //     $web_id = $data['web_id'];

                //     $cust_addresses = DB::table('sap_customer_addresses')
                //     ->whereIn('sap_customer_id', function ($query) use ($web_id) {
                //         $query->select('id')
                //             ->from('sap_customers')
                //             ->where('web_id', $web_id);
                //     })
                //     ->update($params);

                // } else {

                // }

            }

            DB::commit();
            return response()->json(['success' => 1, 'message' => 'Customer Addresses Updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Failed to Update Customer Addresses' . $e->getMessage());
            return response()->json(array('success' => 0, 'message' => "Customer Addresses are not updated. Try again !"));
        }
    }

    function updateCustomerContact(Request $request)
    {
        $data = $request->all();
        try {
            DB::beginTransaction();
            $user_id = Auth::user()->id;

            $params = [
                'name' => $data['name'],
                'designation' => $data['designation'],
                'e_mail_l' => $data['email'],
                'cellolar' => $data['cellolar'],
                'tel1' => $data['tel1'],
                'active' => $data['status'],
                'last_updated_user_id' => $user_id,
                'last_updated_on' => Now()
            ];

            if (empty($data['contact_id'])) {
                // Get SapCustomerID

                $cust = DB::table('sap_customers')->where('web_id', $data['web_id'])->first();

                $params['sap_customer_id'] =  $cust->id;

                $cust_contact = DB::table('sap_customer_contacts')->insert($params);
            } else {

                $cust_contact = DB::table('sap_customer_contacts')
                ->where('id', $data['contact_id'])
                ->update($params);

                // if ($data['update_option'] == 'all')
                // {
                //     $web_id = $data['web_id'];

                //     $cust_contact = DB::table('sap_customer_contacts')
                //     ->whereIn('sap_customer_id', function ($query) use ($web_id) {
                //         $query->select('id')
                //               ->from('sap_customers')
                //               ->where('web_id', $web_id);
                //     })
                //     ->update($params);

                // } else {


                // }
            }

            DB::commit();
            return response()->json(['success' => 1, 'message' => 'Customer Contacts Updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Failed to Update Customer Contacts' . $e->getMessage());
            return response()->json(array('success' => 0, 'message' => "Customer Contacts are not updated. Try again !"));
        }
    }

}
