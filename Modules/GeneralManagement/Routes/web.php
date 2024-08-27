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
    Route::prefix('generalmanagement')->group(function () {
        Route::get('/payrolls_checkpoint/{from}',   [App\Http\Controllers\PayrollController::class, 'payrolls_checkpoint'])->name('generalmanagement.payrolls_checkpoint');
        Route::get('/', 'GeneralManagementController@index');
        Route::get('/dashboard', [Modules\GeneralManagement\Http\Controllers\DashboardController::class, 'index'])->name('GeneralManagement.dashboard');
        Route::prefix('notifications')->group(function () {
            Route::get('/index', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/create', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'create'])->name('notifications.create');
            Route::post('/store', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'storeAndSend'])->name('notifications.send');
            Route::get('/settings', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'settings'])->name('notifications.settings');
            Route::post('/settings.update', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'updateSettings'])->name('notifications.settings.update');
        });
        Route::get('/president_requests', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'index'])->name('president_requests');
        Route::get('/president_pending_requests', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'president_pending_requests'])->name('president_pending_requests');
        Route::get('/president_done_requests', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'president_done_requests'])->name('president_done_requests');
        Route::get('/filtered_requests/{filter}', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'getFilteredRequests'])->name('generalmanagement.getFilteredRequests');

        Route::get('/escalate_requests', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'escalateRequests'])->name('escalate_requests');
        Route::post('/changeEscalationStatus', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'changeEscalationStatus'])->name('generalmanagement.changeEscalationStatus');
        Route::get('/viewGmRequest/{requestId}', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewGmRequest');
        Route::post('/transferRequestToDepartment', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'transferToDepartment'])->name('transferRequestToDepartment');

        Route::get('/requests_types', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'index'])->name('requests_types');
        Route::post('storeRequestType', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'store'])->name('storeRequestType');
        Route::DELETE('deleteRequestType/{id}', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'destroy'])->name('deleteRequestType');
        Route::post('updateRequestType', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'update'])->name('updateRequestType');
        Route::get('/get-tasks-for-type', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'getTasksForType'])->name('get-tasks-for-type');
        Route::get('/getRequestType/{request_type_id}', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'getRequestType'])->name('getRequestType');
        Route::post('/update_selfish_service/{id}', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'updateSelfishService'])->name('update_selfish_service');


        Route::get('/departments', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'index'])->name('departments');
        Route::get('/createDepartment', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'create'])->name('createDepartment');
        Route::post('/storeDepartment', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'store'])->name('storeDepartment');
        Route::get('/departments/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'edit'])->name('department.edit');
        Route::delete('/departments/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'destroy'])->name('department.destroy');
        Route::put('/updateDepartments/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'update'])->name('updateDepartment');
        Route::post('/store-manager/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'storeManager'])->name('storeManager');
        Route::post('/manager_delegating/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'manager_delegating'])->name('manager_delegating');
        Route::get('/getDepartmentInfo/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'getDepartmentInfo'])->name('getDepartmentInfo');
        Route::get('/departments.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'show'])->name('dep.view');
        Route::post('/storeDeputy/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'storeDeputy'])->name('storeDeputy');
        Route::post('/treeview/update/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'update']);
        Route::post('treeview/delete/{id}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'deletenode'])->name('hrm.treeview.delete');
        Route::post('treeview/add/', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'store'])->name('hrm.treeview.add');
        Route::post('get_professions_for_employee/', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'getProfessionsForEmployee'])->name('get_professions_for_employee');

        Route::get('/getParentDepartments/{businessId}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'getParentDepartments'])
            ->name('getParentDepartments');

        Route::get('/employeesProcedures', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'employeesProcedures'])->name('employeesProcedures');
        Route::get('/workersProcedures', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'workersProcedures'])->name('workersProcedures');

        Route::post('/storeEmployeeProcedure', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'storeEmployeeProcedure'])->name('storeEmployeeProcedure');
        Route::post('/storeWorkerProcedure', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'storeWorkerProcedure'])->name('storeWorkerProcedure');


        Route::put('/updateProcedure/{id}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'update'])->name('updateProcedure');
        Route::put('/updateEmployeeProcedure/{id}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'updateEmployeeProcedure'])->name('updateEmployeeProcedure');


        Route::delete('/procedure/{id}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'destroy'])->name('procedure.destroy');
        Route::get('/getProcedure/{procedure_id}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'getProcedure'])->name('getProcedure');


        Route::get('/human_resources_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'human_resources_management'])->name('generalmanagement.human_resources_management');
        Route::get('/financial_accounting_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'financial_accounting_management'])->name('generalmanagement.financial_accounting_management');
        Route::get('/follow_up_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'follow_up_management'])->name('generalmanagement.follow_up_management');
        Route::get('/international_relations_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'international_relations_management'])->name('generalmanagement.international_relations_management');
        Route::get('/housing_movement_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'housing_movement_management'])->name('generalmanagement.housing_movement_management');
        Route::get('/sells_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'sells_management'])->name('generalmanagement.sells_management');
        Route::get('/legal_affairs_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'legal_affairs_management'])->name('generalmanagement.legal_affairs_management');
    });
});