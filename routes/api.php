<?php
use App\Http\Controllers\AarnextimportController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SapSync\SapSyncController;
use App\Http\Controllers\SapSync\SapMissingTransactionSyncController;
use App\Http\Controllers\SapSync\SapInvoicePostByUserController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarMulController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarHuaController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarViaController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarPalController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarLobController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarMalController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarBieController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarDunController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarGabController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarLubController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarMenController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarSauController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarSumController;
use App\Http\Controllers\SapSync\SapInvoicePostByAarUigController;
use App\Http\Controllers\SapSync\SapInvoicePostByAltBenController;
use App\Http\Controllers\SapSync\SapInvoicePostByAltCznController;
use App\Http\Controllers\SapSync\SapInvoicePostByAltMoxController;
use App\Http\Controllers\SapSync\SapInvoicePostByAltZanController;
use App\Http\Controllers\SapSync\SapInvoicePostByEquPalController;
use App\Http\Controllers\StockEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RftPlanningController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('import-excel-categories', [ExcelImportController::class, 'importCategories']);
Route::get('import-excel-subcategories', [ExcelImportController::class, 'importSubCategories']);

Route::get('import-excel-items', [ExcelImportController::class, 'importItems']);

Route::get('users_data',[AarnextimportController::class, 'fetchPdfData1']);
Route::get('warehouse-data',[AarnextimportController::class, 'warehousedata']);
Route::get('bpgroup-data',[AarnextimportController::class, 'bpGroupdata']);
Route::get('itemgroup-data',[AarnextimportController::class, 'Itemgroupdata']);
Route::get('item-data',[AarnextimportController::class, 'ItemsData']);
Route::get('payment-data',[AarnextimportController::class, 'PaymentTerms']);
Route::get('salesemployee-data',[AarnextimportController::class, 'salesEmployee']); 
Route::get('customers-data',[AarnextimportController::class, 'customers']);
Route::get('item-priceses-data',[AarnextimportController::class, 'ItemPriceses']);
Route::get('warehouse-batch-data',[AarnextimportController::class, 'warehouseBatch']);
Route::get('warehouse-batch-data-exec2',[AarnextimportController::class, 'warehouseBatchExec2']);
Route::get('remove-stock-data',[AarnextimportController::class, 'TruncateStockTables']);

Route::get('sap_customer_post_cron',[AarnextimportController::class, 'SapCustomerPostByCron']);
Route::get('sap_invoice_post_cron',[AarnextimportController::class, 'SapInvoicePostByCron']);
Route::get('invoice_sample_post_json/{id}',[AarnextimportController::class, 'InvoiceSampleJsonPrepare']);

Route::get('sap_credit_post_cron',[AarnextimportController::class, 'SapCreditlinePostByCron']);
Route::get('sap_cash_payment_post',[AarnextimportController::class, 'CashPaymentPost']);
Route::get('sap_cash_payment_post1',[AarnextimportController::class, 'CashPaymentPost1']);
Route::get('sap_tpa_payment_post',[AarnextimportController::class, 'TPAPaymentPost']);
Route::get('test_sap_url',[AarnextimportController::class, 'TestSapUrl']);
Route::get('sap_base_line',[AarnextimportController::class, 'baseLine']);
Route::post('rft-data',[AarnextimportController::class, 'rftFromSAP']);
Route::get('sap_cash_tpa_cron',[AarnextimportController::class, 'cashTpaCronPost']);

Route::get('from_rft_post_cron',[AarnextimportController::class, 'fromRftPost']);
Route::get('to_rft_post_cron',[AarnextimportController::class, 'toRftPost']);

Route::get('pending_payments',[AarnextimportController::class, 'pendingPayments']);


Route::post('create_item_from_sap',[SapSyncController::class, 'CreateItemFromSap']);
Route::post('update_item_batchqty_from_sap',[SapSyncController::class, 'UpdateItemBatchesFromSap']);
Route::post('update_create_item_batch_qty_from_sap',[SapSyncController::class, 'UpdateItemBatchQtyFromSap']);


Route::post('update_item_price_from_sap',[SapSyncController::class, 'UpdateItemPrice']);
Route::post('update_document_status_from_sap',[SapSyncController::class, 'UpdateDocumentStatus']);

Route::post('update_customers_from_sap',[SapSyncController::class, 'UpdateCustomesrFromSap']);
Route::post('update_single_customer_from_sap',[SapSyncController::class, 'UpdateSingleCustomerFromSap']);
Route::get('get_invoice_by_id/{web_id}',[SapMissingTransactionSyncController::class, 'GetInvoiceById']);
Route::get('get_bulk_invoices_by_id',[SapMissingTransactionSyncController::class, 'GetBulkInvoicesById']);

Route::get('get_creditmemo_by_id/{web_id}',[SapMissingTransactionSyncController::class, 'GetCreditById']);
Route::get('get_bulk_creditmemos_by_id',[SapMissingTransactionSyncController::class, 'GetBulkCreditsById']);
Route::get('get_invoice_id_creditmemo/{web_id}',[SapMissingTransactionSyncController::class, 'GetInvoiceIdCredit']);
Route::get('get_invoice_payment_by_id/{web_id}',[SapMissingTransactionSyncController::class, 'GetInvoicePaymentsId']);
Route::get('get_rft_by_id/{web_id}',[SapMissingTransactionSyncController::class, 'GetRFTById']);
Route::get('get_bulk_rfts',[SapMissingTransactionSyncController::class, 'SyncRFTBulkFromSAP']);


Route::get('generate_invoice_cron_pdf',[AarnextimportController::class, 'GenerateInvoiceCronPDF']);
Route::get('generate_hashkey',[AarnextimportController::class, 'GenerateHashkey']);

Route::get('sap_invoice_post_by_user',[SapInvoicePostByUserController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_user2',[SapInvoicePostByUserController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_user3',[SapInvoicePostByUserController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_mul',[SapInvoicePostByAarMulController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_mul2',[SapInvoicePostByAarMulController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_mul3',[SapInvoicePostByAarMulController::class, 'SapInvoicePostByUser3']);
Route::get('sap_invoice_post_by_mul4',[SapInvoicePostByAarMulController::class, 'SapInvoicePostByUser4']);
Route::get('sap_invoice_post_by_mul5',[SapInvoicePostByAarMulController::class, 'SapInvoicePostByUser5']);

Route::get('sap_invoice_post_by_hua',[SapInvoicePostByAarHuaController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_hua2',[SapInvoicePostByAarHuaController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_hua3',[SapInvoicePostByAarHuaController::class, 'SapInvoicePostByUser3']);
Route::get('sap_invoice_post_by_hua4',[SapInvoicePostByAarHuaController::class, 'SapInvoicePostByUser4']);
Route::get('sap_invoice_post_by_hua5',[SapInvoicePostByAarHuaController::class, 'SapInvoicePostByUser5']);


Route::get('sap_invoice_post_by_via',[SapInvoicePostByAarViaController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_via2',[SapInvoicePostByAarViaController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_via3',[SapInvoicePostByAarViaController::class, 'SapInvoicePostByUser3']);
Route::get('sap_invoice_post_by_via4',[SapInvoicePostByAarViaController::class, 'SapInvoicePostByUser4']);
Route::get('sap_invoice_post_by_via5',[SapInvoicePostByAarViaController::class, 'SapInvoicePostByUser5']);
Route::get('sap_invoice_post_by_via6',[SapInvoicePostByAarViaController::class, 'SapInvoicePostByUser6']);
Route::get('sap_invoice_post_by_via7',[SapInvoicePostByAarViaController::class, 'SapInvoicePostByUser7']);


Route::get('sap_invoice_post_by_pal',[SapInvoicePostByAarPalController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_pal2',[SapInvoicePostByAarPalController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_pal3',[SapInvoicePostByAarPalController::class, 'SapInvoicePostByUser3']);
Route::get('sap_invoice_post_by_pal4',[SapInvoicePostByAarPalController::class, 'SapInvoicePostByUser4']);
Route::get('sap_invoice_post_by_pal5',[SapInvoicePostByAarPalController::class, 'SapInvoicePostByUser5']);

Route::get('sap_invoice_post_by_lob', [SapInvoicePostByAarLobController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_lob2',[SapInvoicePostByAarLobController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_lob3',[SapInvoicePostByAarLobController::class, 'SapInvoicePostByUser3']);
Route::get('sap_invoice_post_by_lob4',[SapInvoicePostByAarLobController::class, 'SapInvoicePostByUser4']);

Route::get('sap_invoice_post_by_mal', [SapInvoicePostByAarMalController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_mal2',[SapInvoicePostByAarMalController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_mal3',[SapInvoicePostByAarMalController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_bie',[SapInvoicePostByAarBieController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_bie2',[SapInvoicePostByAarBieController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_bie3',[SapInvoicePostByAarBieController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_dun', [SapInvoicePostByAarDunController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_dun2',[SapInvoicePostByAarDunController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_dun3',[SapInvoicePostByAarDunController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_gab', [SapInvoicePostByAarGabController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_gab2',[SapInvoicePostByAarGabController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_gab3',[SapInvoicePostByAarGabController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_lub', [SapInvoicePostByAarLubController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_lub2',[SapInvoicePostByAarLubController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_lub3',[SapInvoicePostByAarLubController::class, 'SapInvoicePostByUser3']);
Route::get('sap_invoice_post_by_lub4',[SapInvoicePostByAarLubController::class, 'SapInvoicePostByUser4']);

Route::get('sap_invoice_post_by_men', [SapInvoicePostByAarMenController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_men2',[SapInvoicePostByAarMenController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_men3',[SapInvoicePostByAarMenController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_sau', [SapInvoicePostByAarSauController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_sau2',[SapInvoicePostByAarSauController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_sau3',[SapInvoicePostByAarSauController::class, 'SapInvoicePostByUser3']);
Route::get('sap_invoice_post_by_sau4',[SapInvoicePostByAarSauController::class, 'SapInvoicePostByUser4']);

Route::get('sap_invoice_post_by_sum', [SapInvoicePostByAarSumController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_sum2',[SapInvoicePostByAarSumController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_sum3',[SapInvoicePostByAarSumController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_uig', [SapInvoicePostByAarUigController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_uig2',[SapInvoicePostByAarUigController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_uig3',[SapInvoicePostByAarUigController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_ben', [SapInvoicePostByAltBenController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_ben2',[SapInvoicePostByAltBenController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_ben3',[SapInvoicePostByAltBenController::class, 'SapInvoicePostByUser3']);
Route::get('sap_invoice_post_by_ben4',[SapInvoicePostByAltBenController::class, 'SapInvoicePostByUser4']);

Route::get('sap_invoice_post_by_czn', [SapInvoicePostByAltCznController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_czn2',[SapInvoicePostByAltCznController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_czn3',[SapInvoicePostByAltCznController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_mox', [SapInvoicePostByAltMoxController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_mox2',[SapInvoicePostByAltMoxController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_mox3',[SapInvoicePostByAltMoxController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_zan', [SapInvoicePostByAltZanController::class, 'SapInvoicePostByUser']);
Route::get('sap_invoice_post_by_zan2',[SapInvoicePostByAltZanController::class, 'SapInvoicePostByUser2']);
Route::get('sap_invoice_post_by_zan3',[SapInvoicePostByAltZanController::class, 'SapInvoicePostByUser3']);

Route::get('sap_invoice_post_by_equ_pal', [SapInvoicePostByEquPalController::class, 'SapInvoicePostByUser']);

Route::get('update_user_session',[AarnextimportController::class, 'UpdateUserSession']);
Route::get('update_user_session2',[AarnextimportController::class, 'UpdateUserSession2']);
Route::get('update_manager_session',[AarnextimportController::class, 'UpdateManagerSession']);

// stock Details 
Route::get('stock-tot-details', [StockEmailController::class, 'stockData']);
Route::get('stock-comparison-data', [StockEmailController::class, 'stockView']);
Route::get('stock-tot-details-grp', [StockEmailController::class, 'stockDataForGroup']);
Route::get('sync-pending-docs', [StockEmailController::class, 'penddingSyncDoc']);


Route::post('update_customer_status_from_sap',[SapSyncController::class, 'customerStatus']);
Route::post('update_rft_cancelled_status_from_sap',[SapSyncController::class, 'RFTStatusUpdate']);
Route::post('update_customer_discount_status_from_sap',[SapSyncController::class, 'CustomerDiscount']);

