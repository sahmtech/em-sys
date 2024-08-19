<?php

namespace Modules\Essentials\Http\Controllers;

use App\Contact;
use App\User;
use Excel;
use App\Utils\ModuleUtil;
use App\Utils\NewArrivalUtil;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeesInsurance;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsInsuranceCompany;
use Modules\Essentials\Entities\EssentialsEmployeesFamily;
use Modules\Essentials\Entities\EssentialsCompaniesInsurancesContract;



class EssentialsEmployeeInsuranceController extends Controller
{
    protected $moduleUtil;
    protected $newArrivalUtil;

    public function __construct(ModuleUtil $moduleUtil, NewArrivalUtil $newArrivalUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->newArrivalUtil = $newArrivalUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */


    public function import_employee_insurance_index()
    {

        $zip_loaded = extension_loaded('zip') ? true : false;

        if ($zip_loaded === false) {
            $output = [
                'success' => 0,
                'msg' => 'Please install/enable PHP Zip archive for import',
            ];


            return view('essentials::employee_affairs.employee_insurance.import_employee_insurance_index')
                ->with($output);
        } else {
            return view('essentials::employee_affairs.employee_insurance.import_employee_insurance_index');
        }
    }

    //store excel insurance 
    public function insurancepostImportEmployee(Request $request)
    {

        try {

            ini_set('max_execution_time', 0);


            if ($request->hasFile('employee_insurance_csv')) {
                $file = $request->file('employee_insurance_csv');
                $parsed_array = Excel::toArray([], $file);
                $imported_data = array_splice($parsed_array[0], 1);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $processedIdProofNumbers = [];
                $formated_data = [];
                $is_valid = true;
                $error_msg = '';



                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                    $row_no = $key + 1;
                    $emp_array = [];

                    $emp_array['eqama_emp_no'] = intval($value[0]);
                    if (!empty($value[0])) {


                        $proof_number = User::where('id_proof_number', $emp_array['eqama_emp_no'])->first();
                        $border_no = User::where('border_no', $emp_array['eqama_emp_no'])->first();
                        $family_proof_number = EssentialsEmployeesFamily::where('eqama_number', $emp_array['eqama_emp_no'])->first();

                        if ($proof_number == null && $border_no == null &&  $family_proof_number == null) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.number_not_found') . $row_no;
                            break;
                        }

                        //   if( $proof_number !=null && $border_no==null &&  $family_proof_number ==null )
                        //   {
                        //     $emp = User::where('id_proof_number', $emp_array['eqama_emp_no'])->first();
                        //     if($emp)
                        //     {
                        //         $emp_insurance=EssentialsEmployeesInsurance::where('employee_id' ,$emp->id)->first();
                        //         if($emp_insurance)
                        //         {
                        //             $is_valid = false;
                        //             $error_msg = __('essentials::lang.proof_number_has_insurance').$row_no;
                        //             break;
                        //         }
                        //     }


                        //   }

                        // else if( $proof_number ==null && $border_no !=null &&  $family_proof_number ==null )
                        //   {
                        //     $emp_border = User::where('border_no', $emp_array['eqama_emp_no'])->first();
                        //     if(  $emp_border )
                        //     {
                        //         $emp_insurance=EssentialsEmployeesInsurance::where('employee_id' ,$emp_border->id)->first();
                        //         if($emp_insurance)
                        //         {
                        //             $is_valid = false;
                        //             $error_msg = __('essentials::lang.border_no_has_insurance').$row_no;
                        //             break;
                        //         }
                        //     }


                        //   }

                        //  else if( $proof_number ==null && $border_no ==null &&  $family_proof_number !=null )
                        //   {

                        //     $family = EssentialsEmployeesFamily::where('eqama_number',$emp_array['eqama_emp_no'])->first();


                        //     if(  $family){
                        //         $emp_insurance=EssentialsEmployeesInsurance::where('family_id' ,$family->id)->first();
                        //         if($emp_insurance)
                        //         {
                        //             $is_valid = false;
                        //             $error_msg = __('essentials::lang.family_has_insurance').$row_no;
                        //             break;
                        //         }
                        //     }


                        //   }



                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.eqama_number_required') . $row_no;
                        break;
                    }


                    $emp_array['insurance_class_id'] = $value[1];
                    if (!empty($value[1])) {
                        $class = EssentialsInsuranceClass::where('id', $emp_array['insurance_class_id'])->first();
                        $proof_number_emp = User::where('id_proof_number', $emp_array['eqama_emp_no'])->first();
                        $family_proof_number = EssentialsEmployeesFamily::where('eqama_number', $emp_array['eqama_emp_no'])->first();

                        if (!$class) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.insurance_class_id_not_found') . $row_no;
                            break;
                        } else if ($proof_number_emp != null &&   $family_proof_number == null) {

                            $company_id = $proof_number_emp->company_id;
                            $insurance_company = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)->first();
                            if ($insurance_company) {
                                $classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company->insur_id)
                                    ->get();


                                if (!in_array($emp_array['insurance_class_id'], $classes->pluck('id')->toArray())) {
                                    $is_valid = false;
                                    $error_msg = __('essentials::lang.insurance_class_not_found') . $row_no;
                                    break;
                                }
                            } else {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.no_company_added') . $row_no;
                                break;
                            }
                        } else if ($family_proof_number != null && $proof_number_emp == null) {
                            $emp = User::where('id', $family_proof_number->employee_id)->first();
                            $company_id = $emp->company_id;
                            $insurance_company = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)->first();
                            if ($insurance_company) {
                                $classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company->insur_id)
                                    ->get();


                                if (!in_array($emp_array['insurance_class_id'], $classes->pluck('id')->toArray())) {
                                    $is_valid = false;
                                    $error_msg = __('essentials::lang.f_insurance_class_not_found') . $row_no;
                                    break;
                                }
                            } else {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.no_company_added') . $row_no;
                                break;
                            }
                        }
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.insurance_class_id_required') . $row_no;
                        break;
                    }


                    $emp_array['insurance_company_id'] = $value[2];
                    if (!empty($value[2])) {
                        $company = Contact::where('id', $emp_array['insurance_company_id'])->where('type', 'insurance')
                            ->first();
                        if (!$company) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.insurance_company_id_not_found') . $row_no;
                            break;
                        } else if ($proof_number_emp != null &&   $family_proof_number == null) {
                            $company_id = $proof_number_emp->company_id;
                            $insurance_company = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)->first();
                            if ($insurance_company) {
                                $cop = contact::where('type', 'insurance')
                                    ->where('id', $insurance_company->insur_id)
                                    ->get();


                                if (!in_array($emp_array['insurance_company_id'], $cop->pluck('id')->toArray())) {
                                    $is_valid = false;
                                    $error_msg = __('essentials::lang.comp_insurance_class_not_found') . $row_no;
                                    break;
                                }
                            } else {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.no_company_added') . $row_no;
                                break;
                            }
                        } else if ($family_proof_number != null && $proof_number_emp == null) {
                            $emp = User::where('id', $family_proof_number->employee_id)->first();
                            $company_id = $emp->company_id;
                            $insurance_company = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)->first();

                            if ($insurance_company) {
                                $cop = contact::where('type', 'insurance')
                                    ->where('id', $insurance_company->insur_id)
                                    ->get();


                                if (!in_array($emp_array['insurance_company_id'], $cop->pluck('id')->toArray())) {
                                    $is_valid = false;
                                    $error_msg = __('essentials::lang.f_comp_insurance_class_not_found') . $row_no;
                                    break;
                                }
                            } else {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.no_company_added') . $row_no;
                                break;
                            }
                        }
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.insurance_company_id_required') . $row_no;
                        break;
                    }

                    $formated_data[] = $emp_array;
                }



                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                $processedEqamaEmpNos = [];
                if (!empty($formated_data)) {

                    foreach ($formated_data as $emp_data) {
                        $eqama_emp_no = $emp_data['eqama_emp_no'];

                        if (in_array($eqama_emp_no, $processedEqamaEmpNos)) {
                            $is_valid = false;
                            $error_msg = __('essentials::lang.duplicated_eqama_number') . $row_no;
                            break;
                        }

                        $emp = User::where('id_proof_number', $emp_data['eqama_emp_no'])->first();
                        $emp_border_no = User::where('border_no', $emp_data['eqama_emp_no'])->first();
                        $family = EssentialsEmployeesFamily::where('eqama_number', $emp_data['eqama_emp_no'])->first();


                        if ($emp != null && $emp_border_no == null &&  $family == null) {
                            $previous_emp_insurance = EssentialsEmployeesInsurance::where('employee_id', $emp->id)
                                ->where('is_deleted', 0)
                                ->latest('created_at')
                                ->first();

                            if ($previous_emp_insurance) {
                                $previous_emp_insurance->is_deleted = 1;
                                $previous_emp_insurance->save();
                            }


                            $insurance = new EssentialsEmployeesInsurance();
                            $insurance->insurance_classes_id = $emp_data['insurance_class_id'];
                            $insurance->insurance_company_id = $emp_data['insurance_company_id'];
                            $insurance->employee_id = $emp->id;
                            $insurance->is_deleted = 0;
                            $insurance->family_id = null;
                            $insurance->save();



                            $emp->has_insurance = 1;

                            if (!empty($emp_array['marital_status'])) {

                                $emp->marital_status =  $emp_array['marital_status'];
                            }
                            $emp->save();


                            $processedEqamaEmpNos[] = $eqama_emp_no;
                        } else if ($emp_border_no != null && $emp == null &&  $family == null) {
                            $previous_emp_insurance = EssentialsEmployeesInsurance::where('employee_id', $emp_border_no->id)
                                ->where('is_deleted', 0)
                                ->latest('created_at')
                                ->first();

                            if ($previous_emp_insurance) {
                                $previous_emp_insurance->is_deleted = 1;
                                $previous_emp_insurance->save();
                            }


                            $insurance = new EssentialsEmployeesInsurance();
                            $insurance->insurance_classes_id = $emp_data['insurance_class_id'];
                            $insurance->insurance_company_id = $emp_data['insurance_company_id'];
                            $insurance->employee_id = $emp_border_no->id;
                            $insurance->family_id = null;
                            $insurance->is_deleted = 0;
                            $insurance->save();


                            $emp_border_no->has_insurance = 1;
                            if (!empty($emp_array['marital_status'])) {

                                $emp_border_no->marital_status =  $emp_array['marital_status'];
                            }
                            $emp_border_no->save();


                            $processedEqamaEmpNos[] = $eqama_emp_no;
                        } else if ($family != null &&  $emp == null && $emp_border_no == null) {

                            $previous_family_insurance = EssentialsEmployeesInsurance::where('family_id', $family->id)
                                ->where('is_deleted', 0)
                                ->latest('created_at')
                                ->first();

                            if ($previous_family_insurance) {
                                $previous_family_insurance->is_deleted = 1;
                                $previous_family_insurance->save();
                            }



                            $insurance = new EssentialsEmployeesInsurance();
                            $insurance->insurance_classes_id = $emp_data['insurance_class_id'];
                            $insurance->insurance_company_id = $emp_data['insurance_company_id'];
                            $insurance->employee_id = null;
                            $insurance->is_deleted = 0;
                            $insurance->family_id = $family->id;
                            $insurance->save();
                            if (!empty($emp_array['marital_status'])) {

                                $family->marital_status =  $emp_array['marital_status'];
                                $family->save();
                            }
                            $processedEqamaEmpNos[] = $eqama_emp_no;
                        }
                    }


                    if (!$is_valid) {
                        throw new \Exception($error_msg);
                    }
                }



                $output = [
                    'success' => 1,
                    'msg' => __('product.file_imported_successfully'),
                ];

                DB::commit();
            }
        } catch (\Exception $e) {

            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect()->route('import_employees_insurance')
                ->with('notification', $output);
        }


        return redirect()->route('employee_insurance')
            ->with('notification', 'success insert');
    }


    //update excel insurance 
    public function insurancepostUpdateImportEmployee(Request $request)
    {
        try {

            ini_set('max_execution_time', 0);


            if ($request->hasFile('update_employee_insurance_csv')) {
                $file = $request->file('update_employee_insurance_csv');
                $parsed_array = Excel::toArray([], $file);
                $imported_data = array_splice($parsed_array[0], 1);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $processedIdProofNumbers = [];
                $formated_data = [];
                $is_valid = true;
                $error_msg = '';



                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                    $row_no = $key + 1;
                    $emp_array = [];

                    $emp_array['eqama_emp_no'] = intval($value[0]);

                    if (!empty($value[0])) {


                        $proof_number = User::where('id_proof_number', $emp_array['eqama_emp_no'])->first();
                        $border_no = User::where('border_no', $emp_array['eqama_emp_no'])->first();
                        $family_proof_number = EssentialsEmployeesFamily::where('eqama_number', $emp_array['eqama_emp_no'])->first();


                        if ($proof_number == null && $border_no == null &&  $family_proof_number == null) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.number_not_found') . $row_no;
                            break;
                        }
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.eqama_number_required') . $row_no;
                        break;
                    }


                    $emp_array['insurance_class_id'] = $value[1];
                    if (!empty($value[1])) {
                        $class = EssentialsInsuranceClass::where('id', $emp_array['insurance_class_id'])->first();
                        $proof_number_emp = User::where('id_proof_number', $emp_array['eqama_emp_no'])->first();
                        $family_proof_number = EssentialsEmployeesFamily::where('eqama_number', $emp_array['eqama_emp_no'])->first();

                        if (!$class) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.insurance_class_id_not_found') . $row_no;
                            break;
                        } else if ($proof_number_emp != null &&   $family_proof_number == null) {
                            $company_id = $proof_number_emp->company_id;
                            $insurance_company = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)->first();
                            if ($insurance_company) {
                                $classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company->insur_id)
                                    ->get();
                                if (!in_array($emp_array['insurance_class_id'], $classes->pluck('id')->toArray())) {
                                    $is_valid = false;
                                    $error_msg = __('essentials::lang.insurance_class_not_found') . $row_no;
                                    break;
                                }
                            } else {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.no_company_added') . $row_no;
                                break;
                            }
                        } else if ($family_proof_number != null && $proof_number_emp == null) {
                            $emp = User::where('id', $family_proof_number->employee_id)->first();
                            $company_id = $emp->company_id;
                            $insurance_company = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)->first();

                            if ($insurance_company) {
                                $classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company->insur_id)
                                    ->get();


                                if (!in_array($emp_array['insurance_class_id'], $classes->pluck('id')->toArray())) {
                                    $is_valid = false;
                                    $error_msg = __('essentials::lang.f_insurance_class_not_found') . $row_no;
                                    break;
                                }
                            } else {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.no_company_added') . $row_no;
                                break;
                            }
                        }
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.insurance_class_id_required') . $row_no;
                        break;
                    }




                    $emp_array['insurance_company_id'] = $value[2];
                    if (!empty($value[2])) {

                        $company = Contact::where('id', $emp_array['insurance_company_id'])->where('type', 'insurance')
                            ->first();
                        if (!$company) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.insurance_company_id_not_found') . $row_no;
                            break;
                        } else if ($proof_number_emp != null &&   $family_proof_number == null) {

                            $company_id = $proof_number_emp->company_id;
                            $insurance_company = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)->first();
                            if ($insurance_company) {
                                $cop = contact::where('type', 'insurance')
                                    ->where('id', $insurance_company->insur_id)
                                    ->get();


                                if (!in_array($emp_array['insurance_company_id'], $cop->pluck('id')->toArray())) {
                                    $is_valid = false;
                                    $error_msg = __('essentials::lang.comp_insurance_class_not_found') . $row_no;
                                    break;
                                }
                            } else {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.no_company_added') . $row_no;
                                break;
                            }
                        } else if ($family_proof_number != null && $proof_number_emp == null) {
                            $emp = User::where('id', $family_proof_number->employee_id)->first();
                            $company_id = $emp->company_id;
                            $insurance_company = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)->first();

                            if ($insurance_company) {
                                $cop = contact::where('type', 'insurance')
                                    ->where('id', $insurance_company->insur_id)
                                    ->get();


                                if (!in_array($emp_array['insurance_company_id'], $cop->pluck('id')->toArray())) {
                                    $is_valid = false;
                                    $error_msg = __('essentials::lang.f_comp_insurance_class_not_found') . $row_no;
                                    break;
                                }
                            } else {

                                $is_valid = false;
                                $error_msg = __('essentials::lang.no_company_added') . $row_no;
                                break;
                            }
                        }
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.insurance_company_id_required') . $row_no;
                        break;
                    }




                    $formated_data[] = $emp_array;
                }


                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                $processedEqamaEmpNos = [];
                if (!empty($formated_data)) {

                    foreach ($formated_data as $emp_data) {
                        $eqama_emp_no = $emp_data['eqama_emp_no'];

                        if (in_array($eqama_emp_no, $processedEqamaEmpNos)) {
                            $is_valid = false;
                            $error_msg = __('essentials::lang.duplicated_eqama_number') . $row_no;
                            break;
                        }

                        $emp = User::where('id_proof_number', $emp_data['eqama_emp_no'])->first();
                        $emp_border_no = User::where('border_no', $emp_data['eqama_emp_no'])->first();
                        $family = EssentialsEmployeesFamily::where('eqama_number', $emp_data['eqama_emp_no'])->first();


                        if ($emp != null && $emp_border_no == null &&  $family == null) {
                            $insurance = EssentialsEmployeesInsurance::where('employee_id', $emp->id)
                                ->where('is_deleted', 0)
                                ->where('family_id', null)
                                ->first();

                            if ($insurance) {

                                $insurance->insurance_classes_id = $emp_data['insurance_class_id'];
                                $insurance->insurance_company_id = $emp_data['insurance_company_id'];
                                $insurance->save();
                            }
                            if (!empty($emp_array['marital_status'])) {

                                $emp->marital_status =  $emp_array['marital_status'];
                                $emp->save();
                            }
                            $processedEqamaEmpNos[] = $eqama_emp_no;
                        } else if ($emp_border_no != null && $emp == null &&  $family == null) {
                            $insurance = EssentialsEmployeesInsurance::where('employee_id', $emp_border_no->id)
                                ->where('family_id', null)
                                ->where('is_deleted', 0)
                                ->first();

                            if ($insurance) {

                                $insurance->insurance_classes_id = $emp_data['insurance_class_id'];
                                $insurance->insurance_company_id = $emp_data['insurance_company_id'];
                                $insurance->save();
                            }
                            if (!empty($emp_array['marital_status'])) {

                                $emp_border_no->marital_status =  $emp_array['marital_status'];
                                $emp_border_no->save();
                            }
                            $processedEqamaEmpNos[] = $eqama_emp_no;
                        } else if ($family != null &&  $emp == null && $emp_border_no == null) {
                            $insurance = EssentialsEmployeesInsurance::where('family_id', $family->id)
                                ->where('employee_id', null)
                                ->where('is_deleted', 0)
                                ->first();

                            if ($insurance) {

                                $insurance->insurance_classes_id = $emp_data['insurance_class_id'];
                                $insurance->insurance_company_id = $emp_data['insurance_company_id'];
                                $insurance->save();
                            }
                            if (!empty($emp_array['marital_status'])) {

                                $family->marital_status =  $emp_array['marital_status'];
                                $family->save();
                            }
                            $processedEqamaEmpNos[] = $eqama_emp_no;
                        }
                    }


                    if (!$is_valid) {
                        throw new \Exception($error_msg);
                    }
                }


                $output = [
                    'success' => 1,
                    'msg' => __('product.file_imported_successfully'),
                ];

                DB::commit();
            }
        } catch (\Exception $e) {

            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect()->route('import_employees_insurance')->with('notification', $output);
        }


        return redirect()->route('employee_insurance')->with('notification', 'success insert');
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_cancel_employees_insurances = auth()->user()->can('essentials.delete_employees_insurances');
        $can_add_employees_insurances = auth()->user()->can('essentials.add_employees_insurances');
        $can_edit_employees_insurances = auth()->user()->can('essentials.edit_employees_insurances');
        $can_insurance = auth()->user()->can('essentials.crud_employees_insurances');

        if (!($is_admin || $can_insurance)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $insurance_companies = Contact::where('type', 'insurance')
            ->pluck('supplier_business_name', 'id');

        $insurance_classes = EssentialsInsuranceClass::all()
            ->pluck('name', 'id');


        $insurances = EssentialsEmployeesInsurance::with('user', 'user.business')
            ->leftjoin('essentials_employees_families', 'essentials_employees_families.id', 'essentials_employees_insurances.family_id')
            ->where(function ($query) use ($userIds) {
                $query->whereHas('user', function ($query1) use ($userIds) {
                    $query1->whereIn('users.id', $userIds)
                        ->where('users.status', '!=', 'inactive');
                })
                    ->orWhereHas('essentialsEmployeesFamily', function ($query2) use ($userIds) {
                        $query2->whereIn('essentials_employees_families.employee_id', $userIds);
                    });
            })
            ->where('essentials_employees_insurances.is_deleted', 0)
            ->select(
                'essentials_employees_insurances.employee_id',
                'essentials_employees_insurances.family_id',
                'essentials_employees_families.employee_id as family_employee_id',
                'essentials_employees_insurances.id as id',
                'essentials_employees_insurances.insurance_company_id',
                'essentials_employees_insurances.insurance_classes_id'
            )

            ->orderBy('essentials_employees_insurances.employee_id');
        // dd($insurances->where('essentials_employees_insurances.employee_id', 1730)->get());

        if (request()->ajax()) {

            return Datatables::of($insurances)
                ->addColumn('user', function ($row) {
                    $item = '';

                    if ($row->employee_id != null) {
                        $item = $row->user->first_name  . ' ' . $row->user->last_name ?? '';
                        //  $item = $row->english_name;
                    } else if ($row->employee_id == null) {
                        $item = $row->essentialsEmployeesFamily->full_name ?? '';
                    }

                    return $item;
                })

                ->addColumn('english_name', function ($row) {
                    $item = '';

                    if ($row->employee_id != null) {
                        $item = $row->user->english_name  ?? '';
                    }

                    return $item;
                })

                ->addColumn('dob', function ($row) {
                    $item = '';
                    if ($row->employee_id != null) {
                        $item = $row->user->dob ?? '';
                    } else if ($row->employee_id == null) {
                        $item = $row->essentialsEmployeesFamily->dob ?? '';
                    }
                    return $item;
                })

                ->editColumn('fixnumber', function ($row) {
                    $item = '';
                    if ($row->employee_id != null) {
                        $item = $row->user->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')
                            ->first()->unified_number ?? '';
                    } else if ($row->employee_id == null) {
                        $item = $row->essentialsEmployeesFamily->user->business?->documents?->where('licence_type', 'COMMERCIALREGISTER')
                            ->first()->unified_number ?? '';
                    }
                    return  $item;
                })


                ->addColumn('proof_number', function ($row) {
                    $item = '';
                    if ($row->employee_id != null) {
                        $item = $row->user->id_proof_number ?? '';
                    } else if ($row->employee_id == null) {
                        $item = $row->essentialsEmployeesFamily->eqama_number ?? '';
                    }

                    return $item;
                })

                ->editColumn('insurance_company_id', function ($row) use ($insurance_companies) {
                    $item = $insurance_companies[$row->insurance_company_id] ?? '';

                    return $item;
                })
                ->editColumn('insurance_classes_id', function ($row) use ($insurance_classes) {
                    $item = $insurance_classes[$row->insurance_classes_id] ?? '';

                    return $item;
                })
                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin,  $can_cancel_employees_insurances, $can_edit_employees_insurances) {
                        $html = '';
                        if ($is_admin ||  $can_cancel_employees_insurances) {
                            $html .= '<button class="btn btn-xs btn-warning delete_insurance_button" data-href="' . route('employee_insurance.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-refresh"></i> ' . __('essentials::lang.cancel_insurance') . '</button>';
                        }

                        if ($is_admin || $can_edit_employees_insurances) {
                            $html .=
                                '  <a href="' . route('employee_insurance.edit', ['id' => $row->id])  . '"
                             data-href="' . route('employee_insurance.edit', ['id' => $row->id])  . ' "
                             
                             class="btn btn-xs btn-modal btn-info"  
                             data-container="#editemployeeInsurance">
                             <i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit')  . '</a>&nbsp;';
                        }

                        return $html;
                    }
                )


                ->filterColumn('user', function ($query, $keyword) {

                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) LIKE ?", ["%$keyword%"])
                        ->orWhereRaw("f.full_name LIKE ?", ["%$keyword%"]);
                })

                ->filterColumn('proof_number', function ($query, $keyword) {
                    $query->whereRaw("CASE
                                            WHEN u.id_proof_number IS NOT NULL THEN u.id_proof_number
                                          
                                            ELSE ''
                                        END LIKE ?", ["%$keyword%"]);
                })



                ->rawColumns(['action'])
                ->make(true);
        }



        $userQuery = User::whereIn('id', $userIds)->select(
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),  ' - ',COALESCE(id_proof_number,'')) as full_name")
        );

        $familyQuery = EssentialsEmployeesFamily::where(function ($query) use ($userIds) {
            $query->whereHas('user', function ($query1) use ($userIds) {
                $query1->whereIn('users.id', $userIds);
            });
        })
            ->select('id as id', 'full_name');

        $combinedQuery = $userQuery->unionAll($familyQuery);
        $users = $combinedQuery->pluck('full_name', 'id');


        return view('essentials::employee_affairs.employee_insurance.index')
            ->with(compact('insurance_companies', 'insurance_classes', 'users'));
    }

    public function show(Request $request)
    {

        $insurance = EssentialsEmployeesInsurance::with(['user', 'user.business', 'insuranceCompany', 'insuranceClass'])
            ->where('essentials_employees_insurances.employee_id', $request->employee_id)
            ->where('essentials_employees_insurances.is_deleted', 0)
            ->first();


        if ($insurance) {
            $response = [
                'success' => true,
                'id_proof_number' => $insurance->user->id_proof_number ?? '',
                'border_number' => $insurance->user->border_no ?? '',
                'border_no' => $insurance->user->border_no,
                'insurance_company' => $insurance->insuranceCompany->supplier_business_name ?? '',
                'insurance_class' => $insurance->insuranceClass->name ?? '',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No insurance information found'
            ];
        }
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {


        try {

            $input = $request->only(['insurance_class', 'employee']);
            $family = EssentialsEmployeesFamily::where('id',  $input['employee'])
                ->with('user')
                ->first();

            if ($family == null) {
                $previous_emp_insurance = EssentialsEmployeesInsurance::where('employee_id', $input['employee'])
                    ->where('is_deleted', 0)
                    ->latest('created_at')
                    ->first();


                if ($previous_emp_insurance) {
                    $previous_emp_insurance->is_deleted = 1;
                    $previous_emp_insurance->save();


                    $insurance_data['is_deleted'] = 0;
                    $insurance_data['insurance_classes_id'] = $input['insurance_class'];
                    $insurance_data['employee_id'] = $input['employee'];
                    $insurance_class_company = EssentialsInsuranceClass::where('id', $insurance_data['insurance_classes_id'])
                        ->select('insurance_company_id')->first();
                    $insurance_data['insurance_company_id'] =  $insurance_class_company->insurance_company_id;
                    EssentialsEmployeesInsurance::create($insurance_data);

                    $emp_insurance = User::where('id', $input['employee'])->where(function ($query) {
                        $query->where('users.status', 'active')
                            ->orWhere(function ($subQuery) {
                                $subQuery->where('users.status', 'inactive')
                                    ->whereIn('users.sub_status', ['vacation', 'escape', 'return_exit']);
                            });
                    })
                        ->first();
                    $emp_insurance->has_insurance = 1;
                    $emp_insurance->save();


                    $output = [
                        'success' => true,
                        'msg' => __('lang_v1.added_success'),
                    ];
                } else {
                    $insurance_data['is_deleted'] = 0;
                    $insurance_data['insurance_classes_id'] = $input['insurance_class'];
                    $insurance_data['employee_id'] = $input['employee'];
                    $insurance_class_company = EssentialsInsuranceClass::where('id', $insurance_data['insurance_classes_id'])
                        ->select('insurance_company_id')->first();
                    $insurance_data['insurance_company_id'] =  $insurance_class_company->insurance_company_id;
                    EssentialsEmployeesInsurance::create($insurance_data);

                    $emp_insurance = User::where('id', $input['employee'])->where('status', 'active')->first();
                    $emp_insurance->has_insurance = 1;
                    $emp_insurance->save();


                    $output = [
                        'success' => true,
                        'msg' => __('lang_v1.added_success'),
                    ];
                }
            } else {

                $previous_family_insurance = EssentialsEmployeesInsurance::where('family_id', $input['employee'])
                    ->where('is_deleted', 0)
                    ->latest('created_at')
                    ->first();


                if ($previous_family_insurance) {
                    $previous_family_insurance->is_deleted = 1;
                    $previous_family_insurance->save();

                    $insurance_data['is_deleted'] = 0;
                    $insurance_data['insurance_classes_id'] = $input['insurance_class'];
                    $insurance_data['family_id'] = $input['employee'];
                    $insurance_class_company = EssentialsInsuranceClass::where('id', $insurance_data['insurance_classes_id'])
                        ->select('insurance_company_id')->first();
                    $insurance_data['insurance_company_id'] =  $insurance_class_company->insurance_company_id;
                    EssentialsEmployeesInsurance::create($insurance_data);
                    $output = [
                        'success' => true,
                        'msg' => __('lang_v1.added_success'),
                    ];
                } else {

                    $insurance_data['is_deleted'] = 0;
                    $insurance_data['insurance_classes_id'] = $input['insurance_class'];
                    $insurance_data['family_id'] = $input['employee'];
                    $insurance_class_company = EssentialsInsuranceClass::where('id', $insurance_data['insurance_classes_id'])
                        ->select('insurance_company_id')->first();
                    $insurance_data['insurance_company_id'] =  $insurance_class_company->insurance_company_id;
                    EssentialsEmployeesInsurance::create($insurance_data);
                    $output = [
                        'success' => true,
                        'msg' => __('lang_v1.added_success'),
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' =>  __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()
            ->with('status', $output);
    }


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function fetchClasses(Request $request)
    {

        $employee_id = $request->input('employee_id');
        $classes = null;
        $family = EssentialsEmployeesFamily::where('id', $employee_id)
            ->with('user')
            ->first();


        if ($family == null) {
            $company_id = User::find($employee_id)->company_id;
            $insurance_company_id = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)
                ->first();

            if ($insurance_company_id) {
                $classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company_id->insur_id)
                    ->pluck('name', 'id');
            } else {
                return response()->json(['message' =>  __('essentials::lang.no_company_added')]);
            }
        } else {
            $emp_id = $family->user->id;
            if ($emp_id) {
                $company_id = User::find($emp_id)->company_id;
                $insurance_company_id = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)
                    ->first();

                if ($insurance_company_id) {
                    $classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company_id->insur_id)
                        ->pluck('name', 'id');
                } else {
                    return response()->json(['message' =>  __('essentials::lang.no_company_added')]);
                }
            }
        }


        return response()->json($classes);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $insurance_classes = null;

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $insurance = EssentialsEmployeesInsurance::findOrFail($id);
        $insurance_companies = Contact::where('type', 'insurance')->pluck('supplier_business_name', 'id');

        if ($insurance->employee_id != null) {

            $emp_id = $insurance->employee_id;
            $company_id = User::find($emp_id)->company_id;
            $insurance_company_id = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)
                ->first();
            if ($insurance_company_id) {
                $insurance_classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company_id->insur_id)
                    ->pluck('name', 'id');
            } else {
                return response()->json(['message' =>  __('essentials::lang.no_company_added')]);
            }
        } else if ($insurance->family_id != null) {
            $employee_relative_id = EssentialsEmployeesFamily::find($insurance->family_id)->user->id;
            $company_id = User::find($employee_relative_id)->company_id;
            $insurance_company_id = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)
                ->first();

            if ($insurance_company_id) {
                $insurance_classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company_id->insur_id)
                    ->pluck('name', 'id');
            } else {
                return response()->json(['message' =>  __('essentials::lang.no_company_added')]);
            }
        }

        $userQuery = User::whereIn('id', $userIds)->select(
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),  ' - ',COALESCE(id_proof_number,'')) as full_name")
        );

        $familyQuery = EssentialsEmployeesFamily::where(function ($query) use ($userIds) {
            $query->whereHas('user', function ($query1) use ($userIds) {
                $query1->whereIn('users.id', $userIds);
            });
        })
            ->select('id as id', 'full_name');

        $combinedQuery = $userQuery->unionAll($familyQuery);
        $users = $combinedQuery->pluck('full_name', 'id');


        return view('essentials::employee_affairs.employee_insurance.edit_modal')
            ->with(compact('insurance_companies', 'insurance_classes', 'users', 'insurance'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try {

            $input = $request->only(['insurance_class', 'employee']);
            $family = EssentialsEmployeesFamily::where('id',  $input['employee'])
                ->with('user')
                ->first();


            if ($family == null) {
                $emp = EssentialsEmployeesInsurance::where('employee_id', $input['employee'])
                    ->where('is_deleted', 0)
                    ->first();

                $insurance_data['insurance_classes_id'] = $input['insurance_class'];
                $insurance_data['employee_id'] = $input['employee'];
                $insurance_class_company = EssentialsInsuranceClass::where('id', $insurance_data['insurance_classes_id'])
                    ->select('insurance_company_id')->first();
                $insurance_data['insurance_company_id'] =  $insurance_class_company->insurance_company_id;
                EssentialsEmployeesInsurance::where('id', $id)->update($insurance_data);
            } else {

                $emp = EssentialsEmployeesInsurance::where('family_id', $input['employee'])
                    ->where('is_deleted', 0)
                    ->first();


                $insurance_data['insurance_classes_id'] = $input['insurance_class'];
                $insurance_data['family_id'] = $input['employee'];
                $insurance_class_company = EssentialsInsuranceClass::where('id', $insurance_data['insurance_classes_id'])
                    ->select('insurance_company_id')->first();
                $insurance_data['insurance_company_id'] =  $insurance_class_company->insurance_company_id;
                EssentialsEmployeesInsurance::where('id', $id)->update($insurance_data);
            }
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('employee_insurance')
            ->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {


        try {

            $insurance = EssentialsEmployeesInsurance::where('is_deleted', 0)
                ->latest('created_at')
                ->find($id);

            if (!$insurance) {
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];

                return redirect()->route('employee_insurance')
                    ->with($output);
            }

            $insurance->update(['is_deleted' => 1]);
            if ($insurance->employee_id != null) {
                $emp = User::where('id', $insurance->employee_id)->first();
                $emp->has_insurance = 0;
                $emp->save();
            }


            $output = [
                'success' => true,
                'msg' => __('essentials::lang.canceled_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
    public function getInsuranceClasses($companyId)
    {

        $insuranceClasses = EssentialsInsuranceClass::where('insurance_company_id', $companyId)
            ->pluck('name', 'id');

        return response()->json($insuranceClasses);
    }

    public function new_arrival_for_workers(Request $request)
    {
        $view = 'essentials::insurance.travelers.index';
        return $this->newArrivalUtil->new_arrival_for_workers($request, $view);
    }

    public function housed_workers_index(Request $request)
    {
        $view = 'essentials::insurance.travelers.partials.housed_workers';
        return $this->newArrivalUtil->housed_workers_index($request, $view);
    }

    public function medicalExamination()
    {
        $view = 'essentials::insurance.travelers.medicalExamination';
        return $this->newArrivalUtil->medicalExamination($view);
    }
    public function SIMCard()
    {
        $view = 'essentials::insurance.travelers.SIMCard';
        return $this->newArrivalUtil->SIMCard($view);
    }
    public function workCardIssuing()
    {
        $view = 'essentials::insurance.travelers.workCardIssuing';
        return $this->newArrivalUtil->workCardIssuing($view);
    }
    public function medicalInsurance()
    {
        $view = 'essentials::insurance.travelers.medicalInsurance';
        return $this->newArrivalUtil->medicalInsurance($view);
    }
    public function bankAccounts()
    {
        $view = 'essentials::insurance.travelers.bankAccounts';
        return $this->newArrivalUtil->bankAccounts($view);
    }
    public function QiwaContracts()
    {
        $view = 'essentials::insurance.travelers.QiwaContracts';
        return $this->newArrivalUtil->QiwaContracts($view);
    }
    public function residencyPrint()
    {
        $view = 'essentials::insurance.travelers.residencyPrint';
        return $this->newArrivalUtil->residencyPrint($view);
    }
    public function residencyDelivery()
    {
        $view = 'essentials::insurance.travelers.residencyDelivery';
        return $this->newArrivalUtil->residencyDelivery($view);
    }
    public function advanceSalaryRequest()
    {
        $view = 'essentials::insurance.travelers.advanceSalaryRequest';
        return $this->newArrivalUtil->advanceSalaryRequest($view);
    }
}