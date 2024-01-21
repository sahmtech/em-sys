<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use App\Company;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;

class EssentialsEmployeeQualificationController extends Controller
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


        $can_crud_employee_qualifications = auth()->user()->can('essentials.crud_employee_qualifications');
        $can_add_employee_qualifications = auth()->user()->can('essentials.add_employee_qualifications');
        $can_edit_employee_qualifications = auth()->user()->can('essentials.edit_employee_qualifications');
        $can_delete_employee_qualifications = auth()->user()->can('essentials.delete_employee_qualifications');

        if (!$can_crud_employee_qualifications) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $sub_spacializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $spacializations = EssentialsProfession::where('type','academic')->pluck('name', 'id');
   
        $countries = EssentialsCountry::forDropdown();

        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $employees_qualifications = EssentialsEmployeesQualification::join('users as u', 'u.id', '=', 'essentials_employees_qualifications.employee_id')
        ->whereIn('u.id', $userIds)
            ->select([
                'essentials_employees_qualifications.id',
                DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                'essentials_employees_qualifications.qualification_type',
                'essentials_employees_qualifications.sub_specialization',
                'essentials_employees_qualifications.specialization',
                'essentials_employees_qualifications.graduation_year',
                'essentials_employees_qualifications.graduation_institution',
                'essentials_employees_qualifications.graduation_country',
                'essentials_employees_qualifications.degree',

            ]);

        if (request()->ajax()) {

            if (!empty(request()->input('qualification_type')) && request()->input('qualification_type') !== 'all') {
                $employees_qualifications->where('essentials_employees_qualifications.qualification_type', request()->input('qualification_type'));
            }

            if (!empty(request()->input('major')) && request()->input('major') !== 'all') {
                $employees_qualifications->where('essentials_employees_qualifications.major', request()->input('major'));
            }


            return Datatables::of($employees_qualifications)
                ->editColumn('graduation_country', function ($row) use ($countries) {
                    $item = $countries[$row->graduation_country] ?? '';

                    return $item;
                })
                ->editColumn('specialization', function ($row) use ($spacializations) {
                    $item = $spacializations[$row->specialization] ?? '';

                    return $item;
                })
                ->editColumn('sub_specialization', function ($row) use ($sub_spacializations) {
                    $item = $sub_spacializations[$row->sub_specialization] ?? '';

                    return $item;
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_employee_qualifications, $can_delete_employee_qualifications) {
                        $html = '';
                        if ($is_admin || $can_edit_employee_qualifications) {
                            $html .= '<button class="btn btn-xs btn-primary open-edit-modal" data-id="' . $row->id . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</button>';
                        }
                        if ($is_admin || $can_delete_employee_qualifications) {
                            $html .= '&nbsp;<button class="btn btn-xs btn-danger delete_qualification_button" data-href="' . route('qualification.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $html;
                    }
                )

                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
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
        $countries = EssentialsCountry::forDropdown();

        return view('essentials::employee_affairs.employees_qualifications.index')
            ->with(compact('users', 'countries', 'spacializations'));
    }


    public function store(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['employee','general_specialization','sub_specialization', 'qualification_type', 'graduation_year', 'graduation_institution', 'graduation_country', 'degree','marksName','great_degree']);


            $input2['qualification_type'] = $input['qualification_type'];
            $input2['specialization']  =  $input['general_specialization'];;
            $input2['sub_specialization']   = request()->input('sub_specialization');
            $input2['graduation_year'] = $input['graduation_year'];
            $input2['graduation_institution'] = $input['graduation_institution'];
            $input2['employee_id'] = $input['employee'];
            $input2['graduation_country'] = $input['graduation_country'];
            $input2['degree'] = $input['degree'];
            $input2['marksName'] = $input['marksName'];
            $input2['great_degree'] = $input['great_degree'];


            EssentialsEmployeesQualification::create($input2);


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
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');
        $countries = EssentialsCountry::forDropdown();

        return redirect()->route('qualifications')->with(compact('users', 'countries'));
    }

    public function show($id)
    {
        return view('essentials::show');
    }

    public function edit(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        try {

            $qualification = EssentialsEmployeesQualification::findOrFail($id);

            $output = [
                'success' => true,
                'data' => [
                    'employee' => $qualification->employee_id,
                    'qualification_type' => $qualification->qualification_type,
                    'general_specialization' => $qualification->specialization,
                    'sub_specialization' => $qualification->sub_specialization,
                    'graduation_year' => $qualification->graduation_year,
                    'graduation_institution' => $qualification->graduation_institution,
                    'graduation_country' => $qualification->graduation_country,
                    'degree' => $qualification->degree,
                    'marksName' => $qualification->marksName,
                    'great_degree' => $qualification->great_degree,

                ],
              
                'msg' => __('lang_v1.fetched_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return response()->json($output);
    }


    public function updateQualification(Request $request, $qualificationId)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {

            $qualification = EssentialsEmployeesQualification::find($qualificationId);
            //    dd( $qualification );
            if ($qualification) {
                $qualification->update([
                    'employee_id' => $request->input('employee'),
                    'qualification_type' => $request->input('qualification_type'),
                    'major' => $request->input('major'),
                    'graduation_year' => $request->input('graduation_year'),
                    'graduation_institution' => $request->input('graduation_institution'),
                    'graduation_country' => $request->input('graduation_country'),
                    'degree' => $request->input('degree'),
                    'marksName' => $request->input('marksName'),
                    'great_degree' => $request->input('great_degree'),

                ]);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } else {
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.no_data'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return response()->json($output);
    }






    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsEmployeesQualification::where('id', $id)
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
}