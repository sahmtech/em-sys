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
   // Route::get('/dashboard', [Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index'])->name('housingmovements.dashboard');
   
   //requests 
   Route::get('/requests', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'index'])->name('requests');
    
    
    //buildings_management
    Route::get('/buildings', [\Modules\HousingMovements\Http\Controllers\BuildingController::class,'index'])->name('buildings');
    Route::get('/createBuilding', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'create'])->name('createBuilding');
    Route::post('/storeBuilding', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'store'])->name('storeBuilding');
    Route::get('/buildings/{id}/edit', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'edit'])->name('building.edit');
    Route::delete('/buildings/{id}', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'destroy'])->name('building.destroy');
    Route::put('/updateBuilding/{id}', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'update'])->name('updateBuilding');
    
    //Rooms
    Route::get('/rooms', [\Modules\HousingMovements\Http\Controllers\RoomController::class,'index'])->name('rooms');


    //facilities
    Route::get('/facilities', [\Modules\HousingMovements\Http\Controllers\FacitityController::class,'index'])->name('facilities');

    
    //movement_management
    Route::get('/movement', [\Modules\HousingMovements\Http\Controllers\MovementController::class,'index'])->name('movement');

   
});


});
