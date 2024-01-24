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
        Route::get('/escalate_requests', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'escalateRequests'])->name('ceomanagment.escalate_requests');
        Route::post('/change-status', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'changeStatus'])->name('ceomanagment.changeStatus');
        Route::get('/viewCEORequest/{requestId}', [\Modules\CEOManagment\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewCEORequest');

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

        Route::get('/getParentDepartments/{businessId}', [\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'getParentDepartments'])
            ->name('getParentDepartments');

        Route::get('/procedures', [\Modules\Essentials\Http\Controllers\EssentialsWkProcedureController::class, 'index'])->name('procedures');
        Route::post('/storeProcedure', [\Modules\Essentials\Http\Controllers\EssentialsWkProcedureController::class, 'store'])->name('storeProcedure');
        Route::delete('/procedure/{id}', [\Modules\Essentials\Http\Controllers\EssentialsWkProcedureController::class, 'destroy'])->name('procedure.destroy');
        Route::get('/getProcedure/{procedure_id}', [\Modules\Essentials\Http\Controllers\EssentialsWkProcedureController::class, 'getProcedure'])->name('getProcedure');
        
    
    });
});
