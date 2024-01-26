<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Excel;
use App\User;
use App\Category;
use App\Business;
use DateTime;
use App\Contact;
use App\ContactLocation;
use App\BusinessLocation;
use App\Company;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
use Modules\Essentials\Entities\EssentialsEmployeesInsurance;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsInsuranceCompany;


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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_crud_import_employee = auth()->user()->can('essentials.crud_import_employee');
      
       
        if (!($is_admin || $can_crud_import_employee)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }



        $zip_loaded = extension_loaded('zip') ? true : false;
        if ($zip_loaded === false) 
        {
            $output = ['success' => 0, 'msg' => 'Please install/enable PHP Zip archive for import',];

            return view('essentials::employees.import')->with('notification', $output);
        } 
        else 
        {
            return view('essentials::employees.import');
        }

       
    }


    
    public function postImportEmployee(Request $request)
    {
        $can_import_create_employees = auth()->user()->can('essentials.import_create_employees');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
       
        if (!($is_admin || $can_import_create_employees)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
	
	try {

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
                else 
                {
                    $is_valid = false;
                    $error_msg = __('essentials::lang.first_name_required') .$row_no;
                    break;
                }

                $emp_array['mid_name'] = $value[1];
                
                if (!empty($value[2])) 
                {
                    $emp_array['last_name'] = $value[2];
                } 
                $emp_array['name'] = implode(' ', [ $emp_array['first_name'], $emp_array['mid_name'], $emp_array['last_name']]);
              
               
                if (!empty($value[3])) 
                {
                    $emp_array['user_type'] = $value[3];
                
                    $allowedUserTypes = ['worker', 'manager', 'user', 'employee'];
                
                    if (!in_array($emp_array['user_type'], $allowedUserTypes)) {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.user_type_is_valid' ).$row_no;
                        break;
                    }
                } 
                else 
                {
                    $is_valid = false;
                    $error_msg = __('essentials::lang.user_type_required') . $row_no;
                    break;
                }
                
                $emp_array['email'] = $value[4];
                
                if (!empty($value[5])) 
                {
                        if (is_numeric($value[5])) {
                        
                            $excelDateValue = (float)$value[5];
                            $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                            $date = date('Y-m-d', $unixTimestamp);
                            $emp_array['dob'] = $date;
                        
                        } else 
                        {
                        
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
                $emp_array['blood_group'] = $value[8];

                if (! empty(trim($value[9]))) 
                {
                    $emp_array['contact_number'] = $value[9];
                }

                $emp_array['alt_number'] = $value[10];
                $emp_array['family_number'] = $value[11];
                $emp_array['current_address'] = $value[12];
                $emp_array['permanent_address'] = $value[13];
                $emp_array['id_proof_name'] = $value[14];
                $emp_array['id_proof_number'] = $value[15];

                if ($emp_array['id_proof_number'] !== null) 
                {
                    $proof_number = user::where('id_proof_number',$emp_array['id_proof_number'])
                    ->first();
                  
                    if ($proof_number) 
                    {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.proof_number_validated' ) .$row_no;
                        break;
                    }
                }

                if (!empty($value[16])) 
                {
                    if (is_numeric($value[16])) {
                    
                        $excelDateValue = (float)$value[16];
                        $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                        $date = date('Y-m-d', $unixTimestamp);
                        $emp_array['proof_end_date'] = $date;
                    
                    } else 
                    {
                    
                        $date = DateTime::createFromFormat('d/m/Y', $value[16]);
                        if ($date) {
                            $dob = $date->format('Y-m-d');
                            $emp_array['proof_end_date'] = $dob;
                        }
                    }
                }
               else{ $emp_array['proof_end_date'] = null;}

               $emp_array['passport_number'] = $value[17];
               
               if (!empty($value[18])) 
               {
                   if (is_numeric($value[18]))
                   {
                   
                       $excelDateValue = (float)$value[18];
                       $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                       $date = date('Y-m-d', $unixTimestamp);
                       $emp_array['passport_end_date'] = $date;
                   
                   } else 
                   {
                   
                       $date = DateTime::createFromFormat('d/m/Y', $value[18]);
                       if ($date) {
                           $dob = $date->format('Y-m-d');
                           $emp_array['passport_end_date'] = $dob;
                       }
                   }
               }
               else{ $emp_array['passport_end_date'] = null;}

               $emp_array['bank_details'] = 
               [
                   'account_holder_name' => $value[19],
                   'account_number' => $value[20],
                   'bank_name' => $value[21],
                   'bank_code' => $value[22],
               ];
               $emp_array['bank_details'] = json_encode($emp_array['bank_details']);
               
               $emp_array['assigned_to'] = $value[23];
               if ($emp_array['assigned_to'] !== null) 
               {
               
                   $assigned_to = SalesProject::find($emp_array['assigned_to']);
                   if (!$assigned_to) 
                   {
                   
                       $is_valid = false;
                       $error_msg = __('essentials::lang.contact_not_found').$row_no;
                       break;
                   }
               }
               else
               {
                   $emp_array['assigned_to']=null;
               }

               $emp_array['business_id'] =  $business_id;

               $emp_array['company_id'] = $value[26];
               if ($emp_array['company_id'] !== null) 
               {
               
                   $company_id = Company::find($emp_array['company_id']);
                 
                   if (!$company_id) 
                   {
                   
                       $is_valid = false;
                       $error_msg = __('essentials::lang.company_not_found').$row_no;
                       break;
                   }
               }
               else
               {
                   $is_valid = false;
                   $error_msg =__('essentials::lang.company_required' ) .$row_no;
                   break;
               } 

          

               $emp_array['essentials_department_id'] = $value[28];
               if ($emp_array['essentials_department_id'] !== null) 
               {
           
                   $dep = EssentialsDepartment::find($emp_array['essentials_department_id']);
                   if (!$dep) 
                   {
                   
                       $is_valid = false;
                       $error_msg = __('essentials::lang.dep_not_found' ) .$row_no;
                       break;
                   }
               } 
               else
               {
                   
                   $emp_array['essentials_department_id'] = null;
               }
               
               $emp_array['admission_date']=$value[29];
               if (!empty($value[29]))
               {
                   if (is_numeric($value[29])) 
                   {
                   
                       $excelDateValue = (float)$value[29];
                       $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                       $date = date('Y-m-d', $unixTimestamp);
                       $emp_array['admission_date'] = $date;
                   
                   } else
                    {
                   
                       $date = DateTime::createFromFormat('d/m/Y', $value[29]);
                       if ($date)
                        {
                           $dob = $date->format('Y-m-d');
                           $emp_array['admission_date'] = $dob;
                        }
                     }

               }
               $emp_array['profession_id']=$value[31];
               if ($emp_array['profession_id'] !== null)
               {
               
                   $profession_id = EssentialsProfession::find($emp_array['profession_id']);
                   if (!$profession_id)
                   {
                   
                       $is_valid = false;
                       $error_msg = __('essentials::lang.prof_not_found') .$row_no;
                       break;
                   }
               } else
               {
               
                   $emp_array['profession_id'] = null;
               }

               $emp_array['border_no']=$value[32];
              
               $emp_array['nationality_id']=$value[33];
               if ($emp_array['nationality_id'] !== null)
               {
                   $nationality_id = EssentialsCountry::find($emp_array['nationality_id']);
                   if (!$nationality_id) 
                   {
                   
                       $is_valid = false;
                       $error_msg =  __('essentials::lang.nationality_not_found') .$row_no;
                       break;
                   }
               } 
               else
               {
               
                   $emp_array['nationality_id'] = null;
               }

            if(!empty($value[34]))
            {
                $emp_array['contract_number']= $value[34];
            }
            else{ $emp_array['contract_number']=null;}


            if (!empty($value[35])) 
            {
                if (is_numeric($value[35]))
                {
                
                    $excelDateValue = (float)$value[35];
                    $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                    $date = date('Y-m-d', $unixTimestamp);
                    
                    $emp_array['contract_start_date'] = $date;
                
                } else
                 {
                
                    $date = DateTime::createFromFormat('d/m/Y', $value[35]);
                    if ($date) {
                        $dob = $date->format('Y-m-d');
                        $emp_array['contract_start_date'] = $dob;

                    }
                
                 }
            }
            else{ $emp_array['contract_start_date'] = null;}


            if (!empty($value[36])) 
            {
                if (is_numeric($value[36])) {
                
                    $excelDateValue = (float)$value[36];
                    $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                    $date = date('Y-m-d', $unixTimestamp);
                    $emp_array['contract_end_date'] = $date;
                
                } else
                 {
                    
                    $date = DateTime::createFromFormat('d/m/Y', $value[36]);
                    if ($date) {
                        $dob = $date->format('Y-m-d');
                        $emp_array['contract_end_date'] = $dob;
                    }
                }
            }
            else{ $emp_array['contract_end_date'] = null;}

            // if (!empty($value[37]))
            // {
            //     $emp_array['contract_duration'] = $value[37];
            //     if(!is_numeric( $emp_array['contract_duration']))
            //     {
            //         $is_valid = false;
            //         $error_msg =  __('essentials::lang.contract_duration_should_be_is_numeric') .$row_no;
            //         break;
            //     }
            // } 
            // else{$emp_array['contract_duration'] = null;}

            if (!empty($value[38])) 
            {
                $emp_array['probation_period'] = $value[38];
                if(!is_numeric( $emp_array['probation_period']))
                {
                    $is_valid = false;
                    $error_msg =  __('essentials::lang.probation_period_should_be_is_numeric') .$row_no;
                    break;
                }
            } 
            else{  $emp_array['probation_period'] = null;}

            if (!empty($value[39]))
            {
                $emp_array['is_renewable'] = $value[39];
            } 
            else{ $emp_array['is_renewable'] = null;}

            $emp_array['essentials_salary'] = $value[40];

            if ($value[41] !== null)
            {
                                        
                $housing_allowance_id = EssentialsAllowanceAndDeduction::find($value[41]);
              
                if (!$housing_allowance_id) {
                
                    $is_valid = false;
                    $error_msg = __('essentials::lang.housing_allowance_id_not_found') .$row_no;
                    break;
                }
            } 

            if ($value[43] !== null) 
            {
                    
                $trans_allowance_id = EssentialsAllowanceAndDeduction::find($value[43]);
              
                if (!$trans_allowance_id) {
                
                    $is_valid = false;
                    $error_msg = __('essentials::lang.trans_allowance_id_not_found') .$row_no;
                    break;
                }
            } 

            if ($value[45] !== null) 
            {
                    
                $other_allowance_id = EssentialsAllowanceAndDeduction::find($value[45]);
              
                if (!$other_allowance_id) {
                
                    $is_valid = false;
                    $error_msg = __('essentials::lang.other_allowance_id_not_found') .$row_no;
                    break;
                }
            }
            
            $emp_array['allowance_data'] = 
            [
                'housing_allowance' => json_encode(['salaryType' => $value[41], 'amount' => $value[42]]),
                'transportation_allowance' => json_encode(['salaryType' => $value[43], 'amount' => $value[44]]),
                'other' => json_encode(['salaryType' => $value[45], 'amount' => $value[46]]),
              
            ];
              
            $emp_array['total_salary'] = $value[47]; 
            
            if($value[48] != null)
            {
                $emp_array['emp_number'] = $value[48];
            }
            else{ $emp_array['emp_number']=null;}
           

            $formated_data[] = $emp_array; 
                                     
            }
          
            if (!$is_valid) 
            {
               throw new \Exception($error_msg);
            }
            $defaultContractData =
            [
              'contract_start_date' => null,
              'contract_end_date' => null,
                                          
            ];
            $formated_data = array_map(fn($emp_data) => array_merge($defaultContractData, $emp_data), $formated_data);  
                      
            if (! empty($formated_data)) 
            {
                 
                foreach ($formated_data as $emp_data) 
                {
                    
                    $emp_data['created_by'] = $user_id;
                    $emp_data['essentials_pay_period'] = 'month';
                    
                    
                    if (in_array($emp_data['id_proof_number'], $processedIdProofNumbers))
                    {
                            throw new \Exception(__('essentials::lang.duplicate_id_proof_number', ['id_proof_number' => $emp_data['id_proof_number']]));
                    }
                    $processedIdProofNumbers[] = $emp_data['id_proof_number'];             
                    
                    
                    if($emp_data['emp_number'] == null)
                    {
                       //add code here
                    }
                    
                    $emp = User::create($emp_data);
                   
                   
                    $emp_data['employee_id'] = $emp->id;
                    $emp_data['contract_type_id'] = null;
                    foreach ($emp_data['allowance_data'] as $allowanceType => $allowanceJson)
                    {
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
                                } 
                                catch (\Exception $e)
                                 {
                                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                                error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                                 }
                            }


                            $previous_proof_date = EssentialsOfficialDocument::where('employee_id',  $emp->id)
                            ->where('type' , 'residence_permit')
                            ->where('is_active',1)
                            ->latest('created_at')
                            ->first();
                        
                            if( $previous_proof_date )
                            {
                                $previous_proof_date->is_active= 0;
                          
                                $previous_proof_date->save();
                            
                            }

                            $doc =new EssentialsOfficialDocument();
                            $doc->type ='residence_permit';
                            $doc->status ='valid';
                            $doc->employee_id = $emp->id;
                            $doc->is_active= 1;
                            $doc->number = $emp_data['id_proof_number'];
                            $doc->expiration_date = $emp_data['proof_end_date'];
                            $doc->save();



                          $previous_passport_date = EssentialsOfficialDocument::where('employee_id',  $emp->id)
                            ->where('type' , 'passport')
                            ->where('is_active',1)
                            ->latest('created_at')
                            ->first();
                           
                            if( $previous_passport_date )
                            {
                                $previous_passport_date->is_active= 0;
                             
                                $previous_passport_date->save();
                              
                            }

                            $passport =new EssentialsOfficialDocument();
                            $passport->type ='passport';
                            $passport->status ='valid';
                            $passport->employee_id = $emp->id;
                            $passport->is_active= 1;
                            $passport->number = $emp_data['passport_number'];
                            $passport->expiration_date = $emp_data['passport_end_date'];
                            $passport->save();
                            
                        


                        $final_contract_start_date=null;
                        if($emp_data['contract_start_date'] != null ||  $emp_data['contract_start_date'] != null )
                        {

                            $previous_contract = EssentialsEmployeesContract::where('employee_id',  $emp->id)
                            ->where('is_active',1)
                            ->latest('created_at')
                            ->first();
                           
                            if( $previous_contract )
                            {
                                $previous_contract->is_active= 0;
                               // $previous_contract->contract_end_date= $emp_data['contract_start_date'];
                                $previous_contract->save();
                              
                            }

                            $contract =new EssentialsEmployeesContract();
                            $contract->employee_id  = $emp->id;

                            if( $emp_data['contract_number'] == null)
                            {
                                $latestRecord = EssentialsEmployeesContract::orderBy('contract_number', 'desc')->first();
                                if ($latestRecord) 
                                {
                                    $latestRefNo = $latestRecord->contract_number;
                                    $numericPart = (int)substr($latestRefNo, 3);
                                    $numericPart++;
                                    $emp_data['contract_number'] = 'EC' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
                                } else 
                                {
                                    $emp_data['contract_number'] = 'EC0001';
                                }
                                $contract->contract_number= $emp_data['contract_number'];
                            }
                            else 
                            {$contract->contract_number = $emp_data['contract_number'] ;}
                           
                          
                            //start date is exist , end date is not exist
                            if($emp_data['contract_start_date'] != null && $emp_data['contract_end_date'] == null )
                            {
    
                                $contract_start_date =$emp_data['contract_start_date']; 
                                $final_contract_start_date=   $contract_start_date;
                                
                                $date = Carbon::parse($contract_start_date);
                                $date->addYear(); 
                                
                                $contract->contract_end_date = $date;
                                $contract->contract_start_date=$emp_data['contract_start_date']; 
                                $contract->contract_duration=1;
    
                            } //end date is exist , start date is not exist
                            else if($emp_data['contract_start_date'] == null && $emp_data['contract_end_date'] != null )
                            {
                                $contract_end_date =$emp_data['contract_end_date']; 
                                $date = Carbon::parse($contract_end_date);
                                $date->subYear(); 
                                $contract->contract_start_date = $date;
                                
                                $final_contract_start_date= $date;
                                
                                $contract->contract_end_date=$emp_data['contract_end_date']; 
                                $contract->contract_duration=1;
     
                            }//end date is exist , start date is  exist
                            else{
                                $contract_end_date =$emp_data['contract_end_date']; 
                                $contract_start_date =$emp_data['contract_start_date']; 
                                
                                $final_contract_start_date= $contract_start_date ;
                                
                                $start = Carbon::parse($contract_start_date);
                                $end = Carbon::parse($contract_end_date);
                            
                                $contract_duration = $start->diffInYears($end);
                                $contract->contract_start_date=$emp_data['contract_start_date']; 
                                $contract->contract_end_date=$emp_data['contract_end_date']; 
                                $contract->contract_duration = $contract_duration;
    
                            }
    
                            $contract->is_renewable= $emp_data['is_renewable'];
                            $contract->probation_period =$emp_data["probation_period"];
                            $contract->contract_type_id  = $emp_data["contract_type_id"];
                            $contract->status = "valid";
                            $contract->is_active  = 1;
                            $contract->save();
    
                        }

                                             
                          
                       
                        if( $emp_data['user_type'] == 'worker')
                        {
                            $previous_appointment = EssentialsEmployeeAppointmet::where('employee_id', $emp->id)
                            ->where('is_active',1)
                            ->latest('created_at')
                            ->first();
                           
                            if( $previous_appointment )
                            {
                                $previous_appointment->is_active= 0;
                                $previous_appointment->end_at=  $final_contract_start_date;
                                $previous_appointment->save();
                              
                            }
                            
                            $essentials_employee_appointmets = new EssentialsEmployeeAppointmet();
                            $essentials_employee_appointmets->employee_id = $emp->id;
                            $essentials_employee_appointmets->start_from= $final_contract_start_date;
                            $essentials_employee_appointmets->department_id= null;
                            $essentials_employee_appointmets->is_active = 1;
                            $essentials_employee_appointmets->profession_id=$emp_data['profession_id'];
                            $essentials_employee_appointmets->save();
    
                        }
                        else
                        {
                            $previous_appointment = EssentialsEmployeeAppointmet::where('employee_id', $emp->id)
                            ->where('is_active',1)
                            ->latest('created_at')
                            ->first();
                           
                            if( $previous_appointment )
                            {
                                $previous_appointment->is_active= 0;
                                $previous_appointment->end_at= $final_contract_start_date;
                                $previous_appointment->save();
                              
                            }
                            //contract start date --> appointement start date
                            $essentials_employee_appointmets = new EssentialsEmployeeAppointmet();
                            $essentials_employee_appointmets->employee_id = $emp->id;
                            $essentials_employee_appointmets->start_from= $final_contract_start_date;
                            $essentials_employee_appointmets->department_id=  $emp_data['essentials_department_id'];
                            $essentials_employee_appointmets->is_active = 1;
                            $essentials_employee_appointmets->profession_id=$emp_data['profession_id'];
                            $essentials_employee_appointmets->save();
                           
                         }
                          
                        
                        
                        if($emp_data['admission_date'] != null)
                        {
                            $previous_admission = EssentialsAdmissionToWork::where('employee_id',$emp->id)
                            ->where('is_active',1)
                            ->latest('created_at')
                            ->first();

                           if( $previous_admission )
                           {
                                $previous_admission->is_active= 0;
                                $previous_admission->save();

                               //contract start date compare 
                                $essentials_admission_to_works = new EssentialsAdmissionToWork();
                                $essentials_admission_to_works->admissions_date=$emp_data['admission_date'];
                                $essentials_admission_to_works->employee_id = $emp->id;
                                $essentials_admission_to_works->admissions_type="after_vac";
                                $essentials_admission_to_works->admissions_status="on_date";
                                $essentials_admission_to_works->is_active = 1;
                                $essentials_admission_to_works->save();
                               
                           }
                           else
                           {
                                //also contract  start date
                                $essentials_admission_to_works = new EssentialsAdmissionToWork();
                                $essentials_admission_to_works->admissions_date=$emp_data['admission_date'];
                                $essentials_admission_to_works->employee_id = $emp->id;
                                $essentials_admission_to_works->admissions_type="first_time";
                                $essentials_admission_to_works->admissions_status="on_date";
                                $essentials_admission_to_works->is_active = 1;
                                $essentials_admission_to_works->save();
                           }
                          
                        }
                        

                      
                    }
                }
                if (!$is_valid) 
                {
                   throw new \Exception($error_msg);
                }

                $output = ['success' => 1,'msg' => __('product.file_imported_successfully'),];
                DB::commit();
            }
        } 
        catch (\Exception $e) 
        {

            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];

            return redirect()->route('import-employees')
            ->with( $output);
        }
       
        return redirect()->action([\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'index'])
        ->with( $output);
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
	