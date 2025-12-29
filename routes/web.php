<?php

use App\Http\Controllers\auth\loginAuthenticationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\itemController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemQuotationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomAuthController;
use App\Http\Controllers\ExcelImportController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\NewCustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalesEmployeeController;
use App\Http\Controllers\SapCustomerController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\AarnextimportController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\RequestForTenderController;
use App\Http\Controllers\DelivaryNoteController;
use App\Http\Controllers\CreateInvoiceController;
use App\Http\Controllers\AdminRepotsController;
use App\Http\Controllers\InvoicePaymentsController;
use App\Http\Controllers\CreditApprovelController;
use App\Http\Controllers\SalesApprovalController;
use App\Http\Controllers\RftPlanningController;
use App\Http\Controllers\StockEmailController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerAccessController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});

// Route::get('symkey', [GSTINAPIController::class, 'DecryptBySymmetricKeyNew']);

Auth::routes();
// Route::get('/aarnext_live', function () {
//     return redirect()->route('home');
// });
Route::get('/home', 'App\Http\Controllers\HomeController@index');
Route::get('/', 'App\Http\Controllers\HomeController@index')->name('home');
Route::get('dashboard-data', [ HomeController::class , 'getDashboardStats']);
Route::post('/update-warehouse', [HomeController::class, 'updateWarehouse'])->name('updateWarehouse');
Route::get('coming-soon', [ HomeController::class , 'comingSoon']);

// Customer authentication routes
Route::get('/customer-login', [CustomerAuthController::class, 'showLoginForm'])
     ->name('customer.login');
Route::post('/customer-login', [CustomerAuthController::class, 'login'])
     ->name('customer.login.post');
Route::post('/customer-logout', [CustomerAuthController::class, 'logout'])
     ->name('customer.logout');

// Customer password reset routes
Route::get('/customer-forgot-password', [CustomerAuthController::class, 'showForgotPasswordForm'])
     ->name('customer.forgot-password');
Route::post('/customer-forgot-password', [CustomerAuthController::class, 'sendResetLinkEmail'])
     ->name('customer.forgot-password.post');
Route::get('/customer/reset-password/{token}', [CustomerAuthController::class, 'showResetPasswordForm'])
     ->name('customer.reset-password');
Route::post('/customer/reset-password', [CustomerAuthController::class, 'resetPassword'])
     ->name('customer.reset-password.post');

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {

    Route::get('/changePassword', [App\Http\Controllers\UserController::class, 'showChangePasswordGet'])->name('changePasswordGet');
    Route::post('/changePassword', [App\Http\Controllers\UserController::class, 'changePasswordPost'])->name('changePasswordPost');

    Route::get('sap-customers', [SapCustomerController::class, 'index'])->name('sap.customers');
    Route::get('pan_from_gst', [App\Http\Controllers\ProjectController::class, 'panFromGst'])->name('pan_from_gst');

    Route::get('common_null_pan_from_gst', [App\Http\Controllers\ProjectController::class, 'panFromGstCommonNameNull']);

    Route::get('sap-customers-json', [SapCustomerController::class, 'customersJson'])->name('sap.customers.json');

    Route::get('sap-customer-companies/{id}', [SapCustomerController::class, 'customerCompanies']);
    Route::get('sap-customers-companies-json', [SapCustomerController::class, 'customerCompaniesJson']);
    Route::post('update_customer_pan', [SapCustomerController::class, 'updateCustomerPan']);
    Route::post('update_customer_address', [SapCustomerController::class, 'updateCustomerAddress']);
    Route::post('update_customer_contact', [SapCustomerController::class, 'updateCustomerContact']);

    Route::get('get-customer-detail/{id}', [SapCustomerController::class, 'getCustomerDetail']);
    Route::get('get-address-detail/{id}', [SapCustomerController::class, 'getAddressDetail']);
    Route::get('get-contact-detail/{id}', [SapCustomerController::class, 'getContactDetail']);

    Route::get('get-customer-contacts/{commonName}', [App\Http\Controllers\CustomerContactController::class, 'index']);
    Route::get('get-customer-contacts-json', [App\Http\Controllers\CustomerContactController::class, 'customerContactsJson']);

    Route::get('new-customer', [App\Http\Controllers\NewCustomerController::class, 'index']);
    Route::get('customers-json', [App\Http\Controllers\NewCustomerController::class, 'getCustomersJson']);
    Route::get('search-customer-by-contactno', [App\Http\Controllers\NewCustomerController::class, 'searchCustomerByContactNo'])->name('search.customer.contactno');
    Route::get('search-customer-by-gstin', [App\Http\Controllers\NewCustomerController::class, 'searchCustomerByGSTIN'])->name('search.customer.gstin');
    Route::post('store-new-customer', [App\Http\Controllers\NewCustomerController::class, 'storeNewCustomer'])->name('store.new.customer');
    Route::post('store-new-company-customer', [App\Http\Controllers\NewCustomerController::class, 'storeNewCompanyCustomer'])->name('store.new.company.customer');
    Route::get('customer-show/{customerId}' , [ NewCustomerController::class , 'showCustomer']);
    Route::post('customer-update/{customerId}' , [ NewCustomerController::class , 'updateCustomer']);
    Route::get('states/{country_name}', [App\Http\Controllers\NewCustomerController::class, 'getStates'])->name('get.states');
    Route::get('states_iso2/{country_code}', [App\Http\Controllers\NewCustomerController::class, 'getStatesByIso2'])->name('get.states.iso2');
    Route::get('cities/{state_name}', [App\Http\Controllers\NewCustomerController::class, 'getCities'])->name('get.cities');

    Route::get('get-customer-addresses/{customerID}', [App\Http\Controllers\NewCustomerController::class, 'getCustomerAddresses'])->name('get-customer-addresses');
    Route::get('get-states', [App\Http\Controllers\NewCustomerController::class, 'getStates']);



    Route::get('get-next-customer-code', [App\Http\Controllers\NewCustomerController::class, 'getCardCode']);


    Route::get('get-location-zipcode/{zipcode}', [App\Http\Controllers\NewCustomerController::class, 'getLocationZipCode']);

    Route::get('sap-webid-customers', [SapCustomerController::class, 'indexwebid'])->name('sap.customers.webid');

    Route::get('sap-customers-webid-json', [SapCustomerController::class, 'customersWEBIDJson'])->name('sap.customers.webid.json');


    Route::get('sap-webid-customer-companies/{id}', [SapCustomerController::class, 'webIDCustomerCompanies']);
    Route::get('sap-webid-customers-companies-json', [SapCustomerController::class, 'webIDCustomerCompaniesJson']);


    Route::get('sales-emp-categories/{salesEmpName}', [SapCustomerController::class, 'getSalesEmpCategories']);
    Route::get('sales-emp-cat-subcats/{salesEmpName}/{category}', [SapCustomerController::class, 'getSalesEmpCatSubcategory']);

    
    Route::get('sales-order/edit/{id}', [SalesOrderController::class, 'editOrder'])->name('sales-order.edit');
    Route::put('/salesorder/update/{id}', [SalesOrderController::class, 'updateSalesOrder'])->name('salesorder.update');

    Route::get('new-quotation', [QuotationController::class, 'newQuotation']);
    Route::get('customer-search', [QuotationController::class, 'searchCustomer']);
    Route::get('get-cust-addrs-contacts/{webId}', [QuotationController::class, 'getCustomerAddressesAndContacts']);
    Route::get('item-search/{type?}', [QuotationController::class, 'getItemSearch']);
    Route::get('get-item/{itemId}/{customerId}', [QuotationController::class, 'getItem']);
    Route::post('store-invoice', [CreateInvoiceController::class, 'storeInvoice']);
    Route::get('generate_invoice_pdf/{inv_id}', [CreateInvoiceController::class, 'GenerateInvoicePDF']);


    Route::get('get-sales-employees', [QuotationController::class, 'salesEmployee']);
    Route::get('get-inv-batches-list/{invoiceId}/{itemCode}', [QuotationController::class, 'invoiceBatchList']);

    //19-2
    Route::get('generate_delivary_pdf/{invoiceId}', [DelivaryNoteController::class, 'generateDelivaryPDF']);
    Route::post('update-order-price/{id}', [PaymentsController::class, 'updateOrderPrice']);
    
    Route::get('Invoice-generation', [QuotationController::class, 'index']);
    Route::get('invoice-number-hashkey', [QuotationController::class, 'HashkeyGenerationInvoice']);

    Route::get('get-batches/{itemId}/{quatation}', [QuotationController::class, 'batchQuantity']);
    Route::get('quotations-json', [QuotationController::class, 'getQuotationsJson']);
    Route::get('pdf/invoices/invoice_{id}', [QuotationController::class, 'generateInvoicePDF']);

    Route::get('new-invoice-format', [QuotationController::class, 'NewInvoicePage']);

    Route::post('invoice-payments', [QuotationController::class, 'storePayments'])->name('invoice-payments-store');
    Route::get('get_inv_payment_details/{invoiceId}', [QuotationController::class, 'getInvoicePaymentDetails']);

    Route::get('/invoice-payment-show/{invoiceNo}', [QuotationController::class, 'showPayments'])->name('invoice-payments-show');

    //21/2
    Route::get('item_search_by_code/{itemCode}', [QuotationController::class, 'getCustomerItems']);


   //Items Quotations
    Route::get('items-quotation', [ItemQuotationController::class, 'index']);
    Route::get('store-customer-search', [ItemQuotationController::class, 'CustomerSearch']);
    Route::get('quotation-item-search/{type?}', [ItemQuotationController::class, 'getItemSearch']);

    Route::post('store-items-quotation', [ItemQuotationController::class, 'storeQuotation']);
    //Route::get('get-batches/{itemId}', [ItemQuotationController::class, 'batchQuantity']);
    Route::get('items-quotation-pdf', [ItemQuotationController::class, 'NewInvoicePage']);


      //--------------23-10-24-------------------
      Route::get('quotations', [ItemQuotationController::class, 'quotationlist']);
      Route::get('itemsquotation-json', [ItemQuotationController::class, 'getItemQuotationsJson']);
      Route::get('pdf/quotations/quotation_{quotationId}', [ItemQuotationController::class, 'generateQuotationPDF']);
      Route::get('customer-address-list/{customerID}', [ItemQuotationController::class, 'getCustomerAddressesList'])->name('customer-address-list');
      Route::post('calculate-due-date',[ItemQuotationController::class, 'calculateDueDate']);
      Route::get('/quotation-payment-show/{id}', [ItemQuotationController::class, 'showQuotationPayments'])->name('quotation-payment-show');



      Route::get('sales-employees-list', [SalesEmployeeController::class, 'getSalesEmps']);
      Route::get('sales-employees-json', [SalesEmployeeController::class, 'getSalesEmpsJson']);
      Route::post('add_user', [SalesEmployeeController::class, 'addEmployees']);
      Route::get('get_user/{userId}', [SalesEmployeeController::class, 'getUsers']);
      Route::post('update_users', [SalesEmployeeController::class, 'updateUser']);
      Route::get('delete_users/{id}', [SalesEmployeeController::class, 'deleteDomain']);
  
    //-----15-10-2024---- Sales Order Quotations------------//
    Route::get('sales', [SalesOrderController::class, 'Salesindex']);
    Route::get('sales-order-json', [SalesOrderController::class, 'SalesOrderJson']);
    Route::get('order-generating', [SalesOrderController::class, 'OrderGeneratingPage']);
    Route::get('sales-customer-search', [SalesOrderController::class, 'searchSalesCustomer']);
    Route::get('sales-item-search/{type?}', [SalesOrderController::class, 'getSalesItemSearch']);
    Route::get('get-sales-item/{itemId}', [SalesOrderController::class, 'getOrderItem']);
    Route::post('store-orders', [SalesOrderController::class, 'storeOrderQuotation']);
    Route::get('pdf/sales/sales_copy_{id}', [SalesOrderController::class, 'generateSalesInvoicePDF']);
    Route::post('order-payments', [SalesOrderController::class, 'storeOrderPayments'])->name('order-payments');
    Route::get('order-payments-list/{id}', [SalesOrderController::class, 'showOrderPayments'])->name('order-payments-list');
    Route::get('customer-quotations', [SalesOrderController::class, 'customerQuotations'])->name('customer-quotations');
    Route::get('get-quotation-details/{quotationId}', [SalesOrderController::class, 'getQuotationData'])->name('get-quotation-details');
    Route::get('customer-sales-order', [SalesOrderController::class, 'customerSalesOrder'])->name('customer-sales-order');
    Route::get('get-sales-order-details/{orderId}', [SalesOrderController::class, 'getSalesOrderData'])->name('get-sales-order-details');
    
    Route::get('cart-products', [CartController::class, 'getCartProducts']);
    Route::get('cart-products-json', [CartController::class, 'getCartProductsJson']);
    Route::post('add-to-cart', [CartController::class, 'addToCart']);
    Route::get('item-cat-subcats/{category}', [CartController::class, 'getItemCatSubCats']);
    Route::get('cart', [CartController::class, 'viewCart']);
    Route::post('delete-cart-item', [CartController::class, 'deleteCartItem']);

    //Route::get('direct-payments', [PaymentsController::class, 'create']);
    //Route::get('direct-payment-list', [PaymentsController::class, 'index']);
    Route::get('customer-invoices/{customerId}', [PaymentsController::class, 'customerInvoices']);
    Route::get('direct-payment-list', [PaymentsController::class, 'index']);
    Route::get('direct-payments', [PaymentsController::class, 'create']);
    Route::get('direct-payments-json', [PaymentsController::class, 'directPaymentsJson']);
    Route::post('/store-direct-payment', [PaymentsController::class, 'storeDirectPayments'])->name('store-direct-payment');
    Route::post('approve-payments-request/{id}/{action}', [PaymentsController::class, 'bankPaymentsApprovel']);

    Route::get('/order-payment-attachments/{id}', [PaymentsController::class, 'getAttachments']);

    Route::get('rft-planning', [RftPlanningController::class, 'index']);
    Route::get('rft-planning-json', [RftPlanningController::class, 'RftStockJson']);

    
    Route::get('expenses-list', [ExpensesController::class , 'index']);
    Route::get('expenses-json', [ExpensesController::class, 'ExpensesJson']);
    Route::get('expenses-payments/{expensesId}', [ExpensesController::class, 'expensesShow']);
    Route::get('expenses', [ExpensesController::class , 'create']);
    Route::post('expenses-store', [ExpensesController::class, 'store'])->name('expenses-store');

    //05-03-2025
    Route::get('admin-sales-reports-list', [AdminRepotsController::class, 'indexInvoice']);
    Route::get('admin-sales-reports-json', [AdminRepotsController::class, 'getAdminSalesReportJson']);
    Route::get('admin-reports-sales-order-list', [AdminRepotsController::class, 'indexSalesOrder']);
    Route::get('admin-reports-sales-order-json', [AdminRepotsController::class,'getAdminSalesOrderReportJson']);
    Route::get('admin-reports-payments-list', [AdminRepotsController::class, 'getPaymentsIndex']);
    Route::get('admin-reports-payments-json', [AdminRepotsController::class,'getAdminPaymentsReportJson']);
    Route::get('admin-reports-items-list', [AdminRepotsController::class, 'getitemsIndex']);
    Route::get('admin-reports-items-json', [AdminRepotsController::class,'getAdminItemsReportJson']);
    Route::get('admin-items-btaches-show/{itemCode}/{store}', [AdminRepotsController::class,'ItemsBatches']);
    Route::get('admin-reports-rft-list', [AdminRepotsController::class,'getRFTReportList']);
    Route::get('admin-reports-rft-json', [AdminRepotsController::class,'getAdminRftItemsJson']);
    
    Route::get('admin-reports-rft-doc-list', [AdminRepotsController::class,'getRFTDocListData']);
    Route::get('admin-reports-rft-doc-json', [AdminRepotsController::class,'getRftDocDetailsJson']);
    // Sap Integration 
    Route::get('sap-order-post', [QuotationController::class, 'SapOrderPost']);
    Route::get('sap-invoice-post', [QuotationController::class, 'SapInvoicePost']);
    Route::get('sap-direct-invoice-post/{invoiceId}', [QuotationController::class, 'SapDirectInvoicePost']);
    Route::get('sap_invoice_post_cron/', [QuotationController::class, 'SapInvoicePostByCron']);

    //credit note approvals
    Route::get('credit-note-approve', [CreditApprovelController::class, 'approvelIndex']);
    Route::get('credit-approve-customers', [CreditApprovelController::class, 'customerSearch']);
    Route::get('credit_customer_invoices/{customerId}', [CreditApprovelController::class, 'getInvoicesByCustomer']);
    Route::post('/recredit-note-request', [CreditApprovelController::class, 'recreditnoteRequest']);
    Route::post('/approve-recredit-request/{id}/{action}', [CreditApprovelController::class, 'approveRecreditRequest']);


    Route::get('sale_order_request_list', [SalesApprovalController::class, 'index']);
    Route::get('sale_order_request_json', [SalesApprovalController::class, 'requestSaleslist']);
    Route::post('approve-sales-request/{id}/{action}', [SalesApprovalController::class, 'approveSales']);


    Route::get('sales-order-approvals/{orderId}', [SalesOrderController::class, 'orderApprovalIndex']);

    // Sales Order Approvals
    // Route::get('sale-order-approval', [CreditApprovelController::class, 'salesIndex']);
    // Route::get('sales-order-list', [CreditApprovelController::class, 'salesList']);
    // Route::post('sales-order-approve_req/{id}', [CreditApprovelController::class, 'salesApprovelReq']);
    // Route::get('sales-order-approve_list', [CreditApprovelController::class, 'salesApprovelList']);
    // Route::post('/approve-sales-request/{id}/{action}', [CreditApprovelController::class, 'approveSalesRequest']);

    Route::get('inv-payments-list', [InvoicePaymentsController::class, 'index']);
    Route::get('get-invoice-payments', [InvoicePaymentsController::class, 'getInvoicePayments']);
    Route::post('store-bulk-inv-payments', [InvoicePaymentsController::class, 'storebulkPayments']);

    //Route::get('testip', [QuotationController::class, 'test']);

    //---------------------29-11-2024---------------------------
    Route::get('credits',[CreditController::class, 'index']);
    Route::get('credit-note', [CreditController::class, 'NewCreditpage']);
    Route::get('credit-items-list/{id}', [CreditController::class, 'CreditItemsIist']);
    Route::get('credit-note-json', [CreditController::class, 'creditNoteJson']);
    Route::get('creditnote-customer-search', [CreditController::class, 'CreditCustomerSearch']);
    Route::get('customer-creditnote', [CreditController::class, 'customerCreditNote'])->name('customer-creditnote');
    Route::get('customer-credit-sales', [CreditController::class, 'customerCreditNoteOrders'])->name('customer-credit-sales');
    Route::get('sales-employees', [CreditController::class, 'salesEmployee']);
    Route::get('get-invoice-order-details/{orderId}', [CreditController::class, 'getInvoiceOrderData'])->name('get-invoice-order-details');
    Route::post('store-credit-memos', [CreditController::class, 'StoreCreditMemos']);
    Route::get('pdf/credits/credit_{id}', [CreditController::class, 'generateCreditPDF']);
    Route::get('invoice_batch_details/{invoiceId}/{itemCode}', [CreditController::class , 'creditBatches']);

    Route::get('reports-list' , [ReportsController::class, 'indexInvoice']);
    Route::get('reports-sales-order-list' , [ReportsController::class, 'indexSalesOrder']);
    Route::get('reports-quotation-list' , [ReportsController::class, 'indexQuotation']);
    Route::get('invoice-reports-json' , [ReportsController::class, 'getPaidInvoiceJson']);
    Route::get('sales-order-reports-json' , [ReportsController::class, 'getPaidSalesOrderJson']);
    Route::get('quotation-reports-json' , [ReportsController::class, 'getPaidQuotationsJson']);
    Route::get('export-sales-order-report', [ReportsController::class, 'exportSalesOrderReport']);
    Route::get('expenses-reports-list' , [ReportsController::class, 'IndexExpenses']);
    Route::get('expenses-reports-json' , [ReportsController::class, 'getPaidExpensesJson']);
    Route::get('export-quotation-report' , [ReportsController::class, 'exportQuotationReport']);
    Route::get('export-invoice-report', [ReportsController::class, 'exportInvoiceReport']);
    Route::get('export-sales-items-reports', [ReportsController::class, 'exportSalesItemsReport']);

    Route::get('credit-note-reports', [ReportsController::class, 'creditNote']);
    Route::get('credit-note-reports-json' , [ReportsController::class, 'CreditNoteJson']);
    Route::get('export-credit-note-report', [ReportsController::class, 'ExportCreditNoteReport']);
    
    Route::get('rft-batches-reports-json', [ReportsController::class, 'getRftItemsWithBatches']);
    Route::get('rft-with-batches-reports', [ReportsController::class, 'indexRftWithBatchesReport']);
    Route::post('rft-block/{rftNum}', [RequestForTenderController::class, 'rftBlock']);

    //04-03
    Route::get('invoice_sap_status_update', [QuotationController::class, 'sapSatusUpdate']);


    Route::get('inv-items-by-batches-list', [ReportsController::class, 'invoiceBatchesIndex']);
    Route::get('inv-items-by-batches-json', [ReportsController::class, 'invoiceItemsWithBatches']);
        // 22/1/2025
    
        Route::get('sales-items-reports', [ReportsController::class, 'indexsalesItems']);
        Route::get('sales-items-reports-json', [ReportsController::class, 'salesReportItmesJson']);
        Route::get('reports-order-items-list', [ReportsController::class, 'indexOrderItems']);
        Route::get('order-items-reports-json', [ReportsController::class, 'getSalesOrderItemsJson']);
        Route::get('payments-reports-list', [ReportsController::class, 'getPaymentsIndex']);
        Route::post('payments-reports-json', [ReportsController::class, 'paymentsJson']);
        Route::get('export-payments-report', [ReportsController::class, 'exportPaymentsReport']);
    
        Route::get('rft-list', [RequestForTenderController::class, 'index']);
        Route::get('rent-for-tendors-json', [RequestForTenderController::class, 'getRentForTenderJson']);
        Route::post('/store-rft-details', [RequestForTenderController::class, 'storeRFTDetails'])->name('storeRFTDetails');
        Route::get('rent-tender-details/{rftNumber}', [RequestForTenderController::class, 'ShowRFTDetails']);
        Route::get('rft-batches/{itemId}/{quantity}', [RequestForTenderController::class, 'rftBatchQuantity']);

        Route::get('rft-items-list-data', [RequestForTenderController::class, 'exportItemsJson']); 
        Route::get('rft-items-list/{id}', [RequestForTenderController::class, 'itemsIndex']); 
        Route::get('get_rft_item_batch_details/{id}', [RequestForTenderController::class, 'rftBatchDetails']); 

        Route::get('rft-to-batches-details/{rftId}/{itemId}/{quantity}', [RequestForTenderController::class, 'toRftBatches']);

        Route::get('rft-reports-list', [ReportsController::class, 'indexRftReport']);
        Route::get('rft-reports-json', [ReportsController::class, 'getRftItemsJson']);
        Route::get('sap_from_rft_web/{id}',[AarnextimportController::class, 'fromRftPostByWeb']);
        Route::get('sap_to_rft_web/{id}',[AarnextimportController::class, 'toRftPostByWeb']);
        Route::get('items-list', [itemController::class, 'index']);
        Route::get('items-json', [itemController::class, 'getItemsJson']);
        Route::get('get_item_batch_details/{itemId}', [itemController::class, 'GetbatchDetails']);

});

Route::view('blank', 'blank');

// Route::get('getGSTINDetails/{gstin}', [GSTINAPIController::class, 'getGSTIN']);
Route::get('update-webid-customers', [NewCustomerController::class, 'updateWebId']);

Route::get('update-commonname-excel', [ExcelImportController::class, 'updateDuplicatePANCustomers']);

Route::get('update-old-salesemp-new', [ExcelImportController::class, 'updateSalesEmployeesCustomers']);
Route::get('import-new-salesemp', [ExcelImportController::class, 'createSalesEmployees']);
Route::get('update-salesemp-manager', [ExcelImportController::class, 'updateManagerSalesEmployees']);
Route::get('update-item-price', [ExcelImportController::class, 'updateItemPrice']);

Route::get('otc-customer-pan-update', [ProjectController::class, 'OTCCustomerPanUpdate']);

Route::get('update-inactive-customers-dbel2023', [ExcelImportController::class, 'updateInactiveCustomersdbel2023']);
Route::get('update-inactive-customers-faled2023', [ExcelImportController::class, 'updateInactiveCustomersfaled2023']);
Route::get('update-inactive-customers-fawcpltd2023', [ExcelImportController::class, 'updateInactiveCustomersfawcpltd2023']);
Route::get('update-inactive-customers-vpl2023', [ExcelImportController::class, 'updateInactiveCustomersvpl2023']);

Route::get('update-customer-category', [ExcelImportController::class, 'updateCustomerCategory']);
Route::get('update-customer-subcategory', [ExcelImportController::class, 'updateCustomerSubcategory']);

//Route::get('store-data', [App\Http\Controllers\UserController::class, 'users']);

Route::get('testip', [QuotationController::class, 'test']);


Route::post('resync_documents', [AarnextimportController::class, 'resyncDocuments']);


Route::get('warehouse-batch-details', [AarnextimportController::class, 'warehouseBatchDetails']);
Route::get('get-warehouse-batches', [AarnextimportController::class, 'warehouseBatch']);
Route::get('sap_invoice_post_web/{id}', [AarnextimportController::class, 'SapInvoicePostByWeb']);
Route::get('sap_credit_post_web/{id}', [AarnextimportController::class, 'SapCreditPostByWeb']);
Route::get('cash_payments_post_web/{id}', [AarnextimportController::class, 'CashPaymentPostByWeb']);
Route::get('tpa_payments_post_web/{id}', [AarnextimportController::class, 'tpaPaymentPostByWeb']);
Route::get('bank_payments_post_web/{id}', [AarnextimportController::class, 'bankPaymentPostByWeb']);
Route::get('sap_cash_tpa_web/{id}',[AarnextimportController::class, 'CashTpaWebPost']);

Route::get('generate-pdf', [PDFController::class, 'generatePDF']);

Route::get('whatsap_msg',[StockEmailController::class, 'sendWhatsAppMessage']);

Route::middleware('auth:customer')->group(function () {
  Route::get('/customer-dashboard', [CustomerAuthController::class, 'dashboard'])->name('customer.dashboard');
    Route::post('/customer/update-password', [CustomerAccessController::class, 'updatePassword'])
    ->name('customer.updatePassword');

  Route::get('customer-order-creation',[CustomerAccessController::class, 'orderIndex'])->name('customer.orders');
  Route::get('customer-item-search', [CustomerAccessController::class, 'getCustomerItemSearch']);
  Route::post('store-customer-orders', [CustomerAccessController::class, 'storeCustomerOrder']);
  Route::get('pdf/sales/sales_copy_{id}', [CustomerAccessController::class, 'generateSalesInvoicePDF']);
  Route::get('customer-order-reports', [CustomerAccessController::class, 'customerOrderReports'])->name('customer.order.reports');
  Route::get('customer-invoice-reports', [CustomerAccessController::class, 'customerInvoiceReports'])->name('customer.invoice.reports');
  Route::get('customer-order-reports-json', [CustomerAccessController::class, 'customerOrderReportsJson'])->name('customer.order.reports.json');
  Route::get('customer-invoice-reports-json', [CustomerAccessController::class, 'customerInvoiceReportsJson'])->name('customer.invoice.reports.json');
  Route::get('pdf/invoices/invoice_{id}', [QuotationController::class, 'generateInvoicePDF']);
  Route::get('customer-addresses-list/{customerID}', [CustomerAccessController::class, 'customerAddresses']);

});
