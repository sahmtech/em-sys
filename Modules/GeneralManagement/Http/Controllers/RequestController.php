<?php

namespace Modules\GeneralManagement\Http\Controllers;


use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use App\User;
use App\Company;
use App\RequestAttachment;
use Modules\Sales\Entities\SalesProject;

use App\AccessRole;
use App\AccessRoleRequest;

use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;

use App\Request as UserRequest;
use App\RequestProcess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\CEOManagment\Entities\RequestsType;

class RequestController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return Renderable
     */

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

        $can_change_status = auth()->user()->can('generalmanagement.change_request_status');
        $can_return_request = auth()->user()->can('generalmanagement.return_request');
        $can_show_request = auth()->user()->can('generalmanagement.view_request');
        $departmentIds = EssentialsDepartment::pluck('id')->toArray();

        $departmentIdsForGeneralManagment = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%')
                    ->orWhere('name', 'like', '%عام%');
            })
            ->pluck('id')->toArray();

        $ownerTypes = ['employee', 'manager', 'worker'];
        $roles = DB::table('roles')
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%')
                    ->orWhere('name', 'like', '%عام%');
            })->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();
        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'generalmanagement::requests.allRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes, $departmentIdsForGeneralManagment);
    }
    public function president_pending_requests()
    {
        error_log('president_pending_requests');
        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('generalmanagement.change_request_status');
        $can_return_request = auth()->user()->can('generalmanagement.return_request');
        $can_show_request = auth()->user()->can('generalmanagement.view_request');
        $departmentIds = EssentialsDepartment::pluck('id')->toArray();

        $departmentIdsForGeneralManagment = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%')
                    ->orWhere('name', 'like', '%عام%');
            })
            ->pluck('id')->toArray();

        $ownerTypes = ['employee', 'manager', 'worker'];
        $roles = DB::table('roles')
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%')
                    ->orWhere('name', 'like', '%عام%');
            })->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();
        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'generalmanagement::requests.pendingRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes, $departmentIdsForGeneralManagment, false, null, 'pending');
    }
    public function president_done_requests()
    {
        error_log('president_done_requests');
        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('generalmanagement.change_request_status');
        $can_return_request = auth()->user()->can('generalmanagement.return_request');
        $can_show_request = auth()->user()->can('generalmanagement.view_request');
        $departmentIds = EssentialsDepartment::pluck('id')->toArray();

        $departmentIdsForGeneralManagment = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%')
                    ->orWhere('name', 'like', '%عام%');
            })
            ->pluck('id')->toArray();

        $ownerTypes = ['employee', 'manager', 'worker'];
        $roles = DB::table('roles')
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%')
                    ->orWhere('name', 'like', '%عام%');
            })->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();
        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'generalmanagement::requests.doneRequest', $can_change_status, $can_return_request, $can_show_request, $requestsTypes, $departmentIdsForGeneralManagment, false, null, 'done');
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
            $query->Where('name', 'like', '%مجلس%')
                ->orWhere('name', 'like', '%عليا%')->orWhere('name', 'like', '%عام%');
        })
            ->pluck('id')->toArray();
        $allRequestTypes = RequestsType::pluck('type', 'id');
        $saleProjects = SalesProject::all()->pluck('name', 'id');
        $companies = Company::all()->pluck('name', 'id');
        $all_status = ['approved', 'pending', 'rejected'];
        $escalatedRequests = null;
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')->groupBy('request_id');
        $allRequestTypes = RequestsType::pluck('type', 'id');
        $companies = Company::all()->pluck('name', 'id');
        $escalatedRequests = UserRequest::where('process.sub_status', 'escalateRequest')->select([

            'requests.request_no',
            'requests.id',
            'requests.request_type_id',
            'requests.created_at',
            'requests.reason',
            'requests.is_new',
            'requests.created_by',

            'process.id as process_id',
            'process.status',
            'process.status as status_now',
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
            'users.company_id',
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
            ->where('process.status', 'pending')->where('users.status', '!=', 'inactive')->groupBy('requests.id')->orderBy('requests.created_at', 'desc');

        if (request()->input('company') && request()->input('company') !== 'all') {
            error_log(request()->input('company'));
            $escalatedRequests->where('users.company_id', request()->input('company'));
        }
        if (request()->input('project') && request()->input('project') !== 'all') {
            error_log(request()->input('project'));
            $escalatedRequests->where('users.assigned_to', request()->input('project'));
        }

        if (request()->input('type') && request()->input('type') !== 'all') {
            error_log(request()->input('type'));
            $types = RequestsType::where('type', request()->input('type'))->pluck('id')->toArray();
            $escalatedRequests->whereIn('requests.request_type_id', $types);
        }
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
                ->editColumn('company_id', function ($row) use ($companies) {
                    if ($row->company_id) {
                        return $companies[$row->company_id];
                    }
                })
                ->editColumn('assigned_to', function ($row) use ($saleProjects) {
                    if ($row->assigned_to) {
                        return $saleProjects[$row->assigned_to];
                    } else {
                        return '';
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
                })->editColumn('can_return', function ($row) {
                    $buttonsHtml = '';

                    $buttonsHtml .= '<button class="btn btn-success btn-sm btn-view-request-details" data-request-id="' . $row->id . '">' . trans('request.view_request_details') . '</button>';
                    $buttonsHtml .= '<button class="btn btn-xs btn-view-activities" style="background-color: #6c757d; color: white;" data-request-id="' . $row->id . '">' . trans('request.view_activities') . '</button>';

                    return $buttonsHtml;
                })

                ->rawColumns(['status', 'id_proof_number', 'can_return'])


                ->make(true);
        }
        $statuses = $this->statuses;
        return view('generalmanagement::requests.escalate_requests')->with(compact('statuses', 'allRequestTypes', 'all_status', 'companies', 'saleProjects'));
    }

    public function changeEscalationStatus(Request $request)
    {
        error_log(json_encode($request->all()));
        try {

            UserRequest::where('id', $request->request_id)->update(['status' => $request->status]);
            RequestProcess::where('request_id', $request->request_id)->where('status', 'pending')->update(['status' => $request->status]);
            $attachmentPath = $request->attachment ? $request->attachment->store('/requests_attachments') : null;
            if ($attachmentPath) {
                RequestAttachment::create([
                    'request_id' => $request->request_id,
                    'added_by' => auth()->user()->id,
                    'name' => $request->status,
                    'file_path' => $attachmentPath,
                ]);
            }
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