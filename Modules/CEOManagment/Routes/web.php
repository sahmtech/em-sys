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
  
    Route::prefix('ceomanagment')->group(function() {
        Route::get('/', 'CEOManagmentController@index'); 
        Route::get('/dashboard', [Modules\CEOManagment\Http\Controllers\DashboardController::class, 'index'])->name('ceomanagment.dashboard');
 
        Route::get('/requests', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'index'])->name('requests');
 
 
    });
  
  
 
});