<?php

namespace Modules\GeneralManagmentOffice\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\AccessRole;
use App\AccessRoleRequest;

use App\User;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use App\Request as UserRequest;
use App\RequestProcess;
use Carbon\Carbon;
use Modules\CEOManagment\Entities\RequestsType;

class RequestController extends Controller
{
    protected $moduleUtil;
    protected $requestUtil;

    protected $statuses;
    protected $statuses2;
    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;

        $this->statuses = [
            'approved' => [
                'name' => __('followup::lang.approved'),
                'class' => 'bg-green',
            ],
            'rejected' => [
                'name' => __('followup::lang.rejected'),
                'class' => 'bg-red',
            ],
            'pending' => [
                'name' => __('followup::lang.pending'),
                'class' => 'bg-yellow',
            ],
        ];
        $this->statuses2 = [
            'approved' => [
                'name' => __('followup::lang.approved'),
                'class' => 'bg-green',
            ],

            'pending' => [
                'name' => __('followup::lang.pending'),
                'class' => 'bg-yellow',
            ],
        ];
    }
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('generalmanagmentoffice.change_request_status');
        $can_return_request = auth()->user()->can('generalmanagmentoffice.return_request');
        $can_show_request = auth()->user()->can('generalmanagmentoffice.view_request');
        $departmentIds = EssentialsDepartment::pluck('id')->toArray();

        $departmentIdsForGeneralManagment = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->Where('name', 'like', '%مكتب%');
            })
            ->pluck('id')->toArray();

        $ownerTypes = ['employee', 'manager', 'worker'];
        $roles = DB::table('roles')
            ->where(function ($query) {
                $query->Where('name', 'like', '%مكتب%');
            })->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();
        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'generalmanagmentoffice::requests.allRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes, $departmentIdsForGeneralManagment);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('generalmanagmentoffice::create');
    }


    public function escalateRequests()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $departmentIds = EssentialsDepartment::where(function ($query) {
            $query->Where('name', 'like', '%مكتب%');
        })
            ->pluck('id')->toArray();


        $escalatedRequests = null;
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')->groupBy('request_id');
        $allRequestTypes = RequestsType::pluck('type', 'id');

        $escalatedRequests = UserRequest::where('process.sub_status', 'escalateRequest')->where('process.is_transfered_from_GM', 0)->select([

            'requests.request_no',
            'requests.id',
            'requests.request_type_id',
            'requests.created_at',
            'requests.reason',

            'process.id as process_id',
            'process.status',
            'process.note as note',
            'process.procedure_id as procedure_id',
            'process.superior_department_id as superior_department_id',

            'wk_procedures.action_type as action_type',
            'wk_procedures.department_id as department_id',
            'wk_procedures.can_return',
            'wk_procedures.start as start',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            'users.id_proof_number',
            'users.assigned_to',
            'users.id as userId',


            'procedure_escalations.escalates_to'

        ])
            ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                $join->on('requests.id', '=', 'latest_process.request_id');
            })
            ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')

            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
            ->leftjoin('procedure_tasks', 'procedure_tasks.procedure_id', '=', 'wk_procedures.id')
            ->leftjoin('tasks', 'tasks.id', '=', 'procedure_tasks.task_id')
            ->leftjoin('request_procedure_tasks', function ($join) {
                $join->on('request_procedure_tasks.procedure_task_id', '=', 'procedure_tasks.id')
                    ->where('request_procedure_tasks.request_id', '=', 'requests.id');
            })

            ->leftJoin('users', 'users.id', '=', 'requests.related_to')

            ->join('procedure_escalations', 'procedure_escalations.procedure_id', '=', 'wk_procedures.id')

            ->whereIn('requests.related_to', $userIds)->whereIn('procedure_escalations.escalates_to', $departmentIds)
            ->where('process.status', 'pending')->where('users.status', '!=', 'inactive')->groupBy('requests.id');


        if (request()->ajax()) {


            return DataTables::of($escalatedRequests ?? [])

                ->editColumn('created_at', function ($row) {

                    return Carbon::parse($row->created_at);
                })
                ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                    if ($row->request_type_id) {
                        return $allRequestTypes[$row->request_type_id];
                    }
                })
                ->editColumn('id_proof_number', function ($row) {
                    if ($row->id_proof_number) {
                        $expiration_date = optional(
                            DB::table('essentials_official_documents')
                                ->where('employee_id', $row->userId)
                                ->where('type', 'residence_permit')
                                ->where('is_active', 1)
                                ->first()
                        )->expiration_date;

                        return $row->id_proof_number . '<br>' . $expiration_date;
                    } else {
                        return '';
                    }
                })
                ->editColumn('status', function ($row) {
                    $status = '';

                    if ($row->status == 'pending') {
                        $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                            . __($this->statuses[$row->status]['name']) . '</span>';


                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    } elseif (in_array($row->status, ['approved', 'rejected'])) {
                        $status = trans('followup::lang.' . $row->status);
                    }

                    return $status;
                })

                ->rawColumns(['status', 'id_proof_number'])


                ->make(true);
        }
        $statuses = $this->statuses;
        return view('generalmanagmentoffice::requests.escalate_requests')->with(compact('statuses'));
    }

    public function changeEscalationStatus(Request $request)
    {
        try {

            UserRequest::where('id', $request->request_id)->update(['status' => $request->status]);
            RequestProcess::where('request_id', $request->request_id)->where('status', 'pending')->update(['status' => $request->status]);

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

        return $output;
    }
}