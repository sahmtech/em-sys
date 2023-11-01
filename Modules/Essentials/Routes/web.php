<?php

// use App\Http\Controllers\Modules;
// use Illuminate\Support\Facades\Route;


Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {
   
   
    Route::prefix('essentials')->group(function () {
        Route::get('/dashboard', [Modules\Essentials\Http\Controllers\DashboardController::class, 'essentialsDashboard']);
        Route::get('/install', [Modules\Essentials\Http\Controllers\InstallController::class, 'index']);
        Route::get('/install/update', [Modules\Essentials\Http\Controllers\InstallController::class, 'update']);
        Route::get('/install/uninstall', [Modules\Essentials\Http\Controllers\InstallController::class, 'uninstall']);

        Route::get('/', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'index'])->name('essentials_landing');

        //document controller
        Route::resource('document', 'Modules\Essentials\Http\Controllers\DocumentController')->only(['index', 'store', 'destroy', 'show']);
        Route::get('document/download/{id}', [Modules\Essentials\Http\Controllers\DocumentController::class, 'download']);

        //document share controller
        Route::resource('document-share', 'Modules\Essentials\Http\Controllers\DocumentShareController')->only(['edit', 'update']);

        //todo controller
        Route::resource('todo', 'ToDoController');

        Route::post('todo/add-comment', [Modules\Essentials\Http\Controllers\ToDoController::class, 'addComment']);
        Route::get('todo/delete-comment/{id}', [Modules\Essentials\Http\Controllers\ToDoController::class, 'deleteComment']);
        Route::get('todo/delete-document/{id}', [Modules\Essentials\Http\Controllers\ToDoController::class, 'deleteDocument']);
        Route::post('todo/upload-document', [Modules\Essentials\Http\Controllers\ToDoController::class, 'uploadDocument']);
        Route::get('view-todo-{id}-share-docs', [Modules\Essentials\Http\Controllers\ToDoController::class, 'viewSharedDocs']);

        //reminder controller
        Route::resource('reminder', 'Modules\Essentials\Http\Controllers\ReminderController')->only(['index', 'store', 'edit', 'update', 'destroy', 'show']);

        //message controller
        Route::get('get-new-messages', [Modules\Essentials\Http\Controllers\EssentialsMessageController::class, 'getNewMessages']);
        Route::resource('messages', 'Modules\Essentials\Http\Controllers\EssentialsMessageController')->only(['index', 'store', 'destroy']);

        //Allowance and deduction controller
        Route::resource('allowance-deduction', 'Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController');
       
        Route::resource('knowledge-base', 'Modules\Essentials\Http\Controllers\KnowledgeBaseController');

        Route::get('user-sales-targets', [Modules\Essentials\Http\Controllers\DashboardController::class, 'getUserSalesTargets']);
    });

    Route::prefix('hrm')->group(function () {
        Route::get('/get-amount/{salaryType}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'getAmount'])->name('get-amount');
        Route::get('/dashboard', [Modules\Essentials\Http\Controllers\DashboardController::class, 'hrmDashboard'])->name('hrmDashboard');
        Route::resource('/leave-type', 'Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController');
        Route::resource('/leave', 'Modules\Essentials\Http\Controllers\EssentialsLeaveController');
        Route::post('/change-status', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'changeStatus'])->name('changeLeavStatus');
        Route::get('/leave/activity/{id}', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'activity']);
        Route::get('/user-leave-summary', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'getUserLeaveSummary']);

        Route::get('/settings', [Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'edit']);
        Route::post('/settings', [Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'update']);
     
        Route::get('/countries', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'index'])->name('countries');
        Route::get('/createCountry', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'create'])->name('createCountry');
        Route::post('/storeCountry', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'store'])->name('storeCountry');
        Route::get('/countries/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'edit'])->name('country.edit');
        Route::delete('/countries/{id}', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'destroy'])->name('country.destroy');
        Route::put('/updateCountry/{id}', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'update'])->name('updateCountry');
      
        Route::get('/contract_types', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'index'])->name('contract_types');
        Route::get('/createContractType', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'create'])->name('createContractType');
        Route::post('/storeContractType', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'store'])->name('storeContractType');
        Route::get('/contract_types/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'edit'])->name('contractType.edit');
        Route::delete('/contract_types/{id}', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'destroy'])->name('contractType.destroy');
        Route::put('/updateContractType/{id}', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'update'])->name('updateContractType');

        Route::get('featureIndex', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class,'featureIndex'])->name('featureIndex');
        Route::post('storeUserAllowance', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class,'storeUserAllowance'])->name('storeUserAllowance');
        Route::get('/featureIndex/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'editAllowance'])->name('employee_allowance.edit');
        Route::delete('/featureIndex/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'destroyAllowance'])->name('employee_allowance.destroy');
        Route::put('/updateUserAllowance/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'updateAllowance'])->name('updateUserAllowance');

        Route::get('userTravelCat', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class,'userTravelCat'])->name('userTravelCat');
        Route::post('storeUserTravelCat', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class,'storeUserTravelCat'])->name('storeUserTravelCat');
        Route::delete('/userTravelCat/{id}', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'destroyUserTravelCat'])->name('userTravelCat.destroy');
        
        Route::post('/storeProfession', [\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class,'store'])->name('storeProfession');
        Route::get('/professions', [\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class, 'index'])->name('professions');
        Route::delete('/professions/{id}',[\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class, 'destroy'])->name('profession.destroy');


        
        Route::get('/cities', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'index'])->name('cities');
        Route::get('/createCity', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'create'])->name('createCity');
        Route::post('/storeCity', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'store'])->name('storeCity');
        Route::get('/cities/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'edit'])->name('city.edit');
        Route::delete('/cities/{id}', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'destroy'])->name('city.destroy');
        Route::put('/updateCity/{id}', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'update'])->name('updateCity');
        
        Route::get('/organizations', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'index'])->name('organizations');
        Route::get('/createOrganization', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'create'])->name('createOrganization');
        Route::post('/storeOrganization', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'store'])->name('storeOrganization');
        Route::get('/organizations/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'edit'])->name('organization.edit');
        Route::delete('/organizations/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'destroy'])->name('organization.destroy');
        Route::put('/updateOrganization/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'update'])->name('updateOrganization');

        Route::get('/departments', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentController::class, 'index'])->name('departments');
        Route::get('/createDepartment', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentController::class, 'create'])->name('createDepartment');
        Route::post('/storeDepartment', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentController::class, 'store'])->name('storeDepartment');
        Route::get('/departments/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentController::class, 'edit'])->name('department.edit');
        Route::delete('/departments/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentController::class, 'destroy'])->name('department.destroy');
        Route::put('/updateDepartments/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentController::class, 'update'])->name('updateDepartment');
        
        
        Route::get('/job_titles', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'index'])->name('job_titles');
        Route::get('/createJob_title', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'create'])->name('createJob_title');
        Route::post('/storeJob_title', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'store'])->name('storeJob_title');
        Route::get('/job_titles/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'edit'])->name('job_title.edit');
        Route::delete('/job_titles/{id}', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'destroy'])->name('job_title.destroy');
        Route::put('/updateJob_title/{id}', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'update'])->name('updateJob_title');

        Route::get('/bank_accounts', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'index'])->name('bank_accounts');
        Route::get('/createBank_account', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'create'])->name('createBank_account');
        Route::post('/storeBank_account', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'store'])->name('storeBank_account');
        Route::get('/bank_accounts/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'edit'])->name('bank_account.edit');
        Route::delete('/bank_accounts/{id}', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'destroy'])->name('bank_account.destroy');
        Route::put('/updateBank_account/{id}', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'update'])->name('updateBank_account');

        Route::get('/travel_categories', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'index'])->name('travel_categories');
        Route::get('/createTravel_categorie', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'create'])->name('createTravel_categorie');
        Route::post('/storeTravel_categorie', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'store'])->name('storeTravel_categorie');
        Route::get('/travel_categories/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'edit'])->name('travel_categorie.edit');
        Route::delete('/travel_categories/{id}', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'destroy'])->name('travel_categorie.destroy');
        Route::put('/updateTravel_categorie/{id}', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'update'])->name('updateTravel_categorie');

        Route::get('/basic_salary_types', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'index'])->name('basic_salary_types');
        Route::get('/createBasicSalary', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'create'])->name('createBasicSalary');
        Route::post('/storeBasicSalary', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'store'])->name('storeBasicSalary');
        Route::get('/basic_salary_types/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'edit'])->name('BasicSalary.edit');
        Route::delete('/basic_salary_types/{id}', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'destroy'])->name('BasicSalary.destroy');
        Route::put('/updateBasicSalary/{id}', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'update'])->name('updateBasicSalary');

        Route::get('/entitlements', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'index'])->name('entitlements');
        Route::get('/createEntitlement', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'create'])->name('createEntitlement');
        Route::post('/storeEntitlement', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'store'])->name('storeEntitlement');
        Route::get('/entitlements/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'edit'])->name('Entitlement.edit');
        Route::delete('/entitlements/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'destroy'])->name('Entitlement.destroy');
        Route::put('/updateEntitlement/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'update'])->name('updateEntitlement');

        Route::get('/allowances', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'index'])->name('allowances');
        Route::get('/createAllowance', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'create'])->name('createAllowance');
        Route::post('/storeAllowance', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'store'])->name('storeAllowance');
        Route::get('/allowances/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'edit'])->name('Allowance.edit');
        Route::delete('/allowances/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'destroy'])->name('Allowance.destroy');
        Route::put('/updateAllowance/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'update'])->name('updateAllowance');
        
        Route::get('/insurance_companies', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'index'])->name('insurance_companies');
        Route::post('/insurance_companies.store', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'store'])->name('insurance_companies.store');
        Route::delete('/insurance_companies/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'destroy'])->name('insurance_companies.destroy');
        Route::get('/insurance_companies.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'show'])->name('insurance_companies.view');

        Route::get('/insurance_categories', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCategoryController::class, 'index'])->name('insurance_categories');
        Route::post('/insurance_categories.store', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCategoryController::class, 'store'])->name('insurance_categories.store');
        Route::delete('/insurance_categories/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCategoryController::class, 'destroy'])->name('insurance_categories.destroy');
        Route::get('/insurance_categories.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCategoryController::class, 'show'])->name('insurance_categories.view');
        
        Route::get('/insurance_contracts', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'index'])->name('insurance_contracts');
        Route::post('/insurance_contracts.store', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'store'])->name('insurance_contracts.store');
        Route::delete('/insurance_contracts/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'destroy'])->name('insurance_contracts.destroy');
        Route::get('/insurance_contracts.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'show'])->name('insurance_contracts.view');
        Route::put('/updateInsuranceContract/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'update'])->name('updateInsuranceContract');
        Route::get('/insurance_contracts/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'edit'])->name('insurance_contracts.edit');

        Route::get('/official_documents', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'index'])->name('official_documents');
        Route::post('/storeOfficialDoc', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'store'])->name('storeOfficialDoc');
        Route::delete('/official_documents/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'destroy'])->name('offDoc.destroy');
        Route::get('/official_documents.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'show'])->name('doc.view');
        Route::put('/updateDoc/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'update'])->name('updateDoc');
        Route::get('/official_documents/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'edit'])->name('doc.edit');
         
        Route::get('/qualifications', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'index'])->name('qualifications');
        Route::post('/storeQualification', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'store'])->name('storeQualification');
        Route::delete('/qualifications/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'destroy'])->name('qualification.destroy');
        Route::get('/qualifications.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'show'])->name('qualification.view');
        Route::put('/updateQualification/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'update'])->name('updateQualification');
        Route::get('/qualifications/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'edit'])->name('qualification.edit');
        
        Route::get('/employeeContracts', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'index'])->name('employeeContracts');
        Route::post('/storeEmployeeContract', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'store'])->name('storeEmployeeContract');
        Route::delete('/employeeContracts/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'destroy'])->name('employeeContract.destroy');
        Route::get('/employeeContracts.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'show'])->name('employeeContract.view');
        Route::put('/updateEmployeeContract/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'update'])->name('updateEmployeeContract');
        Route::get('/employeeContracts/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'edit'])->name('employeeContract.edit');
        
        Route::get('/admissionToWork', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'index'])->name('admissionToWork');
        Route::post('/storeAdmissionToWork', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'store'])->name('storeAdmissionToWork');
        Route::delete('/admissionToWork/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'destroy'])->name('admissionToWork.destroy');
        Route::get('/admissionToWork.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'show'])->name('admissionToWork.view');
        Route::put('/updateAdmissionToWork/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'update'])->name('updateAdmissionToWork');
        Route::get('/admissionToWork/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'edit'])->name('admissionToWork.edit');
        
        Route::get('/appointments', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'index'])->name('appointments');

        Route::post('/change-status2', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'changeStatus'])->name('change-status');
        Route::post('/storeAppointment', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'store'])->name('storeAppointment');
        Route::delete('/appointments/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'destroy'])->name('appointment.destroy');
        Route::get('/appointments.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'show'])->name('appointment.view');
        Route::put('/updateAppointment/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'update'])->name('updateAppointment');
        Route::get('/appointments/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'edit'])->name('appointment.edit');
        
        Route::get('/features', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'index'])->name('features');
        Route::post('/storeFeatures', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'store'])->name('storeFeatures');
        Route::delete('/features/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'destroy'])->name('feature.destroy');
        Route::get('/features.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'show'])->name('feature.view');
        Route::put('/updateFeatures/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'update'])->name('updateFeatures');
        Route::get('/features/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'edit'])->name('feature.edit');
        
        
        Route::get('/getBusiness', [\App\Http\Controllers\BusinessController::class, 'getBusiness'])->name('getBusiness');
        Route::get('/business.view/{id}', [\App\Http\Controllers\BusinessController::class, 'show'])->name('business.view');
        Route::get('/business_documents.view/{id}', [\App\Http\Controllers\BusinessDocumentController::class, 'show'])->name('business_documents.view');
        Route::post('/storeBusiness', [\App\Http\Controllers\BusinessController::class, 'store'])->name('storeBusiness');
        Route::post('/storeBusinessDoc', [\App\Http\Controllers\BusinessDocumentController::class, 'store'])->name('storeBusinessDoc');
        Route::delete('/doc/{id}', [\App\Http\Controllers\BusinessDocumentController::class, 'destroy'])->name('doc.destroy');
     
        

        Route::post('/import-attendance', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'importAttendance']);
        Route::resource('/attendance', 'Modules\Essentials\Http\Controllers\AttendanceController');
        Route::post('/clock-in-clock-out', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'clockInClockOut']);

        Route::post('/validate-clock-in-clock-out', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'validateClockInClockOut']);

        Route::get('/get-attendance-by-shift', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceByShift']);
        Route::get('/get-attendance-by-date', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceByDate']);
        Route::get('/get-attendance-row/{user_id}', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceRow']);

        Route::get(
            '/user-attendance-summary',
            [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getUserAttendanceSummary']
        );

        Route::get('/location-employees', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getEmployeesBasedOnLocation']);
        Route::get('/my-payrolls', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getMyPayrolls']);
        Route::get('/get-allowance-deduction-row', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getAllowanceAndDeductionRow']);
        Route::get('/payroll-group-datatable', [Modules\Essentials\Http\Controllers\PayrollController::class, 'payrollGroupDatatable']);
        Route::get('/view/{id}/payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'viewPayrollGroup']);
        Route::get('/edit/{id}/payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getEditPayrollGroup']);
        Route::post('/update-payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getUpdatePayrollGroup']);
        Route::get('/payroll-group/{id}/add-payment', [Modules\Essentials\Http\Controllers\PayrollController::class, 'addPayment']);
        Route::post('/post-payment-payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'postAddPayment']);
        Route::resource('/payroll', 'Modules\Essentials\Http\Controllers\PayrollController');
        Route::resource('/holiday', 'EssentialsHolidayController');

        Route::get('/shift/assign-users/{shift_id}', [Modules\Essentials\Http\Controllers\ShiftController::class, 'getAssignUsers']);
        Route::post('/shift/assign-users', [Modules\Essentials\Http\Controllers\ShiftController::class, 'postAssignUsers']);
        Route::resource('/shift', 'Modules\Essentials\Http\Controllers\ShiftController');
        Route::get('/sales-target', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'index']);
        Route::get('/set-sales-target/{id}', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'setSalesTarget']);
        Route::post('/save-sales-target', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'saveSalesTarget']);
        
        Route::get('/departments',[\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'index']);
      
        Route::post('/treeview/update/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'update']);
        Route::post('treeview/delete/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'deletenode'])->name('hrm.treeview.delete');
        Route::post('treeview/add/', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'store'])->name('hrm.treeview.add');


        Route::get('/employees', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'index'])->name('employees');
        Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('roles');
        Route::get('/createEmployee', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'create'])->name('createEmployee');
        Route::post('/storeEmployee', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'store'])->name('storeEmployee');
        Route::get('/editEmployee/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'edit'])->name('editEmployee');
        Route::get('/employees/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'show'])->name('showEmployee');
        Route::put('/updateEmployee/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'update'])->name('updateEmployee');

        Route::get('/import-employees', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class, 'index'])->name('import-employees');
        Route::post('/send-employee-file', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class, 'postImportEmployee'])->name('send-employee-file');
   
   
       

   
   
   
   
   
   
   
   
    });
});
