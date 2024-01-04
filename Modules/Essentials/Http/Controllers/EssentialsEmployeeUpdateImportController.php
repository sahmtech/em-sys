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
        $can_crud_import_employee = auth()->user()->can('essentials.crud_import_employee');
        if (! $can_crud_import_employee) {
           //temp  abort(403, 'Unauthorized action.');
        }
		
        try {
           

            //Set maximum php execution time
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
                

                      
                                     
                                        
                 if (!empty($value[0])) 
                 {
                     $emp_array['employee_id'] = $value[0];
                 }
                 else {
                    $is_valid = false;
                    $error_msg = __('essentials::lang.employee_id_required') .$row_no;
                    break;
                }
              
                $emp_array['first_name'] = $value[1];                      
                $emp_array['mid_name'] = $value[2];
                                    
                                    
                if (!empty($value[3])) 
                {
                  $emp_array['last_name'] = $value[3];
                 } 
                                    
                $emp_array['name'] = implode(' ', [ $emp_array['first_name'], $emp_array['mid_name'], $emp_array['last_name']]);
                
                if(!empty($value[4])){
                  $emp_array['user_type'] = $value[4];
                  }
                                        
                                    
                $emp_array['email'] = $value[5];
                            
                                        
                                    if (!empty($value[6])) {
                                            if (is_numeric($value[6])) {
                                            
                                                $excelDateValue = (float)$value[6];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['dob'] = $date;
                                            
                                            } else {
                                            
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
                                    // $emp_array['name'] = implode(' ', [$emp_array['prefix'], $emp_array['first_name'], $emp_array['middle_name'], $emp_array['last_name']]);
                                    $emp_array['blood_group'] = $value[9];
                                    
                                        
                                    
                                        if (! empty(trim($value[10]))) {
                                            $emp_array['contact_number'] = $value[10];
                                        } 
                                       
                                    
                                        $emp_array['alt_number'] = $value[11];
                                        $emp_array['family_number'] = $value[12];
                                        $emp_array['current_address'] = $value[13];
                                        $emp_array['permanent_address'] = $value[14];
                                    


                                        $emp_array['id_proof_name'] = $value[15];
                                        
                                        $emp_array['id_proof_number'] = $value[16];
                                        
                                       

                                        if (!empty($value[17])) {
                                            if (is_numeric($value[17])) {
                                            
                                                $excelDateValue = (float)$value[17];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['proof_end_date'] = $date;
                                            
                                            } else {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[17]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['proof_end_date'] = $dob;
                                                }
                                        }
                                    }
                                    else{ $emp_array['proof_end_date'] = null;}


                                        $emp_array['passport_numbrer'] = $value[18];
                                       
                                        
                                        if (!empty($value[19])) {
                                            if (is_numeric($value[19])) {
                                            
                                                $excelDateValue = (float)$value[19];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['passport_end_date'] = $date;
                                            
                                            } else {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[19]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['passport_end_date'] = $dob;
                                                }
                                        }
                                    }
                                    else{ $emp_array['passport_end_date'] = null;}

                                       
                                    
                                    $emp_array['bank_details'] = [
                                        'account_holder_name' => $value[20],
                                        'account_number' => $value[21],
                                        'bank_name' => $value[22],
                                        'bank_code' => $value[23],
                                    ];
                                    
                                
                                        $emp_array['bank_details'] = json_encode($emp_array['bank_details']);
                                    
                                        $emp_array['assigned_to'] = $value[24];
                                        
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



                                        $emp_array['contact_location_id'] = $value[25];


                                        //---------------------------------------------------
                                        $emp_array['business_id'] = $value[26];

                                        if ($emp_array['business_id'] !== null) {
                                        
                                            $business = Business::find($emp_array['business_id']);
                                          
                                            if (!$business) {
                                            
                                                $is_valid = false;
                                                $error_msg = __('essentials::lang.business_not_found').$row_no;
                                                break;
                                            }
                                        }
                                       


                                        
                                        $emp_array['location_id'] = $value[27];

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





                                        $emp_array['essentials_department_id'] = $value[28];
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




                                        $emp_array['addmission_date']=$value[29];
                                        if (!empty($value[29])) {
                                            if (is_numeric($value[29])) {
                                            
                                                $excelDateValue = (float)$value[29];
                                                $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                                $date = date('Y-m-d', $unixTimestamp);
                                                $emp_array['addmission_date'] = $date;
                                            
                                            } else {
                                            
                                                $date = DateTime::createFromFormat('d/m/Y', $value[29]);
                                                if ($date) {
                                                    $dob = $date->format('Y-m-d');
                                                    $emp_array['addmission_date'] = $dob;
                                                }
                                        }
                                               }



                                        $emp_array['specialization_id']=$value[30];
                                      
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



                                        $emp_array['profession_id']=$value[31];

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
                                        


                                        $emp_array['border_no']=$value[32];
                                        $emp_array['nationality_id']=$value[33];

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


                                        
                                        if (!empty($value[34])) {
                                            $emp_array['contract_number'] = $value[34];
                                        } 
                                        else{$emp_array['contract_number'] = null;}
                                    
                                    
                                        if (!empty($value[35])) {
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
                    
                                    if (!empty($value[36])) {
                                        if (is_numeric($value[36])) {
                                        
                                            $excelDateValue = (float)$value[36];
                                            $unixTimestamp = ($excelDateValue - 25569) * 86400; 
                                            $date = date('Y-m-d', $unixTimestamp);
                                            $emp_array['contract_end_date'] = $date;
                                        
                                        } else {
                                            
                                            $date = DateTime::createFromFormat('d/m/Y', $value[36]);
                                            if ($date) {
                                                $dob = $date->format('Y-m-d');
                                                $emp_array['contract_end_date'] = $dob;
                                            }
                                    }
                                }
                                else{ $emp_array['contract_end_date'] = null;}


                                    
                                    
                                if (!empty($value[37])) {
                                    $emp_array['contract_duration'] = $value[37];
                                } 
                                else{$emp_array['contract_duration'] = null;}

                                if (!empty($value[38])) {
                                    $emp_array['probation_period'] = $value[38];
                                } 
                                else{  $emp_array['probation_period'] = null;}
                                    
                                
                                if (!empty($value[39])) {
                                    $emp_array['is_renewable'] = $value[39];
                                } 
                                else{   $emp_array['is_renewable'] = null;}
                                    
                                $emp_array['essentials_salary'] = $value[41];
                                    
                               
                                if ($value[42] !== null) {
                                        
                                    $housing_allowance_id = EssentialsAllowanceAndDeduction::find($value[42]);
                                  
                                    if (!$housing_allowance_id) {
                                    
                                        $is_valid = false;
                                        $error_msg = __('essentials::lang.housing_allowance_id_not_found') .$row_no;
                                        break;
                                    }
                                } 
                               
                                if ($value[43] !== null) {
                                        
                                    $trans_allowance_id = EssentialsAllowanceAndDeduction::find($value[43]);
                                  
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
                                    'housing_allowance' => json_encode(['salaryType' => $value[41], 'amount' => $value[42]]),
                                    'transportation_allowance' => json_encode(['salaryType' => $value[43], 'amount' => $value[44]]),
                                    'other' => json_encode(['salaryType' => $value[45], 'amount' => $value[46]]),
                                  
                                ];
                                        
                                
                                $emp_array['total_salary'] = $value[47]; 
                                    
                                        $travelcategoryname=$value[48];
                                        $traveltype = EssentialsTravelTicketCategorie::where('name', $travelcategoryname)->first();
                                        if ($traveltype) {
                                            
                                            $traveltypeId = $traveltype->id;
                                            $emp_array['travel_ticket_categorie']=$traveltypeId;
                                        }
                                        else{ $emp_array['travel_ticket_categorie']=null;}

                                        

                                        $emp_array['has_insurance'] = $value[49]; 
                                      
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
                        
                         
                       
                        //     if (in_array($emp_data['id_proof_number'], $processedIdProofNumbers)) {
                        //         throw new \Exception(__('essentials::lang.duplicate_id_proof_number', ['id_proof_number' => $emp_data['id_proof_number']]));
                        //     }
                        
                        //   $processedIdProofNumbers[] = $emp_data['id_proof_number'];             

                         

                            // $numericPart = (int)substr($business_id, 3);
                            // $lastEmployee = User::where('business_id', $business_id)
                            //     ->orderBy('emp_number', 'desc')
                            //     ->first();

                            // if ($lastEmployee) {
                              
                            //     $lastEmpNumber = (int)substr($lastEmployee->emp_number, 3);

                        
                               
                            //     $nextNumericPart = $lastEmpNumber + 1;

                            //     $emp_data['emp_number'] = $business_id . str_pad($nextNumericPart, 6, '0', STR_PAD_LEFT);
                            // } 
                        
                            // else {
                              
                            //     $emp_data['emp_number'] =  $business_id .'000';

                            // }
        
                            // Check if the employee with the given ID exists
                            $existingEmployee = User::find($emp_data['employee_id']);

                            if ($existingEmployee) {
                                // Update existing employee data
                                $existingEmployee->update($emp_data);
                            } else {
                                // Employee does not exist, create a new one
                                $emp = User::create($emp_data);
                            }

                    
                            $emp_data['business_id'] = $emp_data['business_id'];
                            $emp_data['employee_id'] = $existingEmployee ? $existingEmployee->id : $emp->id;
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

                        EssentialsOfficialDocument::updateOrCreate(
                            ['employee_id' => $emp_data['employee_id'],
                            
                            'type' => 'residence_permit'],
                            [
                                'status' => 'valid',
                                'number' => $emp_data['id_proof_number'],
                                'expiration_date' => $emp_data['proof_end_date'],
                            ]
                        );

                        EssentialsOfficialDocument::updateOrCreate(
                            ['employee_id' => $emp_data['employee_id'], 'type' => 'passport'],
                            [
                                'status' => 'valid',
                                'number' => $emp_data['passport_numbrer'],
                                'expiration_date' => $emp_data['passport_end_date'],
                            ]
                        );
                        

                        EssentialsEmployeesContract::updateOrCreate(
                            ['employee_id' => $emp_data['employee_id']],
                            [
                                'contract_number' => $emp_data['contract_number'],
                                'contract_start_date' => $emp_data['contract_start_date'],
                                'contract_end_date' => $emp_data['contract_end_date'],
                                'is_renewable' => $emp_data['is_renewable'],
                                'contract_duration' => $emp_data['contract_duration'],
                                'probation_period' => $emp_data['probation_period'],
                                'contract_type_id' => $emp_data['contract_type_id'],
                                'status' => 'valid',
                            ]
                        );
                       // dd($contract);


                       EssentialsEmployeeAppointmet::updateOrCreate(
                        ['employee_id' => $emp_data['employee_id']],
                        [
                            'department_id' => $emp_data['essentials_department_id'],
                            'business_location_id' => $emp_data['location_id'],
                            'profession_id' => $emp_data['profession_id'],
                            'specialization_id' => $emp_data['specialization_id'],
                        ]
                    );


                    EssentialsAdmissionToWork::updateOrCreate(
                        ['employee_id' => $emp_data['employee_id']],
                        [
                            'admissions_date' => $emp_data['addmission_date'],
                            'admissions_type' => 'first_time',
                            'admissions_status' => 'on_date',
                        ]
                    );

                       

                        if ($emp_data['travel_ticket_categorie'] != null) {
                            EssentialsEmployeeTravelCategorie::updateOrCreate(
                                ['employee_id' => $emp_data['employee_id']],
                                ['categorie_id' => (int)$emp_data['travel_ticket_categorie']]
                            );
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
