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

    Route::prefix('ceomanagment')->group(function () {
        Route::get('/', 'CEOManagmentController@index');
        Route::get('/dashboard', [Modules\CEOManagment\Http\Controllers\DashboardController::class, 'index'])->name('ceomanagment.dashboard');

        Route::get('/requests', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'index'])->name('view_CEO_requests');
        Route::get('/ceo_pending_requests', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'ceo_pending_requests'])->name('ceo_pending_requests');
        Route::get('/ceo_done_requests', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'ceo_done_requests'])->name('ceo_done_requests');
        Route::get('/filtered_requests/{filter}', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'getFilteredRequests'])->name('ceomanagment.getFilteredRequests');

        Route::get('/escalate_requests', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'escalateRequests'])->name('ceomanagment.escalate_requests');
        Route::post('/change-status', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'changeStatus'])->name('ceomanagment.changeStatus');
        Route::get('/viewCEORequest/{requestId}', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewCEORequest');
        Route::post('/changeEscalationStatus', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'changeEscalationStatus'])->name('ceomanagment.changeEscalationStatus');

        Route::get('/requests_types', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'index'])->name('requests_types');
        Route::post('storeRequestType', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'store'])->name('storeRequestType');
        Route::DELETE('deleteRequestType/{id}', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'destroy'])->name('deleteRequestType');
        Route::post('updateRequestType', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'update'])->name('updateRequestType');
        Route::post('updateType', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'updateType'])->name('updateType');
        Route::get('/get-tasks-for-type', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'getTasksForType'])->name('get-tasks-for-type');
        Route::get('/getRequestType/{request_type_id}', [\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'getRequestType'])->name('getRequestType');

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
        Route::get('/getDepartmentsForWk/{businessId}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'getDepartmentsForWk'])->name('getDepartmentsForWk');

        Route::get('/employeesProcedures', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'employeesProcedures'])->name('employeesProcedures');
        Route::get('/workersProcedures', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'workersProcedures'])->name('workersProcedures');
        Route::get('/timesheet_wk', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'timesheet_wk'])->name('timesheet_wk');
        Route::get('/fetch-w-request-types-by-business', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'fetchWorkerRequestTypesByBusiness'])->name('fetch.w.request.types.by.business');
        Route::get('/fetch-emp-request-types-by-business', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'fetchEmployeeRequestTypesByBusiness'])->name('fetch.emp.request.types.by.business');


        Route::post('/storeEmployeeProcedure', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'storeEmployeeProcedure'])->name('storeEmployeeProcedure');
        Route::post('/storeWorkerProcedure', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'storeWorkerProcedure'])->name('storeWorkerProcedure');
        Route::post('/storeTimeSheetProcedure', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'storeTimeSheetProcedure'])->name('storeTimeSheetProcedure');



        Route::put('/updateProcedure/{id}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'update'])->name('updateProcedure');
        Route::put('/updateEmployeeProcedure/{id}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'updateEmployeeProcedure'])->name('updateEmployeeProcedure');


        Route::delete('/procedure/{id}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'destroy'])->name('procedure.destroy');
        Route::get('/getProcedure/{procedure_id}', [\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'getProcedure'])->name('getProcedure');


        Route::get('/payrolls_checkpoint/{from}',   [App\Http\Controllers\PayrollController::class, 'payrolls_checkpoint'])->name('ceo.payrolls_checkpoint');
    });
});
