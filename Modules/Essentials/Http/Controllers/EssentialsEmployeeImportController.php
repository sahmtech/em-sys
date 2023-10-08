<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use Excel;
use App\User;
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
                        $emp_array['surename'] = $value[0];
                       
                    } else {
                        $is_valid = false;
                        $error_msg = "employee Name is required in row no. $row_no";
                        break;
                    }

                    $emp_array['first_name'] = $value[1];
                    $emp_array['last_name'] = $value[2];

                    if (! empty( $emp_array['dob'])) {
                        $emp_array['dob'] = $this->moduleUtil->uf_date($value[3]);
                    }
                    
                    $emp_array['gender'] = $value[4];
                    $emp_array['marital_status'] = $value[5];
                   // $emp_array['name'] = implode(' ', [$emp_array['prefix'], $emp_array['first_name'], $emp_array['middle_name'], $emp_array['last_name']]);
                   $emp_array['blood_group'] = $value[6];
                  
                    
                    //Mobile number
                    if (! empty(trim($value[7]))) {
                        $emp_array['contact_number'] = $value[7];
                    } else {
                        $is_valid = false;
                        $error_msg = "Mobile number is required in row no. $row_no";
                        break;
                    }

                    //Alt contact number
                    $emp_array['family_number'] = $value[8];

                    //Landline
                    $emp_array['fb_link'] = $value[9];

                    //City
                    $emp_array['twitter_link'] = $value[10];

                    //State
                    $emp_array['social_media_1'] = $value[11];

                    //Country
                    $emp_array['social_media_2'] = $value[12];


                    //Cust fields
                    $emp_array['custom_field1'] = $value[13];
                    $emp_array['custom_field2'] = $value[14];
                    $emp_array['custom_field3'] = $value[15];
                    $emp_array['custom_field4'] = $value[16];

                    $emp_array['guardian_name'] = $value[17];
                    $emp_array['id_proof_name'] = $value[18];
                    $emp_array['permanent_address'] = $value[19];
                    $emp_array['current_address'] = $value[20];
                    $emp_array['bank_details']=implode(' ', [$value[21], $value[23], $value[24],$value[25],$value[26]]);

                   // $emp_array['']= $value[27];
                    $emp_array['essentials_designation_id']= $value[28];
                    $emp_array['qualification_type']= $value[29];

                    $contract_array['contract_number'] = $value[30];
                    if (!  $contract_array['contract_start_date']) {
                        $contract_array['contract_start_date'] = $this->moduleUtil->uf_date( $value[31]);
                    }
                    if (!  $contract_array['contract_end_date']) {
                        $contract_array['contract_end_date'] = $this->moduleUtil->uf_date( $value[32]);
                    }
                    
                    $contract_array['contract_duration'] = $value[33];
                  
                    $contract_array['probation_period'] = $value[34];
                    $contract_array['is_renewable'] = $value[35];
                    $contract_array['travel_ticket_categorie'] = $value[42];
                    $contract_array['allowance_type'] = $value[40];
                    $contract_array['entitlement_type'] = $value[41];
                    $contract_array['essentials_salary'] = $value[37];
                    $contract_array['basic_salary_type'] = $value[39];

                    $emp_array['essentials_department_id']= $value[36];
                    $emp_array['essentials_salary']= $value[37];

                    $emp_array['user_type'] = 'employee';

                  
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
