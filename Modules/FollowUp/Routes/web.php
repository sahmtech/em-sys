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
        Route::get('/followup_department_employees', [Modules\FollowUp\Http\Controllers\FollowUpController::class, 'followup_department_employees'])->name('followup_department_employees');
        Route::get('/projects2', [\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'index'])->name('projects2');
        Route::get('/projectShow/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'show'])->name('projectView');

        Route::get('/projects_access_permissions', [\Modules\FollowUp\Http\Controllers\FollowupUserAccessProjectController::class, 'index'])->name('projects_access_permissions');
        Route::get('/getUserProjectsPermissions/{userId}', [\Modules\FollowUp\Http\Controllers\FollowupUserAccessProjectController::class, 'getUserProjectsPermissions'])->name('getUserProjectsPermissions');
        Route::post('/projects_access_permissions/store', [\Modules\FollowUp\Http\Controllers\FollowupUserAccessProjectController::class, 'store'])->name('projects_access_permissions.store');
        Route::get('/workers', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index'])->name('workers');
        Route::get('/workers/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'show'])->name('showWorker');

        //Route::get('/createWorker/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'createWorker'])->name('createWorker');
        // Route::post('/storeWorker', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'storeWorker'])->name('storeWorker');

        Route::get('/createWorker', [\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'createWorker'])->name('createWorker');

        Route::get('/fetch_contract_details', [\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'fetch_contract_details'])->name('fetch_contract_details');
        Route::get('/operation_orders', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderController::class, 'index'])->name('operation_orders');

        Route::post('/upload_attachments', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'upload_attachments'])->name('upload_attachments');

        // Route::post('/storeOperation', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderController::class, 'store'])->name('storeOperation');
        // Route::PUT('/updateOrder/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderControlle::class, 'update'])->name('updateOrder');
        // Route::get('/getUpdatedData/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderControlle::class, 'getUpdatedData'])->name('getUpdatedData');

        Route::get('/agent_time_sheet', [\Modules\FollowUp\Http\Controllers\TimeSheetController::class, 'index'])
            ->name('followup.agentTimeSheetIndex');
        Route::get('/create', [\Modules\FollowUp\Http\Controllers\TimeSheetController::class, 'create'])->name('followup.agentTimeSheet.create');
        Route::post('/submitTmeSheet', [\Modules\FollowUp\Http\Controllers\TimeSheetController::class, 'submitTmeSheet'])->name('followup.agentTimeSheet.submitTmeSheet');
        Route::get('/agentTimeSheetUsers', [\Modules\FollowUp\Http\Controllers\TimeSheetController::class, 'agentTimeSheetUsers'])->name('followup.agentTimeSheetUsers');
        Route::get('/agentTimeSheetGroups', [\Modules\FollowUp\Http\Controllers\TimeSheetController::class, 'agentTimeSheetGroups'])->name('followup.agentTimeSheetGroups');
        Route::get('/timesheet-group/{id}/show', [\Modules\FollowUp\Http\Controllers\TimeSheetController::class, 'showTimeSheet'])->name('followup.agentTimeSheet.showTimeSheet');
        Route::get('time_sheet/edit/{id}', [\Modules\FollowUp\Http\Controllers\TimeSheetController::class, 'editTimeSheet'])->name('followup.agentTimeSheet.editTimeSheet');
        Route::get('agent/time_sheet/deal/{id}', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'dealTimeSheet'])->name('followup.agentTimeSheet.dealTimeSheet');


        Route::prefix('contactLocations')->group(function () {
            Route::get('/', [\App\Http\Controllers\ContactLocationController::class, 'index'])->name('sale.contactLocations');
            Route::post('/addContactLocations', [\App\Http\Controllers\ContactLocationController::class, 'store'])->name('sale.storeContactLocations');
            Route::delete('/destroyContactLocations/{id}', [\App\Http\Controllers\ContactLocationController::class, 'destroy'])->name('sale.destroyContactLocations');
            Route::get('/{id}/edit',  [\App\Http\Controllers\ContactLocationController::class,  'edit'])->name('sale.editContactLocations');
            Route::put('/updateContactLocations/{id}',  [\App\Http\Controllers\ContactLocationController::class, 'update'])->name('sale.updateContactLocations');
        });


        Route::post('/storeRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'store'])->name('storeRequest');
        Route::post('/storeSelectedRowsRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'storeSelectedRowsRequest'])->name('storeSelectedRowsRequest');

        Route::get('/allRequests', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'requests'])->name('allRequests');

        Route::get('/viewRequest/{requestId}', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'viewRequest'])->name('viewRequest');
        Route::get('/filteredRequests', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'filteredRequests'])->name('filteredRequests');
        Route::get('/search/byproof', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'search'])->name('search_proofname');



        Route::get('/cancleContractRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'cancleContractRequestIndex'])->name('cancleContractRequest');
        Route::get('/recruitmentRequests', [\Modules\FollowUp\Http\Controllers\FollowUpRecruitmentRequestController::class, 'index'])->name('recruitmentRequests');
        Route::post('/storeRecruitmentRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRecruitmentRequestController::class, 'store'])->name('storeRecruitmentRequest');


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

        Route::post('/cancleProject', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'cancleProject'])->name('cancleProject');

        Route::get('/followup_travelers', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'new_arrival_for_workers'])->name('followup_travelers');
        Route::get('/followup_housed_workers', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'housed_workers_index'])->name('followup_housed_workers');
        Route::get('/followup_medicalExamination', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'medicalExamination'])->name('followup_medicalExamination');
        Route::get('/followup_medicalInsurance', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'medicalInsurance'])->name('followup_medicalInsurance');
        Route::get('/followup_workCardIssuing', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'workCardIssuing'])->name('followup_workCardIssuing');
        Route::get('/followup_SIMCard', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'SIMCard'])->name('followup_SIMCard');
        Route::get('/followup_bankAccountsForLabors', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'bankAccounts'])->name('followup_bankAccountsForLabors');
        Route::get('/followup_QiwaContract', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'QiwaContracts'])->name('followup_QiwaContract');
        Route::get('/followup_residencyPrint', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'residencyPrint'])->name('followup_residencyPrint');
        Route::get('/followup_residencyDelivery', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'residencyDelivery'])->name('followup_residencyDelivery');
        Route::get('/followup_advanceSalaryRequest', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'advanceSalaryRequest'])->name('followup_advanceSalaryRequest');
    });
});
