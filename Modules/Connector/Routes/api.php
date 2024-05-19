<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api', 'timezone')->prefix('connector/api')->group(function () {
    Route::resource('business-location', Modules\Connector\Http\Controllers\Api\BusinessLocationController::class)->only('index', 'show');

    Route::resource('contactapi', Modules\Connector\Http\Controllers\Api\ContactController::class)->only('index', 'show', 'store', 'update');

    Route::post('contactapi-payment', [Modules\Connector\Http\Controllers\Api\ContactController::class, 'contactPay']);

    Route::resource('unit', Modules\Connector\Http\Controllers\Api\UnitController::class)->only('index', 'show');

    Route::resource('taxonomy', 'Modules\Connector\Http\Controllers\Api\CategoryController')->only('index', 'show');

    Route::resource('brand', Modules\Connector\Http\Controllers\Api\BrandController::class)->only('index', 'show');

    Route::resource('product', Modules\Connector\Http\Controllers\Api\ProductController::class)->only('index', 'show');

    Route::get('selling-price-group', [Modules\Connector\Http\Controllers\Api\ProductController::class, 'getSellingPriceGroup']);

    Route::get('variation/{id?}', [Modules\Connector\Http\Controllers\Api\ProductController::class, 'listVariations']);

    Route::resource('tax', 'Modules\Connector\Http\Controllers\Api\TaxController')->only('index', 'show');

    Route::resource('table', Modules\Connector\Http\Controllers\Api\TableController::class)->only('index', 'show');

    Route::get('user/loggedin', [Modules\Connector\Http\Controllers\Api\UserController::class, 'loggedin']);
    Route::post('user-registration', [Modules\Connector\Http\Controllers\Api\UserController::class, 'registerUser']);
    Route::resource('user', Modules\Connector\Http\Controllers\Api\UserController::class)->only('index', 'show');

    Route::resource('types-of-service', Modules\Connector\Http\Controllers\Api\TypesOfServiceController::class)->only('index', 'show');

    Route::get('payment-accounts', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getPaymentAccounts']);

    Route::get('payment-methods', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getPaymentMethods']);

    Route::resource('sell', Modules\Connector\Http\Controllers\Api\SellController::class)->only('index', 'store', 'show', 'update', 'destroy');

    Route::post('sell-return', [Modules\Connector\Http\Controllers\Api\SellController::class, 'addSellReturn']);

    Route::get('list-sell-return', [Modules\Connector\Http\Controllers\Api\SellController::class, 'listSellReturn']);

    Route::post('update-shipping-status', [Modules\Connector\Http\Controllers\Api\SellController::class, 'updateSellShippingStatus']);

    Route::resource('expense', Modules\Connector\Http\Controllers\Api\ExpenseController::class)->only('index', 'store', 'show', 'update');
    Route::get('expense-refund', [Modules\Connector\Http\Controllers\Api\ExpenseController::class, 'listExpenseRefund']);

    Route::get('expense-categories', [Modules\Connector\Http\Controllers\Api\ExpenseController::class, 'listExpenseCategories']);

    Route::resource('cash-register', Modules\Connector\Http\Controllers\Api\CashRegisterController::class)->only('index', 'store', 'show', 'update');

    Route::get('business-details', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getBusinessDetails']);

    Route::get('profit-loss-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getProfitLoss']);

    Route::get('product-stock-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getProductStock']);
    Route::get('notifications', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getNotifications']);

    Route::get('active-subscription', [Modules\Connector\Http\Controllers\Api\SuperadminController::class, 'getActiveSubscription']);
    Route::get('packages', [Modules\Connector\Http\Controllers\Api\SuperadminController::class, 'getPackages']);

    Route::get('get-attendance/{user_id}', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'getAttendance']);
    // Route::post('clock-in', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'clockin']);
    Route::post('clock-out', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'clockout']);
    Route::get('holidays', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'getHolidays']);
    Route::post('update-password', [Modules\Connector\Http\Controllers\Api\UserController::class, 'updatePassword']);
    Route::post('forget-password', [Modules\Connector\Http\Controllers\Api\UserController::class, 'forgetPassword']);
    Route::get('get-location', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getLocation']);

    Route::get('new_product', [Modules\Connector\Http\Controllers\Api\ProductSellController::class, 'newProduct'])->name('new_product');
    Route::get('new_sell', [Modules\Connector\Http\Controllers\Api\ProductSellController::class, 'newSell'])->name('new_sell');
    Route::get('new_contactapi', [Modules\Connector\Http\Controllers\Api\ProductSellController::class, 'newContactApi'])->name('new_contactapi');
});

Route::middleware('auth:api', 'timezone')->prefix('connector/api/crm')->group(function () {
    Route::resource('follow-ups', 'Modules\Connector\Http\Controllers\Api\Crm\FollowUpController')->only('index', 'store', 'show', 'update');

    Route::get('follow-up-resources', [Modules\Connector\Http\Controllers\Api\Crm\FollowUpController::class, 'getFollowUpResources']);

    Route::get('leads', [Modules\Connector\Http\Controllers\Api\Crm\FollowUpController::class, 'getLeads']);

    Route::post('call-logs', [Modules\Connector\Http\Controllers\Api\Crm\CallLogsController::class, 'saveCallLogs']);
});

Route::middleware('auth:api', 'timezone')->prefix('connector/api')->group(function () {
    Route::get('field-force', [Modules\Connector\Http\Controllers\Api\FieldForce\FieldForceController::class, 'index']);
    Route::post('field-force/create', [Modules\Connector\Http\Controllers\Api\FieldForce\FieldForceController::class, 'store']);
    Route::post('field-force/update-visit-status/{id}', [Modules\Connector\Http\Controllers\Api\FieldForce\FieldForceController::class, 'updateStatus']);
});


Route::middleware('auth:api', 'timezone')->prefix('connector/api')->group(function () {

    Route::get('getAttendanceByDate', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'getAttendanceByDate']);
    Route::get('home', [Modules\Connector\Http\Controllers\Api\HomeController::class, 'home']);
    Route::get('logout', [Modules\Connector\Http\Controllers\Api\HomeController::class, 'logout']);
    Route::get('terms_privacy', [Modules\Connector\Http\Controllers\Api\HomeController::class, 'terms_privacy']);
    Route::get('readAllNotifications', [Modules\Connector\Http\Controllers\Api\HomeController::class, 'readAllNotifications']);
    Route::post('checkDevice', [Modules\Connector\Http\Controllers\Api\HomeController::class, 'checkDevice']);
    // Route::get('removeNotification/{id}', [Modules\Connector\Http\Controllers\Api\HomeController::class, 'removeNotification']);
    //  Route::get('notifications', [Modules\Connector\Http\Controllers\Api\HomeController::class, 'getNotifications']);
});

Route::middleware('auth:api', 'timezone')->prefix('connector/api/essentials')->group(function () {
    // Route::post('/checkPointInPolygon', [Modules\Connector\Http\Controllers\Api\ApiAttendanceController::class, 'checkPointInPolygon']);
    Route::get('getLeaveTypes', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsLeaveTypeController::class, 'getLeaveTypes']);

    //get request types
    Route::get('getRequestTypes', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsRequestsController::class, 'getRequestTypes']);

    Route::get('getMyRequests',  [Modules\Essentials\Http\Controllers\Api\ApiEssentialsRequestsController::class, 'getMyRequests']);
    Route::get('getMyToDo', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsLeaveTypeController::class, 'getMyToDo']);
    //make request / leave request
    Route::post('makeRequest', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsRequestsController::class, 'storeApiRequest']);
    Route::get('getMyLeaves', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsRequestsController::class, 'getMyLeaves']);
    Route::get('getEditUserInfo', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsController::class, 'getEditUserInfo']);
    Route::post('updateUserInfo', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsController::class, 'updateUserInfo']);

    Route::post('changeToDoStatus/{id}', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsController::class, 'changeToDoStatus']);
    Route::get('getPayrollDetails', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsController::class, 'getPayrollDetails']);
    Route::post('clock-in', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsController::class, 'clockin']);
});

Route::post('connector/api/essentials/resetPassword', [Modules\Essentials\Http\Controllers\Api\ApiEssentialsController::class, 'resetPassword']);
