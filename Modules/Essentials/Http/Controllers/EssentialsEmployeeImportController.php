<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use Excel;
use App\User;
use App\Category;
use App\Business;
use DateTime;
use App\BusinessLocation;
use Modules\Essentials\Entities\essentialsAllowanceType;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;
use Modules\Essentials\Entities\EssentialsEmployeeContract;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\Essentials\Entities\EssentialsEmployeeTravelCategorie;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsCountry;

use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;
class EssentialsEmployeeImportController extends Controller
{

    protected $moduleUtil;
   

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }   
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $can_crud_import_employee = auth()->user()->can('essentials.crud_import_employee');
        if (! $can_crud_import_employee) {
            abort(403, 'Unauthorized action.');
        }
        $zip_loaded = extension_loaded('zip') ? true : false;

        //Check if zip extension it loaded or not.
        if ($zip_loaded === false) {
            $output = ['success' => 0,
                'msg' => 'Please install/enable PHP Zip archive for import',
            ];

            return view('essentials::employees.import')
                ->with('notification', $output);
        } else {
            return view('essentials::employees.import');
        }

       
    }

    public function postImportEmployee(Request $request)
    {
        $can_crud_import_employee = auth()->user()->can('essentials.crud_import_employee');
        if (! $can_crud_import_employee) {
            abort(403, 'Unauthorized action.');
        }
        try {
           

            //Set maximum php execution time
            ini_set('max_execution_time', 0);


            if ($request->hasFile('employee_csv')) {
                $file = $request->file('employee_csv');
                $parsed_array = Excel::toArray([], $file);
                $imported_data = array_splice($parsed_array[0], 1);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');

                $formated_data = [];
                $is_valid = true;
                $error_msg = '';

                $column_mapping = [
                    0 => 'first_name',
                    1 => 'mid_name',
                    2 => 'last_name',
                    3=>'employee_type',
                   
                ];
              
            DB::beginTransaction();
            foreach ($imported_data as $key => $value)
             {
                $row_no = $key + 1;
                $emp_array = [];     
                $emp="";

                                      
                                
                                     
                                        
                 if (!empty($value[0])) 
                 {
                     $emp_array['first_name'] = $value[0];
                 }
              
                                        
                                        $emp_array['mid_name'] = $value[1];
                                    
                                    
                                        if (!empty($value[2])) 
                                        {
                                            $emp_array['last_name'] = $value[2];
                                        } else {
                                            // $is_valid = false;
                                            // $error_msg = "First name is required in row no. $row_no";
                                            // break;
                                        }
                                    
                                        $emp_array['name'] = implode(' ', [ $emp_array['first_name'], $emp_array['mid_name'], $emp_array['last_name']]);
                                        if(!empty($value[3])){
                                            $emp_array['user_type'] = $value[3];
                                        }
                                        else
                                        {
                                            //  $is_valid = false;
                                            // $error_msg = "Mobile number is required in row no. $row_no";
                                            // break;
                                        }
                                    
                                        $emp_array['email'] = $value[4];
                            
                                        
                                    if (!empty($value[5])) {
                                            if (is_numeric($value[5])) {
                                            
                                                $excelDateValue = (float)$value[5];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['dob'] = $date;
                                            
                                            } else {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[5]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['dob'] = $dob;
                                                }
                                        }
                                    }
                                    else{ $emp_array['dob'] = null;}

                                    
                                    $emp_array['gender'] = $value[6];
                                    $emp_array['marital_status'] = $value[7];
                                    // $emp_array['name'] = implode(' ', [$emp_array['prefix'], $emp_array['first_name'], $emp_array['middle_name'], $emp_array['last_name']]);
                                    $emp_array['blood_group'] = $value[8];
                                    
                                        
                                    
                                        if (! empty(trim($value[9]))) {
                                            $emp_array['contact_number'] = $value[9];
                                        } 
                                        else {
                                            // $is_valid = false;
                                            // $error_msg = "Mobile number is required in row no. $row_no";
                                            // break;
                                        }

                                    
                                        $emp_array['alt_number'] = $value[10];
                                        $emp_array['family_number'] = $value[11];
                                        $emp_array['current_address'] = $value[12];
                                        $emp_array['permanent_address'] = $value[13];
                                    


                                        $emp_array['id_proof_name'] = $value[14];
                                        $emp_array['id_proof_number'] = $value[15];
                                    
                                    $emp_array['bank_details'] = [
                                        'account_holder_name' => $value[16],
                                        'account_number' => $value[17],
                                        'bank_name' => $value[18],
                                        'bank_code' => $value[19],
                                    ];
                                    
                                
                                        $emp_array['bank_details'] = json_encode($emp_array['bank_details']);
                                    
                                        $emp_array['business_id'] = $value[20];

                                        if ($emp_array['business_id'] !== null) {
                                        
                                            $specialization = BusinessLocation::find($emp_array['business_id']);
                                            if (!$specialization) {
                                            
                                                $is_valid = false;
                                                $error_msg = "Invalid Business ID in row no. $row_no";
                                                break;
                                            }
                                        } 


                                        
                                        $emp_array['location_id'] = $value[21];

                                        if ($emp_array['location_id'] !== null) {
                                        
                                            $specialization = Business::find($emp_array['location_id']);
                                            if (!$specialization) {
                                            
                                                $is_valid = false;
                                                $error_msg = "Invalid location ID in row no. $row_no";
                                                break;
                                            }
                                        } else {
                                        
                                            $emp_array['location_id'] = null;
                                        }





                                        $emp_array['essentials_department_id'] = $value[22];
                                        if ($emp_array['essentials_department_id'] !== null) {
                                    
                                            $specialization = EssentialsDepartment::find($emp_array['essentials_department_id']);
                                            if (!$specialization) {
                                            
                                                $is_valid = false;
                                                $error_msg = "Invalid department ID in row no. $row_no";
                                                break;
                                            }
                                        } else
                                        {
                                            
                                            $emp_array['essentials_department_id'] = null;
                                        }




                                        $emp_array['addmission_date']=$value[23];
                                        if (!empty($value[23])) {
                                            if (is_numeric($value[23])) {
                                            
                                                $excelDateValue = (float)$value[23];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['addmission_date'] = $date;
                                            
                                            } else {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[22]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['addmission_date'] = $dob;
                                                }
                                        }
                                               }



                                        $emp_array['specialization_id']=$value[24];
                                        if ($emp_array['specialization_id'] !== null) {
                                        
                                            $specialization = EssentialsSpecialization::find($emp_array['specialization_id']);
                                            if (!$specialization) {
                                            
                                                $is_valid = false;
                                                $error_msg = "Invalid specialization ID in row no. $row_no";
                                                break;
                                            }
                                        } else {
                                        
                                            $emp_array['specialization_id'] = null;
                                        }



                                        $emp_array['profession_id']=$value[25];

                                        if ($emp_array['profession_id'] !== null) {
                                        
                                            $specialization = EssentialsProfession::find($emp_array['profession_id']);
                                            if (!$specialization) {
                                            
                                                $is_valid = false;
                                                $error_msg = "Invalid profession ID in row no. $row_no";
                                                break;
                                            }
                                        } else {
                                        
                                            $emp_array['profession_id'] = null;
                                        }
                                        


                                        $emp_array['border_no']=$value[26];
                                        $emp_array['nationality_id']=$value[27];

                                        if ($emp_array['nationality_id'] !== null) {
                                        
                                            $nationality_id = EssentialsCountry::find($emp_array['nationality_id']);
                                            if (!$nationality_id) {
                                            
                                                $is_valid = false;
                                                $error_msg = "Invalid nationality ID in row no. $row_no";
                                                break;
                                            }
                                        } else {
                                        
                                            $emp_array['nationality_id'] = null;
                                        }


                                        
                                        if (!empty($value[28])) {
                                            $emp_array['contract_number'] = $value[28];
                                        } 
                                        else{$emp_array['contract_number'] = null;}
                                    
                                    
                                        if (!empty($value[29])) {
                                            if (is_numeric($value[29])) {
                                            
                                                $excelDateValue = (float)$value[29];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                
                                                $emp_array['contract_start_date'] = $date;
                                            
                                            } else
                                             {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[29]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['contract_start_date'] = $dob;

                                                }
                                            
                                        }
                                    }
                                    else{ $emp_array['contract_start_date'] = null;}
                    
                                    if (!empty($value[30])) {
                                        if (is_numeric($value[30])) {
                                        
                                            $excelDateValue = (float)$value[30];
                                            $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                            $date = date('Y-m-d', $unixTimestamp);
                                            $emp_array['contract_end_date'] = $date;
                                        
                                        } else {
                                            
                                            $date = DateTime::createFromFormat('d/m/Y', $value[30]);
                                            if ($date) {
                                                $dob = $date->format('Y-m-d');
                                                $emp_array['contract_end_date'] = $dob;
                                            }
                                    }
                                }
                                else{ $emp_array['contract_end_date'] = null;}


                                    
                                    
                                if (!empty($value[31])) {
                                    $emp_array['contract_duration'] = $value[31];
                                } 
                                else{$emp_array['contract_duration'] = null;}

                                if (!empty($value[32])) {
                                    $emp_array['probation_period'] = $value[32];
                                } 
                                else{  $emp_array['probation_period'] = null;}
                                    
                                
                                if (!empty($value[33])) {
                                    $emp_array['is_renewable'] = $value[33];
                                } 
                                else{   $emp_array['is_renewable'] = null;}
                                    
                                    //  $emp_array['status'] = "vaild";
                                    
                                    

                                        $emp_array['essentials_salary'] = $value[34];
                                
                                        $allowancename=$value[35];
                                        $allowancetype = essentialsAllowanceType::where('name', $allowancename)->first();
                                        if ($allowancetype) {
                                            
                                            $allowancetypeId = $allowancetype->id;
                                            $emp_array['allowance_deduction_id']=$allowancetypeId;
                                        }
                                        else{ $emp_array['allowance_deduction_id']=null;}
                                    
                                        $emp_array['amount']=$value[35];



                                        $travelcategoryname=$value[36];
                                        $traveltype = EssentialsTravelTicketCategorie::where('name', $travelcategoryname)->first();
                                        if ($traveltype) {
                                            
                                            $traveltypeId = $traveltype->id;
                                            $emp_array['travel_ticket_categorie']=$traveltypeId;
                                        }
                                        else{ $emp_array['travel_ticket_categorie']=null;}



                                    // $emp_array['health_insurance']=$value[34];
                                      
                                    $formated_data[] = $emp_array;
                                   // dd( $formated_data);       
                        }
                      
                                    if (!$is_valid) 
                                    {
                                        throw new \Exception($error_msg);
                                    }

                $defaultContractData = [
                        'contract_start_date' => null,
                        'contract_end_date' => null,
                                               
                                            ]; 

                // Iterate over the formated data and add the default keys
$formated_data = array_map(fn($emp_data) => array_merge($defaultContractData, $emp_data), $formated_data); 
      
                if (! empty($formated_data)) 
                {
                    foreach ($formated_data as $emp_data) {
                     
                    
                        $emp_data['business_id'] = $emp_data['business_id'];
                        $emp_data['created_by'] = $user_id;
                        
      
          
                            // $numericPart = (int)substr($business_id, 3);
                            // $lastEmployee = User::where('business_id', $business_id)
                            //     ->orderBy('emp_number', 'desc')
                            //     ->first();
                            
                            // if ($lastEmployee) {
                            //     // Get the numeric part from the last employee's emp_number
                            //     $lastEmpNumber = (int)substr($lastEmployee->emp_number, 3);
                        
                            //     // Increment the numeric part
                            //     $nextNumericPart = $lastEmpNumber + 1;

                            //     $emp_data['emp_number'] = $business_id . str_pad($nextNumericPart, 6, '0', STR_PAD_LEFT);
                            // } 
                        
                            // else {
                            //     // If no previous employee, start from 1
                            //     $emp_data['emp_number'] =  $business_id .'000';
                            // }


                            $numericPart = (int)substr($business_id, 3);
                            $lastEmployee = User::where('business_id', $business_id)
                                ->orderBy('emp_number', 'desc')
                                ->first();

                            if ($lastEmployee) {
                              
                                $lastEmpNumber = (int)substr($lastEmployee->emp_number, 3);

                        
                               
                                $nextNumericPart = $lastEmpNumber + 1;

                                $emp_data['emp_number'] = $business_id . str_pad($nextNumericPart, 6, '0', STR_PAD_LEFT);
                            } 
                        
                            else {
                              
                                $emp_data['emp_number'] =  $business_id .'000';

                            }
        

                        $emp = User::create($emp_data);

                    
                        $emp_data['business_id'] = $emp_data['business_id'];
                        $emp_data['employee_id'] = $emp->id;
                        $emp_data['created_by'] = $user_id;
                        $emp_data['contract_type_id'] = null;
                       
                        $contract =new EssentialsEmployeesContract();
                        $contract->employee_id  = $emp->id;
                        $contract->contract_number= $emp_data['contract_number'];
                        if($emp_data['contract_start_date'] == null){ $contract->contract_start_date=null;}
                        else{$contract->contract_start_date= $emp_data['contract_start_date'];}
                       
                        if($emp_data['contract_end_date'] == null){ $contract->contract_end_date=null;}
                        else{$contract->contract_end_date= $emp_data['contract_end_date'];}
                      
                        $contract->is_renewable= $emp_data['is_renewable'];
                        $contract->contract_duration=$emp_data['contract_duration'];
                        $contract->probation_period =$emp_data["probation_period"];
                        $contract->contract_type_id  =$emp_data["contract_type_id"];
                        $contract->status = "vaild";
                        $contract->save();



                        $essentials_employee_appointmets = new EssentialsEmployeeAppointmet();
                        $essentials_employee_appointmets->employee_id = $emp->id;
                        $essentials_employee_appointmets->department_id= $emp_data['essentials_department_id'];
                        $essentials_employee_appointmets->business_location_id= $emp_data['location_id'];
                  //      $essentials_employee_appointmets->superior = "superior";
                        $essentials_employee_appointmets->profession_id=$emp_data['profession_id'];
                        $essentials_employee_appointmets->specialization_id =$emp_data["specialization_id"];
                        $essentials_employee_appointmets->save();



                        $essentials_admission_to_works = new EssentialsAdmissionToWork();
                        $essentials_admission_to_works->admissions_date=$emp_data['addmission_date'];
                        $essentials_admission_to_works->employee_id = $emp->id;
                        $essentials_admission_to_works->admissions_type="first_time";
                        $essentials_admission_to_works->admissions_status="on_date";
                        $essentials_admission_to_works->save();

                        
                        if ($emp_data['amount']!=null || $emp_data['allowance_deduction_id']!=null ){
                        $userAllowancesAndDeduction = new EssentialsUserAllowancesAndDeduction();
                        $userAllowancesAndDeduction->user_id = $user_id;
                        $userAllowancesAndDeduction->allowance_deduction_id = (int)$emp_data['allowance_deduction_id'];
                        $userAllowancesAndDeduction->amount = $emp_data['amount']; 
                        $userAllowancesAndDeduction->save();
                        }
                        if ($emp_data['travel_ticket_categorie']!=null){
                        $travel_ticket_categorie=new EssentialsEmployeeTravelCategorie();
                        $travel_ticket_categorie->employee_id = $user_id;
                        $travel_ticket_categorie->categorie_id=(int)$emp_data['travel_ticket_categorie'];
                        $travel_ticket_categorie->save();
                        }
                    }
                }
                
            //   // dd($formated_data2);
            //     if (! empty($formated_data2)) {
            //         foreach ($formated_data2 as $con_data) {
                     
                       
            //             $con_data['business_id'] = $business_id;
            //             $con_data['employee_id'] = $emp->id;
            //             $con_data['created_by'] = $user_id;
            //             $contract = EssentialsEmployeesContract::create($con_data);

                      
            //         }
            //     }
               
                $output = ['success' => 1,
                    'msg' => __('product.file_imported_successfully'),
                ];

                DB::commit();
            }
        } catch (\Exception $e) {

            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect()->route('import-employees')->with('notification', $output);
        }
       // $type = ! empty($contact->type) && $contact->type != 'both' ? $contact->type : 'supplier';

        return redirect()->action([\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'index'])->with('notification', 'success insert');
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
