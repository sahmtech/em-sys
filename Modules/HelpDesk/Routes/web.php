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

use Modules\HelpDesk\Http\Controllers\HdTicketAttachementController;
use Modules\HelpDesk\Http\Controllers\HdTicketController;
use Modules\HelpDesk\Http\Controllers\HdTicketReplyController;

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {

    Route::prefix('helpdesk/tickets')->group(function () {
        //  Route::get('/', 'HelpDeskController@index');
        Route::get('/', [HdTicketController::class, 'index'])->name('tickets.index');
        Route::get('/update/{id}', [HdTicketController::class, 'updateStatus'])->name('tickets.status');

        Route::post('/store', [HdTicketController::class, 'store'])->name('tickets.store');
        Route::post('/reply/store', [HdTicketReplyController::class, 'store'])->name('tickets.storeReply');
        Route::get('/show/{id}', [HdTicketController::class, 'show'])->name('tickets.show');
        Route::get('/viewAttachments', [HdTicketAttachementController::class, 'index'])->name('viewAttachments');
        Route::get('/replyAttachIndex', [HdTicketAttachementController::class, 'replyAttachIndex'])->name('replyAttachIndex');

    });
});
