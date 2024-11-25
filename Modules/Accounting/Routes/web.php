<?php

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

use App\Http\Controllers\BrandController;
use App\Http\Controllers\ImportOpeningStockController;
use App\Http\Controllers\ImportProductsController;
use App\Http\Controllers\ImportSalesController;
use App\Http\Controllers\LabelsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SellingPriceGroupController;
use App\Http\Controllers\SellReturnController;
use App\Http\Controllers\TaxonomyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VariationTemplateController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\OpeningBalanceController;
use Modules\Accounting\Http\Controllers\PaymentVouchersController;
use Modules\Accounting\Http\Controllers\ReceiptVouchersController;
use Modules\Accounting\Http\Controllers\SettingsController;

Route::group(['middleware' => ['web', 'SetSessionData', 'auth', 'language', 'timezone', 'CustomAdminSidebarMenu'], 'prefix' => 'all-accounting', 'namespace' => '\Modules\Accounting\Http\Controllers'], function () {
    Route::get('/', [\Modules\Accounting\Http\Controllers\AccountingController::class, 'landing'])->name('accountingLanding');
    Route::get('/setSession', [\Modules\Accounting\Http\Controllers\AccountingController::class, 'setSession'])->name('setSession');
    Route::get('/companies_access_permissions', [\Modules\Accounting\Http\Controllers\AccountingUserAccessCompaniesController::class, 'index'])->name('companies_access_permissions');
    Route::get('/getUserCompaniesPermissions/{userId}', [\Modules\Accounting\Http\Controllers\AccountingUserAccessCompaniesController::class, 'getUserCompaniesPermissions'])->name('getUserCompaniesPermissions');
    Route::post('/companies_access_permissions/store', [\Modules\Accounting\Http\Controllers\AccountingUserAccessCompaniesController::class, 'store'])->name('companies_access_permissions.store');
});

Route::group(['middleware' => ['web', 'compay_session', 'SetSessionData', 'auth', 'language', 'timezone', 'CustomAdminSidebarMenu'], 'prefix' => 'accounting', 'namespace' => '\Modules\Accounting\Http\Controllers'], function () {

    Route::get('/payrolls_checkpoint/{from}', [App\Http\Controllers\PayrollController::class, 'payrolls_checkpoint'])->name('accounting.payrolls_checkpoint');
    Route::post('/create_payment/{id}', [App\Http\Controllers\PayrollController::class, 'create_payment'])->name('accounting.payrolls.create_payment');
    Route::post('/create_single_payment/{id}', [App\Http\Controllers\PayrollController::class, 'create_single_payment'])->name('accounting.payrolls.create_single_payment');
    Route::get('dashboard', 'AccountingController@dashboard')->name('accounting.dashboard');
    Route::get('/filtered_requests/{filter}', [\Modules\Accounting\Http\Controllers\AccountingController::class, 'getFilteredRequests'])->name('accounting.getFilteredRequests');

    Route::get('accounts-dropdown', 'AccountingController@AccountsDropdown')->name('accounts-dropdown');
    Route::get('primary-accounts-dropdown', 'AccountingController@primaryAccountsDropdown')->name('primary-accounts-dropdown');
    Route::get('accounting-business-settings', 'SettingsController@getBusinessSettings_accounting')->name('accounting-business-settings');

    Route::get('/agent_time_sheet', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'index'])
        ->name('accounting.agentTimeSheetIndex');
    Route::get('/create', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'create'])->name('accounting.agentTimeSheet.create');
    Route::post('/submitTmeSheet', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'submitTmeSheet'])->name('accounting.agentTimeSheet.submitTmeSheet');
    Route::get('/agentTimeSheetUsers', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'agentTimeSheetUsers'])->name('accounting.agentTimeSheetUsers');
    Route::get('/agentTimeSheetGroups', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'agentTimeSheetGroups'])->name('accounting.agentTimeSheetGroups');
    Route::get('/timesheet-group/{id}/show', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'showTimeSheet'])->name('accounting.agentTimeSheet.showTimeSheet');
    Route::get('time_sheet/edit/{id}', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'editTimeSheet'])->name('accounting.agentTimeSheet.editTimeSheet');
    Route::get('agent/time_sheet/deal/{id}', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'dealTimeSheet'])->name('accounting.agentTimeSheet.dealTimeSheet');
    Route::get('agent/time_sheet/approvedTimeSheetByAccounting/{id}', [\Modules\Accounting\Http\Controllers\TimeSheetController::class, 'approvedTimeSheetByAccounting'])->name('accounting.agentTimeSheet.approvedTimeSheetByAccounting');

    Route::get('open-create-dialog/{id}', 'CoaController@open_create_dialog')->name('open_create_dialog');
    Route::get('get-account-sub-types', 'CoaController@getAccountSubTypes');
    Route::get('get-account-details-types', 'CoaController@getAccountDetailsType');
    Route::resource('chart-of-accounts', 'CoaController');
    Route::get('ledger/{id}', 'CoaController@ledger')->name('accounting.ledger');
    Route::get('importe-accounts', 'CoaController@viewImporte_accounts')->name('accounting.viewImporte_accounts');
    Route::post('save-importe-accounts', 'CoaController@importe_accounts')->name('accounting.saveImporte_accounts');
    Route::get('activate-deactivate/{id}', 'CoaController@activateDeactivate');
    Route::get('create-default-accounts', 'CoaController@createDefaultAccounts')->name('accounting.create-default-accounts');
    Route::get('ledger/print/{id}', 'CoaController@ledgerPrint');

    Route::resource('journal-entry', 'JournalEntryController');
    Route::get('journal-entry/map/show', 'JournalEntryController@map');
    Route::get('journal-entry/history/{id}', 'JournalEntryController@history_index');
    Route::get('journal-entry/history-view/{id}', 'JournalEntryController@history_view');
    Route::get('journal-entry/print/{id}', 'JournalEntryController@print');
    // Route::delete('journal-entry/{id}', 'JournalEntryController@destroy')->name('delete_journal');
    Route::post('journal-entry/map/save', 'JournalEntryController@saveMap');

    Route::get('settings', 'SettingsController@index');
    Route::get('reset-data', 'SettingsController@resetData');
    Route::get('settings/auto_mapping', [SettingsController::class, 'autoMapping'])
        ->name('settings.auto_mapping');
    Route::get('settings/map', [SettingsController::class, 'map'])
        ->name('settings.map');
    Route::post('settings/save_map', [SettingsController::class, 'saveMap'])
        ->name('settings.saveMap');

    Route::resource('account-type', 'AccountTypeController');
    Route::resource('automated-migration', 'AutomatedMigrationController');
    Route::get('automated-migration-delete-dialog/{id}', 'AutomatedMigrationController@delete_dialog');
    Route::get('automated-migration-active-toggle/{id}', 'AutomatedMigrationController@active_toggle');
    Route::get('automated-migration-delete-acc-trans-mapping/{id}', 'AutomatedMigrationController@destroy_acc_trans_mapping_setting');
    Route::get('create-deflute-auto-migration', [\Modules\Accounting\Http\Controllers\AutomatedMigrationController::class, 'create_deflute_auto_migration'])->name('create_deflute_auto_migration');
    Route::get('store-deflute-auto-migration', [\Modules\Accounting\Http\Controllers\AutomatedMigrationController::class, 'store_deflute_auto_migration'])->name('store_deflute_auto_migration');

    //requests
    Route::get('/accounting.requests', [\Modules\Accounting\Http\Controllers\RequestController::class, 'index'])->name('accounting.requests');
    Route::post('/accounting.returnReq', [\Modules\Accounting\Http\Controllers\RequestController::class, 'returnReq'])->name('accounting.returnReq');
    Route::post('/accounting.returnReq.store', [\Modules\Accounting\Http\Controllers\RequestController::class, 'store'])->name('accounting.returnReq.store');
    Route::get('/viewAccountingRequest/{requestId}', [\Modules\Accounting\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewAccountingRequest');

    Route::resource('transfer', 'TransferController')->except(['show']);

    Route::resource('budget', 'BudgetController')->except(['show', 'edit', 'update', 'destroy']);

    Route::get('reports', 'ReportController@index');
    Route::get('reports/trial-balance', 'ReportController@trialBalance')->name('accounting.trialBalance');
    Route::get('reports/income-statement', 'ReportController@incomeStatement')->name('accounting.incomeStatement');
    Route::get('reports/employees-statement/{id}', 'ReportController@employeesStatement')->name('accounting.employeesStatement');
    Route::get('reports/customers-suppliers-statement/{id?}', 'ReportController@customersSuppliersStatement')->name('accounting.customersSuppliersStatement');
    Route::get('reports/balance-sheet', 'ReportController@balanceSheet')->name('accounting.balanceSheet');
    Route::get(
        'reports/account-receivable-ageing-report',
        'ReportController@accountReceivableAgeingReport'
    )->name('accounting.account_receivable_ageing_report');

    Route::get(
        'reports/account-payable-ageing-report',
        'ReportController@accountPayableAgeingReport'
    )->name('accounting.account_payable_ageing_report');

    Route::resource('cost_centers', 'CostCenterController');
    Route::put('cost-center-update', 'CostCenterController@update')->name('cost_center_update');
    Route::post('cost-center-store', 'CostCenterController@store')->name('cost_center_store');

    Route::resource('opening_balances', 'OpeningBalanceController');
    Route::get('/accounting/opening_balance/equation', [OpeningBalanceController::class, 'calcEquation'])->name('opening_balance.calc');
    Route::get('/opening-balance/importe', [OpeningBalanceController::class, 'viewImporte_openingBalance'])->name('viewImporte_openingBalance');
    Route::post('/opening-balance/save-importe', [OpeningBalanceController::class, 'importe_openingBalance'])->name('save-importe_openingBalance');
    Route::resource('receipt_vouchers', 'ReceiptVouchersController');
    Route::get('/accounting/receipt_vouchers/load/data', [ReceiptVouchersController::class, 'loadNeededData'])->name('receipt_vouchers.load');
    Route::post('/accounting/payment_vouchers', [PaymentVouchersController::class, 'store'])->name('payment_vouchers-index-store');
    Route::get('/accounting/payment_vouchers', [PaymentVouchersController::class, 'index'])->name('index-payment_vouchers');

    Route::resource('payment_vouchers', 'PaymentVouchersController');
    Route::get('/accounting/payment_vouchers/load/data', [PaymentVouchersController::class, 'loadNeededData'])->name('payment_vouchers.load');

    Route::get('transactions', 'TransactionController@index')->name('getTransaction');
    Route::get('transactions/map', 'TransactionController@map');
    Route::post('transactions/save-map', 'TransactionController@saveMap');
    Route::post('save-settings', 'SettingsController@saveSettings');

    Route::get('install', 'InstallController@index');
    Route::post('install', 'InstallController@install');
    Route::get('install/uninstall', 'InstallController@uninstall');
    Route::get('install/update', 'InstallController@update');

    Route::get('validate-invoice-to-return/{invoice_no}', [SellReturnController::class, 'validateInvoiceToReturn']);
    Route::resource('sell-return', SellReturnController::class);
    Route::get('sell-return/get-product-row', [SellReturnController::class, 'getProductRow']);
    Route::get('/sell-return/print/{id}', [SellReturnController::class, 'printInvoice']);
    Route::get('/sell-return/add/{id}', [SellReturnController::class, 'add']);

    Route::get('/import-sales', [ImportSalesController::class, 'index']);
    Route::post('/import-sales/preview', [ImportSalesController::class, 'preview']);
    Route::post('/import-sales', [ImportSalesController::class, 'import']);
    Route::get('/revert-sale-import/{batch}', [ImportSalesController::class, 'revertSaleImport']);

    // Route::resource('products', ProductController::class);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/create', [ProductController::class, 'create']);

    Route::get('update-product-price', [SellingPriceGroupController::class, 'updateProductPrice'])->name('update-product-price');
    Route::get('export-product-price', [SellingPriceGroupController::class, 'export']);
    Route::post('import-product-price', [SellingPriceGroupController::class, 'import']);

    Route::get('/labels/show', [LabelsController::class, 'show']);
    Route::get('/labels/add-product-row', [LabelsController::class, 'addProductRow']);
    Route::get('/labels/preview', [LabelsController::class, 'preview']);

    // Route::resource('variation-templates', VariationTemplateController::class);
    Route::get('/variation-templates', [VariationTemplateController::class, 'index']);

    Route::get('/import-products', [ImportProductsController::class, 'index']);
    Route::post('/import-products/store', [ImportProductsController::class, 'store']);

    //Import opening stock
    Route::get('/import-opening-stock', [ImportOpeningStockController::class, 'index']);
    Route::post('/import-opening-stock/store', [ImportOpeningStockController::class, 'store']);

    //   Route::resource('selling-price-group', SellingPriceGroupController::class);
    Route::get('/selling-price-group', [SellingPriceGroupController::class, 'index']);

    Route::resource('units', UnitController::class);
    Route::get('/units', [UnitController::class, 'index']);

    // Route::resource('taxonomies', TaxonomyController::class);
    Route::get('/taxonomies', [TaxonomyController::class, 'index']);

    // Route::resource('brands', BrandController::class);
    Route::get('/brands', [BrandController::class, 'index']);

    // Route::resource('warranties', WarrantyController::class);
    Route::get('/warranties', [WarrantyController::class, 'index']);

});
