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

Route::prefix('housingmovements')->group(function() {
    Route::get('/dashboard', [Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index'])->name('housingmovements.dashboard');
   
});


});
