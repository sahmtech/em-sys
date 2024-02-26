<?php

namespace Modules\Essentials\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsEmployeesFamily;
use Modules\Essentials\Entities\EssentialsCountry;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Excel;

class EssentialsEmployeeFamilyController extends Controller
{
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }




    public function index()
    {

        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $crud_employee_families = auth()->user()->can('essentials.crud_employee_families');
        $can_add_employee_families = auth()->user()->can('essentials.add_employee_families');
        $can_edit_employee_families = auth()->user()->can('essentials.edit_employee_families');
        $can_delete_employee_families = auth()->user()->can('essentials.delete_employee_families');

        if (!$crud_employee_families) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }


        $EssentialsEmployeesFamilies = EssentialsEmployeesFamily::join('users as u', 'u.id', '=', 'essentials_employees_families.employee_id')
            ->whereIn('u.id', $userIds)->where('u.status', '!=', 'inactive')
            ->select([
                'essentials_employees_families.id',
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                'essentials_employees_families.full_name as family',
                'essentials_employees_families.gender',
                'essentials_employees_families.address',
                'essentials_employees_families.relative_relation',
                'essentials_employees_families.eqama_number',
                'essentials_employees_families.nationality_id',

            ])->orderby('id', 'desc');

        if (request()->ajax()) {


            return Datatables::of($EssentialsEmployeesFamilies)

                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_edit_employee_families, $can_delete_employee_families) {
                        $html = '';
                        if ($is_admin || $can_edit_employee_families) {
                            $html .= '<a  href="' . route('employee_families.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                            '&nbsp;';
                        }
                        if ($is_admin || $can_delete_employee_families) {
                            $html .= '<button class="btn btn-xs btn-danger delete_employee_families_button" data-href="' . route('employee_families.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }


                        return $html;
                    }
                )

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('family', function ($query, $keyword) {
                    $query->whereRaw("essentials_employees_families.full_name like ?", ["%{$keyword}%"]);
                })

                ->filterColumn('gender', function ($query, $keyword) {
                    $query->whereRaw("essentials_employees_families.gender like ?", ["%{$keyword}%"]);
                })

                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        $query = User::whereIn('id', $userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                ' - ',COALESCE(id_proof_number,'')) as 
         full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return view('essentials::employee_affairs.employee_families.index')->with(compact('users'));
    }


    public function import_index()
    {
        $business_id = request()->session()->get('user.business_id');

        $can_crud_import_employee = auth()->user()->can('essentials.view_import_employees_familiy');
        if (!$can_crud_import_employee) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $zip_loaded = extension_loaded('zip') ? true : false;

        //Check if zip extension it loaded or not.
        if ($zip_loaded === false) {
            $output = [
                'success' => 0,
                'msg' => 'Please install/enable PHP Zip archive for import',
            ];


            return view('essentials::employee_affairs.employee_families.import')
                ->with('notification', $output);
        } else {
            return view('essentials::employee_affairs.employee_families.import');
        }
    }

    public function familypostImportEmployee(Request $request)
    {
        $can_crud_import_employee = auth()->user()->can('essentials.view_import_employees_familiy');
        if (!$can_crud_import_employee) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {


            //Set maximum php execution time
            ini_set('max_execution_time', 0);


            if ($request->hasFile('employee_familiy_csv')) {
                $file = $request->file('employee_familiy_csv');
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


                    if (!empty($value[0])) {
                        $emp_array['emp_eqama_no'] =  intval($value[0]);


                        $proof_number = User::where('id_proof_number', $emp_array['emp_eqama_no'])->first();
                        //  $family_proof_number=EssentialsEmployeesFamily::where('eqama_number',$emp_array['emp_eqama_no'])->first();

                        if (!$proof_number) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.emp_eqama_no_not_found') . $row_no;
                            break;
                        }
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.emp_eqama_required') . $row_no;
                        break;
                    }


                    if (!empty($value[1])) {
                        $emp_array['full_name'] = $value[1];
                    }


                    if (!empty($value[2])) {
                        $emp_array['family_eqama_no'] = intval($value[2]);

                        $business = EssentialsEmployeesFamily::where('eqama_number', $emp_array['family_eqama_no'])->first();

                        if ($business) {

                            $is_valid = false;
                            $error_msg = __('essentials::lang.family_eqama_no_exist') . $row_no;
                            break;
                        }
                    } else {
                        $is_valid = false;
                        $error_msg = __('essentials::lang.family_eqama_required') . $row_no;
                        break;
                    }

                    $emp_array['relation'] = $value[3];
                    $emp_array['gender'] = $value[4];
                    //    $emp_array['mobile'] = $value[5];                         
                    //    $emp_array['nationality_id'] = $value[6]; 
                    //    if ($emp_array['nationality_id'] !== null) {

                    //     $business = EssentialsCountry::find($emp_array['nationality_id']);
                    //     if (!$business) {

                    //         $is_valid = false;
                    //         $error_msg = __('essentials::lang.nationality_id_not_found').$row_no;
                    //         break;
                    //     }
                    // }
                    // else
                    // {
                    //     $emp_array['nationality_id']=null;
                    // } 


                    $formated_data[] = $emp_array;
                }


                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                if (!empty($formated_data)) {



                    foreach ($formated_data as $emp_data) {


                        $user = User::where('id_proof_number', $emp_data['emp_eqama_no'])->first();


                        $family = new EssentialsEmployeesFamily();
                        $family->full_name = $emp_data['full_name'];
                        //  $family->mobile_number=$emp_data['mobile'];
                        $family->relative_relation = $emp_data['relation'];
                        $family->eqama_number = $emp_data['family_eqama_no'];
                        //  $family->nationality_id=$emp_data['nationality_id'];
                        $family->gender = $emp_data['gender'];
                        $family->employee_id = $user->id;
                        $family->save();
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
                'msg' => __('messages.somthing_went_wrong'),
            ];
            return redirect()->route('import-employees-familiy')->with('notification', $output);
        }
        // $type = ! empty($contact->type) && $contact->type != 'both' ? $contact->type : 'supplier';

        return redirect()->route('employee_families')->with('notification', 'success insert');
    }


    public function create()
    {
        return view('essentials::create');
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['full_name', 'address', 'gender', 'relative_relation', 'eqama_number', 'employee']);

            $input2['employee_id'] = $input['employee'];

            // $input2['first_name'] = $input['first_name'];

            // $input2['last_name'] = $input['last_name'];

            $input2['full_name'] = $input['full_name'];

            $input2['address'] = $input['address'];

            $input2['relative_relation'] = $input['relative_relation'];

            //$input2['age'] = $input['age'];

            $input2['gender'] = $input['gender'];

            $input2['eqama_number'] = $input['eqama_number'];

            EssentialsEmployeesFamily::create($input2);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as 
 full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return redirect()->route('employee_families')->with(compact('users'));
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsEmployeesFamily::where('id', $id)
                ->delete();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $family = EssentialsEmployeesFamily::findOrFail($id);

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw(" CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return view('essentials::employee_affairs.employee_families.edit')->with(compact('users', 'family'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['full_name', 'address', 'gender', 'relative_relation', 'eqama_number', 'employee']);

            $input2['employee_id'] = $input['employee'];

            // $input2['first_name'] = $input['first_name'];

            // $input2['last_name'] = $input['last_name'];

            $input2['full_name'] = $input['full_name'];

            $input2['address'] = $input['address'];

            $input2['relative_relation'] = $input['relative_relation'];

            //  $input2['age'] = $input['age'];

            $input2['gender'] = $input['gender'];

            $input2['eqama_number'] = $input['eqama_number'];

            EssentialsEmployeesFamily::where('id', $id)->update($input2);
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');

        $all_users = $query->select('id', DB::raw(" CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        return redirect()->route('employee_families')->with(compact('users'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
}
