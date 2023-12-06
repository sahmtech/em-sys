<?php

use Illuminate\Support\Facades\Route;

Route::get('/test_test', function () {
    return "ggg";
});

Route::middleware('auth:api', 'timezone')->prefix('connector/api/essentials')->group(function () {

    Route::get('getLeaveTypes', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsLeaveTypeController::class, 'getLeaveTypes']);
});
