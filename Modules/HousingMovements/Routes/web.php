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

    Route::prefix('housingmovements')->group(function () {
        Route::get('/dashboard', [Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index'])->name('housingmovements.dashboard');

        //requests 
        Route::get('/requests', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'index'])->name('requests');

        Route::get('/', [\Modules\HousingMovements\Http\Controllers\HousingMovementsController::class, 'index'])->name('housingMovements_landingPage');

        //buildings_management
        Route::get('/buildings', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'index'])->name('buildings');
        Route::get('/createBuilding', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'create'])->name('createBuilding');
        Route::post('/storeBuilding', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'store'])->name('storeBuilding');
        Route::get('/buildings/{id}/edit', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'edit'])->name('building.edit');
        Route::delete('/buildings/{id}', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'destroy'])->name('building.destroy');
        Route::put('/updateBuilding/{id}', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'update'])->name('updateBuilding');

        //Rooms
        Route::get('/rooms', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'index'])->name('rooms');
        Route::get('/createRoom', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'create'])->name('createRoom');
        Route::post('/storeRoom', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'store'])->name('storeRoom');
        Route::get('/rooms/{id}/edit', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'edit'])->name('room.edit');
        Route::delete('/rooms/{id}', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'destroy'])->name('room.destroy');
        Route::put('/updateRoom/{id}', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'update'])->name('updateRoom');

        //facilities
        Route::get('/facilities', [\Modules\HousingMovements\Http\Controllers\FacitityController::class, 'index'])->name('facilities');


        //movement_management
        Route::get('/movement', [\Modules\HousingMovements\Http\Controllers\MovementController::class, 'index'])->name('movement');


        //traveleres
        Route::get('/travelers', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'index'])->name('travelers');
        Route::get('/workers', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'index'])->name('workers');
        Route::get('/get-room-numbers/{buildingId}',  [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getRoomNumbers'])->name('getRoomNumbers');
        Route::post('/get-arrived', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getarrived'])->name('get-arrived');
    });
});
