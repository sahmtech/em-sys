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
        Route::get(
            'operationsmanagmentgovernment/water_weight/edit/{id}',
            'Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController@edit_water'
        )
            ->name('operationsmanagmentgovernment.water_weight.edit');

        Route::put(
            'operationsmanagmentgovernment/water_weight/update/{id}',
            'Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController@update_water'
        )
            ->name('operationsmanagmentgovernment.water_weight.update');
        Route::delete(
            'operationsmanagmentgovernment/water_weight/delete/{id}',
            'Modules\OperationsManagmentGovernment\Http\Controllers\OperationsManagmentGovernmentController@delete_water'
        )
            ->name('operationsmanagmentgovernment.water_weight.delete');

        Route::get('/dashboard', [Modules\OperationsManagmentGovernment\Http\Controllers\DashboardController::class, 'index'])->name('operationsmanagmentgovernment.dashboard');
        Route::get('/requests', [\Modules\OperationsManagmentGovernment\Http\Controllers\RequestController::class, 'index'])->name('operationsmanagmentgovernment.view_requests');
        Route::get('/projects_documents', [ProjectDocumentController::class, 'index'])->name('projects_documents');
        Route::get('/documents-create', [ProjectDocumentController::class, 'create'])->name('documents-create');
        Route::post('/documents-store', [ProjectDocumentController::class, 'store'])->name('documents-store');

        Route::post('/storeOperationsManagmentGovernmentRequest', [\Modules\OperationsManagmentGovernment\Http\Controllers\RequestController::class, 'store'])->name('storeOperationsManagmentGovernmentRequest');
    });
});
