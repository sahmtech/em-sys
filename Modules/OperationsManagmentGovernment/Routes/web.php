<?php

use Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDepartmentController;
use Modules\OperationsManagmentGovernment\Http\Controllers\ProjectDocumentController;

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

    Route::prefix('operationsmanagmentgovernment')->group(function () {
        Route::get('/', 'OperationsManagmentGovernmentController@index');
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


    });
});
