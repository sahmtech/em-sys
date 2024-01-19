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

use Modules\LegalAffairs\Http\Controllers\LegalAffairsController;

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {

    Route::prefix('legalaffairs')->group(function () {
        Route::get('/dashboard', [LegalAffairsController::class, 'index'])->name('legalAffairs.dashboard');
        Route::get('/contracts_management', [LegalAffairsController::class, 'index'])->name('legalAffairs.contracts_management');
    });
});
