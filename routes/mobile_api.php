<?php

use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileMasterController;
use App\Http\Controllers\Mobile\MobileQuotationController;
use App\Http\Controllers\NewCustomerController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SapCustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('get-customers', [SapCustomerController::class, 'customersWEBIDJsonMobileApp']);
    Route::get('get-customer-info/{id}/{type}', [SapCustomerController::class, 'webIDCustomerCompanies']);


    Route::get('get-branches', [MobileMasterController::class, 'branches']);
    Route::get('search-customer-by-gstin', [NewCustomerController::class, 'searchCustomerByGSTIN']);
    Route::get('search-customer-by-contactno', [NewCustomerController::class, 'searchCustomerByContactNo']);
    Route::post('store-new-company-customer', [NewCustomerController::class, 'storeNewCompanyCustomer']);
    Route::post('store-new-customer', [NewCustomerController::class, 'storeNewCustomer']);

    // Master
    Route::get('get-branches', [MobileMasterController::class, 'branches']);
    Route::get('get-categories', [MobileMasterController::class, 'categories']);
    Route::get('get-subcategories', [MobileMasterController::class, 'subcategories']);
    Route::get('sales-emps', [MobileMasterController::class, 'saleEmps']);
    Route::get('countries', [MobileMasterController::class, 'countries']);

    Route::get('get-items', [MobileMasterController::class, 'items']);

    Route::get('get-quotations', [QuotationController::class, 'getQuotationsJson']);

    Route::post('quotation-cart', [MobileQuotationController::class, 'addToCart']);
    Route::get('get-quotation-cart/{customerId}', [MobileQuotationController::class, 'getCartDetailsByCustomerId']);
    Route::get('get-quotations', [MobileQuotationController::class, 'QuaotationList']);
    Route::post('create-quotation', [MobileQuotationController::class, 'createQuotationFromCart']);



});


Route::post('/mobile-login', [MobileAuthController::class, 'login']);



