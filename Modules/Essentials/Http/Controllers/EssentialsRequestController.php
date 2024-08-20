<?php

namespace Modules\Essentials\Http\Controllers;

use Modules\Sales\Entities\SalesProject;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use App\User;
use App\AccessRole;
use App\AccessRoleRequest;
use Carbon\Carbon;
use App\Request as Req;
use Modules\CEOManagment\Entities\RequestsType;
use App\ContactLocation;
use App\RequestProcess;
use App\Utils\RequestUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Contracts\Support\Renderable;
use Modules\CEOManagment\Entities\WkProcedure;

use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;

use Modules\Essentials\Entities\EssentialsInsuranceClass;

class EssentialsRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $requestUtil;


    public function __construct(RequestUtil $requestUtil)
    {
        $this->requestUtil = $requestUtil;
    }


    //// HRM ////////
    public function requests()
    {
        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('essentials.change_HR_status');
        $can_return_request = auth()->user()->can('essentials.return_essentials_request');
        $can_show_request = auth()->user()->can('essentials.show_essentials_request');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%بشرية%')
            ->pluck('id')->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_HR_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }
        $roles = DB::table('roles')->where('name', 'LIKE', '%بشرية%')->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();
        $ownerTypes = ['employee', 'manager', 'worker'];

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'essentials::requests.allRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes);
    }

    public function store(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%بشرية%')
            ->pluck('id')->toArray();

        return $this->requestUtil->storeRequest($request, $departmentIds);
    }


    //////// Employee Affairs //////////
    public function employee_affairs_all_requests()
    {

        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('essentials.change_employees_request_status');
        $can_return_request = auth()->user()->can('essentials.return_employees_request');
        $can_show_request = auth()->user()->can('essentials.show_employees_request');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%موظف%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_employee_affairs_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $ownerTypes = ['employee', 'manager'];
        $roles = DB::table('roles')
            ->where('name', 'LIKE', '%موظف%')->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'essentials::employee_affairs.requests.allRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes);
    }
    public function doneEmployeeAffairsRequests()
    {

        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('essentials.change_employees_request_status');
        $can_return_request = auth()->user()->can('essentials.return_employees_request');
        $can_show_request = auth()->user()->can('essentials.show_employees_request');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%موظف%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_employee_affairs_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $ownerTypes = ['employee', 'manager'];
        $roles = DB::table('roles')
            ->where('name', 'LIKE', '%موظف%')->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();
        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'essentials::employee_affairs.requests.doneRequests', $can_change_status, $can_return_request, $can_show_request, $requestsTypes, [], false, null, 'done');
    }
    public function pendingEmployeeAffairsRequests()
    {

        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('essentials.change_employees_request_status');
        $can_return_request = auth()->user()->can('essentials.return_employees_request');
        $can_show_request = auth()->user()->can('essentials.show_employees_request');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%موظف%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_employee_affairs_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $ownerTypes = ['employee', 'manager'];
        $roles = DB::table('roles')
            ->where('name', 'LIKE', '%موظف%')->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();


        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'essentials::employee_affairs.requests.pendingRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes, [], false, null, 'pending');
    }

    public function storeEmployeeAffairsRequest(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%موظف%')
            ->pluck('id')->toArray();

        return $this->requestUtil->storeRequest($request, $departmentIds);
    }



    //////// Employee Requests //////////

    public function my_requests()
    {

        $business_id = request()->session()->get('user.business_id');

        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
        $saleProjects = SalesProject::all()->pluck('name', 'id');



        if (request()->ajax()) {

            $requestsProcess = Req::select([
                'requests.request_no',
                'request_processes.id as process_id',
                'requests.id',
                'requests.type as type',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'requests.created_at',
                'request_processes.status',
                'request_processes.status_note as note',
                'requests.reason',
                'wk_procedures.department_id as department_id',
                'users.id_proof_number',
                'wk_procedures.can_return',
                'users.assigned_to'

            ])
                ->leftjoin('request_processes', 'request_processes.worker_request_id', '=', 'requests.id')
                ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'requests.worker_id')->where('users.id', auth()->user()->id);


            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
                })
                ->editColumn('assigned_to', function ($row) use ($ContactsLocation) {
                    $item = $ContactsLocation[$row->assigned_to] ?? '';

                    return $item;
                })
                ->editColumn('status', function ($row) {

                    $status = trans('essentials::lang.' . $row->status);

                    return $status;
                })

                ->rawColumns(['status'])


                ->make(true);
        }
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');


        return view('essentials::requests.allRequest')->with(compact('main_reasons', 'saleProjects', 'classes', 'leaveTypes'));
    }

    public function employee_requests()
    {

        $user = User::where('id', auth()->user()->id)->first();
        $user_type = $user->user_type;
        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $business_id = request()->session()->get('user.business_id');
        $saleProjects = SalesProject::all()->pluck('name', 'id');
        $requestsProcess = null;
        $all_status = ['approved', 'pending', 'rejected'];

        if ($user_type == 'admin' || $user_type == 'department_head' || $user_type == 'manager') {
            $allRequestTypes = RequestsType::where('for', 'employee')->where('selfish_service', 1)->pluck('type', 'id');
        } else {
            $allRequestTypes = RequestsType::where('for', $user_type)->where('selfish_service', 1)->pluck('type', 'id');
        }
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')
            ->groupBy('request_id');


        $requestsProcess = Req::select([
            'requests.request_no',
            'process.id as process_id',
            'requests.id',
            'requests.request_type_id',
            'requests.created_at',
            'process.status',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            'requests.related_to',
            'process.note as note',
            'requests.reason',
            'wk_procedures.department_id as department_id',
            'users.id_proof_number',
            'wk_procedures.can_return',
            'process.procedure_id as procedure_id',
        ])
            ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                $join->on('requests.id', '=', 'latest_process.request_id');
            })
            ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
            //   ->leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')->where('process.sub_status', null);

        $userDepartment = EssentialsDepartment::pluck('id')->toArray();

        if (!$is_admin) {
            $requestsProcess = $requestsProcess->where('requests.related_to', $user->id);
            $userDepartment = [$user->essentials_department_id];
        }


        if (request()->ajax()) {
            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                    return $allRequestTypes[$row->request_type_id];
                })
                ->editColumn('status', function ($row) {
                    $status = '';

                    $status = trans('essentials::lang.' . $row->status);


                    return $status;
                })
                ->editColumn('can_return', function ($row) {
                    $buttonsHtml = '';


                    $buttonsHtml .= '<button class="btn btn-primary btn-sm btn-view-request" data-request-id="' . $row->id . '">' . trans('essentials::lang.view_request') . '</button>';


                    return $buttonsHtml;
                })
                ->rawColumns(['status', 'can_return'])


                ->make(true);
        }

        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
        return view('essentials::requests.employee_requests')->with(compact(
            'allRequestTypes',
            'job_titles',
            'specializations',
            'nationalities',
            'all_status',
            'saleProjects',
            'main_reasons',
            'classes',
            'leaveTypes'
        ));
    }
}