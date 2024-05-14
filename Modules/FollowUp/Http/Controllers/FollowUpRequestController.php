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
    protected $statuses;

    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;
        $this->statuses = [
            'approved' => [
                'name' => __('request.approved'),
                'class' => 'bg-green',
            ],
            'rejected' => [
                'name' => __('request.rejected'),
                'class' => 'bg-red',
            ],
            'pending' => [
                'name' => __('request.pending'),
                'class' => 'bg-yellow',
            ],
        ];
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
        error_log(request()->query('filter'));
        $business_id = request()->session()->get('user.business_id');

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
        $can_change_status = auth()->user()->can('followup.change_request_status');
        $can_return_request = auth()->user()->can('followup.return_request');
        $can_show_request = auth()->user()->can('followup.show_request');
        $requestsProcess = null;
        $allRequestTypes = RequestsType::pluck('type', 'id');
        $saleProjects = SalesProject::all()->pluck('name', 'id');
        $statuses = $this->statuses;
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')
            ->groupBy('request_id');
        $filter = request()->query('filter');
        $requestsProcess = UserRequest::select([

            'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.reason',

            'process.id as process_id', 'process.status', 'process.note as note',  'process.procedure_id as procedure_id', 'process.superior_department_id as superior_department_id',

            'wk_procedures.action_type as action_type', 'wk_procedures.department_id as department_id', 'wk_procedures.can_return', 'wk_procedures.start as start',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number', 'users.assigned_to',



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
                    ->on('request_procedure_tasks.request_id', '=', 'requests.id');
            })
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')
            ->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('process.superior_department_id', $departmentIds)
                    ->orWhereIn('process.started_department_id', $departmentIds);
            })


            ->whereIn('requests.related_to', $userIds)
            ->where(function ($query) {
                $query->where('users.status', 'active')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('users.status', 'inactive')
                            ->whereIn('users.sub_status', ['vacation', 'escape', 'return_exit']);
                    });
            })
            ->groupBy('requests.id');
        $pageName = __('followup::lang.allRequests');

        if ($filter == 'finished') {
            $pageName = __('followup::lang.finished_requests');
            $requestsProcess = $requestsProcess->where('process.status', 'rejected')
                ->orWhere('process.status', 'approved');
        } elseif ($filter == 'under_process') {
            $pageName = __('followup::lang.under_process_requests');
            $requestsProcess = $requestsProcess->where('process.status', 'pending');
        } elseif ($filter == 'new') {
            $pageName = __('followup::lang.new_requests');
            $business = Business::where('id', $business_id)->first();
            $requestsProcess = $requestsProcess->whereDate('requests.created_at', Carbon::now($business->time_zone)->toDateString());
        }
        $requests = $requestsProcess->get();

        foreach ($requests as $request) {
            $tasksDetails = DB::table('request_procedure_tasks')
                ->join('procedure_tasks', 'procedure_tasks.id', '=', 'request_procedure_tasks.procedure_task_id')
                ->join('tasks', 'tasks.id', '=', 'procedure_tasks.task_id')
                ->where('procedure_tasks.procedure_id', $request->procedure_id)
                ->where('request_procedure_tasks.request_id', $request->id)
                ->select('tasks.description', 'request_procedure_tasks.id', 'request_procedure_tasks.procedure_task_id', 'tasks.link', 'request_procedure_tasks.isDone', 'procedure_tasks.procedure_id')
                ->get();


            $request->tasksDetails = $tasksDetails;
        }


        if (request()->ajax()) {


            return DataTables::of($requests ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                    if ($row->request_type_id) {
                        return $allRequestTypes[$row->request_type_id];
                    }
                })


                ->editColumn('status', function ($row) use ($is_admin, $can_change_status, $departmentIds,  $statuses) {
                    if ($row->status) {
                        $status = '';
                        if ($row->action_type === 'accept_reject' || $row->action_type === null) {
                            $status = trans('request.' . $row->status);

                            if ($row->status == 'pending' && (in_array($row->department_id, $departmentIds) || in_array($row->superior_department_id, $departmentIds))) {
                                if ($is_admin || $can_change_status) {
                                    $status = '<span class="label ' . $statuses[$row->status]['class'] . '">'
                                        . __($statuses[$row->status]['name']) . '</span>';
                                    $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                                }
                            }
                        } elseif ($row->action_type === 'task') {

                            if (isset($row->tasksDetails) && !empty($row->tasksDetails)) {

                                $status = '<ul style="list-style-type:none; padding-left: 0;">';

                                foreach ($row->tasksDetails as $taskDetail) {
                                    $checkmark = $taskDetail->isDone ? '&#9989;' : '';
                                    $taskLink = $taskDetail->link;

                                    $status .= "<li><label>";
                                    if ($taskDetail->isDone) {
                                        if ($taskLink) {
                                            $status .= "<a href='{$taskLink}' target='_blank' style='color: green; font-size: large; text-decoration: none;'>$checkmark</a>";
                                        } else {
                                            $status .= "<span style='color: green; font-size: large;'>$checkmark</span>";
                                        }
                                    } else {
                                        $status .= "<input type='checkbox' disabled>";
                                    }

                                    if ($taskLink) {
                                        $status .= " <a href='{$taskLink}' target='_blank'>{$taskDetail->description}</a>";
                                    } else {
                                        $status .= " {$taskDetail->description}";
                                    }

                                    $status .= "</label></li>";
                                }

                                $status .= '</ul>';
                            }

                            if (isset($departmentIdsForGeneralManagment) && !empty($departmentIdsForGeneralManagment)) {

                                if ($row->status == 'pending' && in_array($row->department_id, $departmentIdsForGeneralManagment)) {
                                    if ($row->tasksDetails) {
                                        $status = '<ul style="list-style-type:none; padding-left: 0;">';

                                        foreach ($row->tasksDetails as $taskDetail) {
                                            $checkmark = $taskDetail->isDone ? '&#9989;' : '';

                                            $taskLink = $taskDetail->link;

                                            $status .= "<li><label>";
                                            if ($taskDetail->isDone) {

                                                $status .= "<a href='{$taskLink}' target='_blank' style='color: green; font-size: large; text-decoration: none;'>$checkmark</a>";
                                            } else {
                                                if (!$taskLink) {
                                                    $status .= "<li><label><input type='checkbox' class='task-checkbox' data-task-id='{$taskDetail->id}' " . ($taskDetail->isDone ? "checked='checked'" : "") . "> {$taskDetail->description}</label></li>";
                                                } else {
                                                    $status .= "<input type='checkbox' disabled>";
                                                }
                                                $status .= " <a href='{$taskLink}' target='_blank'>{$taskDetail->description}</a></label></li>";
                                            }
                                        }
                                        $status .= '</ul>';
                                    }
                                }
                            } elseif ($row->status == 'pending' && (in_array($row->department_id, $departmentIds) || in_array($row->superior_department_id, $departmentIds))) {



                                if ($row->tasksDetails) {
                                    $status = '<ul style="list-style-type:none; padding-left: 0;">';

                                    foreach ($row->tasksDetails as $taskDetail) {

                                        $checkmark = $taskDetail->isDone ? '&#9989;' : '';

                                        $taskLink = $taskDetail->link;

                                        $status .= "<li><label>";
                                        if ($taskDetail->isDone) {

                                            $status .= "<a href='{$taskLink}' target='_blank' style='color: green; font-size: large; text-decoration: none;'>$checkmark</a>";
                                            $status .= " <a href='{$taskLink}' target='_blank'>{$taskDetail->description}</a></label></li>";
                                        } else {
                                            if (!$taskLink) {

                                                $status .= "<li><label><input type='checkbox' class='task-checkbox' data-task-id='{$taskDetail->id}' " . ($taskDetail->isDone ? "checked='checked'" : "") . "> {$taskDetail->description}</label></li>";
                                            } else {
                                                $status .= "<input type='checkbox' disabled>";
                                                $status .= " <a href='{$taskLink}' target='_blank'>{$taskDetail->description}</a></label></li>";
                                            }
                                        }
                                    }
                                    $status .= '</ul>';
                                }
                            }
                        }




                        return $status;
                    }
                })

                ->editColumn('can_return', function ($row) use ($is_admin, $can_return_request, $can_show_request, $departmentIds) {
                    $buttonsHtml = '';

                    if ($row->can_return == 1 && $row->status == 'pending' && in_array($row->department_id, $departmentIds) && $row->start != '1') {


                        if ($is_admin || $can_return_request) {
                            $buttonsHtml .= '<button class="btn btn-danger btn-sm btn-return" data-request-id="' . $row->process_id . '">' . trans('request.return_the_request') . '</button>';
                        }
                    }

                    if ($is_admin || $can_show_request) {
                        $buttonsHtml .= '<button class="btn btn-primary btn-sm btn-view-request" data-request-id="' . $row->id . '">' . trans('request.view_request') . '</button>';
                    }

                    return $buttonsHtml;
                })

                ->rawColumns(['status', 'request_type_id', 'can_return'])


                ->make(true);
        }


        return view('followup::requests.custom_filtered_requests')->with(compact('pageName', 'filter'));
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
