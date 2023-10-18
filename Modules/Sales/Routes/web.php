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

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu')->group(function () {


    Route::prefix('sale')->group(function () {
        Route::get('/clients', [\Modules\Sales\Http\Controllers\ClientsController::class, 'index'])->name('clients');
        Route::get('/cotracts', [\Modules\Sales\Http\Controllers\ContractsController::class, 'index'])->name('cotracts');
        Route::get('/offer-price', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'index'])->name('price_offer');
        Route::get('/createOfferPrice', [\Modules\Sales\Http\Controllers\OfferPriceController::class,'create'])->name('createOfferPrice');
        Route::get('/clientAdd', [\Modules\Sales\Http\Controllers\OfferPriceController::class,'clientAdd'])->name('clientAdd');
        Route::get('/clientAdd', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'clientAdd'])->name('clientAdd');
        Route::post('/saveQuickClient', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'saveQuickClient'])->name('saveQuickClient');
        Route::get('/viewClients', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'index'])->name('viewClients');
        Route::get('/getClientRow', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'getClientRow'])->name('getClientRow');
        Route::post('/storeOfferPrice', [\Modules\Sales\Http\Controllers\OfferPriceController::class,'store'])->name('storeOfferPrice');
        
   
    });
});
