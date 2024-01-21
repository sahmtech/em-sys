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
    Route::prefix('generalmanagement')->group(function() {
        Route::get('/', 'GeneralManagementController@index');
        Route::get('/dashboard', [Modules\GeneralManagement\Http\Controllers\DashboardController::class, 'index'])->name('GeneralManagement.dashboard');
   
        //requests
        Route::get('/president_requests', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'index'])->name('president_requests');
        Route::get('/escalate_requests', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'escalateRequests'])->name('escalate_requests');
        Route::post('/change-status', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'changeStatus'])->name('generalmanagement.changeStatus');
        Route::get('/viewGmRequest/{requestId}', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewGmRequest');
   
    });
});