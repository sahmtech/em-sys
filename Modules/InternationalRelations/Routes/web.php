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

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu')->group(function () {

    Route::prefix('international-Relations')->group(function() {
        Route::get('/dashboard', [Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index'])->name('international-Relations.dashboard');
        Route::get('/Airlines', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'index'])->name('international-Relations.Airlines');
        Route::post('store/Airlines', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'store'])->name('store.Airlines');
        Route::get('/getCitiesByCountry/{country_id}', [Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'getCitiesByCountry']);

        
        Route::get('/EmploymentCompanies', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'index'])->name('international-Relations.EmploymentCompanies');
        Route::post('store/EmploymentCompanies', [Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'store'])->name('store.EmploymentCompanies');
    });
    
    
    });
