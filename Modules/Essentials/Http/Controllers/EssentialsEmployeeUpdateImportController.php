<?php

namespace Modules\Essentials\Http\Controllers;

use App\BusinessLocation;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\User;
use Illuminate\Support\Carbon;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\Validator;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FailedRowsExport;
use App\Exports\FailedRowsExportAttac;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Sales\Entities\SalesProject;
use ParagonIE\Sodium\Compat;

class EssentialsEmployeeUpdateImportController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }


    public function postImportupdateEmployee(Request $request)
    {
        if (!auth()->user()->can('essentials.import_update_employees') && !auth()->user()->hasRole('Admin#1')) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $errors = [];
        $failedRows = [];

        try {
            ini_set('max_execution_time', 0);
            if ($request->hasFile('update_employee_csv')) {
                $file = $request->file('update_employee_csv');
                $data = Excel::toArray([], $file);
                $imported_data = array_splice($data[0], 1);

                DB::beginTransaction();

                foreach ($imported_data as $key => $row) {
                    $row_no = $key + 2;
                    $isEmptyRow = true;
                    for ($i = 0; $i < 51; $i++) {
                        if ($row[$i] != null) {
                            $isEmptyRow = false;
                        }
                    }
                    if ($isEmptyRow) {
                        continue;
                    }
                    $emp_array = $this->prepareEmployeeData($row);
                    error_log('***********************************');
                    $validationResult = $this->validate($emp_array);
                    if ($validationResult['isValid'] == true) {
                        if ($validationResult['isNew']) {
                            $user = $this->createUser($emp_array);
                            // error_log(json_encode($user));
                            $this->createEmployee($user, $emp_array);
                        } else {
                            $this->updateEmployee($emp_array);
                        }
                    } else {
                        $errors[] = "Validation failed for row $row_no";
                        $failedRows[] = [
                            'Row' => $row_no,
                            'Data' => $emp_array,
                            'Errors' => $validationResult['errors']
                        ];
                    }
                }
                DB::commit();
                if (count($failedRows) > 0) {
                    return $this->exportFailedRows($failedRows);
                } else {
                    return redirect()->back()->with('status',   [
                        'success' => 1,
                        'msg' => 'Success',
                    ]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return redirect()->route('import-employees')->with('notification', ['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    public function importAttachements(Request $request)
    {
        error_log("Start importing attachments");

        $errors = [];
        $failedRows = [];

        try {
            ini_set('max_execution_time', 0);

            if ($request->hasFile('import_file')) {
                $file = $request->file('import_file');
                $data = Excel::toArray([], $file);
                $imported_data = array_splice($data[0], 1);

                DB::beginTransaction();

                foreach ($imported_data as $key => $row) {
                    $row_no = $key + 2;

                    if ($this->isEmptyRow($row)) {
                        continue;
                    }

                    $emp_array = $this->prepareEmployeeDataForAttach($row);
                    $validationResult = $this->validateEmployeeDataForAttach($emp_array);

                    error_log($validationResult['isValid']);

                    if ($validationResult['isValid'] === true) {
                        error_log('Valid data row');
                        $this->addFile($emp_array);
                    } else {
                        error_log("Validation failed for row $row_no");
                        $errors[] = "Validation failed for row $row_no";
                        $failedRows[] = [
                            'Row' => $row_no,
                            'Data' => $emp_array,
                            'Errors' => $validationResult['errors']
                        ];
                    }
                }

                DB::commit();

                if (count($failedRows) > 0) {
                    return $this->exportFailedRowsAttac($failedRows);
                } else {
                    return redirect()->back()->with('status', [
                        'success' => 1,
                        'msg' => 'Success',
                    ]);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage());
            return redirect()->route('attachements')->with('notification', ['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    private function isEmptyRow($row)
    {
        foreach ($row as $cell) {
            if (!is_null($cell)) {
                return false;
            }
        }
        return true;
    }


    private function prepareEmployeeDataForAttach($row)
    {
        error_log(json_encode($row));
        return [
            'id' => $row[0],
            'full_name' => $row[1],
            'id_proof_number' => $row[2],
            'profile_image' => $row[3],
            'contract' => $row[4],
            'activeResidencePermit' => $row[5],
            'activeNationalId' => $row[6],
            'activePassport' => $row[7],
            'activeInternationalCertificate' => $row[8],
            'activeDriversLicense' => $row[9],
            'activeIban' => $row[10],
            'activeCarRegistration' => $row[11],
            'activeQualification' => $row[12],
        ];
    }

    private function validateEmployeeDataForAttach($emp_array)
    {
        $errors = [];
        error_log($emp_array['full_name']);

        if (empty($emp_array['full_name'])) {
            $errors[] = 'Full name is required';
        }
        error_log($emp_array['id_proof_number']);
        if (empty($emp_array['id_proof_number'])) {

            $errors[] = 'ID proof number is required';
        } else {
            error_log($emp_array['id_proof_number']);
            $existingEmployee = User::where('id_proof_number', $emp_array['id_proof_number'])->first();
            if (!$existingEmployee) {
                $errors[] = 'User not found';
            }
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function addFile($emp_array)
    {
        try {
            $user = User::where('id_proof_number', $emp_array['id_proof_number'])->first();

            if ($user) {
                error_log($user->id);
                error_log($user->profile_image);

                if (!empty($emp_array['profile_image'])) {
                    error_log('New profile image: ' . $emp_array['profile_image']);
                    $user->profile_image = $emp_array['profile_image'];

                    if ($user->isDirty('profile_image')) {
                        $user->save();
                    }

                    if (!empty($emp_array['contract'])) {

                        $contract = EssentialsEmployeesContract::where('employee_id', $user->id)->where('is_active', 1)->orderBy('created_at', 'desc')->first();
                        if ($contract) {
                            $contract->file_path = $emp_array['contract'];
                            $contract->save();
                        } else {
                            EssentialsEmployeesContract::create([
                                'employee_id' => $user->id,
                                'file_path' => $emp_array['contract'],
                            ]);
                        }
                    }

                    $this->updateOrAddDocument($user->id, $emp_array['activeResidencePermit'], 'residence_permit');
                    $this->updateOrAddDocument($user->id, $emp_array['activeNationalId'], 'national_id');
                    $this->updateOrAddDocument($user->id, $emp_array['activePassport'], 'Passport');
                    $this->updateOrAddDocument($user->id, $emp_array['activeInternationalCertificate'], 'international_certificate');
                    $this->updateOrAddDocument($user->id, $emp_array['activeDriversLicense'], 'drivers_license');
                    $this->updateOrAddDocument($user->id, $emp_array['activeIban'], 'Iban');
                    $this->updateOrAddDocument($user->id, $emp_array['activeCarRegistration'], 'car_registration');

                    if (!empty($emp_array['activeQualification'])) {
                        $qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->orderBy('created_at', 'desc')->first();
                        if ($qualification) {
                            $qualification->file_path = $emp_array['activeQualification'];
                            $qualification->save();
                        } else {
                            EssentialsEmployeesQualification::create([
                                'employee_id' => $user->id,
                                'file_path' => $emp_array['activeQualification'],
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('An error occurred while adding file: ' . $e->getMessage());
        }
    }

    private function updateOrAddDocument($user_id, $file_path, $type)
    {
        error_log($type);

        if (!empty($file_path)) {
            $document = EssentialsOfficialDocument::where('employee_id', $user_id)->where('type', $type)->orderBy('created_at', 'desc')->where('is_active', 1)->first();
            if ($document) {
                //  $document->update(['file_path' => $file_path]);
                $document->file_path = $file_path;
                $document->save();
            } else {
                EssentialsOfficialDocument::create([
                    'employee_id' => $user_id,
                    'type' => $type,
                    'file_path' => $file_path,
                ]);
            }
        }
    }
    private function exportFailedRowsAttac($failedRows)
    {
        $export = new FailedRowsExportAttac($failedRows);
        return Excel::download($export, 'failed_rows.xlsx');
    }
    private function exportFailedRows($failedRows)
    {
        $export = new FailedRowsExport($failedRows);
        return Excel::download($export, 'failed_rows.xlsx');
    }

    // public function convertExcelDate($excelDate)
    // {
    //     error_log($excelDate);
    //     if (!$excelDate) {

    //         return $excelDate;
    //     } else {
    //         $excelDate = (int)$excelDate;

    //         $unixDate = ($excelDate - 25569) * 86400;
    //         error_log("Y-m-d", $unixDate);
    //         return gmdate("Y-m-d", $unixDate);
    //     }
    // }

    public function convertExcelDate($excelDate)
    {
        error_log($excelDate);

        $datePatternYMD = '/^\d{4}-\d{2}-\d{2}$/';
        $datePatternDMY = '/^\d{2}\/\d{2}\/\d{4}$/';

        if (!$excelDate) {
            error_log('111111');
            return $excelDate;
        } elseif (preg_match($datePatternYMD, $excelDate)) {
            error_log('222222222');
            error_log($excelDate);
            return $excelDate;
        } elseif (preg_match($datePatternDMY, $excelDate)) {
            error_log('5555555');
            $date = \DateTime::createFromFormat('d/m/Y', $excelDate);
            $convertedDate = $date->format('Y-m-d');
            error_log("Converted date: " . $convertedDate);
            return $convertedDate;
        } elseif (is_numeric($excelDate)) {
            error_log('333333');
            $excelDate = (int)$excelDate;
            $unixDate = ($excelDate - 25569) * 86400;
            $convertedDate = gmdate("Y-m-d", $unixDate);
            error_log("Converted date: " . $convertedDate);
            return $convertedDate;
        } else {
            error_log('4444444');
            error_log("Invalid date format: " . $excelDate);
            return $excelDate;
        }
    }



    private function validate($emp_array)
    {
        $errors = [];
        $proofNumberFound = true;
        $proofNameFound = true;
        $borderNumberFound = true;

        if (!isset($emp_array['id_proof_number']) || $emp_array['id_proof_number'] == null) {
            if (!isset($emp_array['border_no']) || $emp_array['border_no'] == null) {

                $errors[] = __('essentials::lang.id_proof_number_or_border_number_is_required');

                $borderNumberFound = false;
            }
            $proofNumberFound = false;
        }


        if (!isset($emp_array['id_proof_name']) || $emp_array['id_proof_name'] == null) {
            $errors[] = __('essentials::lang.id_proof_name_is_required');
            $proofNameFound = false;
        }

        $isNew = null;
        if ($proofNumberFound) {
            $isNew = User::where('id_proof_number', $emp_array['id_proof_number'])->first() == null;
        } else {
            if ($borderNumberFound) {
                $isNew = User::where('border_no', $emp_array['border_no'])->first() == null;
            }
        }



        if (($proofNumberFound || $borderNumberFound) && $proofNameFound) {
            if ($isNew) {

                // error_log(json_encode($emp_array));
                if (isset($emp_array['IBN_code']) && $emp_array['IBN_code'] != null) {
                    $notUnique = EssentialsOfficialDocument::where('type', 'Iban')->where('number', $emp_array['IBN_code'])->where('is_active', 1)->first() != null;

                    if ($notUnique) {
                        $errors[] = __('essentials::lang.iban_not_nuique');
                    }
                }

                if (isset($emp_array['border_no']) && $emp_array['border_no'] != null) {
                    $notUnique = User::where('border_no', $emp_array['border_no'])->first() != null;

                    if ($notUnique) {

                        $errors[] = __('essentials::lang.border_no_not_nuique');
                    }
                }
                if (isset($emp_array['passport_number']) && $emp_array['passport_number'] != null) {
                    $notUnique = EssentialsOfficialDocument::where('type', 'passport')->where('number', $emp_array['passport_number'])->where('is_active', 1)->first() != null;

                    if ($notUnique) {
                        $errors[] = __('essentials::lang.passport_number_not_nuique');
                    }
                }
                if (isset($emp_array['email']) && $emp_array['email'] != null) {
                    $notUnique = User::where('email', $emp_array['email'])->first() != null;

                    if ($notUnique) {
                        $errors[] = __('essentials::lang.email_not_nuique');
                    }
                }
                // if (isset($emp_array['emp_number']) && $emp_array['emp_number'] != null) {
                //     $notUnique = User::where('emp_number', $emp_array['emp_number'])->first() != null;

                //     if ($notUnique) {
                //         $errors[] = __('essentials::lang.emp_number_not_nuique');
                //     }
                // }

                if (isset($emp_array['profession_id']) && $emp_array['profession_id'] != null) {
                    $doesntExiste = EssentialsProfession::where('id', $emp_array['profession_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.profession_id_not_found');
                    }
                }
                if (isset($emp_array['sub_specialization']) && $emp_array['sub_specialization'] != null) {
                    $doesntExiste = EssentialsSpecialization::where('id', $emp_array['sub_specialization'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.sub_specialization_id_not_found');
                    }
                }
                // if (isset($emp_array['assigned_to']) && $emp_array['assigned_to'] != null) {
                //     if (is_numeric($emp_array['assigned_to'])) {
                //         $doesntExiste = SalesProject::where('id', $emp_array['assigned_to'])->first() == null;
                //         if ($doesntExiste) {
                //             $errors[] = __('essentials::lang.sales_project_id_not_found');
                //         }
                //     }
                // }
                if (isset($emp_array['assigned_to']) && $emp_array['assigned_to'] != null) {
                    $assignedTo = trim($emp_array['assigned_to']);
                    if (is_numeric($assignedTo)) {
                        $doesntExiste = SalesProject::where('id', $assignedTo)->first() == null;
                        if ($doesntExiste) {
                            $errors[] = __('essentials::lang.sales_project_id_not_found');
                        }
                    } else {
                        $validCategories = array_map('trim', ['vacation', 'reserved', 'escape', 'final_exit', 'return_exit']);
                        if (in_array($assignedTo, $validCategories)) {
                        } else {
                            $errors[] = __('essentials::lang.sales_project_id_not_found');
                        }
                    }
                }
                if (isset($emp_array['essentials_department_id']) && $emp_array['essentials_department_id'] != null) {
                    $doesntExiste = EssentialsDepartment::where('id', $emp_array['essentials_department_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.department_id_not_found');
                    }
                }
                if (isset($emp_array['contact_location_id']) && $emp_array['contact_location_id'] != null) {
                    $doesntExiste = BusinessLocation::where('id', $emp_array['contact_location_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.contact_location_id_not_found');
                    }
                }
                if (isset($emp_array['nationality_id']) && $emp_array['nationality_id'] != null) {
                    $doesntExiste = EssentialsCountry::where('id', $emp_array['nationality_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.nationality_id_not_found');
                    }
                }
                if (isset($emp_array['other_id']) && $emp_array['other_id'] != null) {
                    $doesntExiste = EssentialsAllowanceAndDeduction::where('id', $emp_array['other_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.other_id_not_found');
                    }
                }
                if (isset($emp_array['transportation_allowance_id']) && $emp_array['transportation_allowance_id'] != null) {
                    $doesntExiste = EssentialsAllowanceAndDeduction::where('id', $emp_array['transportation_allowance_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.transportation_allowance_id_not_found');
                    }
                }
                if (isset($emp_array['housing_allowance_id']) && $emp_array['housing_allowance_id'] != null) {
                    $doesntExiste = EssentialsAllowanceAndDeduction::where('id', $emp_array['housing_allowance_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.housing_allowance_id_not_found');
                    }
                }
                if (!(isset($emp_array['company_id'])) && $emp_array['company_id'] == null) {
                    $errors[] = __('essentials::lang.company_is_required');
                }
                if (isset($emp_array['company_id']) && $emp_array['company_id'] != null) {
                    $doesntExiste = Company::where('id', $emp_array['company_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.company_id_not_found');
                    }
                }
                if (isset($emp_array['contract_type_id']) && $emp_array['contract_type_id'] != null) {
                    $doesntExiste = EssentialsContractType::where('id', $emp_array['contract_type_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.contract_type_id_not_found');
                    }
                }
                if (!(isset($emp_array['first_name'])) || $emp_array['first_name'] == null) {
                    $errors[] = __('essentials::lang.first_name_is_required');
                }
                if (!(isset($emp_array['user_type'])) || $emp_array['user_type'] == null) {
                    $errors[] = __('essentials::lang.employee_type_is_required');
                }

                if (isset($emp_array['user_type']) && $emp_array['user_type'] != null) {
                    if ($emp_array['user_type'] != 'worker' && $emp_array['user_type'] != 'department_head' && $emp_array['user_type'] != 'employee' && $emp_array['user_type'] != 'manager') {
                        $errors[] = __('essentials::lang.employee_type_not_defined');
                    }
                }
                if (isset($emp_array['gender']) && $emp_array['gender'] != null) {
                    if ($emp_array['gender'] != 'male' && $emp_array['gender'] != 'female') {
                        $errors[] = __('essentials::lang.gender_not_defined');
                    }
                }
            } else {
                $existingUser = null;
                if ($proofNumberFound) {
                    $existingUser = User::where('id_proof_number', $emp_array['id_proof_number'])->first();
                } else {
                    if ($borderNumberFound) {
                        $existingUser = User::where('border_no', $emp_array['border_no'])->first();
                    }
                }

                if (isset($emp_array['IBN_code']) && $emp_array['IBN_code'] != null) {
                    $existing = json_decode($existingUser->bank_details)->bank_code ?? null;
                    if ($existing == null || $existing != $emp_array['IBN_code']) {
                        $notUnique = EssentialsOfficialDocument::where('type', 'Iban')->where('number', $emp_array['IBN_code'])->where('is_active', 1)->first() != null;

                        if ($notUnique) {
                            $errors[] = __('essentials::lang.iban_not_nuique');
                        }
                    }
                }

                if (isset($emp_array['border_no']) && $emp_array['border_no'] != null) {
                    $existing =  $existingUser->border_no ?? null;
                    if ($existing == null || $existing  != $emp_array['border_no']) {
                        $notUnique = User::where('border_no', $emp_array['border_no'])->first() != null;

                        if ($notUnique) {

                            $errors[] = __('essentials::lang.border_no_not_nuique');
                        }
                    }
                }
                if (isset($emp_array['passport_number']) && $emp_array['passport_number'] != null) {
                    $existing = EssentialsOfficialDocument::where('type', 'passport')->where('employee_id', $existingUser->id)->where('is_active', 1)->first()->number ?? null;
                    if ($existing == null || $existing != $emp_array['passport_number']) {
                        $notUnique = EssentialsOfficialDocument::where('type', 'passport')->where('number', $emp_array['passport_number'])->where('is_active', 1)->first() != null;

                        if ($notUnique) {
                            $errors[] = __('essentials::lang.passport_number_not_nuique');
                        }
                    }
                }
                if (isset($emp_array['email']) && $emp_array['email'] != null) {
                    $existing =  $existingUser->email ?? null;
                    if ($existing == null || $existing  != $emp_array['email']) {
                        $notUnique = User::where('email', $emp_array['email'])->first() != null;

                        if ($notUnique) {
                            $errors[] = __('essentials::lang.email_not_nuique');
                        }
                    }
                }
                if (isset($emp_array['emp_number']) && $emp_array['emp_number'] != null) {
                    $existing =  $existingUser->emp_number ?? null;
                    if ($existing == null || $existing  != $emp_array['emp_number']) {
                        $notUnique = User::where('emp_number', $emp_array['emp_number'])->first() != null;

                        if ($notUnique) {
                            $errors[] = __('essentials::lang.emp_number_not_nuique');
                        }
                    }
                }


                if (isset($emp_array['profession_id']) && $emp_array['profession_id'] != null) {
                    $doesntExiste = EssentialsProfession::where('id', $emp_array['profession_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.profession_id_not_found');
                    }
                }
                if (isset($emp_array['sub_specialization']) && $emp_array['sub_specialization'] != null) {
                    $doesntExiste = EssentialsSpecialization::where('id', $emp_array['sub_specialization'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.sub_specialization_id_not_found');
                    }
                }
                if (isset($emp_array['assigned_to']) && $emp_array['assigned_to'] != null) {
                    $assignedTo = trim($emp_array['assigned_to']);
                    if (is_numeric($assignedTo)) {
                        $doesntExiste = SalesProject::where('id', $assignedTo)->first() == null;
                        if ($doesntExiste) {
                            $errors[] = __('essentials::lang.sales_project_id_not_found');
                        }
                    } else {
                        $validCategories = array_map('trim', ['vacation', 'reserved', 'escape', 'final_exit', 'return_exit']);
                        if (in_array($assignedTo, $validCategories)) {
                        } else {
                            $errors[] = __('essentials::lang.sales_project_id_not_found');
                        }
                    }
                }
                if (isset($emp_array['essentials_department_id']) && $emp_array['essentials_department_id'] != null) {
                    $doesntExiste = EssentialsDepartment::where('id', $emp_array['essentials_department_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.department_id_not_found');
                    }
                }
                if (isset($emp_array['contact_location_id']) && $emp_array['contact_location_id'] != null) {
                    $doesntExiste = BusinessLocation::where('id', $emp_array['contact_location_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.contact_location_id_not_found');
                    }
                }
                if (isset($emp_array['nationality_id']) && $emp_array['nationality_id'] != null) {
                    $doesntExiste = EssentialsCountry::where('id', $emp_array['nationality_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.nationality_id_not_found');
                    }
                }
                if (isset($emp_array['other_id']) && $emp_array['other_id'] != null) {
                    $doesntExiste = EssentialsAllowanceAndDeduction::where('id', $emp_array['other_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.other_id_not_found');
                    }
                }
                if (isset($emp_array['transportation_allowance_id']) && $emp_array['transportation_allowance_id'] != null) {
                    $doesntExiste = EssentialsAllowanceAndDeduction::where('id', $emp_array['transportation_allowance_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.transportation_allowance_id_not_found');
                    }
                }
                if (isset($emp_array['housing_allowance_id']) && $emp_array['housing_allowance_id'] != null) {
                    $doesntExiste = EssentialsAllowanceAndDeduction::where('id', $emp_array['housing_allowance_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.housing_allowance_id_not_found');
                    }
                }
                if (isset($emp_array['company_id']) && $emp_array['company_id'] != null) {
                    $doesntExiste = Company::where('id', $emp_array['company_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.company_id_not_found');
                    }
                }
                if (isset($emp_array['contract_type_id']) && $emp_array['contract_type_id'] != null) {
                    $doesntExiste = EssentialsContractType::where('id', $emp_array['contract_type_id'])->first() == null;
                    if ($doesntExiste) {
                        $errors[] = __('essentials::lang.contract_type_id_not_found');
                    }
                }
            }
        }
        if (!empty($errors)) {
            return [
                'isValid' => false,
                'errors' => $errors
            ];
        }
        return ['isValid' => true, 'isNew' => $isNew];
    }

    private function prepareEmployeeData($row)
    {


        return [
            'emp_number' => $row[0],
            'first_name' => $row[1],
            'mid_name' => $row[2],
            'last_name' => $row[3],
            'user_type' => $row[4],
            'email' => $row[5],
            'dob' => $this->convertExcelDate($row[6]),
            'gender' => $row[7],
            'marital_status' => $row[8],
            'blood_group' => $row[9],
            'contact_number' => trim($row[10]),
            'alt_number' => $row[11],
            'family_number' => $row[12],
            'current_address' => $row[13],
            'permanent_address' => $row[14],
            'id_proof_name' => $row[15],
            'id_proof_number' => $row[16],
            'id_proof_number_expiration_date' => $this->convertExcelDate($row[17]),
            'passport_number' => $row[18],
            'passport_expiration_date' => $this->convertExcelDate($row[19]),
            'account_holder_name' => $row[20],
            'account_number' => $row[21],
            'bank_name' => $row[22],
            'bank_code' => $row[23],
            'bank_details' => json_encode([
                'account_holder_name' => $row[20],
                'account_number' => $row[21],
                'bank_name' => $row[22],
                'bank_code' => $row[23]
            ]),
            'assigned_to' => $row[24],
            'contact_location_id' => $row[25],
            'business_id' => $row[26],
            'location_id' => $row[27],
            'essentials_department_id' => $row[28],
            'admission_date' => $this->convertExcelDate($row[29]),
            'sub_specialization' => $row[30],
            'profession_id' => $row[31],
            'border_no' => $row[32],
            'nationality_id' => $row[33],
            'contract_number' => $row[34],
            'contract_start_date' => $this->convertExcelDate($row[35]),
            'contract_end_date' => $this->convertExcelDate($row[36]),
            'contract_duration' => $row[37],
            'probation_period' => $row[38],
            'is_renewable' => $row[39],
            'contract_type_id' => $row[40],
            'essentials_salary' => $row[41],
            'housing_allowance_id' => $row[42],
            'housing_allowance_amount' => $row[43],
            'transportation_allowance_id' => $row[44],
            'transportation_allowance_amount' => $row[45],
            'other_id' => $row[46],
            'other_amount' => $row[47],
            'allowance_data' =>
            [
                'housing_allowance' => json_encode(['id' => $row[42], 'amount' => $row[43]]),
                'transportation_allowance' => json_encode(['id' => $row[44], 'amount' => $row[45]]),
                'other' => json_encode(['id' => $row[46], 'amount' => $row[47]]),
            ],


            'total_salary' => $row[48],
            'company_id' => $row[49],
            'english_name' => $row[50],
            'appointment_start_from' =>  $this->convertExcelDate($row[51]),
        ];
    }

    // update employee 
    private function updateEmployee($emp_array)
    {
        $existingEmployee = null;
        if (isset($emp_array['id_proof_number']) && $emp_array['id_proof_number'] != null) {
            $existingEmployee = User::where('id_proof_number', $emp_array['id_proof_number'])->first();
        } else {
            if (isset($emp_array['border_no']) && $emp_array['border_no'] != null) {
                $existingEmployee = User::where('border_no', $emp_array['border_no'])->first();
            }
        }

        $filtered_array = array_filter($emp_array, function ($value) {
            return !is_null($value);
        });
        try {


            $this->updateUser($filtered_array, $existingEmployee);
            $this->updateContract($filtered_array, $existingEmployee);
            $this->updateAppointmet($filtered_array, $existingEmployee);
            $this->updateAdmission($filtered_array, $existingEmployee);
            $this->createOrUpdateAllowanceAndDeduction($filtered_array, $existingEmployee);
            $this->updateOfficalDocument($filtered_array, $existingEmployee);
            $this->updateQualification($filtered_array, $existingEmployee);
        } catch (\Exception $e) {

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return redirect()->route('import-employees')->with('notification', ['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    private function updateQualification($formated_data, $existingEmployee)
    {
        $previous_qualification = EssentialsEmployeesQualification::where('employee_id',  $existingEmployee->id)->first();
        if (isset($formated_data['sub_specialization']) && $formated_data['sub_specialization'] != null) {
            if ($previous_qualification) {
                $previous_qualification->update(['sub_specialization' => $formated_data['sub_specialization']]);
            } else {
                $qualification = new EssentialsEmployeesQualification();
                $qualification->employee_id = $existingEmployee->id;
                $qualification->sub_specialization = $formated_data['sub_specialization'];
            }
        }
    }

    private function updateOfficalDocument($formated_data, $existingEmployee)
    {
        if (isset($formated_data['id_proof_name']) && $formated_data['id_proof_name'] === 'eqama') {
            $this->updateDocument(
                $formated_data['id_proof_number_expiration_date'] ?? null,
                'residence_permit',
                $formated_data['id_proof_number'] ?? null,
                $existingEmployee->id
            );
        }

        if (isset($formated_data['passport_expiration_date']) && $formated_data['passport_expiration_date'] !== null) {
            $this->updateDocument(
                $formated_data['passport_expiration_date'],
                'passport',
                $formated_data['passport_number'] ?? null,
                $existingEmployee->id,
                auth()->user()->id
            );
        }
    }

    private function updateDocument($expirationDate, $type, $number, $employeeId, $createdBy = null)
    {
        if ($expirationDate === null) {
            return;
        }

        $previousDocument = EssentialsOfficialDocument::where('employee_id', $employeeId)
            ->where('type', $type)
            ->where('is_active', 1)
            ->first();

        if ($previousDocument && $previousDocument->expiration_date == $expirationDate && $previousDocument->number == $number) {
            error_log("No changes detected in $type data");
            return;
        }

        if ($previousDocument) {
            $filePath = $previousDocument->file_path ?? null;
            $previousDocument->update(['is_active' => 0]);
        }

        $documentData = [
            'status' => 'valid',
            'is_active' => 1,
            'file_path' => $filePath ?? null,
            'type' => $type,
            'employee_id' => $employeeId,
            'number' => $number,
            'expiration_date' => $expirationDate,
            'created_by' => $createdBy
        ];

        $filteredDocumentData = array_filter($documentData, function ($value) {
            return $value !== null;
        });

        if (!empty($filteredDocumentData)) {
            EssentialsOfficialDocument::create($filteredDocumentData);
        }
    }


    private function createOrUpdateAllowanceAndDeduction($formated_data, $existingEmployee)
    {
        foreach ($formated_data['allowance_data'] as $allowanceJson) {
            $allowanceData = json_decode($allowanceJson, true);

            try {
                if ($allowanceData['id'] !== null && $allowanceData['amount'] !== null && isset($allowanceData['amount'])) {

                    EssentialsUserAllowancesAndDeduction::updateOrCreate(
                        [
                            'user_id' => $existingEmployee->id,
                            'allowance_deduction_id' => (int)$allowanceData['id'] ?? null,
                        ],
                        [
                            'amount' => $allowanceData['amount'] ?? null,
                        ]
                    );
                }
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            }
        }
    }

    private function updateAdmission($formated_data, $existingEmployee)
    {
        if (isset($formated_data['admission_date']) && $formated_data['admission_date'] != null) {
            $previous_admission = EssentialsAdmissionToWork::where('employee_id', $existingEmployee->id)
                ->where('is_active', 1)->first();

            if ($previous_admission) {
                $previous_admission->is_active = 0;
                $previous_admission->save();

                //contract start date compare 
                $essentials_admission_to_works = new EssentialsAdmissionToWork();
                $essentials_admission_to_works->admissions_date = $formated_data['admission_date'];
                $essentials_admission_to_works->employee_id = $existingEmployee->id;
                $essentials_admission_to_works->admissions_type = "after_vac";
                $essentials_admission_to_works->admissions_status = "on_date";
                $essentials_admission_to_works->is_active = 1;
                $essentials_admission_to_works->save();
            } else {
                //also contract  start date
                $essentials_admission_to_works = new EssentialsAdmissionToWork();
                $essentials_admission_to_works->admissions_date = $formated_data['admission_date'];
                $essentials_admission_to_works->employee_id = $existingEmployee->id;
                $essentials_admission_to_works->admissions_type = "first_time";
                $essentials_admission_to_works->admissions_status = "on_date";
                $essentials_admission_to_works->is_active = 1;
                $essentials_admission_to_works->save();
            }
        }
    }

    private function updateAppointmet($formatted_data, $existingEmployee)
    {
        if ($formatted_data && ((isset($formatted_data['essentials_department_id']) && $formatted_data['essentials_department_id'] != null) || (isset($formatted_data['profession_id']) && $formatted_data['profession_id'] != null))) {

            $contract = EssentialsEmployeesContract::where('employee_id', $existingEmployee->id)->where('is_active', 1)->first();
            $start_from = $formatted_data['appointment_start_from'] ?? null;

            // if ($contract && $start_from === null) {
            //     $start_from = $contract->contract_start_date;
            // }

            $previous_appointment = EssentialsEmployeeAppointmet::where('employee_id', $existingEmployee->id)->where('is_active', 1)->first();

            if ($previous_appointment) {
                if (
                    $previous_appointment->essentials_department_id == $formatted_data['essentials_department_id'] &&
                    $previous_appointment->profession_id == $formatted_data['profession_id'] &&
                    $previous_appointment->start_from == $start_from
                ) {
                    return;
                } else {

                    $previous_appointment->update(['is_active' => 0, 'end_at' => Carbon::today()]);
                }
            }

            $appointmentData = [
                'employee_id' => $existingEmployee->id,
                'start_from' => $start_from,
                'profession_id' => $formatted_data['profession_id'],
                'is_active' => 1
            ];

            if ($existingEmployee->user_type == 'worker') {
                $appointmentData['department_id'] = null;
            } else {
                $appointmentData['department_id'] = $formatted_data['essentials_department_id'];
            }

            $filteredAppointmentData = array_filter($appointmentData, function ($value) {
                return $value !== null;
            });

            if (!empty($filteredAppointmentData)) {
                EssentialsEmployeeAppointmet::create($filteredAppointmentData);
                $existingEmployee->essentials_department_id = $filteredAppointmentData['department_id'];
                $existingEmployee->save();
            }
        }
    }

    private function updateContract($formated_data, $existingEmployee)
    {
        error_log('here_contract');
        if (!empty($formated_data) && (isset($formated_data['contract_start_date']) || isset($formated_data['contract_end_date']) || isset($formated_data['contract_type_id']))) {
            error_log('here_contract_update');

            $previous_contract = EssentialsEmployeesContract::where('employee_id', $existingEmployee->id)->where('is_active', 1)->first();

            if ($previous_contract && $this->isSameContract($previous_contract, $formated_data)) {
                error_log('No changes detected in contract data');
                return;
            }

            if ((isset($formated_data['contract_start_date']) && $formated_data['contract_start_date'] != null) || (isset($formated_data['contract_end_date']) && $formated_data['contract_end_date'] != null)) {

                $file = $previous_contract->file_path ?? null;
                if ($previous_contract) {
                    $previous_contract->update(['is_active' => 0]);
                }

                $contract = new EssentialsEmployeesContract();

                if (isset($formated_data['contract_start_date']) && !isset($formated_data['contract_end_date'])) {
                    $contract_start_date = $formated_data['contract_start_date'];
                    $date = Carbon::parse($contract_start_date)->addYear();
                    $contract->contract_start_date = $contract_start_date;
                    $contract->contract_end_date = $date;
                    $contract->contract_duration = 1;
                } elseif (!isset($formated_data['contract_start_date']) && isset($formated_data['contract_end_date'])) {
                    $contract_end_date = $formated_data['contract_end_date'];
                    $date = Carbon::parse($contract_end_date)->subYear();
                    $contract->contract_start_date = $date;
                    $contract->contract_end_date = $contract_end_date;
                    $contract->contract_duration = 1;
                } else {
                    $contract_start_date = $formated_data['contract_start_date'];
                    $contract_end_date = $formated_data['contract_end_date'];
                    $start = Carbon::parse($contract_start_date);
                    $end = Carbon::parse($contract_end_date);
                    $contract_duration = $start->diffInYears($end);
                    $contract->contract_start_date = $contract_start_date;
                    $contract->contract_end_date = $contract_end_date;
                    $contract->contract_duration = $contract_duration;
                }

                $contract->file_path = $file;
                $contract->employee_id  = $existingEmployee->id;
                $contract->contract_number = $this->generateContractNumber($formated_data);
                $contract->probation_period = $formated_data["probation_period"] ?? 1;
                $contract->is_renewable = $formated_data['is_renewable'] ?? 1;
                $contract->contract_type_id  = $formated_data["contract_type_id"] ?? null;
                $contract->is_active  = 1;
                $contract->status = "valid";
                $contract->save();
            }
        }
    }

    private function isSameContract($previous_contract, $formated_data)
    {
        return $previous_contract->contract_start_date == ($formated_data['contract_start_date'] ?? $previous_contract->contract_start_date)
            && $previous_contract->contract_end_date == ($formated_data['contract_end_date'] ?? $previous_contract->contract_end_date)
            && $previous_contract->contract_type_id == ($formated_data['contract_type_id'] ?? $previous_contract->contract_type_id);
    }

    private function generateContractNumber(&$formated_data)
    {
        if (!isset($formated_data['contract_number']) || $formated_data['contract_number'] == null) {
            $latestRecord = EssentialsEmployeesContract::orderBy('contract_number', 'desc')->first();
            if ($latestRecord) {
                $latestRefNo = $latestRecord->contract_number;
                $numericPart = (int)substr($latestRefNo, 2);
                $numericPart++;
                $formated_data['contract_number'] = 'EC' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
            } else {
                $formated_data['contract_number'] = 'EC0001';
            }
        }
        return $formated_data['contract_number'];
    }

    private function updateUser($formated_data, $existingEmployee)
    {

        if (!empty($formated_data)) {
            $formated_data['id_proof_number_expiration_date'] = null;
            $formated_data['passport_expiration_date'] = null;
            $formated_data['passport_number'] = null;
            $formated_data['admission_date'] = null;
            $formated_data['sub_specialization'] = null;
            $formated_data['profession_id'] = null;
            $formated_data['contract_number'] = null;
            $formated_data['contract_start_date'] = null;
            $formated_data['contract_end_date'] = null;
            $formated_data['contract_duration'] = null;
            $formated_data['probation_period'] = null;
            $formated_data['is_renewable'] = null;
            $formated_data['contract_type_id'] = null;
            $formated_data['allowance_data'] = null;



            if ((!isset($formated_data['emp_number']) || $formated_data['emp_number'] == null) &&  $existingEmployee->emp_number == null) {
                $formated_data['emp_number'] = $this->moduleUtil->generateEmpNumber($formated_data['company_id'] ?? $existingEmployee->company_id);
            }

            if (isset($formated_data['assigned_to']) && $formated_data['assigned_to'] != null && !is_numeric($formated_data['assigned_to'])) {

                $formated_data['sub_status'] = $formated_data['assigned_to'];
                $formated_data['assigned_to'] = null;
                $formated_data['status'] = 'inactive';
            }

            $formated_data['allowance_data'] = null;


            $formated_data['essentials_pay_period'] = 'month';
            $dataToUpdate = array_filter($formated_data, function ($value) {
                return !is_null($value);
            });

            if (isset($formated_data['assigned_to']) && $formated_data['assigned_to'] == null) {
                $dataToUpdate['assigned_to'] = null;
            }
            $existingEmployee->update($dataToUpdate);
        }
    }




    //create employee

    private function createEmployee($existingEmployee, $emp_array)
    {

        $this->createContract($emp_array, $existingEmployee);
        $this->createAppointmet($emp_array, $existingEmployee);
        $this->createAdmission($emp_array, $existingEmployee);
        $this->createOrUpdateAllowanceAndDeduction($emp_array, $existingEmployee);
        $this->createOfficalDocument($emp_array, $existingEmployee);
        $this->createQualification($emp_array, $existingEmployee);
    }

    private function createQualification($formated_data, $existingEmployee)
    {
        if (isset($formated_data['sub_specialization']) && $formated_data['sub_specialization'] != null) {

            $qualification = new EssentialsEmployeesQualification();
            $qualification->employee_id = $existingEmployee->id;
            $qualification->sub_specialization = $formated_data['sub_specialization'];
        }
    }

    private function createOfficalDocument($formated_data, $existingEmployee)
    {
        if (isset($formated_data['id_proof_name']) && $formated_data['id_proof_name'] != null && $formated_data['id_proof_name'] == 'eqama') {
            if (isset($formated_data['id_proof_number_expiration_date']) && $formated_data['id_proof_number_expiration_date'] != null) {


                $residencePermitData =
                    [
                        'status' => 'valid',
                        'is_active' => 1,
                        'type' => 'residence_permit',
                        'employee_id' => $existingEmployee->id,
                        'number' => $formated_data['id_proof_number'],
                        'expiration_date' => $formated_data['id_proof_number_expiration_date'],
                        'created_by' => auth()->user()->id
                    ];
                $filteredResidencePermitData = array_filter($residencePermitData, function ($value) {
                    return $value !== null;
                });


                if (!empty($filteredResidencePermitData)) {
                    EssentialsOfficialDocument::Create($filteredResidencePermitData);
                }
            }
        }
        if (isset($formated_data['passport_expiration_date']) && $formated_data['passport_expiration_date'] != null) {

            $passportData =
                [
                    'status' => 'valid',
                    'is_active' => 1,
                    'employee_id' => $existingEmployee->id,
                    'type' => 'passport',
                    'number' => $formated_data['passport_number'],
                    'expiration_date' => $formated_data['passport_expiration_date'],
                    'created_by' => auth()->user()->id
                ];


            $filteredPassportData = array_filter($passportData, function ($value) {
                return $value !== null;
            });

            if (!empty($filteredPassportData)) {

                EssentialsOfficialDocument::Create(

                    $filteredPassportData
                );
            }
        }
    }


    private function createAdmission($formated_data, $existingEmployee)
    {
        if (isset($formated_data['admission_date']) && $formated_data['admission_date'] != null) {



            $essentials_admission_to_works = new EssentialsAdmissionToWork();
            $essentials_admission_to_works->admissions_date = $formated_data['admission_date'];
            $essentials_admission_to_works->employee_id = $existingEmployee->id;
            $essentials_admission_to_works->admissions_type = "first_time";
            $essentials_admission_to_works->admissions_status = "on_date";
            $essentials_admission_to_works->is_active = 1;
            $essentials_admission_to_works->save();
        }
    }

    private function createAppointmet($formated_data, $existingEmployee)
    {


        if ($formated_data && ((isset($formated_data['essentials_department_id']) && $formated_data['essentials_department_id'] != null) || (isset($formated_data['profession_id']) && $formated_data['profession_id'] != null))) {

            $contract = EssentialsEmployeesContract::where('employee_id', $existingEmployee->id)->where('is_active', 1)->first();
            $start_from = $formated_data['appointment_start_from'] ?? null;
            if ($contract && $start_from === null) {
                $start_from = $contract->contract_start_date;
            }

            $appointmentData = [];
            if ($existingEmployee->user_type == 'worker') {

                $appointmentData =
                    [
                        'employee_id' => $existingEmployee->id,
                        'start_from' =>   $start_from,
                        'department_id' => null,
                        'profession_id' => $formated_data['profession_id'],
                        'is_active' => 1,

                    ];
            } else {

                $appointmentData =
                    [
                        'employee_id' => $existingEmployee->id,
                        'start_from' =>   $start_from,
                        'department_id' => $formated_data['essentials_department_id'],
                        'profession_id' => $formated_data['profession_id'],
                        'is_active' => 1,

                    ];
            }


            $filteredAppointmentData = array_filter($appointmentData, function ($value) {
                return $value !== null;
            });

            if (!empty($filteredAppointmentData)) {


                EssentialsEmployeeAppointmet::Create($filteredAppointmentData);
                $existingEmployee->essentials_department_id = $filteredAppointmentData['department_id'] ?? null;
                $existingEmployee->save();
            }
        }
    }

    private function createContract($formated_data, $existingEmployee)
    {
        if (!empty($formated_data) && (isset($formated_data['contract_start_date']) || isset($formated_data['contract_end_date']) || isset($formated_data['contract_type_id']))) {


            $contract = new EssentialsEmployeesContract();


            if ((isset($formated_data['contract_start_date']) && $formated_data['contract_start_date'] != null) ||  (isset($formated_data['contract_end_date']) && $formated_data['contract_end_date'] != null)) {

                if ((isset($formated_data['contract_start_date']) && $formated_data['contract_start_date'] != null) && (!isset($formated_data['contract_end_date']) || $formated_data['contract_end_date'] == null)) {

                    $contract_start_date = $formated_data['contract_start_date'];

                    $date = Carbon::parse($contract_start_date);
                    $date->addYear();
                    $contract->contract_end_date = $date;
                    $contract->contract_start_date = $formated_data['contract_start_date'];
                    $contract->contract_duration = 1;
                } else if ((!isset($formated_data['contract_start_date']) || $formated_data['contract_start_date'] == null) && (isset($formated_data['contract_end_date']) && $formated_data['contract_end_date'] != null)) {
                    $contract_end_date = $formated_data['contract_end_date'];
                    $date = Carbon::parse($contract_end_date);
                    $date->subYear();
                    $contract->contract_start_date = $date;

                    $contract->contract_end_date = $formated_data['contract_end_date'];
                    $contract->contract_duration = 1;
                } else {
                    $contract_end_date = $formated_data['contract_end_date'];
                    $contract_start_date = $formated_data['contract_start_date'];


                    $start = Carbon::parse($contract_start_date);
                    $end = Carbon::parse($contract_end_date);

                    $contract_duration = $start->diffInYears($end);
                    $contract->contract_start_date = $formated_data['contract_start_date'];
                    $contract->contract_end_date = $formated_data['contract_end_date'];
                    $contract->contract_duration = $contract_duration;
                }
            }

            $contract->employee_id  = $existingEmployee->id;
            if ((!isset($formated_data['contract_number']) || $formated_data['contract_number'] == null)) {
                $latestRecord = EssentialsEmployeesContract::orderBy('contract_number', 'desc')->first();
                if ($latestRecord) {
                    $latestRefNo = $latestRecord->contract_number;
                    $numericPart = (int)substr($latestRefNo, 3);
                    $numericPart++;
                    $formated_data['contract_number'] = 'EC' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
                } else {
                    $formated_data['contract_number'] = 'EC0001';
                }
                $contract->contract_number = $formated_data['contract_number'];
            } else {
                $contract->contract_number = $formated_data['contract_number'];
            }
            $contract->probation_period = $formated_data["probation_period"] ?? 1;
            $contract->is_renewable = $formated_data['is_renewable'] ?? 1;
            $contract->contract_type_id  = $formated_data["contract_type_id"] ?? null;
            $contract->is_active  = 1;
            $contract->status = "valid";
            $contract->save();
        }
    }
    private function createUser($formated_data)
    {
        try {
            // error_log(json_encode($formated_data));
            if (!empty($formated_data)) {
                $formated_data['id_proof_number_expiration_date'] = null;
                $formated_data['passport_expiration_date'] = null;
                $formated_data['passport_number'] = null;
                $formated_data['admission_date'] = null;
                $formated_data['sub_specialization'] = null;
                $formated_data['profession_id'] = null;
                $formated_data['contract_number'] = null;
                $formated_data['contract_start_date'] = null;
                $formated_data['contract_end_date'] = null;
                $formated_data['contract_duration'] = null;
                $formated_data['probation_period'] = null;
                $formated_data['is_renewable'] = null;
                $formated_data['contract_type_id'] = null;
                $formated_data['allowance_data'] = null;
                $formated_data['appointment_start_from'] = null;




                if ((!isset($formated_data['emp_number']) || $formated_data['emp_number'] == null)) {
                    $formated_data['emp_number'] = $this->moduleUtil->generateEmpNumber($formated_data['company_id']);
                }
                if (isset($formated_data['assigned_to']) && $formated_data['assigned_to'] != null && !is_numeric($formated_data['assigned_to'])) {


                    $formated_data['sub_status'] = $formated_data['assigned_to'];
                    $formated_data['assigned_to'] = null;
                    $formated_data['status'] = 'inactive';
                }
                $formated_data['created_by'] = auth()->user()->id;
                $formated_data['essentials_pay_period'] = 'month';
                $dataToUpdate = array_filter($formated_data, function ($value) {
                    return !is_null($value);
                });
                if (isset($formated_data['assigned_to']) && $formated_data['assigned_to'] == null) {
                    $dataToUpdate['assigned_to'] = null;
                }
                // $user = new User();

                $user = User::create($dataToUpdate);
                return $user;
            }
        } catch (\Exception $e) {

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }

    public function downloadFile($filename)
    {
        $path = public_path('uploads/' . $filename);

        if (file_exists($path)) {
            return response()->download($path);
        }

        abort(404, 'File not found');
    }
}
