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
        Route::get('/housed-workers', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'housed_workers_index'])->name('housed_workers');
        Route::get('/workers', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'index'])->name('workers');
        Route::get('/get-room-numbers/{buildingId}',  [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getRoomNumbers'])->name('getRoomNumbers');
        Route::post('/housed',  [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'housed_data'])->name('housed');
        Route::post('/postarrival_data', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'postarrivaldata'])->name('postarrivaldata');
        Route::post('/get-selected-arrived-data', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getSelectedRowsData'])->name('getSelectedArrivalsData');

        Route::get('/get-shifts/{projectId}', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getShifts'])->name('getShifts');
        Route::get('/getBedsCount/{roomId}', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getBedsCount'])->name('getBedsCount');
        
        // Routes Car Types
        Route::get('/cars-type', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'index'])->name('car-types');
        Route::get('/cars-type-create', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'create'])->name('car-type-create');
        Route::get('/cars-type-edit/{id}', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'edit'])->name('cartype.edit');
        Route::post('/cars-type-store', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'store'])->name('car-type-store');
        Route::post('/cars-type-search', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'search'])->name('car-type-search');
        Route::put('/cars-type-update/{id}', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'update'])->name('car-type-update');
        Route::get('/cars-type-delete/{id}', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'destroy'])->name('cartype.delete');
        Route::get('/cars-model', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'index'])->name('cars-model');
        // Route Car Models
        Route::get('/cars-model', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'index'])->name('car-models');
        Route::get('/cars-model-create', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'create'])->name('car-model-create');
        Route::get('/cars-model-edit/{id}', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'edit'])->name('carmodel.edit');
        Route::post('/cars-model-store', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'store'])->name('car-model-store');
        Route::post('/cars-model-search', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'search'])->name('car-model-search');
        Route::put('/cars-model-update/{id}', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'update'])->name('car-model-update');
        Route::get('/cars-model-delete/{id}', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'destroy'])->name('carmodel.delete');
        // Routes Cars
        Route::get('/cars', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'index'])->name('cars');
        Route::get('/cars-create', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'create'])->name('car-create');
        Route::get('/cars-edit/{id}', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'edit'])->name('car.edit');
        Route::post('/cars-store', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'store'])->name('car-store');
        Route::post('/cars-search', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'search'])->name('car-search');
        Route::put('/cars-update/{id}', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'update'])->name('car-update');
        Route::get('/cars-delete/{id}', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'destroy'])->name('car.delete');
        Route::get('/carModel-by-carType_id/{carType_id}', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'getCarModelByCarType_id'])->name('getCarModelByCarType_id');



        // Routes Shifts
        Route::get('/shifts', [\Modules\HousingMovements\Http\Controllers\ShiftController::class, 'index'])->name('shifts');
        Route::get('/shifts-create', [\Modules\HousingMovements\Http\Controllers\ShiftController::class, 'create'])->name('shifts-create');
        Route::get('/shifts-edit/{id}', [\Modules\HousingMovements\Http\Controllers\ShiftController::class, 'edit'])->name('shifts-edit');
        Route::post('/shifts-store', [\Modules\HousingMovements\Http\Controllers\ShiftController::class, 'store'])->name('shifts-store');
        Route::post('/shifts-search', [\Modules\HousingMovements\Http\Controllers\ShiftController::class, 'search'])->name('shifts-search');
        Route::put('/shifts-update/{id}', [\Modules\HousingMovements\Http\Controllers\ShiftController::class, 'update'])->name('shifts-update');
        Route::get('/shifts-delete/{id}', [\Modules\HousingMovements\Http\Controllers\ShiftController::class, 'destroy'])->name('shifts-delete');
        Route::get('/projects-by-contacts/{id}', [\Modules\HousingMovements\Http\Controllers\ShiftController::class, 'ProjectsByContacts'])->name('ProjectsByContacts');
    });
});