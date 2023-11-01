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


    Route::prefix('sale')->group(function () {
    

        Route::get('/',[\Modules\Sales\Http\Controllers\SalesController::class, 'index'])->name('sales_landing');

        Route::get('/offer-price', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'index'])->name('price_offer');
        Route::get('/createOfferPrice', [\Modules\Sales\Http\Controllers\OfferPriceController::class,'create'])->name('createOfferPrice');
        Route::post('/storeOfferPrice', [\Modules\Sales\Http\Controllers\OfferPriceController::class,'store'])->name('storeOfferPrice');
        Route::post('/change-status', [\Modules\Sales\Http\Controllers\OfferPriceController::class,'changeStatus'])->name('changeStatus');

   
        Route::get('/clientAdd', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'clientAdd'])->name('clientAdd');
        Route::post('/saveQuickClient', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'saveQuickClient'])->name('saveQuickClient');
        Route::get('/viewClients', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'index'])->name('viewClients');
        Route::get('/getClientRow', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'getClientRow'])->name('getClientRow');
        
       
        Route::get('/clients', [\Modules\Sales\Http\Controllers\ClientsController::class, 'index'])->name('sale.clients');
        Route::get('/clients/create', [\Modules\Sales\Http\Controllers\ClientsController::class, 'create'])->name('sale.clients.create');
        Route::post('/storeCustomer', [\Modules\Sales\Http\Controllers\ClientsController::class, 'store'])->name('sale.storeCustomer');
        Route::put('/UpdateCustomer/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'update'])->name('sale.UpdateCustomer');
        Route::get('/clients/view/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'show'])->name('sale.clients.view');
        Route::delete('/clients/delete/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'destroy'])->name('sale.clients.delete');
        Route::get('/clients/edit/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'edit'])->name('sale.clients.edit');
        Route::delete('/deleteCustomer/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'deleteContact'])->name('sale.deleteCustomer');


        Route::get('/cotracts', [\Modules\Sales\Http\Controllers\ContractsController::class, 'index'])->name('saleContracts');
        Route::post('/storeContract', [\Modules\Sales\Http\Controllers\ContractsController::class, 'store'])->name('storeContract');
        Route::get('/getContractValues', [\Modules\Sales\Http\Controllers\ContractsController::class, 'getContractValues'])->name('sale.getContractValues');
        Route::delete('/cotracts/{id}', [\Modules\Sales\Http\Controllers\ContractsController::class, 'destroy'])->name('contract.destroy');
        Route::get('/offer_view/{id}', [\Modules\Sales\Http\Controllers\ContractsController::class, 'show'])->name('offer.view');
        Route::post('/specializations', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class,'fetchSpecializations'])->name('specializations');


    
        Route::get('/orderOperations', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'index'])->name('sale.orderOperations');
        Route::get('create/orderOperations', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'create'])->name('create.sale.orderOperations');
        Route::post('/get-contracts',[\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'getContracts'] )->name('get-contracts');
        Route::post('/store/orderOperations',[\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'store'] )->name('sale.store.orderOperations');
        Route::post('sale/operation/edit/{id}',[\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'edit'] )->name('sale.operation.edit');
        Route::get('show_operation/{id}',[\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'show'] )->name('sale.show_operation');
        Route::delete('destroy/show_operation/{id}',[\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'destroy'] )->name('sale.delete.order_operation');

        Route::get('/contract_itmes', [\Modules\Sales\Http\Controllers\ContractItemController::class, 'index'])->name('contract_itmes');
        Route::get('/createItme', [\Modules\Sales\Http\Controllers\ContractItemController::class, 'create'])->name('createItem');
        Route::post('/storeItem', [\Modules\Sales\Http\Controllers\ContractItemController::class, 'store'])->name('storeItem');
        Route::get('/contract_itmes/{id}/edit', [\Modules\Sales\Http\Controllers\ContractItemController::class, 'edit'])->name('item.edit');
        Route::delete('/contract_itmes/{id}', [\Modules\Sales\Http\Controllers\ContractItemController::class, 'destroy'])->name('item.destroy');
        Route::put('/updateItem/{id}', [\Modules\Sales\Http\Controllers\ContractItemController::class, 'update'])->name('updateItem');
        Route::post('/storeAppindexItem', [\Modules\Sales\Http\Controllers\ContractItemController::class, 'storeAppindexItem'])->name('storeAppindexItem');
      
        Route::get('/contract_appendices', [\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'index'])->name('contract_appendices');
        Route::get('/createAppendix', [\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'create'])->name('createAppendix');
        Route::post('/storeAppendix', [\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'store'])->name('storeAppendix');
        Route::get('/contract_appendices/{id}/edit', [\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'edit'])->name('appendix.edit');
        Route::delete('/contract_appendices/{id}', [\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'destroy'])->name('appendix.destroy');
        Route::put('/updateAppendix/{id}', [\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'update'])->name('updateAppendix');
      
       Route::get('/orderOperations', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'index'])->name('sale.orderOperations');
       //Route::post('/storeSaleOperation', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'store'])->name('sale.storeSaleOperation');
    });
});
