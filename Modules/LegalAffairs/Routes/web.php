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

use Modules\LegalAffairs\Http\Controllers\LegalAffairsContractsManagementController;
use Modules\LegalAffairs\Http\Controllers\LegalAffairsController;
use Modules\LegalAffairs\Http\Controllers\RequestController;


Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {

    Route::prefix('legalaffairs')->group(function () {
        Route::get('/dashboard', [LegalAffairsController::class, 'index'])->name('legalAffairs.dashboard');
        Route::get('/contracts_management', [LegalAffairsController::class, 'index'])->name('legalAffairs.contracts_management');
        Route::get('/employees_contracts', [LegalAffairsContractsManagementController::class, 'employeeContracts'])->name('legalAffairs.employeeContracts');
        Route::get('/sales_contracts', [LegalAffairsContractsManagementController::class, 'salesContracts'])->name('legalAffairs.salesContracts');

        Route::post('/storeLegalRequest', [RequestController::class, 'store'])->name('storeLegalRequest');
        Route::get('/legalrequests', [RequestController::class, 'index'])->name('legalrequests');
    });
});
