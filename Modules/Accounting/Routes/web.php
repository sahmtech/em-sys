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

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\OpeningBalanceController;
use Modules\Accounting\Http\Controllers\PaymentVouchersController;
use Modules\Accounting\Http\Controllers\ReceiptVouchersController;
use Modules\Accounting\Http\Controllers\SettingsController;

Route::group(['middleware' => ['web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'], 'prefix' => 'accounting', 'namespace' => '\Modules\Accounting\Http\Controllers'], function()
{
    Route::get('dashboard', 'AccountingController@dashboard');

    Route::get('accounts-dropdown', 'AccountingController@AccountsDropdown')->name('accounts-dropdown');

    Route::get('get-account-sub-types', 'CoaController@getAccountSubTypes');
    Route::get('get-account-details-types', 'CoaController@getAccountDetailsType');
    Route::resource('chart-of-accounts', 'CoaController');
    Route::get('ledger/{id}', 'CoaController@ledger')->name('accounting.ledger');
    Route::get('activate-deactivate/{id}', 'CoaController@activateDeactivate');
    Route::get('create-default-accounts', 'CoaController@createDefaultAccounts')->name('accounting.create-default-accounts');

    Route::resource('journal-entry', 'JournalEntryController');
    Route::get('journal-entry/map/show', 'JournalEntryController@map');
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

    Route::resource('transfer', 'TransferController')->except(['show']);

    Route::resource('budget', 'BudgetController')->except(['show', 'edit', 'update', 'destroy']);

    Route::get('reports', 'ReportController@index');
    Route::get('reports/trial-balance', 'ReportController@trialBalance')->name('accounting.trialBalance');
    Route::get('reports/balance-sheet', 'ReportController@balanceSheet')->name('accounting.balanceSheet');
    Route::get('reports/account-receivable-ageing-report', 
    'ReportController@accountReceivableAgeingReport')->name('accounting.account_receivable_ageing_report');

    Route::get('reports/account-payable-ageing-report', 
    'ReportController@accountPayableAgeingReport')->name('accounting.account_payable_ageing_report');

    Route::resource('cost_centers', 'CostCenterController');
    Route::resource('opening_balances', 'OpeningBalanceController');
    Route::get('/accounting/opening_balance/equation', [OpeningBalanceController::class, 'calcEquation'])->name('opening_balance.calc');
    Route::resource('receipt_vouchers', 'ReceiptVouchersController');
    Route::get('/accounting/receipt_vouchers/load/data', [ReceiptVouchersController::class, 'loadNeededData'])->name('receipt_vouchers.load');
    Route::resource('payment_vouchers', 'PaymentVouchersController');
    Route::get('/accounting/payment_vouchers/load/data', [PaymentVouchersController::class, 'loadNeededData'])->name('payment_vouchers.load');


    Route::get('transactions', 'TransactionController@index');
    Route::get('transactions/map', 'TransactionController@map');
    Route::post('transactions/save-map', 'TransactionController@saveMap');
    Route::post('save-settings', 'SettingsController@saveSettings');

    Route::get('install', 'InstallController@index');
    Route::post('install', 'InstallController@install');
    Route::get('install/uninstall', 'InstallController@uninstall');
    Route::get('install/update', 'InstallController@update');
    
});
