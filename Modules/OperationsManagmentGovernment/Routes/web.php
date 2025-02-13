<?php

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

use Illuminate\Support\Facades\Route;

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
        Route::get('/zone', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'zone'])->name('operationsmanagmentgovernment.zone');


        //permissions
        Route::get('/permissions', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'permissions'])->name('operationsmanagmentgovernment.permissions');
        Route::get('/get_contact_permissions/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'get_contact_permissions'])->name('operationsmanagmentgovernment.get_contact_permissions');
        Route::put('/permissions/update/{id}', [Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController::class, 'update_permissions'])->name('operationsmanagmentgovernment.permissions.update');


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
    });
});
