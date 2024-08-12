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

use App\Http\Controllers\ContactController;

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {


    Route::prefix('accounting')->group(function () {
        Route::get('contacts/{id}', [ContactController::class, 'show'])->name('contacts-profile');
    });

    Route::prefix('sale')->group(function () {



        Route::get('/', [\Modules\Sales\Http\Controllers\SalesController::class, 'index'])->name('sales_landing');
        Route::get('/getOperationAvailableContracts', [\Modules\Sales\Http\Controllers\SalesController::class, 'getOperationAvailableContracts'])->name('getOperationAvailableContracts');
        Route::get('/sales_sources', [\Modules\Sales\Http\Controllers\SaleSourcesController::class, 'index'])->name('sales_sources');
        Route::post('/store_source', [\Modules\Sales\Http\Controllers\SaleSourcesController::class, 'store'])->name('store_source');
        Route::DELETE('/source/delete/{id}', [\Modules\Sales\Http\Controllers\SaleSourcesController::class, 'destroy'])->name('sale_source_destroy');
        Route::post('/store_source/update', [\Modules\Sales\Http\Controllers\SaleSourcesController::class, 'update'])->name('source.update');


        Route::get('/sales_department_employees', [\Modules\Sales\Http\Controllers\SalesController::class, 'sales_department_employees'])->name('sales_department_employees');

        //requests 
        Route::get('/sales.requests', [\Modules\Sales\Http\Controllers\RequestController::class, 'index'])->name('sales.requests');
        Route::post('/sales.returnReq', [\Modules\Sales\Http\Controllers\RequestController::class, 'returnReq'])->name('sales.returnReq');
        Route::get('/escalate_requests', [\Modules\Sales\Http\Controllers\RequestController::class, 'escalateRequests'])->name('sales.escalate_requests');
        Route::post('/changeEscalateRequestsStatus', [\Modules\Sales\Http\Controllers\RequestController::class, 'changeEscalateRequestsStatus'])->name('sales.changeEscalateRequestsStatus');
        Route::post('/storeRequest', [\Modules\Sales\Http\Controllers\RequestController::class, 'store'])->name('storeSaleRequest');
        Route::get('/viewSaleRequest/{requestId}', [\Modules\Sales\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewSaleRequest');

        Route::get('/offer-price', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'index'])->name('price_offer');
        Route::get('/createOfferPrice', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'create'])->name('createOfferPrice');
        Route::get('/createOfferPrice/{id}', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'create_offer_price_qualified_contacts'])->name('create_offer_price_qualified_contacts');
        Route::post('/storeOfferPrice', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'store'])->name('storeOfferPrice');
        Route::post('/change-status', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'changeStatus'])->name('changeStatus');

        Route::get('/offerPricePrint/{id}', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'print']);
        Route::get('/offerPriceShow/{id}', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'show']);
        Route::get('/offer-price/edit/{id}', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'edit']);
        Route::PUT('/updateOfferPrice/{id}', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'update'])->name('updateOfferPrice');
        Route::get('/offerContractPriceShow/{id}', [\Modules\Sales\Http\Controllers\ContractsController::class, 'showOfferPrice']);


        Route::get('/accepted_offer_prices', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'accepted_offer_prices'])->name('accepted_offer_prices');
        Route::get('/unaccepted_offer_prices', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'unaccepted_offer_prices'])->name('unaccepted_offer_prices');
        Route::get('/under_study_offer_prices', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'index'])->name('under_study_offer_prices');



        Route::get('/clientAdd', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'clientAdd'])->name('clientAdd');
        Route::post('/saveQuickClient', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'saveQuickClient'])->name('saveQuickClient');
        Route::get('/viewClients', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'index'])->name('viewClients');
        Route::get('/getClientRow', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'getClientRow'])->name('getClientRow');
        Route::post('/client/delete', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'deleteClient'])->name('tarclientDelete');
        Route::get('/client/edit/{id}', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'editClient'])->name('sale.editClient');
        Route::post('/sale/updateClient/{id}', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'updateClient'])->name('sale.updateClient');

        Route::get('/Unsupported_workers', [\Modules\Sales\Http\Controllers\SaleWorkerController::class, 'index'])->name('Unsupported_workers');
        Route::post('/storeUnsupported_workers', [\Modules\Sales\Http\Controllers\SaleWorkerController::class, 'store'])->name('storeUnsupported_workers');

        Route::get('/clients', [\Modules\Sales\Http\Controllers\ClientsController::class, 'index'])->name('sale.clients');
        Route::get('/lead_contacts', [\Modules\Sales\Http\Controllers\ClientsController::class, 'lead_contacts'])->name('lead_contacts');
        Route::get('/draft_contacts', [\Modules\Sales\Http\Controllers\ClientsController::class, 'draft_contacts'])->name('draft_contacts');

        Route::get('/qualified_contacts', [\Modules\Sales\Http\Controllers\ClientsController::class, 'qualified_contacts'])->name('qualified_contacts');
        Route::get('/change_contact_status_api', [\Modules\Sales\Http\Controllers\ClientsController::class, 'change_contact_status_api'])->name('change_contact_status_api');
        Route::get('/unqualified_contacts', [\Modules\Sales\Http\Controllers\ClientsController::class, 'unqualified_contacts'])->name('unqualified_contacts');
        Route::get('/converted_contacts', [\Modules\Sales\Http\Controllers\ClientsController::class, 'converted_contacts'])->name('converted_contacts');

        Route::get('/getEnglishNameForCity', [\Modules\Sales\Http\Controllers\ClientsController::class, 'getEnglishNameForCity'])->name('getEnglishNameForCity');
        Route::post('/changeStatus', [\Modules\Sales\Http\Controllers\ClientsController::class, 'changeStatus'])->name('changeStatus');
        Route::post('/change_to_converted_client', [\Modules\Sales\Http\Controllers\ClientsController::class, 'change_to_converted_client'])->name('change_to_converted_client');
        Route::post('/change-status-contact/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'changeStatusContact'])->name('changeStatusContact');
        Route::get('/change-contact-status', [\Modules\Sales\Http\Controllers\ClientsController::class, 'changeContact_Status_dialog'])->name('changeContactStatus');

        // Route::get('changeDraftStatus', [\Modules\Sales\Http\Controllers\ClientsController::class, 'changeDraftStatus'])->name('changeDraftStatus');

        //    Route::get('changeDraftStatus/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'changeDraftStatus'])->name('changeDraftStatus');
        Route::get('sale/changeDraftStatus/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'changeDraftStatus'])->name('changeDraftStatus');
        Route::get('/changeDraftStatus/{contactId}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'changeDraftStatus'])->name('changeDraftStatus');


        Route::get('/clients/create', [\Modules\Sales\Http\Controllers\ClientsController::class, 'create'])->name('sale.clients.create');
        Route::post('/storeCustomer', [\Modules\Sales\Http\Controllers\ClientsController::class, 'store'])->name('sale.storeCustomer');
        Route::post('/storeQualifiedCustomer', [\Modules\Sales\Http\Controllers\ClientsController::class, 'storeQualifiedCustomer'])->name('sale.storeQualifiedCustomer');

        Route::put('/UpdateCustomer/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'update'])->name('sale.UpdateCustomer');
        Route::get('/clients/view/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'show'])->name('sale.clients.view');
        Route::delete('/clients/delete/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'destroy'])->name('sale.clients.delete');
        Route::get('/clients/edit/{id}/{page}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'edit'])->name('sale.clients.edit');
        Route::delete('/deleteCustomer/{id}', [\Modules\Sales\Http\Controllers\ClientsController::class, 'deleteContact'])->name('sale.deleteCustomer');
        Route::get('/editClient/{id}', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'editClient'])->name('sale.editClient');

        Route::get('/cotracts', [\Modules\Sales\Http\Controllers\ContractsController::class, 'index'])->name('saleContracts');
        Route::post('/storeContract', [\Modules\Sales\Http\Controllers\ContractsController::class, 'store'])->name('storeContract');
        Route::get('/getContractValues', [\Modules\Sales\Http\Controllers\ContractsController::class, 'getContractValues'])->name('sale.getContractValues');
        Route::get('/get_projects', [\Modules\Sales\Http\Controllers\ContractsController::class, 'get_projects'])->name('sale.get_projects');
        Route::get('/fetch-contract-duration/{offerPrice}',  [\Modules\Sales\Http\Controllers\ContractsController::class, 'fetchContractDuration'])->name('fetch-contract-duration');

        Route::delete('/cotracts/{id}', [\Modules\Sales\Http\Controllers\ContractsController::class, 'destroy'])->name('contract.destroy');
        Route::get('/offer_view/{id}', [\Modules\Sales\Http\Controllers\ContractsController::class, 'show'])->name('offer.view');
        Route::post('/specializations', [\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'fetchSpecializations'])->name('specializations');



        Route::get('/orderOperations', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'index'])->name('sale.orderOperations');
        Route::get('/orderOperationForUnsupportedWorkers', [\Modules\Sales\Http\Controllers\SaleWorkerController::class, 'orderOperationForUnsupportedWorkers'])->name('sale.orderOperationForUnsupportedWorkers');
        Route::post('/store/storeUnsupportedOrderOperation', [\Modules\Sales\Http\Controllers\SaleWorkerController::class, 'storeOrderOperation'])->name('sale.storeUnsupportedOrderOperation');
        Route::post('/get-order-details', [\Modules\Sales\Http\Controllers\SaleWorkerController::class, 'getOrderDetails'])->name('get-order-details');

        Route::get('create/orderOperations', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'create'])->name('create.sale.orderOperations');
        Route::post('/get-contracts', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'getContracts'])->name('get-contracts');
        Route::post('/store/orderOperations', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'store'])->name('sale.store.orderOperations');
        Route::get('sale/operation/edit/{id}', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'edit'])->name('sale.operation.edit');
        Route::get('show_operation/{id}', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'show'])->name('sale.show_operation');
        Route::get('showForDelegation/{id}', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'showForDelegation'])->name('sale.showForDelegation');
        Route::delete('destroy/show_operation/{id}', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'destroy'])->name('sale.delete.order_operation');
        Route::post('/get-contract-details', [\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'getContractDetails'])->name('get-contract-details');

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

        Route::resource('sales_follow-ups', 'Modules\Sales\Http\Controllers\SalesScheduleController')->except(['show']);



        Route::prefix('saleProjects')->group(function () {
            Route::get('/', [\Modules\Sales\Http\Controllers\SalesProjectController::class, 'index'])->name('sale.saleProjects');
            Route::post('/addSaleProject', [\Modules\Sales\Http\Controllers\SalesProjectController::class, 'store'])->name('sale.storeSaleProject');
            Route::delete('/destroySaleProject/{id}', [\Modules\Sales\Http\Controllers\SalesProjectController::class, 'destroy'])->name('sale.destroySaleProject');
            Route::get('/{id}/edit',  [\Modules\Sales\Http\Controllers\SalesProjectController::class,  'edit'])->name('sale.editSaleProject');
            Route::put('/updateSaleProject/{id}',  [\Modules\Sales\Http\Controllers\SalesProjectController::class, 'update'])->name('sale.updateSaleProject');
        });


        Route::get('sales_costs', [\Modules\Sales\Http\Controllers\SalesCostController::class, 'index'])->name('sales_costs');
        Route::post('/store_cost', [\Modules\Sales\Http\Controllers\SalesCostController::class, 'store'])->name('store_cost');
        Route::DELETE('/sales_costs/delete/{id}', [\Modules\Sales\Http\Controllers\SalesCostController::class, 'destroy'])->name('sales_costs_destroy');
        Route::post('/sales_costs/update', [\Modules\Sales\Http\Controllers\SalesCostController::class, 'update'])->name('cost.update');


        // Route::get('sales_templates', [\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'index'])->name('sales_templates');
        // Route::get('create_sales_templates', [\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'create'])->name('create_sales_templates');
        Route::get('first_choice_offer_price_template', [\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'first_choice_offer_price_template'])->name('first_choice_offer_price_template');
        Route::get('second_choice_offer_price_template', [\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'second_choice_offer_price_template'])->name('second_choice_offer_price_template');
        Route::get('first_choice_sales_contract_template', [\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'first_choice_sales_contract_template'])->name('first_choice_sales_contract_template');
        Route::get('second_choice_sales_contract_template', [\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'second_choice_sales_contract_template'])->name('second_choice_sales_contract_template');

        Route::get('edit-proposal-template/{id}', [Modules\Sales\Http\Controllers\SalesTemplateController::class, 'getEdit']);
        Route::post('update-proposal-template', [Modules\Sales\Http\Controllers\SalesTemplateController::class, 'postEdit']);
        Route::get('view-proposal-template', [Modules\Sales\Http\Controllers\SalesTemplateController::class, 'getView']);
        Route::get('send-proposal/{id}', [Modules\Sales\Http\Controllers\SalesTemplateController::class, 'send']);
        Route::delete('delete-proposal-media/{id}', [Modules\Sales\Http\Controllers\SalesTemplateController::class, 'deleteProposalMedia']);
        Route::resource('proposal-template', 'Modules\Sales\Http\Controllers\SalesTemplateController')->except(['show', 'edit', 'update', 'destroy']);


        //salary_requests
        Route::get('salary-requests-index', [Modules\Sales\Http\Controllers\SalesSalaryRequestsController::class, 'index'])->name('salary-requests-index');
        Route::get('/fetch-worker-details/{workerId}', [Modules\Sales\Http\Controllers\SalesSalaryRequestsController::class, 'fetchWorkerDetails'])->name('fetch-worker-details');

        Route::get('/edit-salary-request/{salaryId}', [Modules\Sales\Http\Controllers\SalesSalaryRequestsController::class, 'edit'])->name('edit_salay_request');

        Route::post('/store_salay_request', [Modules\Sales\Http\Controllers\SalesSalaryRequestsController::class, 'store'])->name('store_salay_request');
        Route::post('salary_request/update/{salaryId}', [Modules\Sales\Http\Controllers\SalesSalaryRequestsController::class, 'update'])->name('salay_request.update');
        Route::delete('delete-salay-request/{id}', [Modules\Sales\Http\Controllers\SalesSalaryRequestsController::class, 'destroy'])->name('salay_request.destroy');



        // Route::get('/preview-file/{id}', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'preview'])->name('preview.file');
        Route::get('/getwithinTwoMonthExpiryContracts', [\Modules\Sales\Http\Controllers\SalesController::class, 'withinTwoMonthExpiryContracts'])->name('sales.withinTwoMonthExpiryContracts');
        Route::get('/all-workers', [\Modules\Sales\Http\Controllers\SalesController::class, 'get_all_workers'])->name('sales.get_all_workers');
        Route::get('/all-active-workers', [\Modules\Sales\Http\Controllers\SalesController::class, 'get_active_workers'])->name('sales.get_active_workers');
        Route::get('/all-inactive-workers', [\Modules\Sales\Http\Controllers\SalesController::class, 'get_inactive_workers'])->name('sales.get_inactive_workers');


        Route::get('/download-file/{id}', [\Modules\Sales\Http\Controllers\OfferPriceController::class, 'print'])->name('download.file');
        Route::get('/download-contract/{id}', [\Modules\Sales\Http\Controllers\ContractsController::class, 'print'])->name('download.contract');
    });
});
