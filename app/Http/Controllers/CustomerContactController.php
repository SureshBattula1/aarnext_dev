<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerContactController extends Controller
{
    public function index($commonName)
    {

        return view('sap_customer_contacts', ['commonName' => $commonName ]);
    }


    public function customerContactsJson(Request $request)
    {
        $data = $request->all();
        $commonName = $data['common_name'];
        if (isset($data['draw'])) {
            $columnArray = ['db_name','card_code', 'card_name', 'u_vsppan', 'u_trpbpnm'];

            try {
                DB::enableQueryLog();
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

                )->Join('sap_customers', function ($join) use($commonName) {
                    $join->on('sap_customers.id', '=', 'sap_customer_contacts.sap_customer_id');
                    $join->on('sap_customers.u_trpbpnm', 'LIKE', DB::raw("'" . $commonName . "'"));
                });

                // if ($data['search_user'] != '') {
                //     $query->Where('sap_customers.u_trpbpnm', 'like', $data['search_user'] );
                // }

                $count = $data['branch_count'] ?? '10';
                $userCount = $query->count();


                    // $query->orderBy(
                    //     $columnArray[$data['order'][0]['column']],
                    //     $data['order'][0]['dir']
                    // );

                    $query->orderBy('sap_customers.db_name');

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

    
}
