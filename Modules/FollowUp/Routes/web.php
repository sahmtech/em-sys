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

    Route::prefix('followup')->group(function() {
        Route::get('/dashboard', [Modules\FollowUp\Http\Controllers\DashboardController::class, 'index'])->name('followup.dashboard');
        Route::get('/', [Modules\FollowUp\Http\Controllers\FollowUpController::class, 'index'])->name('followup_landing');
      
        Route::get('/projects',[\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'index'])->name('projects');
        Route::get('/projectShow/{id}', [\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'show']);
    
        Route::get('/workers',[\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index'])->name('workers');

    });
    
    
    });
