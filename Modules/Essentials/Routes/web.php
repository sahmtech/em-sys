<?php

// use App\Http\Controllers\Modules;
// use Illuminate\Support\Facades\Route;


Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu')->group(function () {
   
   
    Route::prefix('essentials')->group(function () {
        Route::get('/dashboard', [Modules\Essentials\Http\Controllers\DashboardController::class, 'essentialsDashboard']);
        Route::get('/install', [Modules\Essentials\Http\Controllers\InstallController::class, 'index']);
        Route::get('/install/update', [Modules\Essentials\Http\Controllers\InstallController::class, 'update']);
        Route::get('/install/uninstall', [Modules\Essentials\Http\Controllers\InstallController::class, 'uninstall']);

        Route::get('/', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'index']);

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
        Route::get('/dashboard', [Modules\Essentials\Http\Controllers\DashboardController::class, 'hrmDashboard'])->name('hrmDashboard');
        Route::resource('/leave-type', 'Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController');
        Route::resource('/leave', 'Modules\Essentials\Http\Controllers\EssentialsLeaveController');
        Route::post('/change-status', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'changeStatus']);
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

        Route::get('/employees', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'index'])->name('employees');
        Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('roles');
        Route::get('/createEmployee', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'create'])->name('createEmployee');
        Route::post('/storeEmployee', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'store'])->name('storeEmployee');
        Route::get('/editEmployee/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'edit'])->name('editEmployee');
    });
});
