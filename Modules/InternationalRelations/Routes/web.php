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

Route::middleware('web', 'authh', 'auth', 'CustomSetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {

    Route::prefix('international-Relations')->group(function () {

        Route::get('/', [Modules\InternationalRelations\Http\Controllers\InternationalRelationsController::class, 'index'])->name('international_relations_landing');
    });
    Route::prefix('ir')->group(function () {
        Route::get('/dashboard', [Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index'])->name('international-Relations.dashboard');
        Route::get('/Airlines', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'index'])->name('international-Relations.Airlines');
        Route::post('store/Airlines', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'store'])->name('store.Airlines');
        Route::get('/getCitiesByCountry/{country_id}', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'getCitiesByCountry']);
        Route::post('store/EmploymentCompanies', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'store'])->name('store.EmploymentCompanies');
        Route::get('companyRequests/{id}', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'companyRequests'])->name('companyRequests');
        Route::get('proposed_laborIndex',[\Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'proposed_laborIndex'])->name('proposed_laborIndex');
        Route::get('/createProposed_labor', [\Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'createProposed_labor'])->name('createProposed_labor');
        Route::post('/storeProposed_labor', [\Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'storeProposed_labor'])->name('storeProposed_labor');
        
        

        Route::get('/EmploymentCompanies', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'index'])->name('international-Relations.EmploymentCompanies');
        Route::post('store/EmploymentCompanies', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'store'])->name('store.EmploymentCompanies');

        Route::get('/order_request', [Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'index'])->name('order_request');
        Route::get('/order_request/Delegation/{id}', [Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'Delegation'])->name('order_request.Delegation');
        Route::post('/save-data', [Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'saveRequest'])->name('save-data');
    });
});
