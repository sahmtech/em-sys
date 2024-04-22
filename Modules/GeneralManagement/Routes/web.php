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
    Route::prefix('generalmanagement')->group(function () {
        Route::get('/', 'GeneralManagementController@index');
        Route::get('/dashboard', [Modules\GeneralManagement\Http\Controllers\DashboardController::class, 'index'])->name('GeneralManagement.dashboard');
        Route::prefix('notifications')->group(function () {
            Route::get('/index', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/create', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'create'])->name('notifications.create');
            Route::post('/store', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'storeAndSend'])->name('notifications.send');
            Route::get('/settings', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'settings'])->name('notifications.settings');
            Route::post('/settings.update', [Modules\GeneralManagement\Http\Controllers\NotificationController::class, 'updateSettings'])->name('notifications.settings.update');
        });
        Route::get('/president_requests', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'index'])->name('president_requests');
        Route::get('/escalate_requests', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'escalateRequests'])->name('escalate_requests');
        Route::post('/changeEscalationStatus', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'changeEscalationStatus'])->name('generalmanagement.changeEscalationStatus');
        Route::get('/viewGmRequest/{requestId}', [\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewGmRequest');



        Route::get('/human_resources_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'human_resources_management'])->name('generalmanagement.human_resources_management');
        Route::get('/financial_accounting_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'financial_accounting_management'])->name('generalmanagement.financial_accounting_management');
        Route::get('/follow_up_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'follow_up_management'])->name('generalmanagement.follow_up_management');
        Route::get('/international_relations_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'international_relations_management'])->name('generalmanagement.international_relations_management');
        Route::get('/housing_movement_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'housing_movement_management'])->name('generalmanagement.housing_movement_management');
        Route::get('/sells_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'sells_management'])->name('generalmanagement.sells_management');
        Route::get('/legal_affairs_management', [Modules\GeneralManagement\Http\Controllers\GeneralManagementController::class, 'legal_affairs_management'])->name('generalmanagement.legal_affairs_management');
    });
});
