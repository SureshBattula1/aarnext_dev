<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;



class ShareWarehouseListData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
{
    if (Auth::check()) {
        $warehouses = DB::table('ware_houses')->select('whs_code', 'store_logo')->get();
        $warehouseCode = Auth::user()->ware_house;

        $ware_house_image = DB::table('ware_houses')->where('whs_code', $warehouseCode)->first();

        view()->share([
            'warehouses' => $warehouses,
            'ware_house_image' => $ware_house_image
        ]);
    }

    return $next($request);
}

}
