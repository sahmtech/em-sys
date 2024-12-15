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
        Route::get('/filtered_requests/{filter}', [Modules\HousingMovements\Http\Controllers\DashboardController::class, 'getFilteredRequests'])->name('housingmovements.getFilteredRequests');

        Route::get('/dashboard', [Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index'])->name('housingmovements.dashboard');
        Route::get('/housingmovements_department_employees', [Modules\HousingMovements\Http\Controllers\HousingMovementsController::class, 'department_employees'])->name('housingmovements_department_employees');
        // Route::get('/dashboard-movment', [Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index'])->name('housingmovements.dashboard');

        Route::get('/agent_time_sheet', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'index'])
            ->name('housingmovements.agentTimeSheetIndex');
        Route::get('/create', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'create'])->name('housingmovements.agentTimeSheet.create');
        Route::post('/submitTmeSheet', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'submitTmeSheet'])->name('housingmovements.agentTimeSheet.submitTmeSheet');
        Route::get('/agentTimeSheetUsers', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'agentTimeSheetUsers'])->name('housingmovements.agentTimeSheetUsers');
        Route::get('/agentTimeSheetGroups', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'agentTimeSheetGroups'])->name('housingmovements.agentTimeSheetGroups');
        Route::get('/timesheet-group/{id}/show', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'showTimeSheet'])->name('housingmovements.agentTimeSheet.showTimeSheet');
        Route::get('time_sheet/edit/{id}', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'editTimeSheet'])->name('housingmovements.agentTimeSheet.editTimeSheet');
        Route::get('agent/time_sheet/deal/{id}', [\Modules\HousingMovements\Http\Controllers\TimeSheetController::class, 'dealTimeSheet'])->name('housingmovements.agentTimeSheet.dealTimeSheet');

        //requests
        Route::get('/hm.requests', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'index'])->name('hm.requests');
        Route::get('/hm.requestsFillter', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'requestsFillter'])->name('hm.requestsFillter');
        Route::post('/hm.returnReq', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'returnReq'])->name('hm.returnReq');
        Route::post('/hm.returnReq.store', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'store'])->name('hm.returnReq.store');
        Route::get('/escalate_requests', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'escalateRequests'])->name('hm.escalate_requests');
        Route::post('/changeEscalateRequestsStatus', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'changeEscalateRequestsStatus'])->name('hm.changeEscalateRequestsStatus');
        Route::get('/viewHmRequest/{requestId}', [\Modules\HousingMovements\Http\Controllers\RequestController::class, 'viewRequest'])->name('viewHmRequest');

        Route::get('/', [\Modules\HousingMovements\Http\Controllers\HousingMovementsController::class, 'index'])->name('housingMovements_landingPage');

        //buildings_management
        Route::get('/buildings', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'index'])->name('buildings');
        Route::get('/createBuilding', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'create'])->name('createBuilding');
        Route::post('/storeBuilding', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'store'])->name('storeBuilding');
        Route::get('/buildings/edit/{buildingId}', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'edit'])->name('building.edit');
        Route::delete('/buildings/{id}', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'destroy'])->name('building.destroy');
        Route::post('/updateBuilding/{buildingId}', [\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'update'])->name('updateBuilding');

        //Rooms
        Route::get('/rooms', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'index'])->name('rooms');
        Route::get('/empty-rooms', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'emptyRooms'])->name('empty-rooms');
        Route::get('/createRoom', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'create'])->name('createRoom');
        Route::post('/storeRoom', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'store'])->name('storeRoom');
        Route::get('/rooms/edit/{roomId}', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'edit'])->name('room.edit');
        Route::delete('/rooms/{id}', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'destroy'])->name('room.destroy');
        Route::post('/updateRoom/{roomId}', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'update'])->name('updateRoom');
        Route::post('/postRoomsData', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'postRoomsData'])->name('postRoomsData');
        Route::post('/getSelectedroomsData', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'getSelectedroomsData'])->name('getSelectedroomsData');
        Route::post('/room_data', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'room_data'])->name('room_data');
        Route::get('/show_rooms/workers/{id}', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'show_room_workers'])->name('show_room_workers');
        Route::get('/getRooms/{id}', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'getRooms'])->name('getRooms');

        Route::post('/leaveRoom', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'leaveRoom'])->name('leaveRoom');
        Route::post('/transfer_from_room', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'transfer_from_room'])->name('transfer_from_room');
        Route::post('/workers_housed', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'workers_housed'])->name('workers_housed');
        Route::post('/get-room-numbers', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'getRoomNumbers'])->name('rooms.numbers');
        Route::post('assign-workers', [\Modules\HousingMovements\Http\Controllers\RoomController::class, 'assignWorkers'])->name('assignWorkers');

        Route::get('/import_rooms', [\Modules\HousingMovements\Http\Controllers\ImportRoomsController::class, 'index'])
            ->name('import_rooms');
        Route::post('/send_import_rooms', [\Modules\HousingMovements\Http\Controllers\ImportRoomsController::class, 'sendImportRooms'])
            ->name('send_import_rooms');
        //facilities
        Route::get('/facilities', [\Modules\HousingMovements\Http\Controllers\FacitityController::class, 'index'])->name('facilities');

        //movement_management
        Route::get('/movement', [\Modules\HousingMovements\Http\Controllers\MovementController::class, 'index'])->name('movement');

        //traveleres
        Route::get('/travelers', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'index'])->name('travelers');
        Route::get('/housed-workers', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'housed_workers_index'])->name('housed_workers');
        Route::get('/workers/index/', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'index'])->name('workers.index');
        Route::get('/workers/show/{id}', [\Modules\Essentials\Http\Controllers\EssentailsworkersController::class, 'show'])->name('htr.show.workers');

        Route::get('/medicalExamination', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'medicalExamination'])->name('medicalExamination');
        Route::post('/upload-medical-document', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'uploadMedicalDocument'])->name('uploadMedicalDocument');

        Route::get('/medicalInsurance', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'medicalInsurance'])->name('medicalInsurance');

        Route::get('/workCardIssuing', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'workCardIssuing'])->name('workCardIssuing');
        Route::post('/storeWorkCard', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'storeWorkCard'])->name('storeWorkCard');

        Route::get('/SIMCard', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'SIMCard'])->name('SIMCard');
        Route::post('/addSIM', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'addSIM'])->name('addSIM');

        Route::get('/bankAccountsForLabors', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'bankAccounts'])->name('bankAccountsForLabors');
        Route::post('/addBank', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'addBank'])->name('addBank');

        Route::get('/QiwaContract', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'QiwaContracts'])->name('QiwaContract');

        Route::get('/residencyPrint', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'residencyPrint'])->name('residencyPrint');
        Route::post('/printResidency', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'updateResidencyPrint'])->name('printResidency');
        Route::post('/addEqama', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'addEqama'])->name('addEqama');

        Route::get('/residencyDelivery', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'residencyDelivery'])->name('residencyDelivery');
        Route::post('/delivery_residency', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'deliveryResidency'])->name('delivery_residency');

        Route::get('/advanceSalaryRequest', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'advanceSalaryRequest'])->name('advanceSalaryRequest');
        Route::post('/newWorkersAdvSalaryStore', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'newWorkersAdvSalaryStore'])->name('newWorkersAdvSalaryStore');

        // Workers
        Route::get('/workers/available-shopping/', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'available_shopping'])->name('workers.available_shopping');
        Route::get('/workers/reserved-shopping/', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'reserved_shopping'])->name('workers.reserved_shopping');
        Route::get('/workers/final-exit/', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'final_exit'])->name('workers.final_exit');
        Route::get('/workers/book/{id}', [\Modules\HousingMovements\Http\Controllers\WorkerBookingController::class, 'create'])->name('worker.book');
        Route::delete('/workers/unbook/{id}', [\Modules\HousingMovements\Http\Controllers\WorkerBookingController::class, 'destroy'])->name('worker.unbook');
        Route::post('/save-book', [\Modules\HousingMovements\Http\Controllers\WorkerBookingController::class, 'store'])->name('worker.book-store');
        Route::get('/create_project_workers', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'create'])->name('create_project_workers');
        Route::post('/store_project_worker', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'storeProjectWorker'])->name('store_project_worker');

        Route::post('/addProject', [\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'addProject'])->name('addProject');

        Route::get('/get-room-numbers/{buildingId}', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getRoomNumbers'])->name('getRoomNumbers');
        Route::post('/housed', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'housed_data'])->name('housed');
        Route::post('/postarrival_data', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'postarrivaldata'])->name('postarrivaldata');
        Route::post('/get-selected-arrived-data', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getSelectedRowsData'])->name('getSelectedArrivalsData');

        Route::get('/get-shifts/{projectId}', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getShifts'])->name('getShifts');
        Route::get('/getBedsCount/{roomId}', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getBedsCount'])->name('getBedsCount');
        Route::get('/room_status', [\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'getRoomNumberOnStatus'])->name('getRoomStatus');
        // Routes Car Types
        Route::get('/cars-type', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'index'])->name('car-types');
        Route::get('/cars-type-create', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'create'])->name('car-type-create');
        Route::get('/cars-type-edit/{id}', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'edit'])->name('cartype.edit');
        Route::post('/cars-type-store', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'store'])->name('car-type-store');
        Route::post('/cars-type-search', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'search'])->name('car-type-search');
        Route::put('/cars-type-update/{id}', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'update'])->name('car-type-update');
        Route::delete('/cars-type-delete/{id}', [\Modules\HousingMovements\Http\Controllers\CarTypeController::class, 'destroy'])->name('cartype.delete');
        // Route::get('/cars-model', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'index'])->name('cars-model');
        // Route Car Models
        Route::get('/cars-model', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'index'])->name('car-models');
        Route::get('/cars-model-create', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'create'])->name('car-model-create');
        Route::get('/cars-model-edit/{id}', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'edit'])->name('carmodel.edit');
        Route::post('/cars-model-store', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'store'])->name('car-model-store');
        Route::post('/cars-model-search', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'search'])->name('car-model-search');
        Route::put('/cars-model-update/{id}', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'update'])->name('car-model-update');
        Route::delete('/cars-model-delete/{id}', [\Modules\HousingMovements\Http\Controllers\CarModelController::class, 'destroy'])->name('carmodel.delete');
        // Routes Cars

        //TODO::importWorkers_newArrival
        Route::get('proposed_laborIndex', [\Modules\HousingMovements\Http\Controllers\HousingMovementsController::class, 'proposed_laborIndex'])->name('proposed_laborIndex');

        Route::get('/importWorkers_newArrival', [\Modules\HousingMovements\Http\Controllers\HousingMovementsController::class, 'importWorkers_newArrival'])->name('importWorkers_newArrival');
        Route::post('/postImportWorkersNewArrival', [\Modules\HousingMovements\Http\Controllers\HousingMovementsController::class, 'postImportWorkersNewArrival'])->name('postImportWorkersNewArrival');

        Route::get('/cars', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'index'])->name('cars');
        Route::get('/cars-create', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'create'])->name('car-create');
        Route::get('/cars-edit/{id}', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'edit'])->name('car.edit');
        Route::post('/cars-store', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'store'])->name('car-store');
        Route::post('/cars-search', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'search'])->name('car-search');
        Route::put('/cars-update/{id}', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'update'])->name('car-update');
        Route::delete('/cars-delete/{id}', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'destroy'])->name('car.delete');
        Route::get('/carModel-by-carType_id/{carType_id}', [\Modules\HousingMovements\Http\Controllers\CarController::class, 'getCarModelByCarType_id'])->name('getCarModelByCarType_id');

        Route::get('/car-drivers', [\Modules\HousingMovements\Http\Controllers\DriverCarController::class, 'index'])->name('cardrivers');
        Route::get('/cardrivers-create', [\Modules\HousingMovements\Http\Controllers\DriverCarController::class, 'create'])->name('cardrivers-create');
        Route::get('/cardrivers-edit/{id}', [\Modules\HousingMovements\Http\Controllers\DriverCarController::class, 'edit'])->name('cardrivers.edit');
        Route::post('/cardrivers-store', [\Modules\HousingMovements\Http\Controllers\DriverCarController::class, 'store'])->name('cardrivers-store');
        Route::post('/cardrivers-search', [\Modules\HousingMovements\Http\Controllers\DriverCarController::class, 'search'])->name('cardrivers-search');
        Route::put('/cardrivers-update/{id}', [\Modules\HousingMovements\Http\Controllers\DriverCarController::class, 'update'])->name('cardrivers-update');
        Route::delete('/cardrivers-delete/{id}', [\Modules\HousingMovements\Http\Controllers\DriverCarController::class, 'destroy'])->name('cardrivers.delete');
        // Route::get('/carModel-by-carType_id/{carType_id}', [\Modules\HousingMovements\Http\Controllers\DriverCarController::class, 'getCarModelByCarType_id'])->name('getCarModelByCarType_id');

    });
});
