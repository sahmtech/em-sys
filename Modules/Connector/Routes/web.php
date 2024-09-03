<?php

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu', 'CheckUserLogin')->prefix('connector')->group(function () {
    Route::get('install', [Modules\Connector\Http\Controllers\InstallController::class, 'index']);
    Route::post('install', [Modules\Connector\Http\Controllers\InstallController::class, 'install']);
    Route::get('install/uninstall', [Modules\Connector\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('install/update', [Modules\Connector\Http\Controllers\InstallController::class, 'update']);
});

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'CustomAdminSidebarMenu')->prefix('connector')->group(function () {
    Route::get('/api', [Modules\Connector\Http\Controllers\ConnectorController::class, 'index']);
    Route::resource('/client', 'Modules\Connector\Http\Controllers\ClientController');
    Route::get('/regenerate', [Modules\Connector\Http\Controllers\ClientController::class, 'regenerate']);
    Route::get('/user_device', [Modules\Connector\Http\Controllers\ConnectorController::class, 'user_device'])->name('user_device');
    // Route::get('/user_device_edit', [Modules\Connector\Http\Controllers\ConnectorController::class, 'user_device_edit'])->name('user_device_edit');
    Route::get('/user_device_delete/{id}', [Modules\Connector\Http\Controllers\ConnectorController::class, 'user_device_delete'])->name('user_device_delete');
});
