<?php

namespace Modules\FollowUp\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\Utils\RequestUtil;
use App\AccessRoleProject;
use App\Business;
use App\RequestProcess;
use Modules\Sales\Entities\SalesProject;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use App\Request as UserRequest;
use Illuminate\Support\Facades\DB;
use App\User;
use Modules\Essentials\Entities\EssentialsDepartment;

use Modules\FollowUp\Entities\FollowupWorkerRequest;

use Carbon\Carbon;
use Modules\CEOManagment\Entities\RequestsType;

class FollowUpRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;

    protected $requestUtil;

    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;
    }
    public function requests()
    {
        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('followup.change_request_status');
        $can_return_request = auth()->user()->can('followup.return_request');
        $can_show_request = auth()->user()->can('followup.show_request');


        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->pluck('id')->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('followup::lang.there_is_no_followup_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }
        $ownerTypes = ['worker'];

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'followup::requests.allRequest', $can_change_status, $can_return_request, $can_show_request, [], true);
    }

    public function store(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->pluck('id')->toArray();
        return $this->requestUtil->storeRequest($request, $departmentIds);
    }

    public function filteredRequests()
    {
        $business_id = request()->session()->get('user.business_id');
        $filter = request()->query('filter');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->pluck('id')->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('followup::lang.there_is_no_followup_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $requestsProcess = null;
        $allRequestTypes = RequestsType::pluck('type', 'id');

        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')
            ->groupBy('request_id');

        $requestsProcess = UserRequest::select([
            'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.reason',

            'process.id as process_id', 'process.status', 'process.note as note',  'process.procedure_id as procedure_id', 'process.superior_department_id as superior_department_id',

            'wk_procedures.department_id as department_id', 'wk_procedures.can_return',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number',

        ])
            ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                $join->on('requests.id', '=', 'latest_process.request_id');
            })
            ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')
            ->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('process.superior_department_id', $departmentIds);
            })


            ->whereIn('requests.related_to', $userIds)->whereNull('process.sub_status')
            ->where('users.status', '!=', 'inactive');



        $pageName = __('followup::lang.allRequests');
        if ($filter == 'finished') {
            $pageName = __('followup::lang.finished_requests');
            $requestsProcess =   $requestsProcess->where('status', 'rejected')->orWhere('status', 'approved');
        } else if ($filter == 'under_process') {
            $pageName = __('followup::lang.under_process_requests');
            $requestsProcess =   $requestsProcess->where('status', 'under_process');
        } else if ($filter == 'new') {
            $pageName = __('followup::lang.new_requests');
            $business = Business::where('id', $business_id)->first();
            $requestsProcess =   $requestsProcess->whereDate('created_at', Carbon::now($business->time_zone)->toDateString());
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
                    $status = trans('request.' . $row->status);
                    return $status;
                })


                ->rawColumns(['status', 'request_type_id'])


                ->make(true);
        }


        return view('followup::requests.custom_filtered_requests')->with(compact('pageName'));
    }

    public function storeSelectedRowsRequest(Request $request)
    {

        $userIdsArray = json_decode($request->user_id, true);

        $newUserIds = array_map(function ($item) {
            return (string) $item['id'];
        }, $userIdsArray);
        $request->merge(['user_id' => $newUserIds]);

        $business_id = request()->session()->get('user.business_id');
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->pluck('id')->toArray();
        return $this->requestUtil->storeRequest($request, $departmentIds);
    }
}