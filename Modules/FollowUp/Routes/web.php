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

        Route::get('/workers', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index'])->name('workers');
        Route::get('/workers/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'show'])->name('showWorker');
        Route::get('/createWorker/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'createWorker'])->name('createWorker');
        Route::post('/storeWorker', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'storeWorker'])->name('storeWorker');

        Route::get('/operation_orders', [\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderController::class, 'index'])->name('operation_orders');
        Route::post('/storeOperation', [\Modules\Followup\Http\Controllers\FollowUpOperationOrderController::class, 'store'])->name('storeOperation');
        Route::PUT('/updateOrder/{id}', [\Modules\Followup\Http\Controllers\FollowUpOperationOrderControlle::class, 'update'])->name('updateOrder');
        Route::get('/getUpdatedData/{id}', [\Modules\Followup\Http\Controllers\FollowUpOperationOrderControlle::class, 'getUpdatedData'])->name('getUpdatedData');




        Route::post('/storeRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'store'])->name('storeRequest');
        Route::get('/createRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'create'])->name('createRequest');
        Route::get('/allRequests', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'requests'])->name('allRequests');
        Route::get('/filteredRequests', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'filteredRequests'])->name('filteredRequests');


        Route::get('/exitRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'exitRequestIndex'])->name('exitRequest');
        Route::get('/returnRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'returnRequestIndex'])->name('returnRequest');
        Route::get('/escapeRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'escapeRequestIndex'])->name('escapeRequest');
        Route::get('/advanceSalary',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'advanceSalaryIndex'])->name('advanceSalary');
        Route::get('/leavesAndDepartures',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'leavesAndDeparturesIndex'])->name('leavesAndDepartures');
        Route::get('/atmCard',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'atmCardIndex'])->name('atmCard');
        Route::get('/residenceRenewal',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceRenewalIndex'])->name('residenceRenewal');
        Route::get('/residenceCard',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceCardIndex'])->name('residenceCard');
        Route::get('/workerTransfer',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workerTransferIndex'])->name('workerTransfer');
        
        Route::get('/chamberRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'chamberRequestIndex'])->name('chamberRequest');
        Route::get('/mofaRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'mofaRequestIndex'])->name('mofaRequest');
        Route::get('/insuranceUpgradeRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'insuranceUpgradeRequestIndex'])->name('insuranceUpgradeRequest');
        Route::get('/baladyCardRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'baladyCardRequestIndex'])->name('baladyCardRequest');
        Route::get('/residenceEditRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceEditRequestIndex'])->name('residenceEditRequest');
        Route::get('/workInjuriesRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workInjuriesRequestIndex'])->name('workInjuriesRequest');
        Route::get('/cancleContractRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'cancleContractRequestIndex'])->name('cancleContractRequest');
        Route::get('/recruitmentRequests',[\Modules\FollowUp\Http\Controllers\FollowUpRecruitmentRequestController::class, 'index'])->name('recruitmentRequests');
        Route::post('/storeRecruitmentRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRecruitmentRequestController::class, 'store'])->name('storeRecruitmentRequest');

        
        Route::post('/get-sub-reasons', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'getSubReasons'])->name('getSubReasons');
        Route::post('/change-status', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class,'changeStatus'])->name('changeStatus');
        Route::post('/returnReq', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class,'returnReq'])->name('returnReq');



        Route::get('/contracts_wishes', [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'index'])->name('contracts_wishes');
        Route::post('/change_wish', [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'changeWish'])->name('change_wish');
        Route::post('/add_wish', [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'changeWish'])->name('change_wish');


        Route::post('/addWishcontact', [\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'add_wish'])->name('addWishcontact');


        Route::get('/reports/project-workers/choose-fields', [\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'chooseFields_projectsworker'])->name('projectWorkers_chooseFields');
        Route::get('/reports/project-workers', [\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projectWorkers'])->name('projectWorkers');
        Route::get('/reports/projects', [\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projects'])->name('projects');
    
        
        Route::get('/withinTwoMonthExpiryContracts', [\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'withinTwoMonthExpiryContracts'])->name('withinTwoMonthExpiryContracts');
        Route::get('/withinTwoMonthExpiryResidency', [\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'withinTwoMonthExpiryResidency'])->name('withinTwoMonthExpiryResidency');
        Route::get('/withinTwoMonthExpiryWorkCard', [\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'withinTwoMonthExpiryWorkCard'])->name('withinTwoMonthExpiryWorkCard');
    });
});