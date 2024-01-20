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
//Route::get('/testtt', [\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'withinTwoMonthExpiryContracts'])->name('withinTwoMonthExpiryContracts');

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {

    Route::prefix('followup')->group(function () {
        Route::get('/dashboard', [Modules\FollowUp\Http\Controllers\DashboardController::class, 'index'])->name('followup.dashboard');
        Route::get('/', [Modules\FollowUp\Http\Controllers\FollowUpController::class, 'index'])->name('followup_landing');

        Route::get('/projects2', [\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'index'])->name('projects2');
        Route::get('/projectShow/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'show'])->name('projectView');

        Route::get('/projects_access_permissions', [\Modules\FollowUp\Http\Controllers\FollowUpProjectsAccessPermissionsController::class, 'index'])->name('projects_access_permissions');

        Route::get('/workers', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index'])->name('workers');
        Route::get('/workers/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'show'])->name('showWorker');
        Route::get('/createWorker/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'createWorker'])->name('createWorker');
        Route::post('/storeWorker', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'storeWorker'])->name('storeWorker');

        Route::get('/operation_orders', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderController::class, 'index'])->name('operation_orders');
        // Route::post('/storeOperation', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderController::class, 'store'])->name('storeOperation');
        // Route::PUT('/updateOrder/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderControlle::class, 'update'])->name('updateOrder');
        // Route::get('/getUpdatedData/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderControlle::class, 'getUpdatedData'])->name('getUpdatedData');


        Route::prefix('contactLocations')->group(function () {
            Route::get('/', [\App\Http\Controllers\ContactLocationController::class, 'index'])->name('sale.contactLocations');
            Route::post('/addContactLocations', [\App\Http\Controllers\ContactLocationController::class, 'store'])->name('sale.storeContactLocations');
            Route::delete('/destroyContactLocations/{id}', [\App\Http\Controllers\ContactLocationController::class, 'destroy'])->name('sale.destroyContactLocations');
            Route::get('/{id}/edit',  [\App\Http\Controllers\ContactLocationController::class,  'edit'])->name('sale.editContactLocations');
            Route::put('/updateContactLocations/{id}',  [\App\Http\Controllers\ContactLocationController::class, 'update'])->name('sale.updateContactLocations');
        });


        Route::post('/storeRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'store'])->name('storeRequest');
        Route::get('/createRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'create'])->name('createRequest');
        Route::get('/allRequests', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'requests'])->name('allRequests');
        Route::get('/escalate_requests', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'escalateRequests'])->name('followup.escalate_requests');
        Route::get('/viewRequest/{requestId}', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'viewRequest'])->name('viewRequest');
        Route::get('/filteredRequests', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'filteredRequests'])->name('filteredRequests');
        Route::get('/search/byproof', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'search'])->name('search_proofname');



        Route::get('/exitRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'exitRequestIndex'])->name('exitRequest');
        Route::get('/returnRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'returnRequestIndex'])->name('returnRequest');
        Route::get('/escapeRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'escapeRequestIndex'])->name('escapeRequest');
        Route::get('/advanceSalary', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'advanceSalaryIndex'])->name('advanceSalary');
        Route::get('/leavesAndDepartures', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'leavesAndDeparturesIndex'])->name('leavesAndDepartures');
        Route::get('/atmCard', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'atmCardIndex'])->name('atmCard');
        Route::get('/residenceRenewal', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceRenewalIndex'])->name('residenceRenewal');
        Route::get('/residenceCard', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceCardIndex'])->name('residenceCard');
        Route::get('/workerTransfer', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workerTransferIndex'])->name('workerTransfer');

        Route::get('/chamberRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'chamberRequestIndex'])->name('chamberRequest');
        Route::get('/mofaRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'mofaRequestIndex'])->name('mofaRequest');
        Route::get('/insuranceUpgradeRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'insuranceUpgradeRequestIndex'])->name('insuranceUpgradeRequest');
        Route::get('/baladyCardRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'baladyCardRequestIndex'])->name('baladyCardRequest');
        Route::get('/residenceEditRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceEditRequestIndex'])->name('residenceEditRequest');
        Route::get('/workInjuriesRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workInjuriesRequestIndex'])->name('workInjuriesRequest');
        Route::get('/cancleContractRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'cancleContractRequestIndex'])->name('cancleContractRequest');
        Route::get('/recruitmentRequests', [\Modules\FollowUp\Http\Controllers\FollowUpRecruitmentRequestController::class, 'index'])->name('recruitmentRequests');
        Route::post('/storeRecruitmentRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRecruitmentRequestController::class, 'store'])->name('storeRecruitmentRequest');


        Route::post('/get-sub-reasons', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'getSubReasons'])->name('getSubReasons');
        Route::post('/change-status', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'changeStatus'])->name('changeStatus');
        Route::post('/returnReq', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'returnReq'])->name('returnReq');



        Route::get('/contracts_wishes', [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'index'])->name('contracts_wishes');
        Route::post('/change_wish', [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'changeWish'])->name('change_wish');
        Route::post('/add_wish', [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'changeWish'])->name('change_wish');


        Route::post('/addWishcontact', [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'add_wish'])->name('addWishcontact');
        Route::get('/get-wish-file/{employeeId}',  [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'getWishFile'])->name('getWishFile');


        Route::get('/reports/project-workers/choose-fields', [\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'chooseFields_projectsworker'])->name('projectWorkers_chooseFields');
        Route::get('/reports/project-workers', [\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projectWorkers'])->name('projectWorkers');
        Route::get('/reports/projects', [\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projects'])->name('projects');


        Route::get('/withinTwoMonthExpiryContracts', [\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'withinTwoMonthExpiryContracts'])->name('withinTwoMonthExpiryContracts');
        Route::get('/withinTwoMonthExpiryResidency', [\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'withinTwoMonthExpiryResidency'])->name('withinTwoMonthExpiryResidency');
        Route::get('/withinTwoMonthExpiryWorkCard', [\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'withinTwoMonthExpiryWorkCard'])->name('withinTwoMonthExpiryWorkCard');

        // Routes Shifts
        Route::get('/shifts', [\Modules\FollowUp\Http\Controllers\ShiftController::class, 'index'])->name('shifts');
        Route::get('/shifts-create', [\Modules\FollowUp\Http\Controllers\ShiftController::class, 'create'])->name('shifts-create');
        Route::get('/shifts-edit/{id}', [\Modules\FollowUp\Http\Controllers\ShiftController::class, 'edit'])->name('shifts-edit');
        Route::post('/shifts-store', [\Modules\FollowUp\Http\Controllers\ShiftController::class, 'store'])->name('shifts-store');
        Route::post('/shifts-search', [\Modules\FollowUp\Http\Controllers\ShiftController::class, 'search'])->name('shifts-search');
        Route::put('/shifts-update/{id}', [\Modules\FollowUp\Http\Controllers\ShiftController::class, 'update'])->name('shifts-update');
        Route::delete('/shifts-delete/{id}', [\Modules\FollowUp\Http\Controllers\ShiftController::class, 'destroy'])->name('shifts-delete');
        Route::get('/projects-by-contacts/{id}', [\Modules\FollowUp\Http\Controllers\ShiftController::class, 'ProjectsByContacts'])->name('ProjectsByContacts');


        // Documents
        Route::get('/documents', [\Modules\FollowUp\Http\Controllers\FollowupDocumentController::class, 'index'])->name('documents');
        Route::get('/documents-create', [\Modules\FollowUp\Http\Controllers\FollowupDocumentController::class, 'create'])->name('documents-create');
        Route::get('/documents-edit/{id}', [\Modules\FollowUp\Http\Controllers\FollowupDocumentController::class, 'edit'])->name('documents-edit');
        Route::post('/documents-store', [\Modules\FollowUp\Http\Controllers\FollowupDocumentController::class, 'store'])->name('documents-store');
        Route::put('/documents-update/{id}', [\Modules\FollowUp\Http\Controllers\FollowupDocumentController::class, 'update'])->name('documents-update');
        Route::delete('/documents-delete/{id}', [\Modules\FollowUp\Http\Controllers\FollowupDocumentController::class, 'destroy'])->name('documents-delete');


        Route::get('/documents-delivery', [\Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController::class, 'index'])->name('documents-delivery');
        Route::get('/documents-delivery-create', [\Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController::class, 'create'])->name('documents-delivery-create');
        Route::get('/documents-delivery-edit/{id}', [\Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController::class, 'edit'])->name('documents-delivery-edit');
        Route::post('/documents-delivery-store', [\Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController::class, 'store'])->name('documents-delivery-store');
        Route::put('/documents-delivery-update/{id}', [\Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController::class, 'update'])->name('documents-delivery-update');
        Route::delete('/documents-delivery-delete/{id}', [\Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController::class, 'destroy'])->name('documents-delivery-delete');
    });
});
