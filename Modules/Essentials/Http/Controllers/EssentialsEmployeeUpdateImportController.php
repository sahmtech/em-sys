<?php

namespace Modules\Essentials\Http\Controllers;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use Excel;
use DateTime;
use App\User;
use App\Category;
use App\Business;
use App\Contact;
use App\ContactLocation;
use App\BusinessLocation;
use App\Utils\TransactionUtil;
use Illuminate\Support\Carbon;
use App\Utils\ModuleUtil;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
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
use Modules\Essentials\Entities\EssentialsEmployeeImport;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Sales\Entities\SalesProject;
use Modules\Essentials\Entities\EssentialsContractType;




class EssentialsEmployeeUpdateImportController extends Controller
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
        return view('essentials::index');
    }


    public function postImportupdateEmployee(Request $request)
    {
        $can_import_update_employees = auth()->user()->can('essentials.import_update_employees');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        if (!($is_admin || $can_import_update_employees)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
		
        try 
        {
           ini_set('max_execution_time', 0);
           
           if ($request->hasFile('update_employee_csv'))
             {
                $file = $request->file('update_employee_csv');
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
                    $emp_array['emp_number'] = $value[0]; 

                    $emp_array['first_name'] = $value[1];                      
                    $emp_array['mid_name'] = $value[2];
                    $emp_array['last_name'] = $value[3];               
                                        
                    // $emp_array['emp_user_type']=null;
                    // if(!empty($value[4]))
                    // {
                    //     $emp_array['user_type'] = $value[4];
                    //     $allowedUserTypes = [ 'manager'];
                
                    //     if (!in_array($emp_array['user_type'], $allowedUserTypes)) {
                    //         $is_valid = false;
                    //         $error_msg = __('essentials::lang.user_type_is_valid' ).$row_no;
                    //         break;
                    //     }
                    // }
                    // else{  $emp_array['emp_user_type']=null;}
                
                                            
                                        
                    $emp_array['email'] = $value[5];
                            
                                        
                    if (!empty($value[6]))
                    {
                        if (is_numeric($value[6])) 
                        {
                        
                            $excelDateValue = (float)$value[6];
                            $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                            $date = date('Y-m-d', $unixTimestamp);
                            $emp_array['dob'] = $date;
                        
                        } else
                        {
                        
                            $date = DateTime::createFromFormat('d/m/Y', $value[6]);
                            if ($date) {
                                $dob = $date->format('Y-m-d');
                                $emp_array['dob'] = $dob;
                            }
                        }
                                    
                    }
                    else{ $emp_array['dob'] = null;}
                    
                    $emp_array['gender'] = $value[7];
                    $emp_array['marital_status'] = $value[8];
                    $emp_array['blood_group'] = $value[9];

                    
                    if (! empty(trim($value[10])))
                    {
                        $emp_array['contact_number'] = $value[10];
                    }
                    
                    $emp_array['alt_number'] = $value[11];
                    $emp_array['family_number'] = $value[12];
                    $emp_array['current_address'] = $value[13];
                    $emp_array['permanent_address'] = $value[14];
                    $emp_array['id_proof_name'] = $value[15];
                  
                    $emp_array['id_proof_number'] = $value[16];
                    if($value[16] != null)
                    {
                        $proof_number = User::where('id_proof_number',$emp_array['id_proof_number'])->first();
                      if (!$proof_number)
                      {
                      
                          $is_valid = false;
                          $error_msg = __('essentials::lang.user_not_found').$row_no+1;
                          break;
                      } 
                    }
                    else{
                        $is_valid = false;
                        $error_msg = __('essentials::lang.id_proof_number_required').$row_no+1;
                        break;
                    }

                    
                    if (!empty($value[17]))
                    {
                       if (is_numeric($value[17])) {
                       
                           $excelDateValue = (float)$value[17];
                           $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                           $date = date('Y-m-d', $unixTimestamp);
                           $emp_array['proof_end_date'] = $date;
                       
                       } else
                       {
                       
                           $date = DateTime::createFromFormat('d/m/Y', $value[17]);
                           if ($date) {
                               $dob = $date->format('Y-m-d');
                               $emp_array['proof_end_date'] = $dob;
                           }
                        }
                   }
                   else{ $emp_array['proof_end_date'] = null;}
                   
                   $emp_array['passport_number'] = $value[18];
                  
                   if (!empty($value[19]))
                   {
                      if (is_numeric($value[19]))
                      {
                      
                          $excelDateValue = (float)$value[19];
                          $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                          $date = date('Y-m-d', $unixTimestamp);
                          $emp_array['passport_end_date'] = $date;
                      
                      } else
                      {
                      
                          $date = DateTime::createFromFormat('d/m/Y', $value[19]);
                          if ($date) {
                              $dob = $date->format('Y-m-d');
                              $emp_array['passport_end_date'] = $dob;
                          }
                      }
                  }
                  else{ $emp_array['passport_end_date'] = null;}
                    
                  $emp_array['bank_details'] =
                  [
                     'account_holder_name' => $value[20],
                     'account_number' => $value[21],
                     'bank_name' => $value[22],
                     'bank_code' => $value[23],
                  ];
                  $emp_array['bank_details'] = json_encode($emp_array['bank_details']);
                  
                  $emp_array['assigned_to'] = $value[24];
                  if ($emp_array['assigned_to'] !== null)
                  {
                  
                      $business = SalesProject::find($emp_array['assigned_to']);
                      if (!$business)
                      {
                      
                          $is_valid = false;
                          $error_msg = __('essentials::lang.contact_not_found').$row_no+1;
                          break;
                      }
                  }
                  else
                  {
                      $emp_array['assigned_to']=null;
                  } 
                  $emp_array['contact_location_id'] =null;
                  $emp_array['business_id'] =   $business_id;
               

                  
                  $emp_array['essentials_department_id'] = $value[28];
                 
                  if ($emp_array['essentials_department_id'] !== null)
                  {
              
                      $dep = EssentialsDepartment::find($emp_array['essentials_department_id']);
                      if (!$dep)
                      {
                      
                          $is_valid = false;
                          $error_msg = __('essentials::lang.dep_not_found' ) .$row_no+1;
                          break;
                      }
                  } else
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
                     
                         $specialization = EssentialsProfession::find($emp_array['profession_id']);
                         if (!$specialization) {
                         
                             $is_valid = false;
                             $error_msg = __('essentials::lang.prof_not_found') .$row_no+1;
                             break;
                         }
                     } else {
                     
                         $emp_array['profession_id'] = null;
                     }

                     
                     $emp_array['border_no']=$value[32];


                     $emp_array['nationality_id']=$value[33];
                     if ($emp_array['nationality_id'] !== null)
                    {
                     
                         $nationality_id = EssentialsCountry::find($emp_array['nationality_id']);
                         if (!$nationality_id) {
                         
                             $is_valid = false;
                             $error_msg =  __('essentials::lang.nationality_not_found') .$row_no+1;
                             break;
                         }
                     } else {
                     
                         $emp_array['nationality_id'] = null;
                     }
                     if(!empty($value[34]))
                     {
                         $emp_array['contract_number']= $value[34];
                     }
                     else{ $emp_array['contract_number']=null;}
         

                    if (!empty($value[35]))
                    {
                        if (is_numeric($value[35])) {
                        
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

                              
                        if (!empty($value[37]))
                        {
                            $emp_array['contract_duration'] = $value[37];
                            if(!is_numeric( $emp_array['contract_duration']))
                            {
                                $is_valid = false;
                                $error_msg =  __('essentials::lang.contract_duration_should_be_is_numeric') .$row_no+1;
                                break;
                            }
                        } 
                        else{$emp_array['contract_duration'] = null;}
            


                        if (!empty($value[38])) 
                        {
                            $emp_array['probation_period'] = $value[38];
                            if(!is_numeric( $emp_array['probation_period']))
                            {
                                $is_valid = false;
                                $error_msg =  __('essentials::lang.probation_period_should_be_is_numeric') .$row_no+1;
                                break;
                            }
                        } 
                        else{  $emp_array['probation_period'] = null;}
                          
            


                        if (!empty($value[39]))
                        {
                            $emp_array['is_renewable'] = $value[39];
                        } 
                        else{   $emp_array['is_renewable'] = null;}

                       
                        $emp_array['contract_type_id']=$value[40];
                        if (!empty($value[40]))
                        {
                            $contract_type = EssentialsContractType::find($value[40]);
                            if (!$contract_type) {
                           
                                $is_valid = false;
                                $error_msg = __('essentials::lang.contract_type_id_not_found') .$row_no+1;
                                break;
                            }
                           
                        } 
                        else{   $emp_array['contract_type_id'] = null;}
                        
                            
                        $emp_array['essentials_salary'] = $value[41];

                        
                    
                        if ($value[42] !== null)
                        {
                               
                           $housing_allowance_id = EssentialsAllowanceAndDeduction::find($value[42]);
                         
                           if (!$housing_allowance_id) {
                           
                               $is_valid = false;
                               $error_msg = __('essentials::lang.housing_allowance_id_not_found') .$row_no+1;
                               break;
                           }
                        } 
                      
                       if ($value[44] !== null) {
                               
                           $trans_allowance_id = EssentialsAllowanceAndDeduction::find($value[44]);
                         
                           if (!$trans_allowance_id) {
                           
                               $is_valid = false;
                               $error_msg = __('essentials::lang.trans_allowance_id_not_found') .$row_no+1;
                               break;
                           }
                       } 
                       if ($value[46] !== null) {
                               
                           $other_allowance_id = EssentialsAllowanceAndDeduction::find($value[46]);
                         
                           if (!$other_allowance_id) {
                           
                               $is_valid = false;
                               $error_msg = __('essentials::lang.other_allowance_id_not_found') .$row_no+1;
                               break;
                           }
                       } 
                      

                       
                       $emp_array['allowance_data'] =
                        [
                           'housing_allowance' => json_encode(['salaryType' => $value[42], 'amount' => $value[43]]),
                           'transportation_allowance' => json_encode(['salaryType' => $value[44], 'amount' => $value[45]]),
                           'other' => json_encode(['salaryType' => $value[46], 'amount' => $value[47]]),
                        ];
                               
                       
                       $emp_array['total_salary'] = $value[48]; 
                       $emp_array['company_id'] = $value[49]; 
                       $formated_data[] = $emp_array;

                                        
                    }
              
                      
                           

                $defaultContractData = 
                [
                        'contract_start_date' => null,
                        'contract_end_date' => null,
                                               
                ];
            
                if (!$is_valid) 
                {
                    throw new \Exception($error_msg);
                } 

         
            //$formated_data = array_map(fn($emp_data) => array_merge($defaultContractData, $emp_data), $formated_data); 
          //dd($formated_data);
            if (! empty($formated_data)) 
                {
                   
                    foreach ($formated_data as $emp_data) 
                    {
                       
                            $emp_data['business_id'] = $emp_data['business_id'];
                            $emp_data['created_by'] = $user_id;
                           // $emp_data['contract_type_id'] = null;
                            $emp_data['essentials_pay_period'] = 'month';
                           
                            $existingEmployee = User::where('id_proof_number',$emp_data['id_proof_number'])
                            ->where('status','active')
                            ->first();
    
                            
                      
                            if ($existingEmployee)
                            {
                              
                                $fieldsToUpdate = 
                                [
                                      'first_name',
                                      'mid_name',
                                      'last_name',
                                      'email',
                                      'dob',
                                      'essentials_department_id',
                                      'gender',
                                      'marital_status',
                                      'blood_group',
                                      'contact_number',	
                                      'alt_number',	
                                      'family_number',
                                      'essentials_salary',	
                                      'current_address',					   
                                      'permanent_address',
                                      'id_proof_name',
                                      'assigned_to',
                                      'account_holder_name',
                                       'account_number',
                                       'bank_name',
                                       'IBN_code',
                                       'business_id',
                                       'nationality_id',
                                       'bank_details',
                                       'company_id',
                                       'emp_number'
                                      
                                ];
    
                             
                            
                                foreach ($fieldsToUpdate as $field)
                                 {
                                  
                                    if (array_key_exists($field, $emp_data) && $emp_data[$field] !== null)
                                    {
                                        $existingEmployee->$field = $emp_data[$field];
                                    }
    
                                }
                            


                               
                                $existingEmployee->save();
                               
                                foreach ($emp_data['allowance_data'] as $allowanceType => $allowanceJson) {
                                    $allowanceData = json_decode($allowanceJson, true);
                                
                                    try {
                                        if ($allowanceData['salaryType'] !== null && $allowanceData['amount'] !== null && isset($allowanceData['amount'])) {
                                
                                            $userAllowancesAndDeduction = EssentialsUserAllowancesAndDeduction::updateOrCreate(
                                                [
                                                    'user_id' => $existingEmployee->id,
                                                    'allowance_deduction_id' => (int)$allowanceData['salaryType'] ?? null,
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
    
    
                          
    
                            if( $emp_data['proof_end_date'] != null)
                                {
                                    $previous_proof_date = EssentialsOfficialDocument::where('employee_id',  $existingEmployee->id)
                                    ->where('type' , 'residence_permit')
                                    ->where('is_active',1)
                                    ->latest('created_at')
                                    ->first();
                                   
                                    if( $previous_proof_date )
                                    {
                                        $previous_proof_date->is_active= 0;
                                        $previous_proof_date->save();
                                      
                                    }
    
                                    $residencePermitData =
                                    [
                                        'status' => 'valid',
                                        'is_active'=>1,
                                        'type' => 'residence_permit',
                                        'employee_id' => $existingEmployee->id,
                                        'number' => $emp_data['id_proof_number'],
                                        'expiration_date' => $emp_data['proof_end_date'],
                                    ];
                                    $filteredResidencePermitData = array_filter($residencePermitData, function ($value) {
                                        return $value !== null;
                                    });
                                 
        
                                    if (!empty($filteredResidencePermitData))
                                    {
                                        EssentialsOfficialDocument::Create(  $filteredResidencePermitData  );
    
                                    }
                                }
                                
    
    
                             
    
                              if($emp_data['passport_end_date'] != null)
                              {
                                $previous_passport_date = EssentialsOfficialDocument::where('employee_id',  $existingEmployee->id)
                                ->where('type' , 'passport')
                                ->where('is_active',1)
                                ->latest('created_at')
                                ->first();
                               
                                if( $previous_passport_date )
                                {
                                    $previous_passport_date->is_active= 0;
                                    $previous_passport_date->save();
                                  
                                }
    
                                $passportData = 
                                [
                                    'status' => 'valid',
                                    'is_active'=>1,
                                    'employee_id' => $existingEmployee->id,
                                    'type' => 'passport',
                                    'number' => $emp_data['passport_number'],
                                    'expiration_date' => $emp_data['passport_end_date'],
                                ];
    
    
                                $filteredPassportData = array_filter($passportData, function ($value) {
                                    return $value !== null;
                                });
    
                                if (!empty($filteredPassportData))
                                {
                                    
                                    $d=EssentialsOfficialDocument::Create(
                                     
                                        $filteredPassportData
                                    );
                                    
                                }
                              }
    
    
    
                               
                          
                                $final_contract_start_date=null;
                              
                                if($emp_data['contract_start_date'] != null  ||  $emp_data['contract_end_date'] != null )
                                {
                                    $previous_contract = EssentialsEmployeesContract::where('employee_id',  $existingEmployee->id)
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
                                    $contract->employee_id  = $existingEmployee->id;
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
                                    $contract->is_active  = 1;
                                    $contract->status = "valid";
                                    $contract->save();
            
                                }  
                                
                                
                               // dd( $emp_data['essentials_department_id']);
    
                                if($emp_data['essentials_department_id'] != null )
                                {
                                   
                                    $appointmentData=[];
                                    if( $existingEmployee->user_type == 'worker')
                                        {
                                           
                                            $appointmentData =
                                            [
                                                'employee_id'=> $existingEmployee->id,
                                                'start_from'=>  $final_contract_start_date,
                                                'department_id' => null,
                                                'profession_id' => $emp_data['profession_id'],
                                                'is_active' => 1,
                                               
                                            ];
                                        }
                                        
                                   
                                     else
                                        {
                                            
                                            $appointmentData =
                                            [
                                                'employee_id'=> $existingEmployee->id,
                                                'start_from'=>  $final_contract_start_date,
                                                'department_id' => $emp_data['essentials_department_id'],
                                                'profession_id' => $emp_data['profession_id'],
                                                'is_active' => 1,
                                            
                                            ];
                                        }
        
                                   
                                    $filteredAppointmentData = array_filter($appointmentData, function ($value) {
                                        return $value !== null;
                                    });
        
                                    if (!empty($filteredAppointmentData)) 
                                    {
                                        $previous_appointment = EssentialsEmployeeAppointmet::where('employee_id',  $existingEmployee->id)
                                        ->where('is_active',1)
                                        ->latest('created_at')
                                        ->first();
                                       
                                        if( $previous_appointment )
                                        {
                                            $previous_appointment->is_active= 0;
                                            $previous_appointment->end_at=  $final_contract_start_date;
                                            $previous_appointment->save();
                                          
                                        }
                                       
                                        $new_appointement=EssentialsEmployeeAppointmet::Create($filteredAppointmentData);
                                        $existingEmployee->essentials_department_id =$filteredAppointmentData['department_id'];
                                        $existingEmployee->save();
                                        
                                    }
                                }
                                elseif($emp_data['profession_id'] != null && $emp_data['essentials_department_id'] == null) {
                                   
                                    $previous_appointment = EssentialsEmployeeAppointmet::where('employee_id', $existingEmployee->id)
                                        ->where('is_active', 1)
                                        ->latest('created_at')
                                        ->first();
                                
                                    if ($previous_appointment) {
                                        $appointmentData = [
                                            'employee_id' => $existingEmployee->id,
                                            'start_from' => $final_contract_start_date,
                                            'department_id' => $previous_appointment->department_id,
                                            'profession_id' => $emp_data['profession_id'],
                                            'is_active' => 1,
                                        ];
                                
                                        $filteredAppointmentData = array_filter($appointmentData, function ($value) {
                                            return $value !== null;
                                        });
                                
                                        
                                        $previous_appointment->update($filteredAppointmentData);
                                        
                                    }
                                }
                                
    
                                if($emp_data['admission_date'] != null)
                                {
                                    $previous_admission = EssentialsAdmissionToWork::where('employee_id',$existingEmployee->id)
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
                                        $essentials_admission_to_works->employee_id = $existingEmployee->id;
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
                                        $essentials_admission_to_works->employee_id = $existingEmployee->id;
                                        $essentials_admission_to_works->admissions_type="first_time";
                                        $essentials_admission_to_works->admissions_status="on_date";
                                        $essentials_admission_to_works->is_active = 1;
                                        $essentials_admission_to_works->save();
                                   }
                                  
                                }
                               
                                $formdata[] = $emp_data;
    
                            } 

                   }
                  // dd( $formdata);
                      
               
                 
                   
                }

                $output = ['success' => 1,'msg' => __('product.file_imported_successfully'),];

                DB::commit();
            }
        }
        catch (\Exception $e) 
        {

            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,'msg' => $e->getMessage(), ];

            return redirect()->route('import-employees')
            ->with('notification', $output);
        }
       

        return redirect()->action([\Modules\Essentials\Http\Controllers\EssentialsManageEmployeeController::class, 'index'])
        ->with('notification', 'success insert');
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
        
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        
    }
}
