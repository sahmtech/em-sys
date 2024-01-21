<?php

namespace Modules\Essentials\Http\Controllers;

use App\Contact;

use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeesInsurance;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsInsuranceCompany;
use Modules\Essentials\Entities\EssentialsEmployeesFamily;
use Modules\Essentials\Entities\EssentialsCompaniesInsurancesContract;
use Excel;


class EssentialsEmployeeInsuranceController extends Controller
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

   
     public function import_employee_insurance_index()
     {
         $business_id = request()->session()->get('user.business_id');
 
         $can_crud_import_employee = auth()->user()->can('essentials.view_import_employees_insurance');
         if (! $can_crud_import_employee) {
            //temp  abort(403, 'Unauthorized action.');
         }
         $zip_loaded = extension_loaded('zip') ? true : false;
 
       
         if ($zip_loaded === false) {
             $output = ['success' => 0,
                 'msg' => 'Please install/enable PHP Zip archive for import',
             ];
           
           
             return view('essentials::employee_affairs.employee_insurance.import_employee_insurance_index')
                 ->with('notification', $output);
         } else {
             return view('essentials::employee_affairs.employee_insurance.import_employee_insurance_index');
         }
 
        
     }
 
     public function insurancepostImportEmployee(Request $request)
     {
         $can_crud_import_employee = auth()->user()->can('essentials.view_import_employees_insurance');
         if (! $can_crud_import_employee) {
            //temp  abort(403, 'Unauthorized action.');
         }
     
         try {
            
 
             //Set maximum php execution time
             ini_set('max_execution_time', 0);
 
 
             if ($request->hasFile('employee_insurance_csv'))
              {
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
             foreach ($imported_data as $key => $value)
              {
                 $row_no = $key + 1;
                 $emp_array = [];     
                 
                      
                  if (!empty($value[0])) 
                  {
                    $emp_array['eqama_emp_no'] = intval($value[0]);
                     
                      $proof_number = User::where('id_proof_number',$emp_array['eqama_emp_no'])->first();
                      $border_no = User::where('border_no',$emp_array['eqama_emp_no'])->first();
                      $family_proof_number = EssentialsEmployeesFamily::where('eqama_number', $emp_array['eqama_emp_no'])->first();
                    
                  

                      if( $proof_number !=null && $border_no==null &&  $family_proof_number ==null )
                      {
                        $emp = User::where('id_proof_number', $emp_array['eqama_emp_no'])->first();
                        if($emp)
                        {
                            $emp_insurance=EssentialsEmployeesInsurance::where('employee_id' ,$emp->id)->first();
                            if($emp_insurance)
                            {
                                $is_valid = false;
                                $error_msg = __('essentials::lang.proof_number_has_insurance').$row_no;
                                break;
                            }
                        }
                       

                      }

                    else if( $proof_number ==null && $border_no !=null &&  $family_proof_number ==null )
                      {
                        $emp_border = User::where('border_no', $emp_array['eqama_emp_no'])->first();
                        if(  $emp_border )
                        {
                            $emp_insurance=EssentialsEmployeesInsurance::where('employee_id' ,$emp_border->id)->first();
                            if($emp_insurance)
                            {
                                $is_valid = false;
                                $error_msg = __('essentials::lang.border_no_has_insurance').$row_no;
                                break;
                            }
                        }
                       

                      }

                     else if( $proof_number ==null && $border_no ==null &&  $family_proof_number !=null )
                      {
                       
                        $family = EssentialsEmployeesFamily::where('eqama_number',$emp_array['eqama_emp_no'])->first();


                        if(  $family){
                            $emp_insurance=EssentialsEmployeesInsurance::where('family_id' ,$family->id)->first();
                            if($emp_insurance)
                            {
                                $is_valid = false;
                                $error_msg = __('essentials::lang.family_has_insurance').$row_no;
                                break;
                            }
                        }
                       

                      }
                     
                      if ($proof_number == null && $border_no==null &&  $family_proof_number ==null ) {
                      
                          $is_valid = false;
                          $error_msg = __('essentials::lang.number_not_found').$row_no;
                          break;
                      }

                   

                    
                  }
                  else {
                     $is_valid = false;
                     $error_msg = __('essentials::lang.employee_id_required') .$row_no;
                     break;
                 }




                 if (!empty($value[1])) 
                 {
                     $emp_array['insurance_class_id'] = $value[1];
                     $business = EssentialsInsuranceClass::where('id',$emp_array['insurance_class_id'])->first();
                     if (!$business) {
                     
                         $is_valid = false;
                         $error_msg = __('essentials::lang.insurance_class_id_not_found').$row_no;
                         break;
                     }
                 }
                 else {
                    $is_valid = false;
                    $error_msg = __('essentials::lang.insurance_class_id_required') .$row_no;
                    break;
                }




                if (!empty($value[2])) 
                {
                    $emp_array['insurance_company_id'] = $value[2];
                    $business = Contact::where('id',$emp_array['insurance_company_id'])->first();
                    if (!$business) {
                    
                        $is_valid = false;
                        $error_msg = __('essentials::lang.insurance_company_id_not_found').$row_no;
                        break;
                    }
                }
                else{ $emp_array['insurance_company_id'] = null;}
               
                
                
            
             $formated_data[] = $emp_array;                      
                                       
                                                   
                                          
              }
                    
                    
              $processedEqamaEmpNos = [];
                 if (! empty($formated_data)) 
                 {
                  
 
 
                     foreach ($formated_data as $emp_data) {
                        $eqama_emp_no = $emp_data['eqama_emp_no'];

                        if (in_array($eqama_emp_no, $processedEqamaEmpNos)) {
                            $is_valid = false;
                            $error_msg = __('essentials::lang.duplicated_eqama_number').$row_no;
                            break;
                        }
                       
                        $emp = User::where('id_proof_number', $emp_data['eqama_emp_no'])->first();
                        $emp_border_no = User::where('border_no',$emp_data['eqama_emp_no'])->first();
                        $family = EssentialsEmployeesFamily::where('eqama_number',$emp_data['eqama_emp_no'])->first();
                      

                        if($emp != null && $emp_border_no ==null &&  $family==null)
                        {
                            $insurance =new EssentialsEmployeesInsurance();
                            $insurance->insurance_classes_id=$emp_data['insurance_class_id'];
                            $insurance->insurance_company_id=$emp_data['insurance_company_id'];
                            $insurance->employee_id=$emp->id;
                            $insurance->family_id =null;
                            $insurance->save();

                            $processedEqamaEmpNos[] = $eqama_emp_no;

                            
                        }
                        else if( $emp_border_no != null && $emp ==null &&  $family ==null)
                        {
                            $insurance =new EssentialsEmployeesInsurance();
                            $insurance->insurance_classes_id=$emp_data['insurance_class_id'];
                            $insurance->insurance_company_id=$emp_data['insurance_company_id'];
                            $insurance->employee_id=$emp_border_no->id;
                            $insurance->family_id =null;
                            $insurance->save();

                            $processedEqamaEmpNos[] = $eqama_emp_no;
                           
                        }
                        else if( $family != null&&  $emp ==null && $emp_border_no==null)
                        {
                            $insurance =new EssentialsEmployeesInsurance();
                            $insurance->insurance_classes_id=$emp_data['insurance_class_id'];
                            $insurance->insurance_company_id=$emp_data['insurance_company_id'];
                            $insurance->employee_id=null;
                            $insurance->family_id =$family->id;
                            $insurance->save();

                            $processedEqamaEmpNos[] = $eqama_emp_no;
                            
                        }
                    }
                           
                          
 
                     }
               
                 
                     if (!$is_valid) 
                     {
                         throw new \Exception($error_msg);
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
 
             return redirect()->route('import_employees_insurance')->with('notification', $output);
         }
        // $type = ! empty($contact->type) && $contact->type != 'both' ? $contact->type : 'supplier';
 
         return redirect()->route('employee_insurance')->with('notification', 'success insert');
     }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        
        $can_insurance = auth()->user()->can('essentials.crud_employees_insurances');
        if (!($is_admin || $can_insurance)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        

        $can_crud_employees_insurances = auth()->user()->can('essentials.crud_employees_insurances');
        $can_delete_employees_insurances = auth()->user()->can('essentials.delete_employees_insurances');
        $can_add_employees_insurances = auth()->user()->can('essentials.add_employees_insurances');
        $can_edit_employees_insurances = auth()->user()->can('essentials.edit_employees_insurances');
       

        
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

        }
        $insurance_companies = Contact::where('type', 'insurance')->pluck('supplier_business_name', 'id');
        $insurance_classes = EssentialsInsuranceClass::all()->pluck('name', 'id');

       
        $insurances=EssentialsEmployeesInsurance::with('user','essentialsEmployeesFamily')
       
        ->where(function($query) use($userIds) {
            $query->whereHas('user' ,function($query1) use( $userIds){
                $query1->whereIn('users.id', $userIds);
            })
            ->orWhereHas('essentialsEmployeesFamily' ,function($query2) use( $userIds){
                $query2->whereIn('essentials_employees_families.employee_id', $userIds);
            });
            
  
        })->select('essentials_employees_insurances.employee_id' ,
        'essentials_employees_insurances.family_id',
        'essentials_employees_insurances.id as id' ,
        'essentials_employees_insurances.insurance_company_id',
        'essentials_employees_insurances.insurance_classes_id')
        ->orderby('essentials_employees_insurances.id','desc');
      
      
        if (request()->ajax()) {

            return Datatables::of($insurances)
                ->addColumn('user', function ($row)  {
                    if($row->employee_id != null)
                    {
                        $item=$row->user->first_name  .' '. $row->user->last_name?? '';
                    }
                    else if($row->employee_id == null)
                    {
                        $item=$row->essentialsEmployeesFamily->full_name ?? '';
                    }

                    return $item;
                })

               
                ->addColumn('proof_number', function ($row) {
                   
                    if($row->employee_id != null)
                    {
                        $item=$row->user->id_proof_number ?? '';
                    }
                    else if($row->employee_id == null)
                    {
                        $item=$row->essentialsEmployeesFamily->eqama_number ?? '';
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
                    function ($row)  use($is_admin ,  $can_delete_employees_insurances,$can_edit_employees_insurances){
                        $html = '';
                        if($is_admin ||  $can_delete_employees_insurances)
                        {
                            $html .= '<button class="btn btn-xs btn-danger delete_insurance_button" data-href="' . route('employee_insurance.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }
                        
                        if ($is_admin || $can_edit_employees_insurances)
                         {
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
                                            WHEN f.eqama_number IS NOT NULL THEN f.eqama_number
                                            ELSE ''
                                        END LIKE ?", ["%$keyword%"]);
                    })

                ->removeColumn('id')
                ->removeColumn('status')
                ->rawColumns(['action'])
                ->make(true);
        }
        $query = User::whereIn('id',$userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),  ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        return view('essentials::employee_affairs.employee_insurance.index')
        ->with(compact('insurance_companies', 'insurance_classes', 'users'));
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
        $business_id = $request->session()->get('user.business_id');


        $insurance_companies = Contact::where('type', 'insurance')->pluck('id');

        try {

            $input = $request->only(['insurance_class', 'employee']);

            $emp=EssentialsEmployeesInsurance::where('employee_id', $input['employee'])->first();
            if(!$emp)
            {
                $insurance_data['insurance_classes_id'] = $input['insurance_class'];
                $insurance_data['employee_id'] = $input['employee'];
               
                $insurance_class_company=EssentialsInsuranceClass::where('id',$insurance_data['insurance_classes_id'])
                ->select('insurance_company_id')->first();
                $insurance_data['insurance_company_id']=  $insurance_class_company->insurance_company_id;
               
                EssentialsEmployeesInsurance::create($insurance_data);
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.added_success'),
                ];
            }
            else
            {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.employee_has_insurance'),
                ];
            }
           
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('employee_insurance')->with('status', $output);
    }


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function fetchClasses(Request $request)
    {

        $employee_id = $request->input('employee_id');
        $company_id = User::find($employee_id)->company_id;

        $insurance_company_id = EssentialsCompaniesInsurancesContract::where('company_id', $company_id)
        ->first()->insur_id;

        $classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company_id)
        ->pluck('name', 'id');

        if ($classes->isEmpty()) {
            return response()->json(['message' =>  __('essentials::lang.no_company_added')]);
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
         
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

        }

        $query = User::whereIn('id',$userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),  ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        $insurance = EssentialsEmployeesInsurance::findOrFail($id);
    
        $insurance_companies = Contact::where('type', 'insurance')->pluck('supplier_business_name', 'id');
       // $insurance_classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
       $insurance_classes=null;
       if($insurance->employee_id != null)
       {
        $emp_id = $insurance->employee_id;
        $company_id = User::find( $emp_id)->company_id;
        $insurance_company = Contact::where('type', 'insurance')->where('company_id', $company_id)
        ->first()->id;
        $insurance_classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company)->pluck('name', 'id');

       }
       else if($insurance->family_id != null)
       {   
        $employee_relative_id = EssentialsEmployeesFamily::find($insurance->family_id)->user->id;
        $company_id = User::find(  $employee_relative_id)->company_id;
        $insurance_company = Contact::where('type', 'insurance')->where('company_id', $company_id)
        ->first()->id;
        $insurance_classes = EssentialsInsuranceClass::where('insurance_company_id', $insurance_company)->pluck('name', 'id');

       }
      
        return view('essentials::employee_affairs.employee_insurance.edit_modal')
        ->with(compact('insurance_companies', 'insurance_classes', 'users' ,'insurance'));
     
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

          
                $insurance_data['insurance_classes_id'] = $input['insurance_class'];
                $insurance_data['employee_id'] = $input['employee'];
                
                $insurance_class_company=EssentialsInsuranceClass::where('id',$insurance_data['insurance_classes_id'])
                ->select('insurance_company_id')->first();

                $insurance_class_company=EssentialsInsuranceClass::where('id',$insurance_data['insurance_classes_id'])
                ->select('insurance_company_id')->first();
                $insurance_data['insurance_company_id']=  $insurance_class_company->insurance_company_id;
           
               
              
                EssentialsEmployeesInsurance::where('id', $id)->update($insurance_data);
               
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

        return redirect()->route('employee_insurance')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsEmployeesInsurance::where('id', $id)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
