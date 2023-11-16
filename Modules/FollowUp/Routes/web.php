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

    Route::prefix('followup')->group(function() {
        Route::get('/dashboard', [Modules\FollowUp\Http\Controllers\DashboardController::class, 'index'])->name('followup.dashboard');
        Route::get('/', [Modules\FollowUp\Http\Controllers\FollowUpController::class, 'index'])->name('followup_landing');
      
        Route::get('/projects',[\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'index'])->name('projects');
        Route::get('/projectShow/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'show'])->name('projectView');
    
        Route::get('/workers',[\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index'])->name('workers');
        Route::get('/workers/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'show'])->name('showWorker');
     
   //     Route::get('/followUpRequests',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'index'])->name('followUpRequests');
        Route::post('/storeRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'store'])->name('storeRequest');
        Route::get('/createRequest', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'create'])->name('createRequest');
       
        Route::get('/exitRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'exitRequestIndex'])->name('exitRequest');
        Route::get('/returnRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'returnRequestIndex'])->name('returnRequest');
        Route::get('/escapeRequest',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'escapeRequestIndex'])->name('escapeRequest');
        Route::get('/advanceSalary',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'advanceSalaryIndex'])->name('advanceSalary');
        Route::get('/leavesAndDepartures',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'leavesAndDeparturesIndex'])->name('leavesAndDepartures');
        Route::get('/atmCard',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'atmCardIndex'])->name('atmCard');
        Route::get('/residenceRenewal',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceRenewalIndex'])->name('residenceRenewal');
        Route::get('/residenceCard',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceCardIndex'])->name('residenceCard');
        Route::get('/workerTransfer',[\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workerTransferIndex'])->name('workerTransfer');
  
        Route::post('/change-status', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class,'changeStatus'])->name('changeStatus');
        Route::post('/returnReq', [\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class,'returnReq'])->name('returnReq');

        
    });
    
    
    });
