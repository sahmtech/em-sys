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
use Modules\Essentials\Entities\essentialsAllowanceType;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;
use Modules\Essentials\Entities\EssentialsEmployeeContract;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\Essentials\Entities\EssentialsEmployeeTravelCategorie;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
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
                 // dd($parsed_array);
                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);


                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');

                $formated_data = [];
                $is_valid = true;
                $error_msg = '';

                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                   
                    if (count($value) != 34) {
                        $is_valid = false;
                        $error_msg = 'Number of columns mismatch';
                        break;
                    }

                    $row_no = $key + 1;
                    $emp_array = [];
                    $contract_array=[];
                    $emp="";
                    //Check contact type
                    
                    if (!empty($value[0])) {
                        $emp_array['first_name'] = $value[0];
                    } else {
                        // $is_valid = false;
                        // $error_msg = "First name is required in row no. $row_no";
                        break;
                    }
                    $emp_array['mid_name'] = $value[1];
                    $emp_array['last_name'] = $value[2];
                    $emp_array['name'] = implode(' ', [ $emp_array['first_name'], $emp_array['mid_name'], $emp_array['last_name']]);
                    $emp_array['user_type'] = $value[3];
                    $emp_array['email'] = $value[4];
          
                    
                    if (!empty($value[5])) {
                        if (is_numeric($value[5])) {
                            // Convert the float to a human-readable date
                            $excelDateValue = (float)$value[5];
                            $unixTimestamp = ($excelDateValue - 25569) * 86400; // Convert Excel date to Unix timestamp
                            $date = date('Y-m-d', $unixTimestamp);
                            $emp_array['dob'] = $date;
                           // dd($emp_array['dob']);
                        } else {
                            // Try to parse it as a date string
                            $date = DateTime::createFromFormat('d/m/Y', $value[5]);
                            if ($date) {
                                $dob = $date->format('Y-m-d');
                                $emp_array['dob'] = $dob;
                            }
                    }
                }

                 
                    $emp_array['gender'] = $value[6];
                    $emp_array['marital_status'] = $value[7];
                   // $emp_array['name'] = implode(' ', [$emp_array['prefix'], $emp_array['first_name'], $emp_array['middle_name'], $emp_array['last_name']]);
                   $emp_array['blood_group'] = $value[8];
                  
                    
                    //Mobile number
                    if (! empty(trim($value[9]))) {
                        $emp_array['contact_number'] = $value[9];
                    } 
                    else {
                        $is_valid = false;
                        $error_msg = "Mobile number is required in row no. $row_no";
                        break;
                    }

                    //Alt contact number
                    $emp_array['alt_number'] = $value[10];
                    $emp_array['family_number'] = $value[11];
                    $emp_array['current_address'] = $value[12];
                    $emp_array['permanent_address'] = $value[13];
                  


                    $emp_array['id_proof_name'] = $value[14];
                    $emp_array['id_proof_number'] = $value[15];
                    $emp_array['bank_details']=implode(' ', [$value[16], $value[17], $value[18],$value[19]]);

                    $emp_array['location_id'] = $value[20];
                    $emp_array['essentials_department_id'] = $value[21];
                   


                 
                    $emp_array['job_title']=$value[22];
               

                    
                  
                    if (!empty($value[23])) {
                        $contract_array['contract_number'] = $value[23];
                    } 
                 
                    
                    if (!empty($value[24])) {
                        if (is_numeric($value[24])) {
                            // Convert the float to a human-readable date
                            $excelDateValue = (float)$value[24];
                            $unixTimestamp = ($excelDateValue - 25569) * 86400; // Convert Excel date to Unix timestamp
                            $date = date('Y-m-d', $unixTimestamp);
                            $emp_array['contract_start_date'] = $date;
                           // dd($emp_array['dob']);
                        } else {
                            // Try to parse it as a date string
                            $date = DateTime::createFromFormat('d/m/Y', $value[24]);
                            if ($date) {
                                $dob = $date->format('Y-m-d');
                                $emp_array['contract_start_date'] = $dob;
                            }
                    }
                }

                if (!empty($value[25])) {
                    if (is_numeric($value[25])) {
                        // Convert the float to a human-readable date
                        $excelDateValue = (float)$value[25];
                        $unixTimestamp = ($excelDateValue - 25569) * 86400; // Convert Excel date to Unix timestamp
                        $date = date('Y-m-d', $unixTimestamp);
                        $emp_array['contract_end_date'] = $date;
                       // dd($emp_array['dob']);
                    } else {
                        // Try to parse it as a date string
                        $date = DateTime::createFromFormat('d/m/Y', $value[25]);
                        if ($date) {
                            $dob = $date->format('Y-m-d');
                            $emp_array['contract_end_date'] = $dob;
                        }
                }
            }


                    if (!empty($value[25])) {
                        $date = DateTime::createFromFormat('d-m-Y', $value[25]);
                        
                        if ($date) 
                        {
                            $contract_end_date = $date->format('Y-m-d');
                            $emp_array['contract_end_date'] = $contract_end_date;
                          //  dd( $emp_array['dob']);
                        }
                    }
                     
                   
                    // else {
                    //     $is_valid = false;
                    //     $error_msg = "contract_end_date is required in row no. $row_no";
                    //     break;
                    // }
                    
                    $contract_array['contract_duration'] = $value[26];
                    $contract_array['probation_period'] = $value[27];
                    $contract_array['is_renewable'] = $value[28];
                    $contract_array['status'] = $value[29];

                  
                    $emp_array['essentials_salary'] = $value[30];
                  
                    $allowancename=$value[31];
                    $allowancetype = essentialsAllowanceType::where('name', $allowancename)->first();
                    if ($allowancetype) {
                        
                        $allowancetypeId = $allowancetype->id;
                        $emp_array['allowance_deduction_id']=$allowancetypeId;
                    }
                    else{ $emp_array['allowance_deduction_id']=null;}
                    $emp_array['amount']=$value[32];



                    $travelcategoryname=$value[33];
                    $traveltype = EssentialsTravelTicketCategorie::where('name', $travelcategoryname)->first();
                    if ($traveltype) {
                        
                        $traveltypeId = $traveltype->id;
                        $emp_array['travel_ticket_categorie']=$traveltypeId;
                    }
                    else{ $emp_array['travel_ticket_categorie']=null;}

                   // $emp_array['health_insurance']=$value[34];
                    $formated_data[] = $emp_array;
                    $formated_data2[] = $contract_array;
                }
                if (! $is_valid) {
                    throw new \Exception($error_msg);
                }

                if (! empty($formated_data)) {
                    foreach ($formated_data as $emp_data) {
                     
                       //dd($emp_data);
                        $emp_data['business_id'] = $business_id;
                        $emp_data['created_by'] = $user_id;
                        $emp = User::create($emp_data);

                    
                       
                      
                        $essentials_employee_appointmets = new EssentialsEmployeeAppointmet();
                        $essentials_employee_appointmets->employee_id = $emp->id;
                        $essentials_employee_appointmets->department_id= $emp_data['essentials_department_id'];
                        $essentials_employee_appointmets->business_location_id= $emp_data['location_id'];
                        $essentials_employee_appointmets->superior = "superior";
                        $essentials_employee_appointmets->job_title=$emp_data['job_title'];
                        $essentials_employee_appointmets->employee_status ="active";
                        $essentials_employee_appointmets->save();
                        

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
               
                if (! empty($formated_data2)) {
                    foreach ($formated_data2 as $con_data) {
                     
                       
                        $con_data['business_id'] = $business_id;
                        $con_data['employee_id'] = $emp->id;
                        $con_data['created_by'] = $user_id;
                        $contract = EssentialsEmployeesContract::create($con_data);

                      
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
