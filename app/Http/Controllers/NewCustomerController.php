<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewCustomerController extends Controller
{
    function index()
    {
        //$user_code = Auth::user()->code;
        //$group_data = DB::table('bp_groups')->select('code','name')->get();
        //$customer_data = DB::table('cust_whs_data')->where('user_code', $user_code)->select('user_code','user_name','group_code', 'group_name','payment_terms_code','payment_term_name','price_list_code','price_list_name','currency')->first();
        //return view('new-customer',compact("group_data","customer_data"));
        $user_code = Auth::user()->code;
        $group_data = DB::table('bp_groups')->select('code','name')->get();
        $customer_data = DB::table('cust_whs_data')
            ->where('user_code', $user_code)
            ->select('user_code','user_name','group_code', 'group_name','payment_terms_code','payment_term_name','price_list_code','price_list_name','currency')
            ->first();

        if (Auth::user()->role == 1 && !$customer_data) {

            $customer_data = (object)[
                'currency' => 'Default Currency',
                'group_name' => 'Default Currency',
                'group_code' => 'Default Currency',
                'payment_term_name' => 'Default Currency',
                'price_list_name' => 'Default Currency',
                'price_list_code' => 'Default Currency',
            ];
        }

        return view('new-customer', compact("group_data", "customer_data"));
    }


    public function getCustomersJson(Request $request)
    {
        $data = $request->all();
        $warehouseCode = auth()->user()->ware_house;

        if (isset($data['draw'])) {
            $columnArray = ['id', 'series', 'card_code', 'card_name', 'card_fname','alias_name','card_typ_nm','currency','grp_code','grp_name','phone1','phone2','cellular','email','grp_num','credit_line','list_num','product_list','slp_code','sal_emp_name','pymnt_grp','deb_pay_acct','deb_pay_name','valid_for','frozen_for','sub_grp','nif_no','store_location','sales_commission','hike_percent','create_date','create_ts','update_date','update_ts', 'status'];

            try {
                $query = DB::table('customers')
                    ->select('id', 'series', 'card_code', 'card_name', 'card_fname','alias_name','card_typ_nm','currency','grp_code','grp_name','phone1','phone2','cellular','email','grp_num','credit_line','list_num','product_list','factor','slp_code','sal_emp_name','pymnt_grp','deb_pay_acct','deb_pay_name','valid_for','frozen_for','sub_grp','nif_no','store_location','sales_commission','hike_percent','create_date','create_ts','update_date','update_ts', 'status')
                    ->orderBy('id', 'DESC');
                if($warehouseCode == 'AAR-CAB')
                {
                    $query->whereIn('customers.grp_code',[108,109]);
                }else{
                    $query->whereNotIn('customers.grp_code',[108,109]);
                }


                    if (!empty($data['search_customer'])) {
                        $searchTerm = '%' . $data['search_customer'] . '%';
                        $query->where(function($q) use ($searchTerm) {
                            $q->where('card_code', 'LIKE', $searchTerm)
                              ->orWhere('card_name', 'LIKE', $searchTerm);
                        });
                    }

                $totalRecords = $query->count();

                if (isset($data['order']) && isset($columnArray[$data['order'][0]['column']])) {
                    $query->orderBy($columnArray[$data['order'][0]['column']], $data['order'][0]['dir']);
                }

                $count = $data['page_length'] ?? 10;
                if ($data['length'] != -1) {
                    $query->skip($data['start'])->take($data['length']);
                }

                $customers = $query->get();
            } catch (\Exception $e) {
                $customers = [];
                $totalRecords = 0;
            }

            return response()->json([
                'draw' => intval($data['draw']),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $customers
            ]);
        }
    }

    public function searchCustomerByContactNo(Request $request)
    {
        $contactNumber = $request->get('contactNumber');
        // First query
        $query1 = DB::table('sap_customers')
            ->select('db_name', 'web_id', 'card_code', 'card_name')
            ->where(function ($query) use ($contactNumber) {
                $query->where('phone1', 'LIKE', '%' . $contactNumber . '%')
                    ->orWhere('phone2', 'LIKE', '%' . $contactNumber . '%')
                    ->orWhere('cellular', 'LIKE', '%' . $contactNumber . '%');
            });

        // Second query
        $query2 = DB::table('sap_customers')
            ->join('sap_customer_contacts', 'sap_customer_contacts.sap_customer_id', '=', 'sap_customers.id')
            ->select('db_name', 'web_id', 'card_code', 'card_name')
            ->where('sap_customer_contacts.cellolar', 'LIKE', '%' . $contactNumber . '%');

        // Union the queries
        // $customers = $query1->union($query2)->get();
        $customers = DB::table(DB::raw("({$query1->union($query2)->toSql()}) as sub"))
            ->mergeBindings($query1->union($query2))
            ->orderBy('db_name')
            ->orderBy('card_name')
            ->get();

        return response()->json(['customers' => $customers]);
    }

    public function storeNewCustomer(Request $request)
    {
        //dd($request->all());
        $warehouseCode = Auth()->user()->ware_house;
        DB::beginTransaction();
        try {
            $data = $request->all();
            if (!isset($data['nif_no'])) {
                return response()->json([
                    'success' => 0,
                    'message' => 'NIF number is required.',
                ], 400);
            }




            $lastCustomer = DB::table('customer_code_generate')
            ->select('card_code')
            ->first();

        

        $lastCardCode = $lastCustomer ? $lastCustomer->card_code : 'AARCW00000';

        // Extract numeric part (ignore any existing prefix)
        preg_match('/\d+$/', $lastCardCode, $matches);

        $number = isset($matches[0]) ? intval($matches[0]) + 1 : 1; // Increment number
        $newCardCode = 'AARCW' . str_pad($number, 5, '0', STR_PAD_LEFT); // Always start with 'AARCW'



            $existingCustomer = DB::table('customers')->where('nif_no', $data['nif_no'])->first();
            if ($existingCustomer) {
                return response()->json([
                    'success' => 0,
                    'message' => 'The provided NIF number already exists. Please use a unique NIF number.',
                ], 400);
            }

            // Get system details
            $systemName = gethostname();
            $systemIP = $this->getLaptopIp();

            $sapCustomerID = DB::table('customers')->insertGetId([
                'card_code' => $newCardCode,
                'card_name' => $data['card_name'] ?? null,
                'card_typ_nm' => $data['customerType'] ?? null,
                'currency' => $data['currency'] ?? null,
                'grp_code' => $data['grp_code'] ?? null,
                'grp_name' => $data['grp_name'] ?? null,
                'phone1' => $data['phone1'] ?? null,
                'email' => $data['email'] ?? null,
                'credit_line' => $data['credit_line'] ?? null,
                'list_num' => $data['list_num'] ?? null,
                'product_list' => $data['product_list'] ?? null,
                'sub_grp' => $data['sub_grp'] ?? null,
                'nif_no' => $data['nif_no'],
                'store_location' => $data['store_location'] ?? null,
                'system_name' => $systemName,
                'system_ip' => $systemIP,
                'factor' => 1,
                'status' => $data['is_active'],
                'create_date' => now(),
                'update_date' => now(),
            ]);

            $addresses = [
                [
                    'address_type' => 'ShipTo',
                    'country' => $data['country'] ?? null,
                    'state' => $data['state'] ?? null,
                    'city' => $data['new_city'] ?? null,
                    'address' => $data['address'] ?? null,
                ]
            ];

            if (!empty($data['country_billing']) && !empty($data['state_billing'])) {
                $addresses[] = [
                    'address_type' => 'BillTo',
                    'country' => $data['country_billing'],
                    'state' => $data['state_billing'],
                    'city' => $data['city_billing'] ?? null,
                    'address' => $data['address_billing'] ?? null,
                ];
            }

            foreach ($addresses as $address) {
                DB::table('customer_addresses')->insert([
                    'card_code' => $newCardCode, 
                    'address_type' => $address['address_type'],
                    'country' => $address['country'],
                    'state' => $address['state'],
                    'city' => $address['city'],
                    'address' => $address['address'],
                    'created_at' => now(),
                ]);
            }
            $updateCardCode = DB::table('customer_code_generate')->update([
                'card_code' => $newCardCode,
            ]);


            DB::commit();
            return response()->json([
                'success' => 1,
                'message' => 'Customer Created successfully.',
                'customer_code' => $newCardCode,
                'customer_name' => $data['card_name'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to store new customer', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => 0,
                'message' => "Error: " . $e->getMessage(),
            ], 500);
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

    public function showCustomer(Request $request , $customerId){
        $customer = DB::table('customers')
        ->where('id', $customerId)
        ->first();
        return response()->json(['success' => 1, 'customer' => $customer]);
    }

    public function updateCustomer(Request $request, $customerId)
    {
        try {
            DB::beginTransaction();

            $customer = DB::table('customers')->where('id', $customerId)->first();
            if (!$customer) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Customer not found',
                ]);
            }

            DB::table('customers')
                ->where('id', $customerId)
                ->update(['card_name' => $request->card_name,
                        'status' => $request->is_active,
                        'factor' => $request->factor ?? 1, 
            ]);

            DB::commit();

            return response()->json([
                'success' => 1,
                'message' => 'Customer updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update customer', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => 0,
                'message' => 'Customer details could not be updated. Please try again!',
            ]);
        }
    }


    public function getCustomerAddresses($cardCode, Request $request)
    {
        $search = $request->input('search', '');

        $addresses = DB::table('customer_addresses')

            ->where('customer_addresses.card_code', $cardCode)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('customer_addresses.street', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.city', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.state', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.country', 'LIKE', "%$search%")
                    ->orWhere('customer_addresses.zip_code', 'LIKE', "%$search%");
                });
            })
            ->groupBy('customer_addresses.card_code')
            ->get();

        return response()->json([
            'addresses' => $addresses
        ]);
    }



    public function getCardCode()
{
    try {
        $warehouseCode = auth()->user()->ware_house;
        $prefix = 'AARC';
        $lastCustomer = DB::table('customers')->orderBy('id', 'desc')->first();

        $lastNumber = $lastCustomer->id;

        $newCustomerCode = $prefix . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

        return response()->json(['customerCode' => $newCustomerCode , 'wareHouse' => $warehouseCode]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to generate customer code'], 500);
    }
}



    function getCountryCode($countryName)
    {
        $country = DB::table('countries')->where('name', $countryName)->first();

        if ($country)
            return $country->iso2;

        return 'IN';
    }

    public function getStates()
    {
        $states = DB::table('states')->get();
        if ($states) {
            return response()->json($states);
        } else {
            return response()->json(['message' => 'No states found'], 404);
        }
    }


    public function storeNewCompanyCustomer(Request $request)
    {
        $data = $request->all();

        DB::beginTransaction();

        [$webid, $lastcardCodeNumber] = $this->getCardCode();

        if (!$webid) {
            return response()->json(['success' => 0, 'message' => "Unable to Create Customer, Refresh and Try again."]);
        }

        try {
            $user_id = Auth::user()->id;
            $gst_pan = $this->getPANNumberFromGST($data['gstNumber']);
            $sapCustomerID = DB::table('sap_customers')->insertGetId([
                'db_name' => '-',
                'card_code' => '-',
                'web_id' => $webid,
                'card_name' => $data['gst_card_name'],
                'u_trpbpnm' => $data['gst_card_name'],
                'sales_emp_name' => $data['gst_sales_emp_name'],
                'u_csubcat' => $data['gst_category'],
                'u_csubcat2' => $data['gst_subcategory'],
                // 'cellular' => $data['contactNumber'],
                'u_vsppan' => $gst_pan,
                'gst_pan' => $gst_pan,
                'last_updated_user_id' => $user_id,
                'last_updated_on' => Now(),
                'is_created_in_web' => 1,
                'new_customer_type' => 'company',
                'new_cust_created_via' => 'gstin',
                'last_card_code_number' => $lastcardCodeNumber
            ]);

            if ($sapCustomerID) {
                $countryCode = $this->getCountryCode($data['gst_country']);
                $sap_customer_addresses = DB::table('sap_customer_addresses')->insertGetId([
                    'sap_customer_id' => $sapCustomerID,
                    'adres_type' => $data['gst_adres_type'],
                    'street' => $data['gst_street'],
                    'block' => $data['gst_block'] ?? Null,
                    'city' => $data['gst_city'] ?? Null,
                    'state' => $this->getStateCode($countryCode, $data['gst_state']),
                    'county' => $data['gst_country'],
                    'country' => $countryCode,
                    'zip_code' => $data['gst_zip_code'],
                    'address2' => $data['gst_address2'] ?? Null,
                    'address3' => $data['gst_address3'] ?? Null,
                    'gst_regn_no' => $data['gstNumber'],
                    'gst_status' => $data['gst_status']
                ]);

                // Store Customer Contact details
                if (isset($data['gst_cnt_name'])) {

                    $i = 0;
                    $contactsRow = [];
                    foreach ($data['gst_cnt_name'] as $line) {
                        $crow = [];
                        $crow['sap_customer_id'] = $sapCustomerID;
                        $crow['name'] = $data['gst_cnt_name'][$i];
                        $crow['designation'] = $data['gst_cnt_desig'][$i];
                        $crow['cellolar'] = $data['gst_cnt_contactno'][$i];
                        $crow['tel1'] = $data['gst_cnt_tel1'][$i];
                        $crow['e_mail_l'] = $data['gst_cnt_email'][$i];

                        $contactsRow[] = $crow;
                        $i++;
                    }

                    $CustContacts = DB::table('sap_customer_contacts')->insert($contactsRow);
                }

                // Store Customer Addresses details
                if (isset($data['gst_addtl_street'])) {

                    $i = 0;
                    $addressesRow = [];
                    foreach ($data['gst_addtl_street'] as $line) {
                        $countryCode = $this->getCountryCode($data['gst_addtl_country'][$i]);
                        $arow = [];
                        $arow['sap_customer_id'] = $sapCustomerID;
                        $arow['adres_type'] = $data['gst_addtl_adres_type'][$i];
                        $arow['street'] = $data['gst_addtl_street'][$i];
                        $arow['block'] = $data['gst_addtl_block'][$i] ?? Null;
                        $arow['city'] = $data['gst_addtl_city'][$i] ?? Null;
                        $arow['state'] = $this->getStateCode($countryCode, $data['gst_addtl_state'][$i]);
                        $arow['county'] = $data['gst_addtl_country'][$i];
                        $arow['country'] = $countryCode;
                        $arow['zip_code'] = $data['gst_addtl_zip_code'][$i];
                        $arow['address2'] = $data['gst_addtl_address2'][$i] ?? Null;
                        $arow['address3'] = $data['gst_addtl_address3'][$i] ?? Null;
                        $arow['gst_regn_no'] = $data['gstNumber'];
                        $arow['gst_status'] = $data['gst_status'];

                        $addressesRow[] = $arow;
                        $i++;
                    }

                    $CustAddresses = DB::table('sap_customer_addresses')->insert($addressesRow);
                }
            }

            DB::commit();
            return response()->json(['success' => 1, 'message' => 'Customer Created successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('Failed to Store New Customers via GSTIN' . $e->getMessage());
            return response()->json(array('success' => 0, 'message' => " Customer Details are not stored. Try again !"));
        }
    }



    public function searchCustomerByGSTIN(Request $request)
    {
        $gstNumber = $request->get('gstNumber');
        // First query
        $panNo = $this->getPANNumberFromGST($gstNumber);


        $Pancustomers = DB::table('sap_customers as s')
            ->join('sap_customer_addresses as sca', 's.id', '=', 'sca.sap_customer_id')
            ->select('s.db_name', 's.web_id', 's.card_code', 's.card_name', 'sca.gst_regn_no')
            ->where('sca.gst_regn_no', 'like', '%' . $panNo . '%')
            ->distinct()
            ->orderBy('s.db_name')
            ->orderBy('s.card_name')
            ->get()
            ->toArray();

        // foreach($Pancustomers as $customer) {

        // }
        $gstInData = '';
        $postalData = '';
        $gstInStatus = false;
        if (count($Pancustomers) < 1) {
            // Get GSTIN Details based on that
            $gstINAPIController = new GSTINAPIController();
            [$gstInJsonData, $postalData] = $gstINAPIController->getGSTIN($gstNumber);

            if ($gstInJsonData) {
                $gstInData = $gstInJsonData;
                $gstInStatus = true;
            }
        }

        return response()->json([
            'customers' => $Pancustomers,
            'gstInData' => $gstInData,
            'gstInStatus' => $gstInStatus,
            'postalData' => $postalData
        ]);
    }

    function getPANNumberFromGST($gstNumber, $newCustomer = false)
    {

        $prefix = substr($gstNumber, 0, 2);
        $prefixesGST = array(
            "01",
            "02",
            "03",
            "04",
            "05",
            "06",
            "07",
            "08",
            "09",
            "10",
            "11",
            "12",
            "13",
            "14",
            "15",
            "16",
            "17",
            "18",
            "19",
            "20",
            "21",
            "22",
            "23",
            "24",
            "25",
            "26",
            "27",
            "28",
            "29",
            "30",
            "31",
            "32",
            "33",
            "34",
            "35",
            "36",
            "37",
            "38",
            "97",
            "99"
        );

        if (in_array($prefix, $prefixesGST)) {
            $panNo = substr($gstNumber, 2, 10);
            return $panNo;
        }

        if ($newCustomer)
            return '-';

        return '-';
    }



    //public function getStatesByIso2($country_name)
    //{

    //    $country = DB::table('countries')->where('iso2', $country_name)->first();

    //    if (!$country) {
    //        return response()->json(['error' => 'Country not found'], 404);
    //    }

    //    $states = DB::table('states')
    //        ->select('name', 'id', 'iso2')
    //        ->where('country_id', $country->id)
    //        ->orderBy('name', 'asc')
    //        ->get()
    //        ->toArray();

    //    return response()->json($states);
    //}

    //public function getStates($country_name)
    //{
    //    $country = DB::table('countries')->where('name', $country_name)->first();

    //    if (!$country) {
    //        return response()->json(['error' => 'Country not found'], 404);
    //    }

    //    $states = DB::table('states')
    //        ->select('name', 'id', 'iso2')
    //        ->where('country_id', $country->id)
    //        ->orderBy('name', 'asc')
    //        ->get()
    //        ->toArray();

    //    return response()->json($states);
    //}

    public function getCities($state_name)
    {
        $state = DB::table('states')->where('name', $state_name)->first();

        if (!$state) {
            return response()->json(['error' => 'State not found'], 404);
        }

        $cities = DB::table('cities')
            ->select('name', 'id')
            ->where('state_id', $state->id)
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        return response()->json($cities);
    }


    public function getLocationZipCode($zip_code)
    {
        $gstInApiController = new GSTINAPIController();
        $res = $gstInApiController->getPostOfficeDetails($zip_code);

        if ($res) {
            return response()->json([
                'status' => 1,
                'message' => 'Success Location Details',
                'country' => $res['country'],
                'district' => $res['district'],
                'state' => $res['state'],
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Unable to get Location Details'
            ]);
        }
    }


    function updateWebId()
    {
        $customers = DB::table('sap_customers')
            ->select('u_trpbpnm')
            ->distinct()
            ->whereNull('web_id')
            ->where('is_created_in_web', 0)
            ->whereNotNull('u_trpbpnm')
            ->orderBy('u_trpbpnm')
            ->limit(5000)
            ->get();


        foreach ($customers as $customer) {
            // Update WebId and LastNumber
            [$web_id, $web_id_last_number] = $this->generateWebId();

            echo $web_id . " - ", $web_id_last_number . "<br />";


            if ($web_id) {
                $NoOfRowsUpdated = DB::table('sap_customers')
                    ->whereNull('web_id')
                    ->where('u_trpbpnm', '=', $customer->u_trpbpnm)
                    ->update([
                        'web_id' => $web_id,
                        'web_id_last_number' => $web_id_last_number
                    ]);

                Log::info("$web_id - WebID Updated to Customer Name " . $customer->u_trpbpnm . " - Count" . $NoOfRowsUpdated);
            } else {
                Log::info('WebID Generation Failed for the Customer Name: ' . $customer->u_trpbpnm);
            }

        }
    }

    function generateWebId()
    {
        try {
            $prefix = 'CWO1';
            $lastCustomer = DB::table('sap_customers')->where('is_created_in_web', 0)->orderBy('web_id_last_number', 'desc')->first();

            if (!empty($lastCustomer->web_id_last_number)) {
                $lastNumber = $lastCustomer->web_id_last_number + 1;
            } else {
                $lastNumber = 1;
            }
            $newCustomerCode = $prefix . str_pad($lastNumber, 5, '0', STR_PAD_LEFT);

            $lastCustomer = DB::table('sap_customers')->where('web_id', $newCustomerCode)->first();
            if ($lastCustomer) {
                return $this->generateWebId();
            } else {

                return [$newCustomerCode, $lastNumber];
            }
        } catch (\Exception $e) {
            Log::info('Failed Create' . $e->getMessage());
            return [false, false];
        }
    }

}
