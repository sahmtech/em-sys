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

    Route::prefix('international-Relations')->group(function () {

        Route::get('/', [Modules\InternationalRelations\Http\Controllers\InternationalRelationsController::class, 'index'])->name('international_relations_landing');
    });
    Route::prefix('ir')->group(function () {
        Route::get('/dashboard', [Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index'])->name('international-Relations.dashboard');
        Route::get('/Airlines', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'index'])->name('international-Relations.Airlines');
        Route::post('store/Airlines', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'store'])->name('store.Airlines');
        Route::get('/getCitiesByCountry/{country_id}', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'getCitiesByCountry']);
        Route::post('store/EmploymentCompanies', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'store'])->name('store.EmploymentCompanies');
        Route::get('companyRequests/{id}', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'companyRequests'])->name('companyRequests');
        Route::get('delegations', [Modules\InternationalRelations\Http\Controllers\DelegationController::class, 'index'])->name('delegations');

        Route::get('viewDelegation/{id}', [\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'viewDelegation'])->name('viewDelegation');

        Route::get('proposed_laborIndex', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'proposed_laborIndex'])->name('proposed_laborIndex');
        Route::get('/createProposed_labor/{delegation_id}/{agency_id}/{transaction_sell_line_id}', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'createProposed_labor'])->name('createProposed_labor');
        Route::post('/storeProposed_labor', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'storeProposed_labor'])->name('storeProposed_labor');

        Route::get('/importWorkers/{delegation_id}/{agency_id}/{transaction_sell_line_id}', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'importWorkers'])->name('importWorkers');
        Route::post('/postImportWorkers', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'postImportWorkers'])->name('postImportWorkers');

        Route::get('accepted_workers', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'accepted_workers'])->name('accepted_workers');
        Route::get('unaccepted_workers', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'unaccepted_workers'])->name('unaccepted_workers');
        Route::get('workers_under_trialPeriod', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'workers_under_trialPeriod'])->name('workers_under_trialPeriod');
        Route::post('/add_worker_visa', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'storeVisaForWorkers'])->name('add_worker_visa');


        Route::post('/changeStatus', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'changeStatus'])->name('changeStatus');
        Route::post('/send_offer_price', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'send_offer_price'])->name('send_offer_price');
        Route::post('/accepted_by_worker', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'accepted_by_worker'])->name('accepted_by_worker');

        Route::get('/EmploymentCompanies', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'index'])->name('international-Relations.EmploymentCompanies');
        Route::post('store/EmploymentCompanies', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'store'])->name('store.EmploymentCompanies');

        Route::get('/order_request', [Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'index'])->name('order_request');
        Route::get('/order_request/Delegation/{id}', [Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'Delegation'])->name('order_request.Delegation');
        Route::post('/save-data', [Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'saveRequest'])->name('save-data');

        Route::get('visa_cards', [\Modules\InternationalRelations\Http\Controllers\VisaCardController::class, 'index'])->name('visa_cards');
        Route::post('/storeVisa', [Modules\InternationalRelations\Http\Controllers\VisaCardController::class, 'store'])->name('storeVisa');
        Route::get('/ir/viewVisaWorkers/{id}', [\Modules\InternationalRelations\Http\Controllers\VisaCardController::class, 'viewVisaWorkers'])->name('viewVisaWorkers');


        Route::post('/medical_examination', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'medical_examination'])->name('medical_examination');
        Route::post('/fingerprinting', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'fingerprinting'])->name('fingerprinting');
        Route::post('/passport_stamped', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'passport_stamped'])->name('passport_stamped');
        Route::post('/storeVisaWorker', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'storeVisaWorker'])->name('storeVisaWorker');
        Route::get('/ir_showWorker/{id}', [\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'showWorker'])->name('ir_showWorker');

        Route::get('/allIrRequests', [\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'index'])->name('allIrRequests');
        Route::post('/ir_change-status', [\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'changeStatus'])->name('ir_changeStatus');
        Route::get('/escalate_requests', [\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'escalateRequests'])->name('ir.escalate_requests');
        Route::post('/changeEscalateRequestsStatus', [\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'changeEscalateRequestsStatus'])->name('ir.changeEscalateRequestsStatus');
        Route::get('/viewIrRequest/{requestId}', [\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'viewRequest'])->name('viewIrRequest');


        Route::post('/storeIrRequest', [\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'store'])->name('storeIrRequest');
        Route::get('/search/byproof_number', [\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'search'])->name('ir.search_byproof_number');

        Route::get('/get_order_nationlities', [\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'getNationalities'])->name('get_order_nationlities');

        Route::get('/get-Irsalary-requests', [\Modules\InternationalRelations\Http\Controllers\IRsalaryRequestController::class, 'index'])->name('get_Irsalary_requests');



        // Route::get('/getBusiness', [\App\Http\Controllers\BusinessController::class, 'getBusiness'])->name('getBusiness');
        // Route::get('/business.view/{id}', [\App\Http\Controllers\BusinessController::class, 'show'])->name('business.view');
        // Route::get('/business_documents.view/{id}', [\App\Http\Controllers\BusinessDocumentController::class, 'show'])->name('business_documents.view');
        // Route::post('/storeBusiness', [\App\Http\Controllers\BusinessController::class, 'store'])->name('storeBusiness');
        // Route::post('/storeBusinessDoc', [\App\Http\Controllers\BusinessDocumentController::class, 'store'])->name('storeBusinessDoc');
        // Route::delete('/doc/{id}', [\App\Http\Controllers\BusinessDocumentController::class, 'destroy'])->name('doc.destroy');
        // Route::get('/business_subscriptions.view/{id}', [\App\Http\Controllers\BusinessSubscriptionController::class, 'show'])->name('business_subscriptions.view');
        // Route::post('/storeBusinessSubscription', [\App\Http\Controllers\BusinessSubscriptionController::class, 'store'])->name('storeBusinessSubscription');
        // Route::delete('/business_subscriptions/{id}', [\App\Http\Controllers\BusinessSubscriptionController::class, 'destroy'])->name('busSubscription.destroy');
        // Route::post('/check-username', [\App\Http\Controllers\BusinessController::class, 'checkUsername'])->name('check-username');
    });
});