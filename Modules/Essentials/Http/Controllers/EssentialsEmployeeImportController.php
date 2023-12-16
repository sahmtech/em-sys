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
use App\Contact;
use App\ContactLocation;
use App\BusinessLocation;
use Modules\Essentials\Entities\essentialsAllowanceType;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
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
use Modules\Sales\Entities\SalesProject;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;

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
		$column_mapping = [
						  'first_name',
						  'mid_name',
						  'last_name',
						  'employee_type',
						  'email',
						  'dob',
						  'gender',
						   'marital_status',
						   'blood_group',
					       'contact_number',	
                            'alt_number',	
                            'family_number',	
                            'current_address',					   
				            'permanent_address',
						    'id_proof_name',
						     'assigned_to',
							 'account_holder_name',
							 'account_number',
							 'bank_name',
							 'IBN_code',
							 'business_id',
							 'location',
							 'essentials_department',
							 'addmission_date',
                              'specialization_id',
							  'profession_id',
							  'border_no',
							  'nationality_id',
							  'contract_number',
							  'contract_start_date',
							  'contract_end_date',
							  'contract_duration',
							  'probation_period',
							  'is_renewable',
							  'salary_status',
							  'essentials_salary',
							  'additional_salary_type',
							  'additional_salary_amount',
							  'travel_ticket_categorie',
							  'health_insurance',
							 
							 
				];
        try {
           

            //Set maximum php execution time
            ini_set('max_execution_time', 0);


            if ($request->hasFile('employee_csv'))
             {
                $file = $request->file('employee_csv');
                $parsed_array = Excel::toArray([], $file);
                $imported_data = array_splice($parsed_array[0], 1);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $processedIdProofNumbers = [];
                $formated_data = [];
                $is_valid = true;
                $error_msg = '';

             
              
            DB::beginTransaction();
            foreach ($imported_data as $key => $value)
             {
                $row_no = $key + 1;
                $emp_array = [];     
                

                      
                                     
                                        
                 if (!empty($value[0])) 
                 {
                     $emp_array['first_name'] = $value[0];
                 }
              
                                        
                                        $emp_array['mid_name'] = $value[1];
                                    
                                    
                                        if (!empty($value[2])) 
                                        {
                                            $emp_array['last_name'] = $value[2];
                                        } else {
                                            $is_valid = false;
                                            $error_msg = __('essentials::lang.first_name_required') .$row_no;
                                            break;
                                        }
                                    
                                        $emp_array['name'] = implode(' ', [ $emp_array['first_name'], $emp_array['mid_name'], $emp_array['last_name']]);
                                        if(!empty($value[3])){
                                            $emp_array['user_type'] = $value[3];
                                        }
                                        else
                                        {
                                             $is_valid = false;
                                            $error_msg = __('essentials::lang.user_type_required' ).$row_no;
                                            break;
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
                                        
                                       

                                        if (!empty($value[16])) {
                                            if (is_numeric($value[16])) {
                                            
                                                $excelDateValue = (float)$value[16];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['proof_end_date'] = $date;
                                            
                                            } else {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[16]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['proof_end_date'] = $dob;
                                                }
                                        }
                                    }
                                    else{ $emp_array['proof_end_date'] = null;}


                                        $emp_array['passport_numbrer'] = $value[17];
                                       
                                        
                                        if (!empty($value[18])) {
                                            if (is_numeric($value[18])) {
                                            
                                                $excelDateValue = (float)$value[18];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['passport_end_date'] = $date;
                                            
                                            } else {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[18]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['passport_end_date'] = $dob;
                                                }
                                        }
                                    }
                                    else{ $emp_array['passport_end_date'] = null;}

                                       
                                    
                                    $emp_array['bank_details'] = [
                                        'account_holder_name' => $value[19],
                                        'account_number' => $value[20],
                                        'bank_name' => $value[21],
                                        'bank_code' => $value[22],
                                    ];
                                    
                                
                                        $emp_array['bank_details'] = json_encode($emp_array['bank_details']);
                                    
                                        $emp_array['assigned_to'] = $value[23];
                                        
                                        if ($emp_array['assigned_to'] !== null) {
                                        
                                            $business = SalesProject::find($emp_array['assigned_to']);
                                            if (!$business) {
                                            
                                                $is_valid = false;
                                                $error_msg = __('essentials::lang.contact_not_found').$row_no;
                                                break;
                                            }
                                        }
                                        else
                                        {
                                            $emp_array['assigned_to']=null;
                                        } 



                                      


                                        //---------------------------------------------------
                                        $emp_array['business_id'] = $value[25];

                                        if ($emp_array['business_id'] !== null) {
                                        
                                            $business = Business::find($emp_array['business_id']);
                                          
                                            if (!$business) {
                                            
                                                $is_valid = false;
                                                $error_msg = __('essentials::lang.business_not_found').$row_no;
                                                break;
                                            }
                                        }
                                        else
                                        {
                                            $is_valid = false;
                                            $error_msg =__('essentials::lang.business_required' ) .$row_no;
                                            break;
                                        } 


                                        
                                        $emp_array['location_id'] = $value[26];

                                        if ($emp_array['location_id'] !== null) {
                                        
                                            $location = BusinessLocation::find($emp_array['location_id']);
                                            
                                            if (!$location) {
                                            
                                                $is_valid = false;
                                                $error_msg = __('essentials::lang.location_not_found') .$row_no;
                                                break;
                                            }
                                        } else {
                                        
                                            $emp_array['location_id'] = null;
                                        }





                                        $emp_array['essentials_department_id'] = $value[27];
                                        if ($emp_array['essentials_department_id'] !== null) {
                                    
                                            $dep = EssentialsDepartment::find($emp_array['essentials_department_id']);
                                            if (!$dep) {
                                            
                                                $is_valid = false;
                                                $error_msg = __('essentials::lang.dep_not_found' ) .$row_no;
                                                break;
                                            }
                                        } else
                                        {
                                            
                                            $emp_array['essentials_department_id'] = null;
                                        }




                                        $emp_array['addmission_date']=$value[28];
                                        if (!empty($value[28])) {
                                            if (is_numeric($value[28])) {
                                            
                                                $excelDateValue = (float)$value[28];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['addmission_date'] = $date;
                                            
                                            } else {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[28]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['addmission_date'] = $dob;
                                                }
                                        }
                                               }



                                        $emp_array['specialization_id']=$value[29];
                                      
                                        if ($emp_array['specialization_id'] !== null) {
                                        
                                            $specialization = EssentialsSpecialization::find($emp_array['specialization_id']);
                                          
                                            if (!$specialization) {
                                            
                                                $is_valid = false;
                                                $error_msg = __('essentials::lang.special_not_found') .$row_no;
                                                break;
                                            }
                                        } else {
                                        
                                            $emp_array['specialization_id'] = null;
                                        }



                                        $emp_array['profession_id']=$value[30];

                                        if ($emp_array['profession_id'] !== null) {
                                        
                                            $specialization = EssentialsProfession::find($emp_array['profession_id']);
                                            if (!$specialization) {
                                            
                                                $is_valid = false;
                                                $error_msg = __('essentials::lang.prof_not_found') .$row_no;
                                                break;
                                            }
                                        } else {
                                        
                                            $emp_array['profession_id'] = null;
                                        }
                                        


                                        $emp_array['border_no']=$value[31];
                                        $emp_array['nationality_id']=$value[32];

                                        if ($emp_array['nationality_id'] !== null) {
                                        
                                            $nationality_id = EssentialsCountry::find($emp_array['nationality_id']);
                                            if (!$nationality_id) {
                                            
                                                $is_valid = false;
                                                $error_msg =  __('essentials::lang.nationality_not_found') .$row_no;
                                                break;
                                            }
                                        } else {
                                        
                                            $emp_array['nationality_id'] = null;
                                        }


                                        
                                        if (!empty($value[33])) {
                                            $emp_array['contract_number'] = $value[33];
                                        } 
                                        else{$emp_array['contract_number'] = null;}
                                    
                                    
                                        if (!empty($value[34])) {
                                            if (is_numeric($value[34])) {
                                            
                                                $excelDateValue = (float)$value[34];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                
                                                $emp_array['contract_start_date'] = $date;
                                            
                                            } else
                                             {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[34]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['contract_start_date'] = $dob;

                                                }
                                            
                                        }
                                    }
                                    else{ $emp_array['contract_start_date'] = null;}
                    
                                    if (!empty($value[35])) {
                                        if (is_numeric($value[35])) {
                                        
                                            $excelDateValue = (float)$value[35];
                                            $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                            $date = date('Y-m-d', $unixTimestamp);
                                            $emp_array['contract_end_date'] = $date;
                                        
                                        } else {
                                            
                                            $date = DateTime::createFromFormat('d/m/Y', $value[35]);
                                            if ($date) {
                                                $dob = $date->format('Y-m-d');
                                                $emp_array['contract_end_date'] = $dob;
                                            }
                                    }
                                }
                                else{ $emp_array['contract_end_date'] = null;}


                                    
                                    
                                if (!empty($value[36])) {
                                    $emp_array['contract_duration'] = $value[36];
                                } 
                                else{$emp_array['contract_duration'] = null;}

                                if (!empty($value[37])) {
                                    $emp_array['probation_period'] = $value[37];
                                } 
                                else{  $emp_array['probation_period'] = null;}
                                    
                                
                                if (!empty($value[38])) {
                                    $emp_array['is_renewable'] = $value[38];
                                } 
                                else{   $emp_array['is_renewable'] = null;}
                                    
                                $emp_array['essentials_salary'] = $value[39];
                                    
                               
                                if ($value[40] !== null) {
                                        
                                    $housing_allowance_id = EssentialsAllowanceAndDeduction::find($value[40]);
                                  
                                    if (!$housing_allowance_id) {
                                    
                                        $is_valid = false;
                                        $error_msg = __('essentials::lang.housing_allowance_id_not_found') .$row_no;
                                        break;
                                    }
                                } 
                               
                                if ($value[42] !== null) {
                                        
                                    $trans_allowance_id = EssentialsAllowanceAndDeduction::find($value[42]);
                                  
                                    if (!$trans_allowance_id) {
                                    
                                        $is_valid = false;
                                        $error_msg = __('essentials::lang.trans_allowance_id_not_found') .$row_no;
                                        break;
                                    }
                                } 
                                if ($value[44] !== null) {
                                        
                                    $other_allowance_id = EssentialsAllowanceAndDeduction::find($value[44]);
                                  
                                    if (!$other_allowance_id) {
                                    
                                        $is_valid = false;
                                        $error_msg = __('essentials::lang.other_allowance_id_not_found') .$row_no;
                                        break;
                                    }
                                } 
                               

                                
                                $emp_array['allowance_data'] = [
                                    'housing_allowance' => json_encode(['salaryType' => $value[40], 'amount' => $value[41]]),
                                    'transportation_allowance' => json_encode(['salaryType' => $value[42], 'amount' => $value[43]]),
                                    'other' => json_encode(['salaryType' => $value[44], 'amount' => $value[45]]),
                                  
                                ];
                                        
                                
                                $emp_array['total_salary'] = $value[46]; 
                                    
                                       
                              


                                        $travelcategoryname=$value[47];
                                        $traveltype = EssentialsTravelTicketCategorie::where('name', $travelcategoryname)->first();
                                        if ($traveltype) {
                                            
                                            $traveltypeId = $traveltype->id;
                                            $emp_array['travel_ticket_categorie']=$traveltypeId;
                                        }
                                        else{ $emp_array['travel_ticket_categorie']=null;}

                                        

                                        $emp_array['has_insurance'] = $value[48]; 
                                      
                                    $formated_data[] = $emp_array;
                                         
                        }
                      
                                    if (!$is_valid) 
                                    {
                                        throw new \Exception($error_msg);
                                    }

                $defaultContractData = [
                        'contract_start_date' => null,
                        'contract_end_date' => null,
                                               
                                            ]; 
             
               $formated_data = array_map(fn($emp_data) => array_merge($defaultContractData, $emp_data), $formated_data); 
      
                if (! empty($formated_data)) 
                {
                 


                    foreach ($formated_data as $emp_data) {
                     
                    
                        $emp_data['business_id'] = $emp_data['business_id'];
                        $emp_data['created_by'] = $user_id;
                        
                         
                       
                            if (in_array($emp_data['id_proof_number'], $processedIdProofNumbers)) {
                                throw new \Exception(__('essentials::lang.duplicate_id_proof_number', ['id_proof_number' => $emp_data['id_proof_number']]));
                            }
                        
                          $processedIdProofNumbers[] = $emp_data['id_proof_number'];             

                         

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
                       
                      
                        foreach ($emp_data['allowance_data'] as $allowanceType => $allowanceJson) {
                            $allowanceData = json_decode($allowanceJson, true);
                        
                            try {

                                if($allowanceData['salaryType'] != null && $allowanceData['amount'] !== null && isset($allowanceData['amount']))
                                {
                                            $userAllowancesAndDeduction = new EssentialsUserAllowancesAndDeduction(); 
                                            $userAllowancesAndDeduction->user_id = $emp_data['employee_id'];
                                            $userAllowancesAndDeduction->allowance_deduction_id = (int)$allowanceData['salaryType'];
                        
                                            $userAllowancesAndDeduction->amount = $allowanceData['amount'];

                                         
                                            $userAllowancesAndDeduction->save();
                                }
                            } catch (\Exception $e) {
                                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                                error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                            }
                        }

                          $doc =new EssentialsOfficialDocument();
                          $doc->type ='residence_permit';
                          $doc->status ='valid';
                          $doc->employee_id = $emp->id;
                          $doc->number = $emp_data['id_proof_number'];
                          $doc->expiration_date = $emp_data['proof_end_date'];
                          $doc->save();


                          $passport =new EssentialsOfficialDocument();
                          $passport->type ='passport';
                          $passport->status ='valid';
                          $passport->employee_id = $emp->id;
                          $passport->number = $emp_data['passport_numbrer'];
                          $passport->expiration_date = $emp_data['passport_end_date'];
                          $passport->save();
                        

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
                        $contract->status = "valid";
                        $contract->save();
                       // dd($contract);



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

                        
                        // if ($emp_data['amount']!=null || $emp_data['allowance_deduction_id']!=null ){
                        // $userAllowancesAndDeduction = new EssentialsUserAllowancesAndDeduction();
                        // $userAllowancesAndDeduction->user_id = $user_id;
                        // $userAllowancesAndDeduction->allowance_deduction_id = (int)$emp_data['allowance_deduction_id'];
                        // $userAllowancesAndDeduction->amount = $emp_data['amount']; 
                        // $userAllowancesAndDeduction->save();
                        // }
                        if ($emp_data['travel_ticket_categorie']!=null){
                        $travel_ticket_categorie=new EssentialsEmployeeTravelCategorie();
                        $travel_ticket_categorie->employee_id = $user_id;
                        $travel_ticket_categorie->categorie_id=(int)$emp_data['travel_ticket_categorie'];
                        $travel_ticket_categorie->save();
                        }
                    }
                }
                
      
               
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


    // public function storeDataofExcel(Request $request)
    // {
    //     $can_crud_import_employee = auth()->user()->can('essentials.crud_import_employee');
    //     if (! $can_crud_import_employee) {
    //         abort(403, 'Unauthorized action.');
    //     }
	// 	$column_mapping = [
	// 					  'first_name',
	// 					  'mid_name',
	// 					  'last_name',
	// 					  'employee_type',
	// 					  'email',
	// 					  'dob',
	// 					  'gender',
	// 					   'marital_status',
	// 					   'blood_group',
	// 				       'contact_number',	
    //                         'alt_number',	
    //                         'family_number',	
    //                         'current_address',					   
	// 			            'permanent_address',
	// 					    'id_proof_name',
	// 					     'assigned_to',
	// 						 'account_holder_name',
	// 						 'account_number',
	// 						 'bank_name',
	// 						 'IBN_code',
	// 						 'business_id',
	// 						 'location',
	// 						 'essentials_department',
	// 						 'addmission_date',
    //                           'specialization_id',
	// 						  'profession_id',
	// 						  'border_no',
	// 						  'nationality_id',
	// 						  'contract_number',
	// 						  'contract_start_date',
	// 						  'contract_end_date',
	// 						  'contract_duration',
	// 						  'probation_period',
	// 						  'is_renewable',
	// 						  'salary_status',
	// 						  'essentials_salary',
	// 						  'additional_salary_type',
	// 						  'additional_salary_amount',
	// 						  'travel_ticket_categorie',
	// 						  'health_insurance',
							 
							 
	// 			];
    //             try
    //             {
    //                 ini_set('max_execution_time', 0);
    //                 if ($request->hasFile('employee_csv')) 
    //                 {
    //                     $file = $request->file('employee_csv');
    //                     $parsed_array = Excel::toArray([], $file);
    //                     $imported_data = array_splice($parsed_array[0], 1);
    //                     $business_id = $request->session()->get('user.business_id');
    //                     $user_id = $request->session()->get('user.id');
    //                     $formated_data = [];
    //                     $is_valid = true;
    //                     $error_msg = '';


    //                     DB::beginTransaction();
    //                     foreach ($imported_data as $key => $value)
    //                     {
    //                         $row_no = $key + 1;
    //                         $emp_array = []; 
    //                         foreach ($column_mapping as $column_index => $column_name) {
                              
    //                             if (isset($value[$column_index]) && !empty($value[$column_index])) {
    //                                 $emp_array[$column_name] = $value[$column_index];
                    
                                  
    //                                 switch ($column_name) {
                                        
    //                                     case 'dob':
    //                                         if (is_numeric($value[5])) {
                                            
    //                                             $excelDateValue = (float)$value[5];
    //                                             $unixTimestamp = ($excelDateValue - 25569) * 86400; 
    //                                             $date = date('Y-m-d', $unixTimestamp);
    //                                             $emp_array['dob'] = $date;
                                            
    //                                         } else {
                                            
    //                                             $date = DateTime::createFromFormat('d/m/Y', $value[5]);
    //                                             if ($date) {
    //                                                 $dob = $date->format('Y-m-d');
    //                                                 $emp_array['dob'] = $dob;
    //                                             }
    //                                     }
    //                                     break;
                    

    //                                     case 'bank_details':
    //                                         $emp_array[$column_name] = [
    //                                             'account_holder_name' => $value[16],
    //                                             'account_number' => $value[17],
    //                                             'bank_name' => $value[18],
    //                                             'bank_code' => $value[19],
    //                                         ];
                                            
                                        
    //                                             $emp_array[$column_name] = json_encode($emp_array[$column_name]);
    //                                         break;
                    
                                      
                    
    //                                      default:
                                           
    //                                         break;
    //                                 }
    //                             } else {
    //                                 // Handle missing data if needed
    //                                  $emp_array[$column_name] = null; 
    //                             }
    //                         }
    //                     }

    //                 }

    //             }
    //             catch (\Exception $e) 
    //             {

    //                 DB::rollBack();
    //                 \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        
    //                 $output = ['success' => 0,
    //                     'msg' => $e->getMessage(),
    //                 ];
        
    //                 return redirect()->route('import-employees')->with('notification', $output);
    //             }
              
        
    //             return redirect()->action([\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'index'])->with('notification', 'success insert');
    // }
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
	