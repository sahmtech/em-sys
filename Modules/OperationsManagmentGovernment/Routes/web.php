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


Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {

    Route::prefix('operationsmanagmentgovernment')->group(function () {
        Route::get('/', 'OperationsManagmentGovernmentController@index');
        Route::get('/dashboard', [Modules\OperationsManagmentGovernment\Http\Controllers\DashboardController::class, 'index'])->name('operationsmanagmentgovernment.dashboard');
        Route::get('/requests', [\Modules\OperationsManagmentGovernment\Http\Controllers\RequestController::class, 'index'])->name('operationsmanagmentgovernment.view_requests');
        Route::post('/storeOperationsManagmentGovernmentRequest', [\Modules\OperationsManagmentGovernment\Http\Controllers\RequestController::class, 'store'])->name('storeOperationsManagmentGovernmentRequest');
    });
});