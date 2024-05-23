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
    Route::prefix('generalmanagmentoffice')->group(function () {
        Route::get('/', 'GeneralManagmentOfficeController@index');
        Route::get('/dashboard', [Modules\GeneralManagmentOffice\Http\Controllers\DashboardController::class, 'index'])->name('GeneralManagmentOffice.dashboard');
        // Route::prefix('notifications')->group(function () {
        //     Route::get('/index', [Modules\GeneralManagmentOffice\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        //     Route::get('/create', [Modules\GeneralManagmentOffice\Http\Controllers\NotificationController::class, 'create'])->name('notifications.create');
        //     Route::post('/store', [Modules\GeneralManagmentOffice\Http\Controllers\NotificationController::class, 'storeAndSend'])->name('notifications.send');
        //     Route::get('/settings', [Modules\GeneralManagmentOffice\Http\Controllers\NotificationController::class, 'settings'])->name('notifications.settings');
        //     Route::post('/settings.update', [Modules\GeneralManagmentOffice\Http\Controllers\NotificationController::class, 'updateSettings'])->name('notifications.settings.update');
        // });
        Route::get('/GMO_president_requests', [\Modules\GeneralManagmentOffice\Http\Controllers\RequestController::class, 'index'])->name('GMO_president_requests');
        Route::get('/GMO_escalate_requests', [\Modules\GeneralManagmentOffice\Http\Controllers\RequestController::class, 'escalateRequests'])->name('GMO_escalate_requests');
        Route::post('/GMO_changeEscalationStatus', [\Modules\GeneralManagmentOffice\Http\Controllers\RequestController::class, 'changeEscalationStatus'])->name('GMO_changeEscalationStatus');
        Route::get('/viewGmORequest/{requestId}', [\Modules\GeneralManagmentOffice\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewGmORequest');
    });
});