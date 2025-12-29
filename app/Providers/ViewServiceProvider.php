<?php
// app/Providers/ViewServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {

            $query = DB::table('sap_customers')
                        ->select(
                                DB::raw('(SELECT COUNT(DISTINCT se.slp_name)
                                FROM sap_customers sub_sc
                                JOIN sap_sales_employees se ON se.db_name = sub_sc.db_name AND se.slp_code = sub_sc.slp_code
                                WHERE sub_sc.u_trpbpnm = sap_customers.u_trpbpnm and sub_sc.slp_code != -1) as distinct_slp_count')

                            );


                $query->where('sap_customers.is_updated', '!=', 1);
                $query->groupBy('sap_customers.u_trpbpnm')
                // ->having('total_count', '>', 1);
                ->having('distinct_slp_count', '>', 1);
                $totMultiSlpCount = $query->count();


                // $userCount = $query->count();



            $query = DB::table('sap_customers')
                        ->select(
                            DB::raw('(SELECT COUNT(DISTINCT sub_sc.u_csubcat)
                            FROM sap_customers sub_sc
                            WHERE sub_sc.u_trpbpnm = sap_customers.u_trpbpnm ) as distinct_category_count'),
                            );
                $query->where('sap_customers.is_updated', '!=', 1);
                $query->groupBy('sap_customers.u_trpbpnm')
                ->having('distinct_category_count', '>', 1);
                $totMultiCatCount = $query->count();


                $query = DB::table('sap_customers')
                        ->select(
                            DB::raw('(SELECT COUNT(DISTINCT sub_sc.u_csubcat2)
                            FROM sap_customers sub_sc
                            WHERE sub_sc.u_trpbpnm = sap_customers.u_trpbpnm ) as distinct_subcategory_count'),

                            );

                $query->where('sap_customers.is_updated', '!=', 1);
                $query->groupBy('sap_customers.u_trpbpnm')
                ->having('distinct_subcategory_count', '>', 1);

                $totMultiSubCatCount = $query->count();


//                 $sql = $query->toSql();
// $bindings = $query->getBindings();
// echo vsprintf(str_replace('?', "'%s'", $sql), $bindings);

            $view->with([
                'totMultiCatCount' => $totMultiCatCount,
                'totMultiSubCatCount' => $totMultiSubCatCount,
                'totMultiSlpCount' => $totMultiSlpCount
            ]);
        });
    }

    public function register()
    {
        //
    }
}
