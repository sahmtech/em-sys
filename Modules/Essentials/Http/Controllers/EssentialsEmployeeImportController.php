<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use Excel;
use App\User;
use App\Category;
use Modules\Essentials\Entities\EssentialsEmployeeContract;
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
                    if (count($value) != 44) {
                        $is_valid = false;
                        $error_msg = 'Number of columns mismatch';
                        break;
                    }

                    $row_no = $key + 1;
                    $emp_array = [];
                    $contract_array=[];
                    $emp="";
                    //Check contact type
                    
                    if (! empty($value[0])) {
                        $emp_array['first_name'] = $value[0];
                    } else {
                        $is_valid = false;
                        $error_msg = "First name is required in row no. $row_no";
                        break;
                    }
                    $emp_array['mid_name'] = $value[1];
                    $emp_array['last_name'] = $value[2];
                    $emp_array['name'] = implode(' ', [ $emp_array['first_name'], $emp_array['middle_name'], $emp_array['last_name']]);
                    $emp_array['email'] = $value[4];
                    $emp_array['user_type'] = $value[5];
                    if (!empty($value[6])) {
                        // Parse the date using strtotime and then format it as needed
                        $dob = date('Y-m-d', strtotime($value[6]));
                        
                        // Add the formatted date to the array
                        $emp_array['dob'] = $dob;
                    }
                  
                    
                    $emp_array['gender'] = $value[7];
                    $emp_array['marital_status'] = $value[8];
                   // $emp_array['name'] = implode(' ', [$emp_array['prefix'], $emp_array['first_name'], $emp_array['middle_name'], $emp_array['last_name']]);
                   $emp_array['blood_group'] = $value[9];
                  
                    
                    //Mobile number
                    if (! empty(trim($value[10]))) {
                        $emp_array['contact_number'] = $value[10];
                    } else {
                        $is_valid = false;
                        $error_msg = "Mobile number is required in row no. $row_no";
                        break;
                    }

                    //Alt contact number
                    $emp_array['alt_number'] = $value[11];
                    $emp_array['family_number'] = $value[12];
                    $emp_array['current_address'] = $value[13];
                    $emp_array['permanent_address'] = $value[14];
                  


                    $emp_array['id_proof_name'] = $value[15];
                    $emp_array['id_proof_number'] = $value[16];
                    $emp_array['bank_details']=implode(' ', [$value[17], $value[18], $value[19],$value[20]]);

                    $businessname = $row[21];
                    $business = Business::where('name', $businessname)->first();
                    if ($business) {
                        
                        $businessId = $business->id;
                        $emp_array['location_id ']=$businessId;
                    }


                    $departmentname = $row[22];
                    $department = Business::where('name', $departmentname)->first();
                    if ($department) {
                        
                        $departmentId = $department->id;
                        $emp_array['essentials_department_id  ']=$departmentId;
                    }


                    $categoryname = $row[23];
                    $category = Category::where('name', $categoryname)->first();
                    if ($category) {
                        
                        $categoryId = $category->id;
                        $emp_array['essentials_designation_id ']=$categoryId;
                    }
                   

                    
                    $contract_array['contract_number'] = $value[24];
                    if (!empty($value[25])) {
                        // Parse the date using strtotime and then format it as needed
                        $contract_start_date = date('Y-m-d', strtotime($value[25]));
                        
                        // Add the formatted date to the array
                        $contract_array['contract_start_date'] = $contract_start_date;
                    }

                     
                    if (!empty($value[26])) {
                        // Parse the date using strtotime and then format it as needed
                        $contract_end_date = date('Y-m-d', strtotime($value[26]));
                        
                        // Add the formatted date to the array
                        $contract_array['contract_end_date'] = $contract_end_date;
                    }
                    
                    $contract_array['contract_duration'] = $value[27];
                  
                    $contract_array['probation_period'] = $value[28];
                    $contract_array['is_renewable'] = $value[29];


                  
                    $emp_array['essentials_salary'] = $value[30];
                  
                    $formated_data[] = $emp_array;
                    $formated_data2[] = $contract_array;
                }
                if (! $is_valid) {
                    throw new \Exception($error_msg);
                }

                if (! empty($formated_data)) {
                    foreach ($formated_data as $emp_data) {
                     
                       
                        $emp_data['business_id'] = $business_id;
                        $emp_data['created_by'] = $user_id;

                        $emp = User::create($emp_data);
                         
                      // $this->transactionUtil->activityLog($emp, 'imported');
                    }
                }
               
                if (! empty($formated_data2)) {
                    foreach ($formated_data2 as $con_data) {
                     
                       
                        $con_data['business_id'] = $business_id;
                        $con_data['employee_id'] = $emp->id;
                        $con_data['created_by'] = $user_id;

                        $contract = EssentialsEmployeeContract::create($con_data);

                      // $this->transactionUtil->activityLog($emp, 'imported');
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
