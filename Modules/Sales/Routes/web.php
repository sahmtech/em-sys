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
        Route::get('/clients', [\Modules\Sales\Http\Controllers\ClientsController::class, 'index'])->name('sale.clients');

        Route::get('/clients/create', [\Modules\Sales\Http\Controllers\ClientsController::class, 'create'])->name('sale.clients.create');
        Route::post('/storeCustomer', [\Modules\Sales\Http\Controllers\ClientsController::class, 'store'])->name('sale.storeCustomer');
        
        Route::put('/UpdateCustomer/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'update'])->name('sale.UpdateCustomer');
        
        Route::get('/clients/view/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'show'])->name('sale.clients.view');
        Route::delete('/clients/delete/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'destroy'])->name('sale.clients.delete');
        Route::get('/clients/edit/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'edit'])->name('sale.clients.edit');

        Route::delete('/deleteCustomer/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'deleteContact'])->name('sale.deleteCustomer');


        Route::get('/cotracts', [\Modules\Sales\Http\Controllers\ContractsController::class, 'index'])->name('sale.cotracts');
        Route::get('/offer-price', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'index'])->name('sale.offer-price');
    });
});
