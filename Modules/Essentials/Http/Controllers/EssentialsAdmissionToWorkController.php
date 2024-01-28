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
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;

class EssentialsAdmissionToWorkController extends Controller
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

        $can_crud_employee_work_admissions = auth()->user()->can('essentials.crud_employee_work_admissions');
        $can_add_employee_work_admissions = auth()->user()->can('essentials.add_employee_work_admissions');
        $can_edit_employee_work_admissions = auth()->user()->can('essentials.edit_employee_work_admissions');
        $can_delete_employee_work_admissions = auth()->user()->can('essentials.delete_employee_work_admissions');
        $can_activate_employee_admission = auth()->user()->can('essentials.activate_employee_admission');

        if (!$can_crud_employee_work_admissions) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $departments =  EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id');
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $admissionToWork = EssentialsAdmissionToWork::whereIn('employee_id', $userIds)
        ->with('user')
            ->select(
                'id',
                'employee_id',
                'admissions_type as admissions_type',
                'admissions_status as admissions_status',
                'admissions_date as admissions_date',
                'is_active as is_active',

            );

        if (request()->ajax()) {



            if (!empty(request()->input('admissions_status')) && request()->input('admissions_status') !== 'all') {
                $admissionToWork->where('essentials_admission_to_works.admissions_status', request()->input('admissions_status'));
            }

            if (!empty(request()->input('admissions_type')) && request()->input('admissions_type') !== 'all') {
                $admissionToWork->where('essentials_admission_to_works.admissions_type', request()->input('admissions_type'));
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $admissionToWork->whereDate('essentials_admission_to_works.admissions_date', '>=', $start)
                    ->whereDate('essentials_admission_to_works.admissions_date', '<=', $end);
            }

            return Datatables::of($admissionToWork)

                ->addColumn('user', function ($row) {
                    return $row->user->first_name . ' ' . $row->user->mid_name . ' ' . $row->user->last_name ?? '';
                })

                ->addColumn('id_proof_number', function ($row) {
                    return $row->user->id_proof_number ?? '';
                })
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_edit_employee_work_admissions, $can_delete_employee_work_admissions ,$can_activate_employee_admission) {
                        $html = '';
                        if ($is_admin || $can_edit_employee_work_admissions) {
                            $html .= '<a href="' . route('admissionToWork.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>&nbsp;';
                        }
                        if ($is_admin || $can_delete_employee_work_admissions) {
                            $html .= '<button class="btn btn-xs btn-danger delete_admissionToWork_button" data-href="' . route('admissionToWork.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }
                        if ($is_admin || $can_activate_employee_admission) {
                            $html .= '&nbsp; <a href="#" class="btn btn-xs btn-warning change_admission_activity"  data-admission-id="' . $row->id . '" data-orig-value="' . $row->is_active . '"><i class="glyphicon glyphicon-stop"></i> ' . __('essentials::lang.end_admission_activate') . '</a>';
                        }


                        return $html;
                    }
                )
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereHas('user', function ($query) use ($keyword) {
                        $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%$keyword%"]);
                    });
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->where('users.id_proof_number', 'like', "%$keyword%");
                })
                ->filterColumn('admissions_type', function ($query, $keyword) {
                    $query->where('essentials_admission_to_works.admissions_type', 'like', "%$keyword%");
                })
                ->filterColumn('admissions_status', function ($query, $keyword) {
                    $query->where('essentials_admission_to_works.admissions_status', 'like', "%$keyword%");
                })
                ->filterColumn('admissions_date', function ($query, $keyword) {
                    $query->whereDate('essentials_admission_to_works.admissions_date', '=', $keyword);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $query = User::whereIn('id', $userIds);
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
                ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        return view('essentials::employee_affairs.admission_to_work.index')->with(compact('users', 'departments'));
    }

    public function change_admission_activity(Request $request, $admissionId)
    {
        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
       
        try {
            $input = $request->only(['origValue']);
           
            $admission = EssentialsAdmissionToWork::where('id',$admissionId)->first();
            
            if ($admission) {
                $admission->is_active = $input['origValue'];
                $admission->admissions_date = now();
                $admission->save();
                
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } else {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.not_found'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }
    
        return $output;
    }

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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['employee', 'admissions_type', 'admissions_status', 'admissions_date']);

            $input2['employee_id'] = $input['employee'];
            $input2['admissions_type'] = $input['admissions_type'];
            $input2['admissions_status'] = $input['admissions_status'];
            $input2['admissions_date'] = $input['admissions_date'];

            $previous_admission = EssentialsAdmissionToWork::where('employee_id',$input2['employee_id'])
            ->latest('created_at')
            ->first();
           

            if( $previous_admission )
            {
                $previous_admission->is_active= 0;
                $previous_admission->admissions_date= $input2['admissions_date'];
                $previous_admission->save();
              
            }

            EssentialsAdmissionToWork::create($input2);


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

        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as
 full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        $departments = EssentialsDepartment::forDropdown();

        return redirect()->route('admissionToWork')->with(compact('users', 'departments'));
    }


    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            EssentialsAdmissionToWork::where('id', $id)
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
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        $work = EssentialsAdmissionToWork::findOrFail($id);
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $query = User::where('business_id', $business_id)->where('users.user_type', '!=', 'admin');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        return view('essentials::employee_affairs.admission_to_work.edit')->with(compact('users', 'departments', 'work'));
    }


    public function update(Request $request, $id)
    {

        $business_id = $request->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;



        try {
            $input = $request->only(['employee', 'admissions_type', 'admissions_status', 'admissions_date']);

            $input2['employee_id'] = $input['employee'];

            $input2['admissions_type'] = $input['admissions_type'];
            $input2['admissions_status'] = $input['admissions_status'];
            $input2['admissions_date'] = $input['admissions_date'];



            EssentialsAdmissionToWork::where('id', $id)->update($input2);
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

        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');

        $departments = EssentialsDepartment::forDropdown();

        return redirect()->route('admissionToWork')->with(compact('users', 'departments'));
    }
}
