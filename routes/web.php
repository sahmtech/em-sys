<?php

use App\Business;
use App\Contact;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountReportsController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\BankAccountsController;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsOfficialDocument;


// use App\Http\Controllers\Auth;
use App\Http\Controllers\BackUpController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessLocationController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CombinedPurchaseReturnController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardConfiguratorController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\DocumentAndNoteController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupTaxController;

use App\Http\Controllers\ImportOpeningStockController;
use App\Http\Controllers\ImportProductsController;
use App\Http\Controllers\ImportSalesController;
use App\Http\Controllers\Install;
use App\Http\Controllers\InvoiceLayoutController;
use App\Http\Controllers\InvoiceSchemeController;
use App\Http\Controllers\LabelsController;
use App\Http\Controllers\LedgerDiscountController;
use App\Http\Controllers\LocationSettingsController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Restaurant;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesCommissionAgentController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\SellingPriceGroupController;
use App\Http\Controllers\SellPosController;
use App\Http\Controllers\SellReturnController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\TaxonomyController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TimeSheetController;
use App\Http\Controllers\TransactionPaymentController;
use App\Http\Controllers\TypesOfServiceController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VariationTemplateController;
use App\Http\Controllers\WarrantyController;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Modules\FollowUp\Http\Controllers\FollowUpRequestController;


use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

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

Route::get('/contactUsFromWebsite', function () {

    return view('contactUsFromWebsite');
});

Route::post('/store_from_website', [\Modules\Sales\Http\Controllers\ClientsController::class, 'store_from_website']);

include_once 'install_r.php';

// Route::get('/testcomposer', function () {
//     $output = "";
//     exec('composer update --working-dir=/home/974206.cloudwaysapps.com/bysznmnkcv/public_html', $output);
//     return $output;
// });

// Route::get(
//     '/insurance_xlsx',
//     function () {
//         $reader = new Xlsx();
//         $filePath = public_path('employee_insurance_csv.xlsx');
//         $spreadsheet = $reader->load($filePath);
//         $worksheet = $spreadsheet->getActiveSheet();

//         $valuesA = [];
//         $valuesB = [];

//         foreach ($worksheet->getRowIterator() as $row) {
//             $cellIterator = $row->getCellIterator();
//             $cellIterator->setIterateOnlyExistingCells(true);

//             $cells = [];
//             foreach ($cellIterator as $cell) {
//                 $cells[] = $cell->getValue();
//             }

//             $valueA = $cells[0];
//             $comp = User::where('id_proof_number',$valueA)?->first()?->company_id ?? '';
//             $worksheet->getCell('D' . $row->getRowIndex())->setValue($comp);
//         }

//         $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
//         $writer->save(public_path('result.xlsx'));
//     }

// );

// Route::get(
//     '/xlsx',
//     function () {

//         $reader = new Xlsx();
//         $filePath = public_path('xls.xlsx');
//         $spreadsheet = $reader->load($filePath);
//         $worksheet = $spreadsheet->getActiveSheet();

//         $valuesA = [];
//         $valuesC = [];
//         $i = 1;
//         foreach ($worksheet->getRowIterator() as $row) {
//             $cellIterator = $row->getCellIterator();
//             $cellIterator->setIterateOnlyExistingCells(true);

//             $cells = [];
//             foreach ($cellIterator as $cell) {
//                 $cells[] = $cell->getValue();
//             }

//             $valueA = substr($cells[0], 0, -3); // Modify column A value
//             $valueC = $cells[2];

//             if (array_key_exists($valueA, $valuesA)) {
//                 $worksheet->getCell('B' . $row->getRowIndex())->setValue($valuesC[$valueA]);
//             } else {
//                 $valuesA[$valueA] = true;
//                 $valuesC[$valueA] = $valueC;
//             }
//             $i++;
//         }

//         $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
//         $writer->save(public_path('result.xlsx'));
//     }

// );
// Route::get(
//     '/xlsx',
//     function () {
//         $reader = new Xlsx();
//         $filePath = public_path('xlsx.xlsx'); // Make sure this is your source file path
//         $spreadsheet = $reader->load($filePath);
//         $worksheet = $spreadsheet->getActiveSheet();

//         $highestRow = $worksheet->getHighestRow(); // Get the highest row number

//         for ($row = 195; $row >= 3; $row--) { // Start from the last row to avoid messing up row numbers after deletion
//             $idProofNumber = $worksheet->getCell('D' . $row)->getValue();

//             // Check if ID Proof Number exists in users table
//             if (User::where('id_proof_number', $idProofNumber)->exists()) {
//                 // If exists, delete the row from the worksheet
//                 $worksheet->removeRow($row);
//             }
//         }

//         // Save the modified spreadsheet back to the file
//         $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
//         $writer->save(public_path('modified_result.xlsx')); // This will save the modified file under a new name
//     }

// );
// Route::get('/db_fix', function () {
//     $businesses = Business::all();
//     $numbersArray = $businesses->mapWithKeys(function ($business) {
//         return [$business->id => $business->id . '000001'];
//     })->toArray();
//     $users = User::all();
//     foreach ($users as $user) {
//         // Check if the user's business_id is in numbersArray
//         if (array_key_exists($user->business_id, $numbersArray)) {
//             // Set emp_number for the user
//             $user->emp_number = $numbersArray[$user->business_id];

//             // Increment the number in numbersArray for the next user
//             $numbersArray[$user->business_id] = ++$numbersArray[$user->business_id];

//             // Save the user with the updated emp_number
//             $user->save();
//         }
//     }
// });

// Route::get('/fix_emp', function () {
//     DB::beginTransaction();
//     try {

//         $companySequences = [];

//         $users = User::whereNot('company_id', 2)->get();

//         foreach ($users as $user) {
//             if (!isset($companySequences[$user->company_id])) {
//                 $companySequences[$user->company_id] = 1;
//             } else {
//                 $companySequences[$user->company_id]++;
//             }

//             $sequencePart = str_pad($companySequences[$user->company_id], 5, '0', STR_PAD_LEFT);
//             $companyPart = str_pad($user->company_id, 2, '0', STR_PAD_LEFT);
//             $newEmpNumber = $companyPart . $sequencePart;

//             $user->emp_number = $newEmpNumber;
//             $user->save();
//         }
//         DB::commit();
//         return response()->json(['message' => 'Success',]);
//     } catch (Exception $e) {
//         DB::rollback();
//         return response()->json(['error' => 'Failed ', 'message' => $e->getMessage()], 500);
//     }
// });
// Route::get('/fix_emp2', function () {
//     DB::beginTransaction();
//     try {

//         $companySequences = [];

//         $users = User::where('company_id', 2)->whereRaw('LENGTH(emp_number) > 6')->get();

//         foreach ($users as $user) {
//             if (!isset($companySequences[$user->company_id])) {
//                 $companySequences[$user->company_id] = 2;
//             } else {
//                 $companySequences[$user->company_id]++;
//             }

//             $sequencePart = str_pad($companySequences[$user->company_id], 5, '0', STR_PAD_LEFT);
//             $companyPart = str_pad($user->company_id, 2, '0', STR_PAD_LEFT);
//             $newEmpNumber = $companyPart . $sequencePart;

//             $user->emp_number = $newEmpNumber;
//             $user->save();
//         }
//         DB::commit();
//         return response()->json(['message' => 'Success',]);
//     } catch (Exception $e) {
//         DB::rollback();
//         return response()->json(['error' => 'Failed ', 'message' => $e->getMessage()], 500);
//     }
// });

// Route::get('/swap_k', function () {
//     DB::beginTransaction();
//     try {
//         $tmp = User::where('emp_number', "0100001")->first();
//         $kh = User::where('id', 5901)->first();
//         User::where('emp_number', "0100001")->update(['emp_number' => $kh->emp_number]);
//         User::where('id', 5901)->first()->update(['emp_number' => $tmp->emp_number]);
//         DB::commit();
//         return response()->json(['message' => 'Success',]);
//     } catch (Exception $e) {
//         DB::rollback();
//         return response()->json(['error' => 'Failed ', 'message' => $e->getMessage()], 500);
//     }
// });


// Route::get('/getContractsByDate', function () {
//     $date = '2024-04-28';


//     DB::transaction(function () use ($date) {

//         $contracts = DB::table('essentials_employees_contracts as a')
//             ->select('a.id', 'a.employee_id', 'a.created_at')
//             ->whereDate('a.created_at', '=', $date)
//             ->whereExists(function ($query) use ($date) {
//                 $query->select(DB::raw(1))
//                     ->from('essentials_employees_contracts as b')
//                     ->whereColumn('b.employee_id', 'a.employee_id')
//                     ->whereDate('b.updated_at', '=', $date)
//                     ->whereNotNull('b.updated_at');
//             })
//             ->get();

//         $Keep = $contracts->groupBy('employee_id')->map(function ($items) {
//             return $items->sortBy('created_at')->first()->id;
//         });
//         DB::table('essentials_employees_contracts')
//             ->whereIn('id', $Keep->all())
//             ->update(['is_active' => 1]);

//         if ($Keep->isNotEmpty()) {
//             DB::table('essentials_employees_contracts')
//                 ->whereDate('created_at', '=', $date)
//                 ->whereNotIn('id', $Keep->all())
//                 ->delete();
//         }
//     });

//     return response()->json(['message' => 'success']);
// });
// Route::get('/updateContractsBetweenDates', function () {

//     $start_date = '2024-04-25';
//     $end_date = '2024-05-01';

//     DB::transaction(function () use ($start_date, $end_date) {

//         $contracts = DB::table('essentials_employees_contracts as a')
//             ->select('a.id', 'a.employee_id', 'a.created_at')
//             ->whereBetween('a.created_at', [$start_date, $end_date])
//             ->whereExists(function ($query) use ($start_date, $end_date) {
//                 $query->select(DB::raw(1))
//                     ->from('essentials_employees_contracts as b')
//                     ->whereColumn('b.employee_id', 'a.employee_id')
//                     ->whereBetween('b.updated_at', [$start_date, $end_date])
//                     ->whereNotNull('b.updated_at');
//             })
//             ->get();

//         $Keep = $contracts->groupBy('employee_id')->map(function ($items) {
//             return $items->sortBy('created_at')->first()->id;
//         });

//         DB::table('essentials_employees_contracts')
//             ->whereIn('id', $Keep->all())
//             ->update(['is_active' => 1]);

//         if ($Keep->isNotEmpty()) {
//             DB::table('essentials_employees_contracts')
//                 ->whereBetween('created_at', [$start_date, $end_date])
//                 ->whereNotIn('id', $Keep->all())
//                 ->delete();
//         }
//     });

//     return response()->json(['message' => 'success']);
// });
// Route::get('/updateContractNumbers', function () {

//     EssentialsEmployeesContract::query()->update(['contract_number' => null]);


//     $contracts = EssentialsEmployeesContract::orderBy('id')->get();

//     $latestRecord = EssentialsEmployeesContract::orderBy('contract_number', 'desc')->first();
//     $latestRefNo = $latestRecord ? $latestRecord->contract_number : null;
//     $numericPart = $latestRefNo ? (int)substr($latestRefNo, 2) : 0;

//     foreach ($contracts as $contract) {
//         $numericPart++;
//         $newContractNumber = 'EC' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
//         $contract->contract_number = $newContractNumber;
//         $contract->save();
//     }
//     return 'success';
// });
// Route::get('/updateContractsBefore2000', function () {
//     $employeeIds = EssentialsEmployeesContract::where('contract_end_date', '<', '2000-01-01')
//         ->pluck('employee_id');

//     foreach ($employeeIds as $employeeId) {

//         $latestContract = EssentialsEmployeesContract::where('employee_id', $employeeId)
//             ->where('is_active', 1)
//             ->first();

//         if ($latestContract) {

//             $latestContract->delete();

//             $previousContract = EssentialsEmployeesContract::where('employee_id', $employeeId)
//                 ->orderBy('created_at', 'desc')
//                 ->first();

//             if ($previousContract) {

//                 $previousContract->is_active = 1;
//                 $previousContract->save();
//             }
//         }
//     }
// });

// Route::get('/updateContractsStatusForAll', function () {
//     $contracts = EssentialsEmployeesContract::all();

//     foreach ($contracts as $contract) {
//         $today = Carbon::today();

//         // If essential fields are missing, determine if the contract should be inactive
//         if (is_null($contract->is_renewable) || is_null($contract->contract_duration) || is_null($contract->contract_start_date)) {
//             if (is_null($contract->contract_end_date) || Carbon::parse($contract->contract_end_date)->isPast()) {
//                 $contract->is_active = 0;
//             } else if (is_null($contract->is_renewable) && Carbon::parse($contract->contract_end_date)->isFuture()) {
//                 $contract->is_active = 1;
//             }
//             $contract->save();
//             continue;
//         }

//         if (is_null($contract->contract_end_date)) {
//             continue;
//         }

//         $contractEndDate = Carbon::parse($contract->contract_end_date);

//         // If the contract end date is in the future and the status is inactive, make it active
//         if ($contract->is_active == 0 && $contractEndDate->isFuture()) {
//             $contract->is_active = 1;
//             $contract->save();
//             continue;
//         }

//         // If the contract end date is in the past and the contract is renewable, renew it
//         if ($contractEndDate->isPast() && $contract->is_renewable == 1) {
//             while ($contractEndDate->isPast()) {
//                 $contract->contract_start_date = $contractEndDate;
//                 $contractEndDate = $contractEndDate->copy()->addYears($contract->contract_duration);
//             }
//             $contract->contract_end_date = $contractEndDate;
//             $contract->is_active = 1; // Set the status to active if it has been renewed
//             $contract->save();
//             continue;
//         }

//         // If the contract is not renewable and the end date is in the past, make it inactive
//         if ($contract->is_active == 1 && $contractEndDate->isPast() && $contract->is_renewable == 0) {
//             $contract->is_active = 0;
//             $contract->save();
//             continue;
//         }
//     }
//     return 'success';
// });
// Route::get('/updateOfficialDocumentsStatusForAll', function () {

//     $docs = EssentialsOfficialDocument::all();

//     foreach ($docs as $doc) {


//         if (is_null($doc->expiration_date)) {
//             continue;
//         }

//         $expiration_date = Carbon::parse($doc->expiration_date);

//         if ($expiration_date->isPast()) {
//             $doc->is_active = 0;
//         } else {
//             $doc->is_active = 1;
//         }

//         $doc->save();
//     }
//     return 'success';
// });
// Route::get('/updateContractPerPeriod', function () {

//     EssentialsEmployeesContract::query()->update(['contract_per_period' => 'years']);

//     return 'success';
// });

// Route::get('/removeDuplicateDocuments', function () {
//     DB::beginTransaction();

//     try {
//         $subquery = "
//             SELECT MIN(id) as id 
//             FROM essentials_official_documents 
//             GROUP BY type, number, issue_date, issue_place, expiration_date, is_active,file_path, employee_id
//         ";

//         $duplicateIds = DB::table(DB::raw("($subquery) as sub"))
//             ->select('id')
//             ->pluck('id')
//             ->toArray();

//         $deletedRows = DB::table('essentials_official_documents')
//             ->whereNotIn('id', $duplicateIds)
//             ->delete();

//         DB::commit();

//         return response()->json(['success' => true, 'message' => "$deletedRows duplicate rows deleted."]);
//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json(['success' => false, 'message' => $e->getMessage()]);
//     }
// });
// Route::get('/removeDuplicateContracts', function () {

//     DB::beginTransaction();

//     try {
//         $subquery = "
//             SELECT MIN(id) as id 
//             FROM essentials_employees_contracts 
//             GROUP BY 
//                 employee_id, is_active, contract_start_date, contract_end_date,
//                 contract_duration, contract_per_period, probation_period, file_path, wish_file,
//                 is_renewable, contract_type_id
//         ";

//         $duplicateIds = DB::table(DB::raw("($subquery) as sub"))
//             ->select('id')
//             ->pluck('id')
//             ->toArray();

//         $deletedRows = DB::table('essentials_employees_contracts')
//             ->whereNotIn('id', $duplicateIds)
//             ->delete();

//         DB::commit();

//         return response()->json(['success' => true, 'message' => "$deletedRows duplicate rows deleted."]);
//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json(['success' => false, 'message' => $e->getMessage()]);
//     }
// });

Route::get('/clear_cache', function () {
    try {
        // Call the artisan command
        Artisan::call('cache:clear');

        // Retrieve the output from the command
        $output = Artisan::output();

        return response()->json(['message' => 'Cache cleared successfully', 'output' => $output]);
    } catch (Exception $e) {
        // Handle the exception
        return response()->json(['error' => 'Failed to clear cache', 'message' => $e->getMessage()], 500);
    }
});
Route::get('/privacy-policy', function () {
    return view('privacy_policy');
});

// Route::get('/userFromContact', function () {
//     $contacts = Contact::where('type', 'lead')->get();
//     foreach ($contacts as $contact) {
//         $temp = User::where('first_name', $contact->supplier_business_name)->first();
//         if (!($temp  && $temp->user_type == 'customer')) {
//             $userInfo['user_type'] = 'customer';
//             $userInfo['first_name'] = $contact->supplier_business_name;
//             $userInfo['allow_login'] = 0;
//             $userInfo['business_id'] =  1;
//             $userInfo['crm_contact_id'] =  $contact->id;
//             $user = User::create($userInfo);
//             // error_log($user);
//             // error_log("********************************");
//         }
//     }
// });

Route::middleware(['setData'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('home');
    });

    Auth::routes();
    //  Route::delete('/services/{id}', [App\Modules\Sales\Http\Controllers\SalesTargetedClientController::class, 'destroy'])->name('service.destroy');






    Route::get('/business/register', [BusinessController::class, 'getRegister'])->name('business.getRegister');
    Route::post('/business/register', [BusinessController::class, 'postRegister'])->name('business.postRegister');
    Route::post('/business/register/check-username', [BusinessController::class, 'postCheckUsername'])->name('business.postCheckUsername');
    Route::post('/business/register/check-email', [BusinessController::class, 'postCheckEmail'])->name('business.postCheckEmail');

    Route::get('/invoice/{token}', [SellPosController::class, 'showInvoice'])
        ->name('show_invoice');
    Route::get('/quote/{token}', [SellPosController::class, 'showInvoice'])
        ->name('show_quote');

    Route::get('/pay/{token}', [SellPosController::class, 'invoicePayment'])
        ->name('invoice_payment');
    Route::post('/confirm-payment/{id}', [SellPosController::class, 'confirmPayment'])
        ->name('confirm_payment');
});

//Routes for authenticated users only
Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'CustomAdminSidebarMenu', 'CheckUserLogin'])->group(function () {
    Route::get('/my_notifications', [HomeController::class, 'getMyNotifications'])->name('getMyNotification');

    Route::get('pos/payment/{id}', [SellPosController::class, 'edit'])->name('edit-pos-payment');
    Route::get('service-staff-availability', [SellPosController::class, 'showServiceStaffAvailibility']);
    Route::get('pause-resume-service-staff-timer/{user_id}', [SellPosController::class, 'pauseResumeServiceStaffTimer']);
    Route::get('mark-as-available/{user_id}', [SellPosController::class, 'markAsAvailable']);

    Route::resource('purchase-requisition', PurchaseRequisitionController::class)->except(['edit', 'update']);
    Route::post('/get-requisition-products', [PurchaseRequisitionController::class, 'getRequisitionProducts'])->name('get-requisition-products');
    Route::get('get-purchase-requisitions/{location_id}', [PurchaseRequisitionController::class, 'getPurchaseRequisitions']);
    Route::get('get-purchase-requisition-lines/{purchase_requisition_id}', [PurchaseRequisitionController::class, 'getPurchaseRequisitionLines']);

    Route::get('/sign-in-as-user/{id}', [ManageUserController::class, 'signInAsUser'])->name('sign-in-as-user');
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/showCard/{cardId}', [HomeController::class, 'showCard'])->name('showCard');
    Route::get('/home/get-totals', [HomeController::class, 'getTotals']);
    Route::get('/home/product-stock-alert', [HomeController::class, 'getProductStockAlert']);
    Route::get('/home/purchase-payment-dues', [HomeController::class, 'getPurchasePaymentDues']);
    Route::get('/home/sales-payment-dues', [HomeController::class, 'getSalesPaymentDues']);
    Route::post('/attach-medias-to-model', [HomeController::class, 'attachMediasToGivenModel'])->name('attach.medias.to.model');
    Route::get('/calendar', [HomeController::class, 'getCalendar'])->name('calendar');

    Route::post('/test-email', [BusinessController::class, 'testEmailConfiguration']);
    Route::post('/test-sms', [BusinessController::class, 'testSmsConfiguration']);
    Route::get('/business/settings', [BusinessController::class, 'getBusinessSettings'])->name('business.getBusinessSettings');


    Route::get('/create-bank-account', [BankAccountsController::class, 'create'])->name('create-bank-account');
    Route::post('/save-bank-account', [BankAccountsController::class, 'store']);
    Route::get('/edit-bank-account/{id}', [BankAccountsController::class, 'edit']);
    Route::post('/update-bank-account/{id}', [BankAccountsController::class, 'update']);
    Route::get('/delete-bank-account/{id}', [BankAccountsController::class, 'delete']);


    Route::post('/business/update', [BusinessController::class, 'postBusinessSettings'])->name('business.postBusinessSettings');
    Route::get('/user/profile', [UserController::class, 'getProfile'])->name('user.getProfile');
    Route::post('/user/update', [UserController::class, 'updateProfile'])->name('user.updateProfile');
    Route::post('/user/update-password', [UserController::class, 'updatePassword'])->name('user.updatePassword');

    Route::resource('brands', BrandController::class);

    //Route::resource('payment-account', 'PaymentAccountController');

    Route::resource('tax-rates', TaxRateController::class);

    Route::resource('units', UnitController::class);

    Route::resource('ledger-discount', LedgerDiscountController::class)->only('edit', 'destroy', 'store', 'update');

    Route::post('check-mobile', [ContactController::class, 'checkMobile']);
    Route::get('/get-contact-due/{contact_id}', [ContactController::class, 'getContactDue']);
    Route::get('/contacts/payments/{contact_id}', [ContactController::class, 'getContactPayments']);
    Route::get('/contacts/map', [ContactController::class, 'contactMap']);
    Route::get('/contacts/update-status/{id}', [ContactController::class, 'updateStatus']);
    Route::get('/contacts/stock-report/{supplier_id}', [ContactController::class, 'getSupplierStockReport']);
    Route::get('/contacts/ledger', [ContactController::class, 'getLedger']);
    Route::post('/contacts/send-ledger', [ContactController::class, 'sendLedger']);
    Route::get('/contacts/import', [ContactController::class, 'getImportContacts'])->name('contacts.import');
    Route::post('/contacts/import', [ContactController::class, 'postImportContacts']);
    Route::post('/contacts/check-contacts-id', [ContactController::class, 'checkContactId']);
    Route::get('/contacts/customers', [ContactController::class, 'getCustomers']);
    Route::resource('contacts', ContactController::class);

    Route::get('taxonomies-ajax-index-page', [TaxonomyController::class, 'getTaxonomyIndexPage']);
    Route::resource('taxonomies', TaxonomyController::class);

    Route::resource('variation-templates', VariationTemplateController::class);

    Route::get('/products/download-excel', [ProductController::class, 'downloadExcel']);

    Route::get('/products/stock-history/{id}', [ProductController::class, 'productStockHistory']);
    Route::get('/delete-media/{media_id}', [ProductController::class, 'deleteMedia']);
    Route::post('/products/mass-deactivate', [ProductController::class, 'massDeactivate']);
    Route::get('/products/activate/{id}', [ProductController::class, 'activate']);
    Route::get('/products/view-product-group-price/{id}', [ProductController::class, 'viewGroupPrice']);
    Route::get('/products/add-selling-prices/{id}', [ProductController::class, 'addSellingPrices']);
    Route::post('/products/save-selling-prices', [ProductController::class, 'saveSellingPrices']);
    Route::post('/products/mass-delete', [ProductController::class, 'massDestroy']);
    Route::get('/products/view/{id}', [ProductController::class, 'view']);
    Route::get('/products/list', [ProductController::class, 'getProducts']);
    Route::get('/products/list-no-variation', [ProductController::class, 'getProductsWithoutVariations']);
    Route::post('/products/bulk-edit', [ProductController::class, 'bulkEdit']);
    Route::post('/products/bulk-update', [ProductController::class, 'bulkUpdate']);
    Route::post('/products/bulk-update-location', [ProductController::class, 'updateProductLocation']);
    Route::get('/products/get-product-to-edit/{product_id}', [ProductController::class, 'getProductToEdit']);

    Route::post('/products/get_sub_categories', [ProductController::class, 'getSubCategories']);
    Route::get('/products/get_sub_units', [ProductController::class, 'getSubUnits']);
    Route::post('/products/product_form_part', [ProductController::class, 'getProductVariationFormPart']);
    Route::post('/products/get_product_variation_row', [ProductController::class, 'getProductVariationRow']);
    Route::post('/products/get_variation_template', [ProductController::class, 'getVariationTemplate']);
    Route::get('/products/get_variation_value_row', [ProductController::class, 'getVariationValueRow']);
    Route::post('/products/check_product_sku', [ProductController::class, 'checkProductSku']);
    Route::post('/products/validate_variation_skus', [ProductController::class, 'validateVaritionSkus']); //validates multiple skus at once
    Route::get('/products/quick_add', [ProductController::class, 'quickAdd']);
    Route::post('/products/save_quick_product', [ProductController::class, 'saveQuickProduct'])->name('save_Quick_Product');
    Route::get('/products/get-combo-product-entry-row', [ProductController::class, 'getComboProductEntryRow']);
    Route::post('/products/toggle-woocommerce-sync', [ProductController::class, 'toggleWooCommerceSync']);

    Route::resource('products', ProductController::class);
    Route::get('/toggle-subscription/{id}', 'SellPosController@toggleRecurringInvoices');
    Route::post('/sells/pos/get-types-of-service-details', 'SellPosController@getTypesOfServiceDetails');
    Route::get('/sells/subscriptions', 'SellPosController@listSubscriptions');
    Route::get('/sells/duplicate/{id}', 'SellController@duplicateSell');
    Route::get('/sells/drafts', 'SellController@getDrafts');
    Route::get('/sells/convert-to-draft/{id}', 'SellPosController@convertToInvoice');
    Route::get('/sells/convert-to-proforma/{id}', 'SellPosController@convertToProforma');
    Route::get('/sells/quotations', 'SellController@getQuotations');
    Route::get('/sells/draft-dt', 'SellController@getDraftDatables');
    Route::resource('sells', 'SellController')->except(['show']);
    Route::get('/sells/copy-quotation/{id}', [SellPosController::class, 'copyQuotation']);

    Route::post('/import-purchase-products', [PurchaseController::class, 'importPurchaseProducts']);
    Route::post('/purchases/update-status', [PurchaseController::class, 'updateStatus']);
    Route::get('/purchases/get_products', [PurchaseController::class, 'getProducts']);
    Route::get('/purchases/get_suppliers', [PurchaseController::class, 'getSuppliers']);
    Route::post('/purchases/get_purchase_entry_row', [PurchaseController::class, 'getPurchaseEntryRow']);
    Route::post('/purchases/check_ref_number', [PurchaseController::class, 'checkRefNumber']);
    Route::resource('purchases', PurchaseController::class)->except(['show']);

    Route::get('/toggle-subscription/{id}', [SellPosController::class, 'toggleRecurringInvoices']);
    Route::post('/sells/pos/get-types-of-service-details', [SellPosController::class, 'getTypesOfServiceDetails']);
    Route::get('/sells/subscriptions', [SellPosController::class, 'listSubscriptions']);
    Route::get('/sells/duplicate/{id}', [SellController::class, 'duplicateSell']);
    Route::get('/sells/drafts', [SellController::class, 'getDrafts']);
    Route::get('/sells/convert-to-draft/{id}', [SellPosController::class, 'convertToInvoice']);
    Route::get('/sells/convert-to-proforma/{id}', [SellPosController::class, 'convertToProforma']);
    Route::get('/sells/quotations', [SellController::class, 'getQuotations']);
    Route::get('/sells/draft-dt', [SellController::class, 'getDraftDatables']);
    Route::resource('sells', SellController::class)->except(['show']);

    Route::get('/import-sales', [ImportSalesController::class, 'index']);
    Route::post('/import-sales/preview', [ImportSalesController::class, 'preview']);
    Route::post('/import-sales', [ImportSalesController::class, 'import']);
    Route::get('/revert-sale-import/{batch}', [ImportSalesController::class, 'revertSaleImport']);

    Route::get('/sells/pos/get_product_row/{variation_id}/{location_id}', [SellPosController::class, 'getProductRow']);
    Route::post('/sells/pos/get_payment_row', [SellPosController::class, 'getPaymentRow']);
    Route::post('/sells/pos/get-reward-details', [SellPosController::class, 'getRewardDetails']);
    Route::get('/sells/pos/get-recent-transactions', [SellPosController::class, 'getRecentTransactions']);
    Route::get('/sells/pos/get-product-suggestion', [SellPosController::class, 'getProductSuggestion']);
    Route::get('/sells/pos/get-featured-products/{location_id}', [SellPosController::class, 'getFeaturedProducts']);
    Route::get('/reset-mapping', [SellController::class, 'resetMapping']);

    Route::resource('pos', SellPosController::class);

    Route::resource('roles', RoleController::class);
    Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('roles');

    Route::get('/roles/editOrCreateAccessRole/{id}', [RoleController::class, 'editOrCreateAccessRole'])->name('editOrCreateAccessRole');
    Route::get('/roles/editOrCreateReportAccessRole/{id}', [RoleController::class, 'editOrCreateReportAccessRole'])->name('editOrCreateReportAccessRole');
    Route::get('/roles/editOrCreateRequestAccessRole/{id}', [RoleController::class, 'editOrCreateRequestAccessRole'])->name('editOrCreateRequestAccessRole');

    Route::post('/roles/updateAccessRole/{roleId}', [RoleController::class, 'updateAccessRole'])->name('updateAccessRole');
    Route::post('/roles/updateAccessRoleReport/{roleId}', [RoleController::class, 'updateAccessRoleReport'])->name('updateAccessRoleReport');
    Route::post('/roles/updateAccessRoleRequest/{roleId}', [RoleController::class, 'updateAccessRoleRequest'])->name('updateAccessRoleRequest');



    Route::prefix('reports')->group(function () {
        Route::get('/reports', [ReportsController::class, 'landing'])->name('reports.landing');
        Route::get('expired_residencies', [ReportsController::class, 'expired_residencies'])->name('expired_residencies');
        Route::get('residencies-almost-finished', [ReportsController::class, 'residencies_almost_finished'])->name('residencies_almost_finished');
        Route::get('contracts-almost-finished', [ReportsController::class, 'contracts_almost_finished'])->name('contracts_almost_finished');
        Route::get('expired-contracts', [ReportsController::class, 'expired_contracts'])->name('expired_contracts');
        Route::get('rooms-and-beds', [ReportsController::class, 'rooms_and_beds'])->name('rooms_and_beds');
        Route::get('building', [ReportsController::class, 'building'])->name('building');
        Route::get('employee-medical-insurance', [ReportsController::class, 'employee_medical_insurance'])->name('employee_medical_insurance');
        Route::get('cars-change-oil', [ReportsController::class, 'cars_change_oil'])->name('cars_change_oil');
        Route::get('car-maintenances', [ReportsController::class, 'car_maintenances'])->name('car_maintenances');
        Route::get('worker-medical-insurance', [ReportsController::class, 'worker_medical_insurance'])->name('worker_medical_insurance');
        Route::get('worker-without-medical-insurance', [ReportsController::class, 'worker_without_medical_insurance'])->name('worker_without_medical_insurance');
        Route::get('employee-without-medical-insurance', [ReportsController::class, 'employee_without_medical_insurance'])->name('employee_without_medical_insurance');
        Route::get('final-exit', [ReportsController::class, 'final_exit'])->name('final_exit');
        Route::get('projects', [ReportsController::class, 'projects'])->name('reports-projects');
        Route::get('project-workers', [ReportsController::class, 'project_workers'])->name('project_workers');
        Route::get('employee-almost-finish-contracts', [ReportsController::class, 'employee_almost_finish_contracts'])->name('employee_almost_finish_contracts');
        Route::get('employee-finish-contracts', [ReportsController::class, 'employee_finish_contracts'])->name('employee_finish_contracts');
    });


    Route::resource('users', ManageUserController::class);
    Route::get('get-all-users', [ManageUserController::class, 'index'])->name('get-all-users');
    Route::resource('group-taxes', GroupTaxController::class);

    Route::get('/barcodes/set_default/{id}', [BarcodeController::class, 'setDefault']);
    Route::resource('barcodes', BarcodeController::class);

    //Invoice schemes..
    Route::get('/invoice-schemes/set_default/{id}', [InvoiceSchemeController::class, 'setDefault']);
    Route::resource('invoice-schemes', InvoiceSchemeController::class);

    //Print Labels
    Route::get('/labels/show', [LabelsController::class, 'show']);
    Route::get('/labels/add-product-row', [LabelsController::class, 'addProductRow']);
    Route::get('/labels/preview', [LabelsController::class, 'preview']);

    //Reports...
    Route::get('/reports/gst-purchase-report', [ReportController::class, 'gstPurchaseReport']);
    Route::get('/reports/gst-sales-report', [ReportController::class, 'gstSalesReport']);
    Route::get('/reports/get-stock-by-sell-price', [ReportController::class, 'getStockBySellingPrice']);
    Route::get('/reports/purchase-report', [ReportController::class, 'purchaseReport']);
    Route::get('/reports/sale-report', [ReportController::class, 'saleReport']);
    Route::get('/reports/service-staff-report', [ReportController::class, 'getServiceStaffReport']);
    Route::get('/reports/service-staff-line-orders', [ReportController::class, 'serviceStaffLineOrders']);
    Route::get('/reports/table-report', [ReportController::class, 'getTableReport']);
    Route::get('/reports/profit-loss', [ReportController::class, 'getProfitLoss'])->name('reports.profit-loss');
    Route::get('/reports/get-opening-stock', [ReportController::class, 'getOpeningStock']);
    Route::get('/reports/purchase-sell', [ReportController::class, 'getPurchaseSell']);
    Route::get('/reports/customer-supplier', [ReportController::class, 'getCustomerSuppliers']);
    Route::get('/reports/stock-report', [ReportController::class, 'getStockReport']);
    Route::get('/reports/stock-details', [ReportController::class, 'getStockDetails']);
    Route::get('/reports/tax-report', [ReportController::class, 'getTaxReport']);
    Route::get('/reports/tax-details', [ReportController::class, 'getTaxDetails']);
    Route::get('/reports/trending-products', [ReportController::class, 'getTrendingProducts']);
    Route::get('/reports/expense-report', [ReportController::class, 'getExpenseReport']);
    Route::get('/reports/stock-adjustment-report', [ReportController::class, 'getStockAdjustmentReport']);
    Route::get('/reports/register-report', [ReportController::class, 'getRegisterReport']);
    Route::get('/reports/sales-representative-report', [ReportController::class, 'getSalesRepresentativeReport']);
    Route::get('/reports/sales-representative-total-expense', [ReportController::class, 'getSalesRepresentativeTotalExpense']);
    Route::get('/reports/sales-representative-total-sell', [ReportController::class, 'getSalesRepresentativeTotalSell']);
    Route::get('/reports/sales-representative-total-commission', [ReportController::class, 'getSalesRepresentativeTotalCommission']);
    Route::get('/reports/stock-expiry', [ReportController::class, 'getStockExpiryReport']);
    Route::get('/reports/stock-expiry-edit-modal/{purchase_line_id}', [ReportController::class, 'getStockExpiryReportEditModal']);
    Route::post('/reports/stock-expiry-update', [ReportController::class, 'updateStockExpiryReport'])->name('updateStockExpiryReport');
    Route::get('/reports/customer-group', [ReportController::class, 'getCustomerGroup']);
    Route::get('/reports/product-purchase-report', [ReportController::class, 'getproductPurchaseReport']);
    Route::get('/reports/product-sell-grouped-by', [ReportController::class, 'productSellReportBy']);
    Route::get('/reports/product-sell-report', [ReportController::class, 'getproductSellReport']);
    Route::get('/reports/product-sell-report-with-purchase', [ReportController::class, 'getproductSellReportWithPurchase']);
    Route::get('/reports/product-sell-grouped-report', [ReportController::class, 'getproductSellGroupedReport']);
    Route::get('/reports/lot-report', [ReportController::class, 'getLotReport']);
    Route::get('/reports/purchase-payment-report', [ReportController::class, 'purchasePaymentReport']);
    Route::get('/reports/sell-payment-report', [ReportController::class, 'sellPaymentReport']);
    Route::get('/reports/product-stock-details', [ReportController::class, 'productStockDetails']);
    Route::get('/reports/adjust-product-stock', [ReportController::class, 'adjustProductStock']);
    Route::get('/reports/get-profit/{by?}', [ReportController::class, 'getProfit']);
    Route::get('/reports/items-report', [ReportController::class, 'itemsReport']);
    Route::get('/reports/get-stock-value', [ReportController::class, 'getStockValue']);

    Route::get('business-location/activate-deactivate/{location_id}', [BusinessLocationController::class, 'activateDeactivateLocation']);
    Route::post('/changeRequestStatus', [\App\Utils\RequestUtil::class, 'changeRequestStatus'])->name('changeRequestStatus');
    Route::get('/viewUserRequest/{requestId}', [\App\Utils\RequestUtil::class, 'viewRequest'])->name('viewUserRequest');
    Route::post('/returnRequest', [\App\Utils\RequestUtil::class, 'returnRequest'])->name('returnRequest');
    Route::get('/get-request-type/{selectedId}', [\App\Utils\RequestUtil::class, 'getTypeById'])->name('get-request-type');
    Route::post('/get-sub-reasons', [\App\Utils\RequestUtil::class, 'getSubReasons'])->name('getSubReasons');
    Route::post('/save-attachment/{requestId}',  [\App\Utils\RequestUtil::class, 'saveAttachment'])->name('saveAttachment');
    Route::post('/get-non-saudi-users', [\App\Utils\RequestUtil::class, 'getNonSaudiUsers'])->name('getNonSaudiUsers');
    Route::post('/get-unsigned-workers', [\App\Utils\RequestUtil::class, 'getUnsignedWorkers'])->name('getUnsignedWorkers');
    Route::post('/get-unsigned-workers', [\App\Utils\RequestUtil::class, 'getUnsignedWorkers'])->name('getUnsignedWorkers');
    Route::get('/fetch-users-by-type', [\App\Utils\RequestUtil::class, 'fetchUsersByType'])->name('fetch.users.by.type');

    Route::get('/test', [\App\Utils\RequestUtil::class, 'test'])->name('test');
    Route::post('/update-task-status', [\App\Utils\RequestUtil::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::get('/work_cards/view_requests_operations', [\App\Utils\RequestUtil::class, 'viewRequestsOperations'])->name('view_requests_operations');
    Route::get('/finish_operation/{requestId}', [\App\Utils\RequestUtil::class,  'finish_operation'])->name('finish_operation');
    Route::get('/get-request/{id}', [\App\Utils\RequestUtil::class, 'getRequest'])->name('getRequest');
    Route::post('/update-request/{id}', [\App\Utils\RequestUtil::class, 'updateRequest'])->name('updateRequest');
    Route::delete('/delete-request/{id}', [\App\Utils\RequestUtil::class, 'deleteRequest'])->name('deleteRequest');

    //Business Location Settings...
    Route::prefix('business-location/{location_id}')->name('location.')->group(function () {
        Route::get('settings', [LocationSettingsController::class, 'index'])->name('settings');
        Route::post('settings', [LocationSettingsController::class, 'updateSettings'])->name('settings_update');
        Route::get('map', [LocationSettingsController::class, 'map'])->name('map');
        Route::post('save_polygon', [LocationSettingsController::class, 'savePolygon'])->name('save_polygon');
    });

    //Business Locations...
    Route::post('business-location/check-location-id', [BusinessLocationController::class, 'checkLocationId']);
    Route::resource('business-location', BusinessLocationController::class);

    //Invoice layouts..
    Route::resource('invoice-layouts', InvoiceLayoutController::class);

    Route::post('get-expense-sub-categories', [ExpenseCategoryController::class, 'getSubCategories']);

    //Expense Categories...
    Route::resource('expense-categories', ExpenseCategoryController::class);

    //Expenses...
    Route::resource('expenses', ExpenseController::class);

    //Transaction payments...
    // Route::get('/payments/opening-balance/{contact_id}', 'TransactionPaymentController@getOpeningBalancePayments');
    Route::get('/payments/show-child-payments/{payment_id}', [TransactionPaymentController::class, 'showChildPayments']);
    Route::get('/payments/view-payment/{payment_id}', [TransactionPaymentController::class, 'viewPayment']);
    Route::get('/payments/add_payment/{transaction_id}', [TransactionPaymentController::class, 'addPayment']);
    Route::get('/payments/view-payment-vouchers/{payment_id}', [TransactionPaymentController::class, 'view_payment_vouchers']);
    Route::get('/payments/view-receipt-vouchers/{payment_id}', [TransactionPaymentController::class, 'view_receipt_vouchers']);

    Route::get('/payments/pay-contact-due/{contact_id}', [TransactionPaymentController::class, 'getPayContactDue']);
    Route::post('/payments/pay-contact-due', [TransactionPaymentController::class, 'postPayContactDue']);
    Route::resource('payments', TransactionPaymentController::class);

    //Printers...
    Route::resource('printers', PrinterController::class);

    Route::get('/stock-adjustments/remove-expired-stock/{purchase_line_id}', [StockAdjustmentController::class, 'removeExpiredStock']);
    Route::post('/stock-adjustments/get_product_row', [StockAdjustmentController::class, 'getProductRow']);
    Route::resource('stock-adjustments', StockAdjustmentController::class);

    Route::get('/cash-register/register-details', [CashRegisterController::class, 'getRegisterDetails']);
    Route::get('/cash-register/close-register/{id?}', [CashRegisterController::class, 'getCloseRegister']);
    Route::post('/cash-register/close-register', [CashRegisterController::class, 'postCloseRegister']);
    Route::resource('cash-register', CashRegisterController::class);

    //Import products
    Route::get('/import-products', [ImportProductsController::class, 'index']);
    Route::post('/import-products/store', [ImportProductsController::class, 'store']);

    //Sales Commission Agent
    Route::resource('sales-commission-agents', SalesCommissionAgentController::class);

    //Stock Transfer
    Route::get('stock-transfers/print/{id}', [StockTransferController::class, 'printInvoice']);
    Route::post('stock-transfers/update-status/{id}', [StockTransferController::class, 'updateStatus']);
    Route::resource('stock-transfers', StockTransferController::class);

    Route::get('/opening-stock/add/{product_id}', [OpeningStockController::class, 'add']);
    Route::post('/opening-stock/save', [OpeningStockController::class, 'save']);

    //Customer Groups
    Route::resource('customer-group', CustomerGroupController::class);

    //Import opening stock
    Route::get('/import-opening-stock', [ImportOpeningStockController::class, 'index']);
    Route::post('/import-opening-stock/store', [ImportOpeningStockController::class, 'store']);

    //Sell return
    Route::get('validate-invoice-to-return/{invoice_no}', [SellReturnController::class, 'validateInvoiceToReturn']);
    Route::resource('sell-return', SellReturnController::class);
    Route::get('sell-return/get-product-row', [SellReturnController::class, 'getProductRow']);
    Route::get('/sell-return/print/{id}', [SellReturnController::class, 'printInvoice']);
    Route::get('/sell-return/add/{id}', [SellReturnController::class, 'add']);

    //Backup
    Route::get('backup/download/{file_name}', [BackUpController::class, 'download']);
    Route::get('backup/delete/{file_name}', [BackUpController::class, 'delete']);
    Route::resource('backup', BackUpController::class)->only('index', 'create', 'store');

    Route::get('selling-price-group/activate-deactivate/{id}', [SellingPriceGroupController::class, 'activateDeactivate']);
    Route::get('update-product-price', [SellingPriceGroupController::class, 'updateProductPrice'])->name('update-product-price');
    Route::get('export-product-price', [SellingPriceGroupController::class, 'export']);
    Route::post('import-product-price', [SellingPriceGroupController::class, 'import']);

    Route::resource('selling-price-group', SellingPriceGroupController::class);

    Route::resource('notification-templates', NotificationTemplateController::class)->only(['index', 'store']);
    Route::get('notification/get-template/{transaction_id}/{template_for}', [NotificationController::class, 'getTemplate']);
    Route::post('notification/send', [NotificationController::class, 'send']);

    Route::post('/purchase-return/update', [CombinedPurchaseReturnController::class, 'update']);
    Route::get('/purchase-return/edit/{id}', [CombinedPurchaseReturnController::class, 'edit']);
    Route::post('/purchase-return/save', [CombinedPurchaseReturnController::class, 'save']);
    Route::post('/purchase-return/get_product_row', [CombinedPurchaseReturnController::class, 'getProductRow']);
    Route::get('/purchase-return/create', [CombinedPurchaseReturnController::class, 'create']);
    Route::get('/purchase-return/add/{id}', [PurchaseReturnController::class, 'add']);
    Route::resource('/purchase-return', PurchaseReturnController::class)->except('create');

    Route::get('/discount/activate/{id}', [DiscountController::class, 'activate']);
    Route::post('/discount/mass-deactivate', [DiscountController::class, 'massDeactivate']);
    Route::resource('discount', DiscountController::class);

    Route::prefix('account')->group(function () {
        Route::resource('/account', AccountController::class);
        Route::get('/fund-transfer/{id}', [AccountController::class, 'getFundTransfer']);
        Route::post('/fund-transfer', [AccountController::class, 'postFundTransfer']);
        Route::get('/deposit/{id}', [AccountController::class, 'getDeposit']);
        Route::post('/deposit', [AccountController::class, 'postDeposit']);
        Route::get('/close/{id}', [AccountController::class, 'close']);
        Route::get('/activate/{id}', [AccountController::class, 'activate']);
        Route::get('/delete-account-transaction/{id}', [AccountController::class, 'destroyAccountTransaction']);
        Route::get('/edit-account-transaction/{id}', [AccountController::class, 'editAccountTransaction']);
        Route::post('/update-account-transaction/{id}', [AccountController::class, 'updateAccountTransaction']);
        Route::get('/get-account-balance/{id}', [AccountController::class, 'getAccountBalance']);
        Route::get('/balance-sheet', [AccountReportsController::class, 'balanceSheet']);
        Route::get('/trial-balance', [AccountReportsController::class, 'trialBalance']);
        Route::get('/payment-account-report', [AccountReportsController::class, 'paymentAccountReport']);
        Route::get('/link-account/{id}', [AccountReportsController::class, 'getLinkAccount']);
        Route::post('/link-account', [AccountReportsController::class, 'postLinkAccount']);
        Route::get('/cash-flow', [AccountController::class, 'cashFlow']);
    });

    Route::resource('account-types', AccountTypeController::class);

    //Restaurant module
    Route::prefix('modules')->group(function () {
        Route::resource('tables', Restaurant\TableController::class);
        Route::resource('modifiers', Restaurant\ModifierSetsController::class);

        //Map modifier to products
        Route::get('/product-modifiers/{id}/edit', [Restaurant\ProductModifierSetController::class, 'edit']);
        Route::post('/product-modifiers/{id}/update', [Restaurant\ProductModifierSetController::class, 'update']);
        Route::get('/product-modifiers/product-row/{product_id}', [Restaurant\ProductModifierSetController::class, 'product_row']);

        Route::get('/add-selected-modifiers', [Restaurant\ProductModifierSetController::class, 'add_selected_modifiers']);

        Route::get('/kitchen', [Restaurant\KitchenController::class, 'index']);
        Route::get('/kitchen/mark-as-cooked/{id}', [Restaurant\KitchenController::class, 'markAsCooked']);
        Route::post('/refresh-orders-list', [Restaurant\KitchenController::class, 'refreshOrdersList']);
        Route::post('/refresh-line-orders-list', [Restaurant\KitchenController::class, 'refreshLineOrdersList']);

        Route::get('/orders', [Restaurant\OrderController::class, 'index']);
        Route::get('/orders/mark-as-served/{id}', [Restaurant\OrderController::class, 'markAsServed']);
        Route::get('/data/get-pos-details', [Restaurant\DataController::class, 'getPosDetails']);
        Route::get('/orders/mark-line-order-as-served/{id}', [Restaurant\OrderController::class, 'markLineOrderAsServed']);
        Route::get('/print-line-order', [Restaurant\OrderController::class, 'printLineOrder']);
    });

    Route::get('bookings/get-todays-bookings', [Restaurant\BookingController::class, 'getTodaysBookings']);
    Route::resource('bookings', Restaurant\BookingController::class);

    Route::resource('types-of-service', TypesOfServiceController::class);
    Route::get('sells/edit-shipping/{id}', [SellController::class, 'editShipping']);
    Route::put('sells/update-shipping/{id}', [SellController::class, 'updateShipping']);
    Route::get('shipments', [SellController::class, 'shipments']);

    Route::post('upload-module', [Install\ModulesController::class, 'uploadModule']);
    Route::delete('manage-modules/destroy/{module_name}', [Install\ModulesController::class, 'destroy']);
    Route::resource('manage-modules', Install\ModulesController::class)
        ->only(['index', 'update']);
    Route::get('regenerate', [Install\ModulesController::class, 'regenerate']);

    Route::resource('warranties', WarrantyController::class);

    Route::resource('dashboard-configurator', DashboardConfiguratorController::class)
        ->only(['edit', 'update']);

    Route::get('view-media/{model_id}', [SellController::class, 'viewMedia']);

    //common controller for document & note
    Route::get('get-document-note-page', [DocumentAndNoteController::class, 'getDocAndNoteIndexPage']);
    Route::post('post-document-upload', [DocumentAndNoteController::class, 'postMedia']);
    Route::resource('note-documents', DocumentAndNoteController::class);
    Route::resource('purchase-order', PurchaseOrderController::class);
    Route::get('get-purchase-orders/{contact_id}', [PurchaseOrderController::class, 'getPurchaseOrders']);
    Route::get('get-purchase-order-lines/{purchase_order_id}', [PurchaseController::class, 'getPurchaseOrderLines']);
    Route::get('edit-purchase-orders/{id}/status', [PurchaseOrderController::class, 'getEditPurchaseOrderStatus']);
    Route::put('update-purchase-orders/{id}/status', [PurchaseOrderController::class, 'postEditPurchaseOrderStatus']);
    Route::resource('sales-order', SalesOrderController::class)->only(['index']);
    Route::get('get-sales-orders/{customer_id}', [SalesOrderController::class, 'getSalesOrders']);
    Route::get('get-sales-order-lines', [SellPosController::class, 'getSalesOrderLines']);
    Route::get('edit-sales-orders/{id}/status', [SalesOrderController::class, 'getEditSalesOrderStatus']);
    Route::put('update-sales-orders/{id}/status', [SalesOrderController::class, 'postEditSalesOrderStatus']);
    Route::get('reports/activity-log', [ReportController::class, 'activityLog']);
    Route::get('user-location/{latlng}', [HomeController::class, 'getUserLocation']);


    Route::get('/manage_user/employeesIndex', [ManageUserController::class, 'employeesIndex'])->name('employeesIndex');
    Route::get('/manage_user/makeUser/{id}', [ManageUserController::class, 'makeUser'])->name('makeUser');

    Route::prefix('agent')->group(function () {
        Route::get('/home', [AgentController::class, 'agentHome'])->name('agent_home');
        Route::get('/workers_requests', [AgentController::class, 'agentWorkersRequests'])->name('agent_workers_requests');
        Route::get('/projects', [AgentController::class, 'agentProjects'])->name('agent_projects');
        Route::get('/contracts', [AgentController::class, 'agentContracts'])->name('agent_contracts');
        Route::get('/workers', [AgentController::class, 'agentWorker'])->name('agent_workers');
        Route::get('/workers/{id}', [AgentController::class, 'showAgentWorker'])->name('show_agent_worker');

        Route::get('/requests', [AgentController::class, 'agentRequests'])->name('agentRequests');
        Route::post('/storeAgentRequests', [AgentController::class, 'storeAgentRequests'])->name('storeAgentRequests');

        Route::prefix('time_sheet')->group(function () {
            Route::get('/index', [TimeSheetController::class, 'index'])->name('agentTimeSheet.index');
            Route::get('/agentTimeSheetGroups', [TimeSheetController::class, 'agentTimeSheetGroups'])->name('agentTimeSheetGroups');
            Route::get('/create', [TimeSheetController::class, 'create'])->name('agentTimeSheet.create');
            Route::get('/getPayrollGroup', [TimeSheetController::class, 'getPayrollGroup'])->name('agentTimeSheet.getPayrollGroup');
            Route::get('/agentTimeSheetUsers', [TimeSheetController::class, 'agentTimeSheetUsers'])->name('agentTimeSheetUsers');
            Route::get('/showPayroll/{id}', [TimeSheetController::class, 'showPayroll'])->name('agentTimeSheet.showPayroll');

            Route::get('/timesheet-group/{id}/show', [TimeSheetController::class, 'showTimeSheet'])->name('agentTimeSheet.showTimeSheet');
            Route::get('/getWorkersBasedOnProject', [TimeSheetController::class, 'getWorkersBasedOnProject'])->name('agentTimeSheet.getWorkersBasedOnProject');
            // Route::get('/get_sheet/{id}', [TimeSheetController::class, 'timeSheet'])->name('agentTimeSheet.timeSheet');
            Route::get('/timeSheet', [TimeSheetController::class, 'timeSheet'])->name('agentTimeSheet.timeSheet');
            Route::post('/submitTmeSheet', [TimeSheetController::class, 'submitTmeSheet'])->name('agentTimeSheet.submitTmeSheet');
            Route::post('/store', [TimeSheetController::class, 'storeTimeSheet'])->name('agentTimeSheet.store');
            //viewPayrollGroup
            Route::get('/view/{id}/payroll-group', [TimeSheetController::class, 'viewPayrollGroup'])->name('agentTimeSheet.viewPayrollGroup');
            Route::get('/edit/{id}/payroll-group', [TimeSheetController::class, 'getEditPayrollGroup'])->name('agentTimeSheet.getEditPayrollGroup');
            Route::get('time_sheet/edit/{id}', [TimeSheetController::class, 'editTimeSheet'])->name('agentTimeSheet.editTimeSheet');
        });
    });
    Route::get('/employee_requests', [\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'employee_requests'])->name('employee_requests');



    Route::prefix('templates')->group(function () {
        Route::get('/create', [TemplateController::class, 'create'])->name('templates.create');

        Route::post('/', [TemplateController::class, 'store'])->name('templates.store');

        Route::get('/{id}/edit', [TemplateController::class, 'edit'])->name('templates.edit');

        Route::get('/', [TemplateController::class, 'index'])->name('templates.index');

        Route::post('/{id}', [TemplateController::class, 'update'])->name('templates.update');

        Route::get('/{id}', [TemplateController::class, 'show'])->name('templates.show');

        Route::get('/{id}/print', [TemplateController::class, 'print'])->name('templates.print');

        Route::get('/{id}/delete', [TemplateController::class, 'destroy'])->name('templates.delete');
    });
});

// Route::middleware(['EcomApi'])->prefix('api/ecom')->group(function () {
//     Route::get('products/{id?}', [ProductController::class, 'getProductsApi']);
//     Route::get('categories', [CategoryController::class, 'getCategoriesApi']);
//     Route::get('brands', [BrandController::class, 'getBrandsApi']);
//     Route::post('customers', [ContactController::class, 'postCustomersApi']);
//     Route::get('settings', [BusinessController::class, 'getEcomSettings']);
//     Route::get('variations', [ProductController::class, 'getVariationsApi']);
//     Route::post('orders', [SellPosController::class, 'placeOrdersApi']);
// });

//common route
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone'])->group(function () {

    Route::get('/load-more-notifications', [HomeController::class, 'loadMoreNotifications']);
    Route::get('/showNotificationModal', [HomeController::class, 'showNotificationModal'])->name('showNotificationModal');
    Route::get('/get-total-unread', [HomeController::class, 'getTotalUnreadNotifications']);
    Route::get('/purchases/print/{id}', [PurchaseController::class, 'printInvoice']);
    Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
    Route::get('/download-purchase-order/{id}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('purchaseOrder.downloadPdf');
    Route::get('/sells/{id}', [SellController::class, 'show']);
    Route::get('/sells/{transaction_id}/print', [SellPosController::class, 'printInvoice'])->name('sell.printInvoice');
    Route::get('/download-sells/{transaction_id}/pdf', [SellPosController::class, 'downloadPdf'])->name('sell.downloadPdf');
    Route::get('/download-quotation/{id}/pdf', [SellPosController::class, 'downloadQuotationPdf'])
        ->name('quotation.downloadPdf');
    Route::get('/download-packing-list/{id}/pdf', [SellPosController::class, 'downloadPackingListPdf'])
        ->name('packing.downloadPdf');
    Route::get('/sells/invoice-url/{id}', [SellPosController::class, 'showInvoiceUrl']);
    Route::get('/show-notification/{id}', [HomeController::class, 'showNotification']);
});