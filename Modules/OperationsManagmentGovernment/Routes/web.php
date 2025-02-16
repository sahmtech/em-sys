<?php

use Illuminate\Support\Facades\Route;
use Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDepartmentController;

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

use Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController;
use Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController;

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {

    Route::prefix('operationsmanagmentgovernment')->group(function () {
        Route::get('/', 'OperationsManagmentGovernmentController@index');
        //water
        Route::get('/water', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'water'])->name('operationsmanagmentgovernment.water');
        Route::post('/store_water', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'store_water'])->name('operationsmanagmentgovernment.water_weight.store');
        Route::get('water_weight/edit/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'edit_water'])->name('operationsmanagmentgovernment.water_weight.edit');
        Route::put('water_weight/update/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'update_water'])->name('operationsmanagmentgovernment.water_weight.update');
        Route::delete('water_weight/delete/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'delete_water'])->name('operationsmanagmentgovernment.water_weight.delete');

        //zone
        Route::get('/zone', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'zones'])->name('operationsmanagmentgovernment.zone');
        Route::post('/zone/store', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'store_zone'])->name('operationsmanagmentgovernment.zone.store');
        Route::delete('/zone/delete/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'delete_zone'])->name('operationsmanagmentgovernment.zone.delete');
        Route::get('/zone/edit/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'edit_zone'])->name('operationsmanagmentgovernment.zone.edit');
        Route::post('/zone/update/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'update_zone'])->name('operationsmanagmentgovernment.zone.update');

        //permissions
        Route::get('/permissions', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'permissions'])->name('operationsmanagmentgovernment.permissions');
        Route::get('/get_contact_permissions/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'get_contact_permissions'])->name('operationsmanagmentgovernment.get_contact_permissions');
        Route::put('/permissions/update/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'update_permissions'])->name('operationsmanagmentgovernment.permissions.update');

        //asset assessment
        Route::get('/assets', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'asset_assessment'])->name('operationsmanagmentgovernment.asset_assessment');
        Route::post('/assets/store', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'store_asset_assessment'])->name('operationsmanagmentgovernment.asset_assessment.store');
        Route::get('/assets/edit/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'edit_asset_assessment'])->name('operationsmanagmentgovernment.asset_assessment.edit');
        Route::put('/assets/update/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'update_asset_assessment'])->name('operationsmanagmentgovernment.asset_assessment.update');
        Route::delete('/assets/delete/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'destroy_asset_assessment'])->name('operationsmanagmentgovernment.asset_assessment.delete');


        //helpers
        Route::get('/getProjectsFromContact/{contact_id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'getProjectsFromContact'])
            ->name('operationsmanagmentgovernment.getProjectsFromContact');

        Route::get('/getZonesFromProjects/{project_id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'getZonesFromProjects'])
            ->name('operationsmanagmentgovernment.getZonesFromProjects');

        Route::get('/dashboard', [Modules\OperationsManagmentGovernment\Http\Controllers\DashboardController::class, 'index'])->name('operationsmanagmentgovernment.dashboard');
        Route::get('/requests', [\Modules\OperationsManagmentGovernment\Http\Controllers\RequestController::class, 'index'])->name('operationsmanagmentgovernment.view_requests');
        Route::get('/projects_documents', [ProjectDocumentController::class, 'index'])->name('projects_documents');

        Route::get('/projects_documents_blueprint', [ProjectDocumentController::class, 'blueprintIndex'])->name('projects_documents.blueprint');

        Route::get('/documents-create', [ProjectDocumentController::class, 'create'])->name('documents-create');
        Route::get('/documents-createBluePrint', [ProjectDocumentController::class, 'createBluePrint'])->name('documents-createBluePrint');
        Route::post('/documents-store', [ProjectDocumentController::class, 'store'])->name('documents-store');
        Route::post('/documents-storeBluePrint', [ProjectDocumentController::class, 'storeBluePrint'])->name('documents-storeBluePrint');

        Route::delete('projects_documents_destroy/{id}', [ProjectDocumentController::class, 'destroy'])->name('projects_documents.destroy');
        Route::post('/projects_documents_edit/{id}', [ProjectDocumentController::class, 'update'])->name('projects_documents.edit');

        Route::post('/storeOperationsManagmentGovernmentRequest', [\Modules\OperationsManagmentGovernment\Http\Controllers\RequestController::class, 'store'])->name('storeOperationsManagmentGovernmentRequest');

        // project_departments
        // Route::resource('project_departments', ProjectDepartmentController::class);
        Route::get('/project_departments-create', [ProjectDepartmentController::class, 'create'])->name('project_departments-create');
        Route::post('/project_departments-store', [ProjectDepartmentController::class, 'store'])->name('project_departments-store');
        Route::get('/project_departments', [ProjectDepartmentController::class, 'index'])->name('project_departments');
        Route::delete('project_departments_destroy/{id}', [ProjectDepartmentController::class, 'destroy'])->name('project_departments.destroy');

        // security_guards
        Route::get('/security_guards', [\Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController::class, 'index'])->name('security_guards');
        Route::get('/security_guards-create', [\Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController::class, 'create'])->name('security_guards-create');
        Route::post('/security_guards-store', [\Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController::class, 'store'])->name('security_guards-store');
        Route::delete('security_guards/{id}', [\Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController::class, 'destroy'])->name('security_guards.destroy');
        Route::get('security_guards/edit/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\SecurityGuardController::class, 'edit'])->name('security_guards.edit');

        Route::post(
            'operationsmanagmentgovernment/security_guards/update/{id}',
            [SecurityGuardController::class, 'update']
        )->name('security_guards.update');
    });
});
