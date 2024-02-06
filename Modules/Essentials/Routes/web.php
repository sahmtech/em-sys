<?php

// use App\Http\Controllers\Modules;
// use Illuminate\Support\Facades\Route;


Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu')->group(function () {



    Route::prefix('essentials')->group(function () {
        Route::get('/dashboard', [Modules\Essentials\Http\Controllers\DashboardController::class, 'essentialsDashboard']);
        Route::get('/install', [Modules\Essentials\Http\Controllers\InstallController::class, 'index']);
        Route::get('/install/update', [Modules\Essentials\Http\Controllers\InstallController::class, 'update']);
        Route::get('/install/uninstall', [Modules\Essentials\Http\Controllers\InstallController::class, 'uninstall']);

        Route::get('/', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'index'])->name('essentials_landing');


        Route::get('/leave-status-data', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'getLeaveStatusData'])->name('leaveStatusData');
        Route::get('/getLeaves', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'getLeaves'])->name('getLeaves');
        Route::get('/contract-status-data', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'getContractStatusData'])->name('contractStatusData');
        Route::get('/hr_department_employees', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'hr_department_employees'])->name('hr_department_employees');
    });


    Route::prefix('toDo')->group(function () {


        //todo controller
        Route::resource('todo', 'ToDoController');

        Route::post('todo/add-comment', [Modules\Essentials\Http\Controllers\ToDoController::class, 'addComment']);
        Route::get('todo/delete-comment/{id}', [Modules\Essentials\Http\Controllers\ToDoController::class, 'deleteComment']);
        Route::get('todo/delete-document/{id}', [Modules\Essentials\Http\Controllers\ToDoController::class, 'deleteDocument']);
        Route::post('todo/upload-document', [Modules\Essentials\Http\Controllers\ToDoController::class, 'uploadDocument']);
        Route::get('view-todo-{id}-share-docs', [Modules\Essentials\Http\Controllers\ToDoController::class, 'viewSharedDocs']);
        Route::get('my_todo', [Modules\Essentials\Http\Controllers\ToDoController::class, 'my_todo'])->name('my_todo');

        //reminder controller
        Route::resource('reminder', 'Modules\Essentials\Http\Controllers\ReminderController')->only(['index', 'store', 'edit', 'update', 'destroy', 'show']);

        //message controller
        Route::get('get-new-messages', [Modules\Essentials\Http\Controllers\EssentialsMessageController::class, 'getNewMessages']);
        Route::resource('messages', 'Modules\Essentials\Http\Controllers\EssentialsMessageController')->only(['index', 'store', 'destroy']);

        //document controller
        Route::resource('document', 'Modules\Essentials\Http\Controllers\DocumentController')->only(['index', 'store', 'destroy', 'show']);
        Route::get('document/download/{id}', [Modules\Essentials\Http\Controllers\DocumentController::class, 'download']);

        //document share controller
        Route::resource('document-share', 'Modules\Essentials\Http\Controllers\DocumentShareController')->only(['edit', 'update']);
        //Allowance and deduction controller
        Route::resource('allowance-deduction', 'Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController');

        Route::resource('knowledge-base', 'Modules\Essentials\Http\Controllers\KnowledgeBaseController');

        Route::get('user-sales-targets', [Modules\Essentials\Http\Controllers\DashboardController::class, 'getUserSalesTargets']);
    });


    Route::prefix('work_cards')->group(function () {

        Route::get('/getBusiness', [\App\Http\Controllers\BusinessController::class, 'getBusiness'])->name('getBusiness');

        Route::get('/work_cards_department_employees', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'work_cards_department_employees'])->name('work_cards_department_employees');
        Route::get('/business.view/{id}', [\App\Http\Controllers\BusinessController::class, 'show'])->name('business.view');
        Route::get('/business_documents.view/{id}', [\App\Http\Controllers\BusinessDocumentController::class, 'show'])->name('business_documents.view');
        Route::post('/storeBusiness', [\App\Http\Controllers\BusinessController::class, 'store'])->name('storeBusiness');
        Route::post('/storeBusinessDoc', [\App\Http\Controllers\BusinessDocumentController::class, 'store'])->name('storeBusinessDoc');
        Route::delete('/doc/{id}', [\App\Http\Controllers\BusinessDocumentController::class, 'destroy'])->name('doc.destroy');
        Route::get('/doc/{id}', [\App\Http\Controllers\BusinessDocumentController::class, 'edit'])->name('doc.edit');
        Route::put('/doc/{id}', [\App\Http\Controllers\BusinessDocumentController::class, 'update'])->name('doc.update');
        Route::get('/business_subscriptions.view/{id}', [\App\Http\Controllers\BusinessSubscriptionController::class, 'show'])->name('business_subscriptions.view');
        Route::post('/storeBusinessSubscription', [\App\Http\Controllers\BusinessSubscriptionController::class, 'store'])->name('storeBusinessSubscription');
        Route::delete('/business_subscriptions/{id}', [\App\Http\Controllers\BusinessSubscriptionController::class, 'destroy'])->name('busSubscription.destroy');
        Route::post('/check-username', [\App\Http\Controllers\BusinessController::class, 'checkUsername'])->name('check-username');


        Route::get('/cards', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'index'])->name('cards');
        Route::get('card/get-residency-data', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'getResidencyData'])->name('getResidencyData');
        Route::get('/cards/create', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'create'])->name('create.Cards');
        Route::post('/cards/store', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'store'])->name('card.store');
        Route::get('/get-responsible-data', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'get_responsible_data'])->name('get_responsible_data');
        Route::post('/post_renew_data', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'postRenewData'])->name('postRenewData');
        Route::post('/get_selected_workcards_data', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'getSelectedRowsData'])->name('getSelectedworkcardData');
        Route::get('/get_residency_report',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'residencyreports'])->name('getResidencyreport');


        Route::get('/work_cards_dashboard', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'word_cards_dashboard'])->name('essentials_word_cards_dashboard');
        Route::get('/work_cards_all_requests',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'work_cards_all_requests'])->name('work_cards_all_requests');
        Route::get('/work_cards_vaction_requests',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'work_cards_vaction_requests'])->name('work_cards_vaction_requests');
        Route::get('/work_cards_operation',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'work_cards_operation'])->name('work_cards_operation');
        Route::post('/post_return_visa_data',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'post_return_visa_data'])->name('post_return_visa_data');
        Route::post('/post_final_visa_data',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'post_final_visa_data'])->name('post_final_visa_data');
        Route::post('/post_absent_report_data',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'post_absent_report_data'])->name('post_absent_report_data');
        Route::post('/Wk_storeRequest', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'storeRequest'])->name('Wk_storeRequest');


        Route::get('/workers', [\Modules\Essentials\Http\Controllers\EssentialsWorkCardsWorkerController::class, 'index'])->name('work_cards-workers');
        Route::get('/workers/{id}', [\Modules\Essentials\Http\Controllers\EssentialsWorkCardsWorkerController::class, 'show'])->name('work_cards-showWorker');

        Route::get('/viewWorkCardsRequest/{requestId}', [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'viewRequest'])->name('viewWorkCardsRequest');



        Route::get('/expired_residencies',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'expired_residencies'])->name('expired.residencies');
        Route::get('/all_expired_residencies',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'all_expired_residencies'])->name('all.expired.residencies');
        Route::get('/late_for_vacation',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'late_for_vacation'])->name('late_for_vacation');
        Route::get('/final_visa_index',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'final_visa'])->name('final_visa_index');

        Route::get('/operations_show_employee/{id}',  [\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'operations_show_employee'])->name('operations_show_employee');
    });


    Route::prefix('employee_affairs')->group(function () {
        Route::get('/employee_affairs_dashboard', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'employee_affairs_dashboard'])->name('employee_affairs_dashboard');
        Route::get('/finsish_contract_duration', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'finsish_contract_duration'])->name('finsish_contract_duration');
        Route::get('/finish_contracts', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'finish_contracts'])->name('finish_contracts');
        Route::get('/uncomplete_profiles', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'uncomplete_profiles'])->name('uncomplete_profiles');
        Route::get('/late_admission', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'late_admission'])->name('late_admission');

        Route::get('/employees', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'index'])->name('employees');
        Route::get('/createEmployee', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'create'])->name('createEmployee');
        Route::post('/storeEmployee', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'store'])->name('storeEmployee');
        Route::put('/updateEmployeeProfilePicture/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'updateEmployeeProfilePicture'])->name('updateEmployeeProfilePicture');
        Route::get('/editEmployee/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'edit'])->name('editEmployee');
        Route::get('/employees/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'show'])->name('showEmployee');
        Route::put('/updateEmployee/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'update'])->name('updateEmployee');


        Route::get('/employee_affairs_department_employees', [Modules\Essentials\Http\Controllers\EssentialsController::class, 'employee_affairs_department_employees'])->name('employee_affairs_department_employees');
        //workers
        Route::get('/workers_affairs', [\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'index'])->name('workers_affairs');
        Route::get('/show_workers_affairs/{id}', [\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'show'])->name('show_workers_affairs');
        Route::get('/add_workers_affairs', [\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'create'])->name('add_workers_affairs');
        Route::post('/store-worker-affairs', [\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'store'])->name('store-worker-affairs');
        Route::put('/updateWorkerProfilePicture/{id}', [\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'updateWorkerProfilePicture'])->name('updateWorkerProfilePicture');
        Route::get('/editWorker/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'edit'])->name('editWorker');
        Route::put('/updateWorker/{id}', [\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'update'])->name('updateWorker');

        Route::get('/import-employees', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class, 'index'])->name('import-employees');
        Route::post('/send-employee-file', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class, 'postImportEmployee'])->name('send-employee-file');
        Route::post('/send-update-employee-file', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeUpdateImportController::class, 'postImportupdateEmployee'])->name('send-update-employee-file');



        Route::get('/appointments', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'index'])->name('appointments');
        Route::post('/changeStatusApp', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'changeStatus'])->name('changeStatusApp');
        Route::post('/storeAppointment', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'store'])->name('storeAppointment');
        Route::delete('/appointments/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'destroy'])->name('appointment.destroy');
        Route::get('/appointments.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'show'])->name('appointment.view');
        Route::put('/updateAppointment/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'update'])->name('updateAppointment');
        Route::get('/appointments/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'edit'])->name('appointment.edit');
        Route::post('/change_activity/{appointmentId}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeAppointmentController::class, 'change_activity'])->name('change_activity');


        Route::get('/admissionToWork', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'index'])->name('admissionToWork');
        Route::post('/storeAdmissionToWork', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'store'])->name('storeAdmissionToWork');
        Route::delete('/admissionToWork/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'destroy'])->name('admissionToWork.destroy');
        Route::get('/admissionToWork.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'show'])->name('admissionToWork.view');
        Route::put('/updateAdmissionToWork/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'update'])->name('updateAdmissionToWork');
        Route::get('/admissionToWork/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'edit'])->name('admissionToWork.edit');
        Route::post('/change_admission_activity/{admissionId}', [\Modules\Essentials\Http\Controllers\EssentialsAdmissionToWorkController::class, 'change_admission_activity'])->name('change_admission_activity');


        Route::get('/employeeContracts', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'index'])->name('employeeContracts');
        Route::post('/storeEmployeeContract', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'store'])->name('storeEmployeeContract');
        Route::delete('/employeeContracts/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'destroy'])->name('employeeContract.destroy');
        Route::get('/employeeContracts.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'show'])->name('employeeContract.view');
        Route::put('/updateEmployeeContract/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'update'])->name('updateEmployeeContract');
        Route::get('/employeeContracts/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeContractController::class, 'edit'])->name('employeeContract.edit');



        Route::get('/qualifications', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'index'])->name('qualifications');
        Route::post('/storeQualification', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'store'])->name('storeQualification');
        Route::delete('/qualifications/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'destroy'])->name('qualification.destroy');
        Route::get('/qualifications.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'show'])->name('qualification.view');
        Route::post('/updateQualification/{qualificationId}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'updateQualification'])->name('updateQualification');
        Route::get('/qualifications/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'edit'])->name('qualification.edit');
        Route::put('/updateEmployeeQualificationAttachement/{user_id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeQualificationController::class, 'updateEmployeeQualificationAttachement'])->name('updateEmployeeQualificationAttachement');


        Route::get('/official_documents', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'index'])->name('official_documents');
        Route::post('/storeOfficialDoc', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'store'])->name('storeOfficialDoc');
        Route::delete('/official_documents/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'destroy'])->name('offDoc.destroy');
        Route::get('/official_documents.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'show'])->name('doc.view');
        Route::post('/updateDoc', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'update'])->name('updateDoc');
        Route::get('/official_documents/edit/{docId}', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'edit'])->name('official_documents.edit');
        Route::post('/storeDocFile', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'storeDocFile'])->name('storeDocFile');
        Route::post('/updateEmployeeOfficalDocuments', [\Modules\Essentials\Http\Controllers\EssentialsOfficialDocumentController::class, 'updateEmployeeOfficalDocuments'])->name('updateEmployeeOfficalDocuments');

        Route::get('/employee_families', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFamilyController::class, 'index'])->name('employee_families');
        Route::post('/storeEmployeeFamily', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFamilyController::class, 'store'])->name('storeEmployeeFamily');
        Route::delete('/employee_families/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFamilyController::class, 'destroy'])->name('employee_families.destroy');
        Route::get('/employee_families.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFamilyController::class, 'show'])->name('employee_families.view');
        Route::put('/updateEmployeeFamily/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFamilyController::class, 'update'])->name('updateEmployeeFamily');
        Route::get('/employee_families/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFamilyController::class, 'edit'])->name('employee_families.edit');
        Route::get('/import-employees-familiy', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFamilyController::class, 'import_index'])->name('import-employees-familiy');
        Route::post('/send-employee-familiy-file', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFamilyController::class, 'familypostImportEmployee'])->name('send-employee-familiy-file');

        Route::get('featureIndex', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'featureIndex'])->name('featureIndex');
        Route::post('storeUserAllowance', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'storeUserAllowance'])->name('storeUserAllowance');
        Route::get('/featureIndex/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'editAllowance'])->name('employee_allowance.edit');
        Route::delete('/featureIndex/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'destroyAllowance'])->name('employee_allowance.destroy');
        Route::put('/updateUserAllowance/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceAndDeductionController::class, 'updateAllowance'])->name('updateUserAllowance');

        Route::get('userTravelCat', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'userTravelCat'])->name('userTravelCat');
        Route::post('storeUserTravelCat', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'storeUserTravelCat'])->name('storeUserTravelCat');
        Route::delete('/userTravelCat/{id}', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'destroyUserTravelCat'])->name('userTravelCat.destroy');

        Route::get('/allEmployeeAffairsRequests', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'employee_affairs_all_requests'])->name('allEmployeeAffairsRequests');
        Route::get('/viewEmployeeAffairsRequest/{requestId}', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'viewEmployeeAffairsRequest'])->name('viewEmployeeAffairsRequest');
        Route::get('/viewEmployeeAffRequest/{requestId}', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'viewEmployeeAffRequest'])->name('viewEmployeeAffRequest');
        Route::post('/storeEmployeeAffairsRequest', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'storeEmployeeAffairsRequest'])->name('storeEmployeeAffairsRequest');
    });

    Route::prefix('medicalInsurance')->group(function () {

        Route::get('/insurance-dashbord', [\Modules\Essentials\Http\Controllers\InsuranceDashbordConrollerController::class, 'index'])->name('insurance-dashbord');
        Route::get('/employee_insurance', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'index'])->name('employee_insurance');
        Route::post('/employee_insurance.store', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'store'])->name('employee_insurance.store');
        Route::delete('/employee_insurance/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'destroy'])->name('employee_insurance.destroy');
        Route::get('/employee_insurance.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'show'])->name('employee_insurance.view');
        Route::put('/updateInsuranceContract/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'update'])->name('updateInsuranceContract');
        Route::get('/employee_insurance/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'edit'])->name('employee_insurance.edit');
        Route::post('/classes', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'fetchClasses'])->name('classes');
        Route::get('/employee_insurance/edit/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'edit'])->name('employee_insurance.edit');
        Route::post('/updateInsurance/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'update'])->name('updateInsurance');



        Route::get('/import_employees_insurance', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'import_employee_insurance_index'])->name('import_employees_insurance');
        Route::post('/send_import_employee_insurance', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'insurancepostImportEmployee'])->name('send_import_employee_insurance');
        Route::post('/send_import_update_employee_insurance', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeInsuranceController::class, 'insurancepostUpdateImportEmployee'])
            ->name('send_import_update_employee_insurance');



        Route::get('/insurance_contracts', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'index'])->name('insurance_contracts');
        Route::post('/insurance_contracts.store', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'store'])->name('insurance_contracts.store');
        Route::delete('/insurance_contracts/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'destroy'])->name('insurance_contracts.destroy');
        Route::get('/insurance_contracts.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'show'])->name('insurance_contracts.view');
        Route::put('/updateInsuranceContract/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'update'])->name('updateInsuranceContract');
        Route::get('/insurance_contracts/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'edit'])->name('insurance_contracts.edit');

        Route::get('/insurance_companies', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'index'])->name('insurance_companies');
        Route::post('/insurance_companies.store', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'store'])->name('insurance_companies.store');
        Route::delete('/insurance_companies/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'destroy'])->name('insurance_companies.destroy');
        Route::get('/insurance_companies.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'show'])->name('insurance_companies.view');


        Route::get('/insurance_requests',  [\Modules\Essentials\Http\Controllers\InsuranceRequestController::class, 'index'])->name('insurance_requests');
        Route::post('/insurance_storeRequest', [\Modules\Essentials\Http\Controllers\InsuranceRequestController::class, 'store'])->name('insurance_storeRequest');


        Route::get('/insurance_categories', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCategoryController::class, 'index'])->name('insurance_categories');
        Route::post('/insurance_categories.store', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCategoryController::class, 'store'])->name('insurance_categories.store');
        Route::delete('/insurance_categories/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCategoryController::class, 'destroy'])->name('insurance_categories.destroy');
        Route::get('/insurance_categories.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsInsuranceCategoryController::class, 'show'])->name('insurance_categories.view');

        Route::get('/workers', [\Modules\Essentials\Http\Controllers\EssentialsWorkerController::class, 'index'])->name('insurance-workers');
        Route::get('/workers/{id}', [\Modules\Essentials\Http\Controllers\EssentialsWorkerController::class, 'show'])->name('insurance-showWorker');



        Route::get('/get_companies_insurance_contracts', [\Modules\Essentials\Http\Controllers\EssentialCompaniesInsuranceContractsController::class, 'index'])
            ->name('get_companies_insurance_contracts');

        Route::get('/insurance_companies_contracts/edit/{id}', [\Modules\Essentials\Http\Controllers\EssentialCompaniesInsuranceContractsController::class, 'edit'])
            ->name('insurance_companies_contracts.edit');

        Route::post('/insurance_companies_contracts/update/{id}', [\Modules\Essentials\Http\Controllers\EssentialCompaniesInsuranceContractsController::class, 'update'])
            ->name('insurance_companies_contracts.update');

        Route::delete('/insurance_companies_contracts/delete/{id}', [\Modules\Essentials\Http\Controllers\EssentialCompaniesInsuranceContractsController::class, 'destroy'])
            ->name('insurance_companies_contracts.delete');
    });


    Route::prefix('hrm')->group(function () {

        Route::get('/get-essentials-workers',  [Modules\Essentials\Http\Controllers\EssentailsworkersController::class, 'index'])->name('get-essentials-workers');
        Route::get('/show-essentials-workers/{id}', [\Modules\Essentials\Http\Controllers\EssentailsworkersController::class, 'show'])->name('show-essentials-workers');

        Route::get('/get-amount/{salaryType}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'getAmount'])->name('get-amount');
        Route::get('/dashboard', [Modules\Essentials\Http\Controllers\DashboardController::class, 'hrmDashboard'])->name('hrmDashboard');
        Route::resource('/leave-type', 'Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController');
        Route::resource('/leave', 'Modules\Essentials\Http\Controllers\EssentialsLeaveController');
        Route::post('/change-status', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'changeStatus'])->name('changeLeavStatus');
        Route::get('/leave/activity/{id}', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'activity']);
        Route::get('/user-leave-summary', [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'getUserLeaveSummary']);
        Route::get('/get-admission-date',  [Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'getAdmissionDate'])->name('get-admission-date');


        Route::get('/fetch-user/{id}', [\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'fetch_user'])->name('fetch_user');
        Route::get('/settings', [Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'edit']);
        Route::post('/settings', [Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'update']);

        Route::get('/countries', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'index'])->name('countries');
        Route::get('/createCountry', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'create'])->name('createCountry');
        Route::post('/storeCountry', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'store'])->name('storeCountry');
        Route::get('/countries/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'edit'])->name('country.edit');
        Route::delete('/countries/{id}', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'destroy'])->name('country.destroy');
        Route::put('/updateCountry/{id}', [\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'update'])->name('updateCountry');


        Route::get('/attendanceStatus', [\Modules\Essentials\Http\Controllers\AttendanceStatusController::class, 'index'])->name('attendanceStatus');
        Route::post('/storeAttendanceStatus', [\Modules\Essentials\Http\Controllers\AttendanceStatusController::class, 'store'])->name('storeAttendanceStatus');
        Route::delete('/attendanceStatus/{id}', [\Modules\Essentials\Http\Controllers\AttendanceStatusController::class, 'destroy'])->name('attendanceStatus.destroy');
        Route::put('/updateAttendanceStatus/{id}', [\Modules\Essentials\Http\Controllers\AttendanceStatusController::class, 'update'])->name('updateAttendanceStatus');
        Route::get('/editAttendanceStatus/{id}', [\Modules\Essentials\Http\Controllers\AttendanceStatusController::class, 'edit'])->name('editAttendanceStatus');




        Route::get('/contract_types', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'index'])->name('contract_types');
        Route::get('/createContractType', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'create'])->name('createContractType');
        Route::post('/storeContractType', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'store'])->name('storeContractType');
        Route::get('/contract_types/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'edit'])->name('contractType.edit');
        Route::delete('/contract_types/{id}', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'destroy'])->name('contractType.destroy');
        Route::put('/updateContractType/{id}', [\Modules\Essentials\Http\Controllers\EssentialsContractTypeController::class, 'update'])->name('updateContractType');


        Route::post('/storeProfession', [\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class, 'store'])->name('storeProfession');
        Route::get('/professions', [\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class, 'index'])->name('professions');
        Route::delete('/professions/{id}', [\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class, 'destroy'])->name('profession.destroy');

        Route::post('/storeAcademicSpecializations', [\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class, 'storeAcademicSpecializations'])->name('storeAcademicSpecializations');
        Route::get('/academic_specializations', [\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class, 'academic_specializations'])->name('academic_specializations');
        Route::delete('/academic_specializations/{id}', [\Modules\Essentials\Http\Controllers\EssentialsProfessionController::class, 'destroy'])->name('academic_specializations.destroy');


        Route::get('/cities', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'index'])->name('cities');
        Route::get('/createCity', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'create'])->name('createCity');
        Route::post('/storeCity', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'store'])->name('storeCity');
        Route::get('/cities/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'edit'])->name('city.edit');
        Route::delete('/cities/{id}', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'destroy'])->name('city.destroy');
        Route::put('/updateCity/{id}', [\Modules\Essentials\Http\Controllers\EssentialsCityController::class, 'update'])->name('updateCity');

        Route::get('/regions', [\Modules\Essentials\Http\Controllers\EssentialsRegionController::class, 'index'])->name('regions');
        Route::get('/createRegion', [\Modules\Essentials\Http\Controllers\EssentialsRegionController::class, 'create'])->name('createRegion');
        Route::post('/storeRegion', [\Modules\Essentials\Http\Controllers\EssentialsRegionController::class, 'store'])->name('storeRegion');
        Route::get('/regions/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsRegionController::class, 'edit'])->name('region.edit');
        Route::delete('/regions/{id}', [\Modules\Essentials\Http\Controllers\EssentialsRegionController::class, 'destroy'])->name('region.destroy');
        Route::put('/updateRegion/{id}', [\Modules\Essentials\Http\Controllers\EssentialsRegionController::class, 'update'])->name('updateRegion');

        Route::get('/organizations', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'index'])->name('organizations');
        Route::get('/createOrganization', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'create'])->name('createOrganization');
        Route::post('/storeOrganization', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'store'])->name('storeOrganization');
        Route::get('/organizations/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'edit'])->name('organization.edit');
        Route::delete('/organizations/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'destroy'])->name('organization.destroy');
        Route::put('/updateOrganization/{id}', [\Modules\Essentials\Http\Controllers\EssentialsOrganizationController::class, 'update'])->name('updateOrganization');





        Route::get('/job_titles', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'index'])->name('job_titles');
        Route::get('/createJob_title', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'create'])->name('createJob_title');
        Route::post('/storeJob_title', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'store'])->name('storeJob_title');
        Route::get('/job_titles/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'edit'])->name('job_title.edit');
        Route::delete('/job_titles/{id}', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'destroy'])->name('job_title.destroy');
        Route::put('/updateJob_title/{id}', [\Modules\Essentials\Http\Controllers\EssentialsJobTitleController::class, 'update'])->name('updateJob_title');

        Route::get('/bank_accounts', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'index'])->name('bank_accounts');
        Route::get('/createBank_account', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'create'])->name('createBank_account');
        Route::post('/storeBank_account', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'store'])->name('storeBank_account');
        Route::get('/bank_accounts/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'edit'])->name('bank_account.edit');
        Route::delete('/bank_accounts/{id}', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'destroy'])->name('bank_account.destroy');
        Route::put('/updateBank_account/{id}', [\Modules\Essentials\Http\Controllers\EssentialsBankAccountController::class, 'update'])->name('updateBank_account');

        Route::get('/travel_categories', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'index'])->name('travel_categories');
        Route::get('/createTravel_categorie', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'create'])->name('createTravel_categorie');
        Route::post('/storeTravel_categorie', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'store'])->name('storeTravel_categorie');
        Route::get('/travel_categories/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'edit'])->name('travel_categorie.edit');
        Route::delete('/travel_categories/{id}', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'destroy'])->name('travel_categorie.destroy');
        Route::put('/updateTravel_categorie/{id}', [\Modules\Essentials\Http\Controllers\EssentialsTravelCategorieController::class, 'update'])->name('updateTravel_categorie');

        Route::get('/basic_salary_types', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'index'])->name('basic_salary_types');
        Route::get('/createBasicSalary', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'create'])->name('createBasicSalary');
        Route::post('/storeBasicSalary', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'store'])->name('storeBasicSalary');
        Route::get('/basic_salary_types/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'edit'])->name('BasicSalary.edit');
        Route::delete('/basic_salary_types/{id}', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'destroy'])->name('BasicSalary.destroy');
        Route::put('/updateBasicSalary/{id}', [\Modules\Essentials\Http\Controllers\EssentialsBasicSalayController::class, 'update'])->name('updateBasicSalary');

        Route::get('/entitlements', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'index'])->name('entitlements');
        Route::get('/createEntitlement', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'create'])->name('createEntitlement');
        Route::post('/storeEntitlement', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'store'])->name('storeEntitlement');
        Route::get('/entitlements/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'edit'])->name('Entitlement.edit');
        Route::delete('/entitlements/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'destroy'])->name('Entitlement.destroy');
        Route::put('/updateEntitlement/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEntitlementController::class, 'update'])->name('updateEntitlement');

        Route::get('/allowances', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'index'])->name('allowances');
        Route::get('/createAllowance', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'create'])->name('createAllowance');
        Route::post('/storeAllowance', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'store'])->name('storeAllowance');
        Route::get('/allowances/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'edit'])->name('Allowance.edit');
        Route::delete('/allowances/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'destroy'])->name('Allowance.destroy');
        Route::put('/updateAllowance/{id}', [\Modules\Essentials\Http\Controllers\EssentialsAllowanceController::class, 'update'])->name('updateAllowance');






        Route::get('/features', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'index'])->name('features');
        Route::post('/storeFeatures', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'store'])->name('storeFeatures');
        Route::delete('/features/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'destroy'])->name('feature.destroy');
        Route::get('/features.view/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'show'])->name('feature.view');
        Route::put('/updateFeatures/{id}', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'update'])->name('updateFeatures');
        Route::get('/features/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsEmployeeFeatureController::class, 'edit'])->name('feature.edit');





        Route::post('/import-attendance', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'importAttendance']);
        Route::resource('/attendance', 'Modules\Essentials\Http\Controllers\AttendanceController');
        Route::post('/clock-in-clock-out', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'clockInClockOut']);

        Route::post('/validate-clock-in-clock-out', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'validateClockInClockOut']);

        Route::get('/get-attendance-by-shift', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceByShift']);
        Route::get('/get-attendance-by-date', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceByDate']);
        Route::get('/get-attendance-row/{user_id}', [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getAttendanceRow']);

        Route::get(
            '/user-attendance-summary',
            [Modules\Essentials\Http\Controllers\AttendanceController::class, 'getUserAttendanceSummary']
        );

        Route::get('/location-employees', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getEmployeesBasedOnLocation']);
        Route::get('/my-payrolls', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getMyPayrolls']);
        Route::get('/get-allowance-deduction-row', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getAllowanceAndDeductionRow']);
        Route::get('/payroll-group-datatable', [Modules\Essentials\Http\Controllers\PayrollController::class, 'payrollGroupDatatable']);
        Route::get('/view/{id}/payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'viewPayrollGroup']);
        Route::get('/edit/{id}/payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getEditPayrollGroup']);
        Route::post('/update-payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'getUpdatePayrollGroup']);
        Route::get('/payroll-group/{id}/add-payment', [Modules\Essentials\Http\Controllers\PayrollController::class, 'addPayment']);
        Route::post('/post-payment-payroll-group', [Modules\Essentials\Http\Controllers\PayrollController::class, 'postAddPayment']);
        Route::resource('/payroll', 'Modules\Essentials\Http\Controllers\PayrollController');
        Route::resource('/holiday', 'EssentialsHolidayController');

        Route::get('/shift/assign-users/{shift_id}', [Modules\Essentials\Http\Controllers\ShiftController::class, 'getAssignUsers']);
        Route::post('/shift/assign-users', [Modules\Essentials\Http\Controllers\ShiftController::class, 'postAssignUsers']);
        Route::resource('/shift', 'Modules\Essentials\Http\Controllers\ShiftController');
        Route::get('/sales-target', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'index']);
        Route::get('/set-sales-target/{id}', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'setSalesTarget']);
        Route::post('/save-sales-target', [Modules\Essentials\Http\Controllers\SalesTargetController::class, 'saveSalesTarget']);












        Route::get('/my_requests', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'my_requests'])->name('my_requests');
        Route::get('/viewHrRequest/{requestId}', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'viewRequest'])->name('viewHrRequest');
        Route::post('/ess_change-status', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'changeStatus'])->name('ess_changeStatus');
        Route::post('/ess_returnReq', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'returnReq'])->name('ess_returnReq');
        Route::post('/storeEssentialRequest', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'store'])->name('storeEssentialRequest');
        Route::get('/allEssentialsRequests', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'requests'])->name('allEssentialsRequests');
        Route::get('/escalate_requests', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'escalateRequests'])->name('essentials.escalate_requests');
        Route::post('/changeEscalateRequestsStatus', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'changeEscalateRequestsStatus'])->name('essentials.changeEscalateRequestsStatus');


        //reports

        Route::get('/employess-info-report', [\Modules\Essentials\Http\Controllers\EssentialsReportController::class, 'index'])->name('employess-info-report');

        //contracts finish reasons
        Route::get('/contracts-finish-reasons', [\Modules\Essentials\Http\Controllers\EssentialsContractsFinishReasonsController::class, 'index'])->name('contracts_finish_reasons');
        Route::get('/contracts-finish-reasons/create', [\Modules\Essentials\Http\Controllers\EssentialsContractsFinishReasonsController::class, 'create'])->name('create-contracts-finish-reasons');
        Route::post('/contracts-finish-reasons/store', [\Modules\Essentials\Http\Controllers\EssentialsContractsFinishReasonsController::class, 'store'])->name('store_finish_reasons');
        Route::delete('/contracts-finish-reasons/delete/{id}', [\Modules\Essentials\Http\Controllers\EssentialsContractsFinishReasonsController::class, 'destroy'])->name('finish_contract.destroy');

        Route::get('/cancel_contract_requests', [\Modules\Essentials\Http\Controllers\EssentialsCancelContractsController::class, 'index'])->name('cancel_contract_requests');
        Route::get('/finish_contract_procedure/{requestId}', [\Modules\Essentials\Http\Controllers\EssentialsCancelContractsController::class, 'finish_contract_procedure'])->name('finish_contract_procedure');



        Route::get('/wishes', [\Modules\Essentials\Http\Controllers\EssentialsWishesController::class, 'index'])->name('wishes');
        Route::post('/wish/store', [\Modules\Essentials\Http\Controllers\EssentialsWishesController::class, 'store'])->name('store_wish');
        Route::get('/wishes/{id}/edit', [\Modules\Essentials\Http\Controllers\EssentialsWishesController::class, 'edit'])->name('wishes.edit');
        Route::post('/wishes/{id}/update', [\Modules\Essentials\Http\Controllers\EssentialsWishesController::class, 'update'])->name('update_wish');
        Route::delete('/wish/delete/{id}', [\Modules\Essentials\Http\Controllers\EssentialsWishesController::class, 'destroy'])->name('wish.destroy');

        Route::get('/search/byproof', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'search'])->name('hrm.search_proofname');
        Route::post('/save-attachment/{requestId}',  [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'saveAttachment'])->name('saveAttachment');



        //Recuirements Requests
        Route::get('/get-recuirements-requests', [\Modules\Essentials\Http\Controllers\RecuirementsRequestsController::class, 'index'])->name('get-recuirements-requests');
        Route::get('/accepted-recuirements-requests', [\Modules\Essentials\Http\Controllers\RecuirementsRequestsController::class, 'acceptedRequestIndex'])->name('accepted-recuirements-requests');
        Route::get('/unaccepted-recuirements-requests', [\Modules\Essentials\Http\Controllers\RecuirementsRequestsController::class, 'unacceptedRequestIndex'])->name('unaccepted-recuirements-requests');

        Route::post('/requirement-request-changeStatus', [\Modules\Essentials\Http\Controllers\RecuirementsRequestsController::class, 'changeStatus'])->name('requirement-request-changeStatus');
    });


    Route::prefix('movment')->group(function () {
        Route::get('/dashboard-movment', [Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'index']);
        Route::get('/latest-change-oil', [Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'latestChangeOil'])->name('essentials.latest-change-oil');
        Route::get('/latest-form', [Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'latestForm'])->name('essentials.latest-form');
        Route::get('/latest-maintenances', [Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'latestMaintenances'])->name('essentials.latest-maintenances');
        Route::get('/latest-insurance', [Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'latestInsurance'])->name('essentials.latest-insurances');
        // Movments
        // Routes Car Types
        Route::get('/cars-type', [\Modules\Essentials\Http\Controllers\CarTypeController::class, 'index'])->name('essentials.car-types');
        Route::get('/cars-type-create', [\Modules\Essentials\Http\Controllers\CarTypeController::class, 'create'])->name('essentials.car-type-create');
        Route::get('/cars-type-edit/{id}', [\Modules\Essentials\Http\Controllers\CarTypeController::class, 'edit'])->name('essentials.cartype.edit');
        Route::post('/cars-type-store', [\Modules\Essentials\Http\Controllers\CarTypeController::class, 'store'])->name('essentials.car-type-store');
        Route::post('/cars-type-search', [\Modules\Essentials\Http\Controllers\CarTypeController::class, 'search'])->name('essentials.car-type-search');
        Route::put('/cars-type-update/{id}', [\Modules\Essentials\Http\Controllers\CarTypeController::class, 'update'])->name('essentials.car-type-update');
        Route::delete('/cars-type-delete/{id}', [\Modules\Essentials\Http\Controllers\CarTypeController::class, 'destroy'])->name('essentials.cartype.delete');
        // Route::get('/cars-model', [\Modules\Essentials\Http\Controllers\CarModelController::class, 'index'])->name('cars-model');


        Route::get('/cars-insurance', [\Modules\Essentials\Http\Controllers\CarInsuranceController::class, 'index'])->name('essentials.car-insurance');
        Route::get('/cars-insurance-create/{id}', [\Modules\Essentials\Http\Controllers\CarInsuranceController::class, 'create'])->name('essentials.car-insurance-create');
        Route::get('/cars-insurance-edit/{id}', [\Modules\Essentials\Http\Controllers\CarInsuranceController::class, 'edit'])->name('essentials.car.insurance.edit');
        Route::post('/cars-insurance-store', [\Modules\Essentials\Http\Controllers\CarInsuranceController::class, 'store'])->name('essentials.carinsurance-store');
        Route::put('/cars-insurance-update/{id}', [\Modules\Essentials\Http\Controllers\CarInsuranceController::class, 'update'])->name('essentials.car-insurance-update');
        Route::delete('/cars-insurance-delete/{id}', [\Modules\Essentials\Http\Controllers\CarInsuranceController::class, 'destroy'])->name('essentials.carinsurance.delete');

        // Route Car Models
        Route::get('/cars-model', [\Modules\Essentials\Http\Controllers\CarModelController::class, 'index'])->name('essentials.car-models');
        Route::get('/cars-model-create', [\Modules\Essentials\Http\Controllers\CarModelController::class, 'create'])->name('essentials.car-model-create');
        Route::get('/cars-model-edit/{id}', [\Modules\Essentials\Http\Controllers\CarModelController::class, 'edit'])->name('essentials.carmodel.edit');
        Route::post('/cars-model-store', [\Modules\Essentials\Http\Controllers\CarModelController::class, 'store'])->name('essentials.car-model-store');
        Route::post('/cars-model-search', [\Modules\Essentials\Http\Controllers\CarModelController::class, 'search'])->name('essentials.car-model-search');
        Route::put('/cars-model-update/{id}', [\Modules\Essentials\Http\Controllers\CarModelController::class, 'update'])->name('essentials.car-model-update');
        Route::delete('/cars-model-delete/{id}', [\Modules\Essentials\Http\Controllers\CarModelController::class, 'destroy'])->name('essentials.carmodel.delete');
        // Routes Cars

        Route::get('/cars', [\Modules\Essentials\Http\Controllers\CarController::class, 'index'])->name('essentials.cars');
        Route::get('/cars-create', [\Modules\Essentials\Http\Controllers\CarController::class, 'create'])->name('essentials.car-create');
        Route::get('/cars-edit/{id}', [\Modules\Essentials\Http\Controllers\CarController::class, 'edit'])->name('essentials.car.edit');
        Route::post('/cars-store', [\Modules\Essentials\Http\Controllers\CarController::class, 'store'])->name('essentials.car-store');
        Route::post('/cars-search', [\Modules\Essentials\Http\Controllers\CarController::class, 'search'])->name('essentials.car-search');
        Route::put('/cars-update/{id}', [\Modules\Essentials\Http\Controllers\CarController::class, 'update'])->name('essentials.car-update');
        Route::delete('/cars-delete/{id}', [\Modules\Essentials\Http\Controllers\CarController::class, 'destroy'])->name('essentials.car.delete');
        Route::get('/carModel-by-carType_id/{carType_id}', [\Modules\Essentials\Http\Controllers\CarController::class, 'getCarModelByCarType_id'])->name('essentials.getCarModelByCarType_id');



        Route::get('/car-drivers', [\Modules\Essentials\Http\Controllers\DriverCarController::class, 'index'])->name('essentials.cardrivers');
        Route::get('/cardrivers-create', [\Modules\Essentials\Http\Controllers\DriverCarController::class, 'create'])->name('essentials.cardrivers-create');
        Route::get('/cardrivers-edit/{id}', [\Modules\Essentials\Http\Controllers\DriverCarController::class, 'edit'])->name('essentials.cardrivers.edit');
        Route::post('/cardrivers-store', [\Modules\Essentials\Http\Controllers\DriverCarController::class, 'store'])->name('essentials.cardrivers-store');
        Route::post('/cardrivers-search', [\Modules\Essentials\Http\Controllers\DriverCarController::class, 'search'])->name('essentials.cardrivers-search');
        Route::put('/cardrivers-update/{id}', [\Modules\Essentials\Http\Controllers\DriverCarController::class, 'update'])->name('essentials.cardrivers-update');
        Route::delete('/cardrivers-delete/{id}', [\Modules\Essentials\Http\Controllers\DriverCarController::class, 'destroy'])->name('essentials.cardrivers.delete');


        Route::get('/cars-change-oil', [\Modules\Essentials\Http\Controllers\CarsChangeOilController::class, 'index'])->name('essentials.cars-change-oil');
        Route::get('/cars-change-oil-create', [\Modules\Essentials\Http\Controllers\CarsChangeOilController::class, 'create'])->name('essentials.cars-change-oil-create');
        Route::get('/cars-change-oil-edit/{id}', [\Modules\Essentials\Http\Controllers\CarsChangeOilController::class, 'edit'])->name('essentials.cars-change-oil.edit');
        Route::post('/cars-change-oil-store', [\Modules\Essentials\Http\Controllers\CarsChangeOilController::class, 'store'])->name('essentials.cars-change-oil-store');
        Route::post('/cars-change-oil-search', [\Modules\Essentials\Http\Controllers\CarsChangeOilController::class, 'search'])->name('essentials.cars-change-oil-search');
        Route::put('/cars-change-oil-update/{id}', [\Modules\Essentials\Http\Controllers\CarsChangeOilController::class, 'update'])->name('essentials.cars-change-oil-update');
        Route::delete('/cars-change-oil-delete/{id}', [\Modules\Essentials\Http\Controllers\CarsChangeOilController::class, 'destroy'])->name('essentials.cars-change-oil.delete');


        Route::get('/cars-maintenances', [\Modules\Essentials\Http\Controllers\CarsMaintenanceController::class, 'index'])->name('essentials.cars-maintenances');
        Route::get('/cars-maintenances-create', [\Modules\Essentials\Http\Controllers\CarsMaintenanceController::class, 'create'])->name('essentials.cars-maintenances-create');
        Route::get('/cars-maintenances-edit/{id}', [\Modules\Essentials\Http\Controllers\CarsMaintenanceController::class, 'edit'])->name('essentials.cars-maintenances.edit');
        Route::post('/cars-maintenances-store', [\Modules\Essentials\Http\Controllers\CarsMaintenanceController::class, 'store'])->name('essentials.cars-maintenances-store');
        Route::post('/cars-maintenances-search', [\Modules\Essentials\Http\Controllers\CarsMaintenanceController::class, 'search'])->name('essentials.cars-maintenances-search');
        Route::put('/cars-maintenances-update/{id}', [\Modules\Essentials\Http\Controllers\CarsMaintenanceController::class, 'update'])->name('essentials.cars-maintenances-update');
        Route::delete('/cars-maintenances-delete/{id}', [\Modules\Essentials\Http\Controllers\CarsMaintenanceController::class, 'destroy'])->name('essentials.cars-maintenances.delete');



        Route::get('/cars-change-oil-report', [\Modules\Essentials\Http\Controllers\CarsReportsController::class, 'CarsChangeOil'])->name('essentials.cars-change-oil-report');
        Route::get('/cars-maintenances-report', [\Modules\Essentials\Http\Controllers\CarsReportsController::class, 'carMaintenances'])->name('essentials.cars-maintenances-report');
    });
});
