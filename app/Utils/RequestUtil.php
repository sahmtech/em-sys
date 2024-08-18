<?php

namespace App\Utils;

use App\AccessRole;
use App\Company;
use App\AccessRoleCompany;
use App\Request as UserRequest;
use App\RequestProcess;
use App\RequestAttachment;
use App\SentNotification;
use App\SentNotificationsUser;
use App\User;
use Carbon\Carbon;
use App\Utils\ModuleUtil;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Modules\CEOManagment\Entities\WkProcedure;
use Modules\CEOManagment\Entities\ProcedureTask;
use Modules\CEOManagment\Entities\RequestProcedureTask;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\Essentials\Entities\ToDo;

use Modules\Essentials\Notifications\NewTaskNotification;
use Modules\FollowUp\Entities\FollowupUserAccessProject;

use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\UserLeaveBalance;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsEmployeeTravelCategorie;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsInsuranceClass;

use Modules\Sales\Entities\SalesProject;

use Modules\CEOManagment\Entities\Task;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use stdClass;

class RequestUtil extends Util
{

    protected $moduleUtil;
    protected $statuses;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
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


    ////// get requests /////////////////// 
    public function getRequests($departmentIds,  $ownerTypes, $view, $can_change_status, $can_return_request, $can_show_request, $requestsTypes, $departmentIdsForGeneralManagment = [], $isFollowup = false, $company_id = null, $condition = null)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->whereNot('user_type', 'customer')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $allRequestTypes = RequestsType::pluck('type', 'id');
        //  $requestTypeIds = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();

        // $requestTypeIds = WkProcedure::distinct()
        //     ->with('request_type')
        //     ->whereIn('department_id', $departmentIds)
        //     ->whereIn('request_owner_type', $ownerTypes)
        //     ->where('start', '1')
        //     ->pluck('request_type_id')
        //     ->toArray();

        $requestTypes = RequestsType::whereIn('id', $requestsTypes)
            ->get()
            ->map(function ($requestType) {
                return [
                    'id' => $requestType->id,
                    'type' => $requestType->type,
                    'for' => $requestType->for,
                ];
            })
            ->toArray();



        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();
        $statuses = $this->statuses;
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->whereIn('employee_type', $ownerTypes)->pluck('reason', 'id');

        $all_users = User::select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $all_users = $all_users->pluck('full_name', 'id');

        if ($isFollowup) {
            $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
            if (!($is_admin || $is_manager)) {
                $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
                $worker_ids = User::whereIn('id', $userIds)->whereIn('assigned_to',  $followupUserAccessProject)->pluck('id')->toArray();
                $userIds = array_intersect($userIds, $worker_ids);
            }
        }
        if ($company_id) {

            $ids = User::whereIn('id', $userIds)->where('company_id',  $company_id)->pluck('id')->toArray();
            $userIds = array_intersect($userIds, $ids);
        }


        // $users = User::whereIn('id', $userIds)
        //     // ->whereIn('user_type', $ownerTypes)
        //     ->where(function ($query) {
        //         $query->where('status', 'active')
        //             ->orWhere(function ($subQuery) {
        //                 $subQuery->where('status', 'inactive')
        //                     ->whereIn('sub_status', ['vacation', 'escape', 'return_exit']);
        //             });
        //     })
        //     ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'') ,' - ',COALESCE(company_id,'')) as full_name"))
        //     ->pluck('full_name', 'id');
        $users = DB::table('users')
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->select('users.id', DB::raw("CONCAT(
                COALESCE(users.first_name, ''), ' ', 
                COALESCE(users.last_name, ''), ' - ', 
                COALESCE(users.id_proof_number, ''), ' - ', 
                COALESCE(companies.name, '')
            ) as full_name"))
            ->where(function ($query) use ($userIds) {
                $query->where('users.status', 'active')
                    ->orWhere(function ($subQuery) use ($userIds) {
                        $subQuery->where('users.status', 'inactive')
                            ->whereIn('users.sub_status', ['vacation', 'escape', 'return_exit']);
                    });
            })
            ->whereIn('users.id', $userIds)
            ->pluck('full_name', 'users.id');

        $saleProjects = SalesProject::all()->pluck('name', 'id');
        $companies = Company::all()->pluck('name', 'id');


        $requestsProcess = null;
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')->whereNull('sub_status')->groupBy('request_id');

        $requestsProcess = UserRequest::select([

            'requests.request_no',
            'requests.id',
            'requests.request_type_id',
            'requests.is_new',
            'requests.created_at',
            'requests.created_by',
            'requests.reason',

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
            DB::raw("IF(process.superior_department_id IN (" . implode(',', $departmentIds) . "), 1, 0) as is_superior"),
            DB::raw("IF(process.started_department_id IN (" . implode(',', $departmentIds) . "), 1, 0) as is_started")
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
            ->groupBy('requests.id')->orderBy('requests.created_at', 'desc');
        //  return $requestsProcess->get();
        if (request()->input('status') && request()->input('status') !== 'all') {
            error_log(request()->input('status'));
            $requestsProcess->where('process.status', request()->input('status'));
        }
        error_log($condition);
        if ($condition && $condition == 'pending') {
            $requestsProcess->where('process.status', 'pending');
        }
        if ($condition && $condition == 'done') {
            $requestsProcess->whereIn('process.status', ['approved', 'rejected']);
        }
        if (request()->input('company') && request()->input('company') !== 'all') {
            error_log(request()->input('company'));
            $requestsProcess->where('users.company_id', request()->input('company'));
        }
        if (request()->input('project') && request()->input('project') !== 'all') {
            error_log(request()->input('project'));
            $requestsProcess->where('users.assigned_to', request()->input('project'));
        }

        if (request()->input('type') && request()->input('type') !== 'all') {
            error_log(request()->input('type'));
            $types = RequestsType::where('type', request()->input('type'))->pluck('id')->toArray();
            $requestsProcess->whereIn('requests.request_type_id', $types);
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
                ->addColumn('created_user', function ($row) use ($all_users) {

                    return $all_users[$row->created_by];
                })
                ->editColumn('status', function ($row) use ($is_admin, $can_change_status, $departmentIds,  $departmentIdsForGeneralManagment, $statuses) {
                    if ($row->status) {
                        $status = '';
                        if ($row->action_type === 'accept_reject' || $row->action_type === null) {
                            $status = trans('request.' . $row->status);
                            if ($departmentIdsForGeneralManagment) {
                                if ($row->status == 'pending' && in_array($row->department_id, $departmentIdsForGeneralManagment)) {
                                    if ($is_admin || $can_change_status) {
                                        $status = '<span class="label ' . $statuses[$row->status]['class'] . '">' . __($statuses[$row->status]['name']) . '</span>';
                                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                                    }
                                }
                            } else {
                                if ($row->status == 'pending' && (in_array($row->department_id, $departmentIds) || in_array($row->superior_department_id, $departmentIds))) {
                                    if ($is_admin || $can_change_status) {
                                        $status = '<span class="label ' . $statuses[$row->status]['class'] . '">'
                                            . __($statuses[$row->status]['name']) . '</span>';
                                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                                    }
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

                ->editColumn('can_return', function ($row) use ($is_admin, $can_return_request, $can_show_request, $departmentIds, $departmentIdsForGeneralManagment) {
                    $buttonsHtml = '';
                    if ($departmentIdsForGeneralManagment) {
                        if ($row->can_return == 1 && $row->status == 'pending' && in_array($row->department_id, $departmentIdsForGeneralManagment)) {
                            if ($is_admin || $can_return_request) {
                                $buttonsHtml .= '<button class="btn btn-danger btn-sm btn-return" data-request-id="' . $row->id . '">' . trans('request.return_the_request') . '</button>';
                            }
                        }
                    } else {
                        if ($row->can_return == 1 && $row->status == 'pending' && in_array($row->department_id, $departmentIds)) {


                            if ($is_admin || $can_return_request) {
                                $buttonsHtml .= '<button class="btn btn-danger btn-sm btn-return" data-request-id="' . $row->id . '">' . trans('request.return_the_request') . '</button>';
                            }
                        }
                    }
                    if ($is_admin || $can_show_request) {
                        $buttonsHtml .= '<button class="btn btn-success btn-sm btn-view-request-details" data-request-id="' . $row->id . '">' . trans('request.view_request_details') . '</button>';
                        $buttonsHtml .= '<button class="btn btn-xs btn-view-activities" style="background-color: #6c757d; color: white;" data-request-id="' . $row->id . '">' . trans('request.view_activities') . '</button>';
                    }


                    // if ($is_admin || $can_show_request) {
                    //     $buttonsHtml .= '<button class="btn btn-primary btn-sm btn-view-request" data-request-id="' . $row->id . '">' . trans('request.view_request') . '</button>';
                    // }

                    return $buttonsHtml;
                })

                ->rawColumns(['status', 'request_type_id', 'can_return', 'created_user', 'id_proof_number', 'assigned_to'])


                ->make(true);
        }
        $all_status = ['approved', 'pending', 'rejected'];
        return view($view)->with(compact(
            'users',
            'requestTypes',
            'statuses',
            'main_reasons',
            'classes',
            'saleProjects',
            'companies',
            'leaveTypes',
            'job_titles',
            'specializations',
            'nationalities',
            'allRequestTypes',
            'all_status'
        ));
    }


    ////// store request /////////////////// 
    public function storeRequest($request, $departmentIds)
    {


        try {

            $attachmentPath = $request->attachment ? $request->attachment->store('/requests_attachments') : null;
            $startDate = $request->start_date ?? $request->escape_date ?? $request->exit_date;
            $end_date = $request->end_date ?? $request->return_date;
            $today = Carbon::today();
            $type = RequestsType::where('id', $request->type)->first()->type;


            if ($startDate && $type != 'escapeRequest') {
                $startDateCarbon = Carbon::parse($startDate);
                if ($startDateCarbon->lt($today)) {
                    $message = __('request.time_is_gone');
                    return redirect()->back()->withErrors([$message]);
                }
                if ($end_date) {

                    $endDateCarbon = Carbon::parse($end_date);

                    if ($startDateCarbon->gt($endDateCarbon)) {
                        $message = __('request.start_date_after_end_date');
                        return redirect()->back()->withErrors([$message]);
                    }
                }
            }


            if ($type == 'leavesAndDepartures' && is_null($request->leaveType)) {
                $output = [
                    'success' => false,
                    'msg' => __('request.please select the type of leave'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }



            $requestTypeFor = RequestsType::findOrFail($request->type)->for;
            $createdByUser = auth()->user();
            $createdBy_type = $createdByUser->user_type;
            $createdBy_department = $createdByUser->essentials_department_id;

            $success = 1;

            foreach ($request->user_id as $userId) {
                error_log($userId);
                $count_of_users = count($request->user_id);
                if ($userId === null) continue;
                $isExists = UserRequest::where('related_to', $userId)->where('request_type_id', $request->type)->where('status', 'pending')->first();
                if ($isExists && count($request->user_id) == 1) {
                    $output = [
                        'success' => 0,
                        'msg' => __('request.this_user_has_this_request_recently'),
                    ];
                    return redirect()->back()->withErrors([$output['msg']]);
                }

                if (!$isExists) {
                    error_log('not exist');
                    $user = User::find($userId);
                    $business_id = ($user && $user->company_id == 2) ? 2 : 1;
                    $userType = User::where('id', $userId)->first()->user_type;
                    $procedure = WkProcedure::where('business_id', $business_id)
                        ->where('request_type_id', $request->type)->where('start', 1)->first();
                    if (!$procedure) {

                        $message = __('request.this_type_has_not_procedure');
                        $output = [
                            'success' => false,
                            'msg' => $message
                        ];
                        return redirect()->back()->withErrors([$output['msg']]);
                    }
                    if (($userType == 'worker' && $requestTypeFor == 'employee') || ($userType == 'employee' && $requestTypeFor == 'worker') || ($userType == 'manager' && $requestTypeFor == 'worker')) {

                        $message = __('request.this_type_id_for_') . " " . __('request.' . $requestTypeFor);
                        $output = [
                            'success' => false,
                            'msg' => $message
                        ];
                        return redirect()->back()->withErrors([$output['msg']]);
                    }


                    if ($type == "exitRequest") {

                        $startDate = DB::table('essentials_employees_contracts')->where('employee_id', $userId)->first()->contract_end_date ?? null;
                    }
                    if ($type == "leavesAndDepartures") {
                        $leaveBalance = UserLeaveBalance::where([
                            'user_id' => $userId,
                            'essentials_leave_type_id' => $request->leaveType,
                        ])->first();

                        if (!$leaveBalance || $leaveBalance->amount == 0) {
                            if ($count_of_users == 1) {
                                $messageKey = !$leaveBalance ? 'this_user_cant_ask_for_leave_request' : 'this_user_has_not_enough_leave_balance';
                                $message = __("request.$messageKey");
                                DB::rollBack();
                                return redirect()->back()->withErrors([$message]);
                            }
                            continue;
                        } else {

                            $startDate = Carbon::parse($startDate);
                            $endDate = Carbon::parse($end_date);
                            $daysRequested = $startDate->diffInDays($endDate) + 1;

                            if ($daysRequested > $leaveBalance->amount) {
                                if ($count_of_users == 1) {
                                    $message = __("request.this_user_has_not_enough_leave_balance");
                                    DB::rollBack();
                                    return redirect()->back()->withErrors([$message]);
                                }
                                continue;
                            }
                        }
                    }
                    if ($type == 'cancleContractRequest' && !empty($request->main_reason)) {

                        $contract = EssentialsEmployeesContract::where('employee_id', $userId)->firstOrFail();
                        if (is_null($contract->wish_id)) {
                            if ($count_of_users == 1) {
                                $output = [
                                    'success' => false,
                                    'msg' => __('request.no_wishes_found'),
                                ];

                                return redirect()->back()->withErrors([$output['msg']]);
                            }
                            continue;
                        }
                        if (now()->diffInMonths($contract->contract_end_date) > 1) {
                            if ($count_of_users == 1) {
                                $output = [
                                    'success' => false,
                                    'msg' => __('request.contract_expired'),
                                ];

                                return redirect()->back()->withErrors([$output['msg']]);
                            }
                            continue;
                        }
                    }
                    $Request = new UserRequest;

                    $Request->request_no = $this->generateRequestNo($request->type);
                    $Request->related_to = $userId;
                    $Request->request_type_id = $request->type;
                    $Request->start_date = $startDate;
                    $Request->end_date = $end_date;
                    $Request->reason = $request->reason;
                    $Request->note = $request->note;
                    $Request->attachment = $attachmentPath;
                    $Request->essentials_leave_type_id = $request->leaveType;
                    $Request->escape_time = $request->escape_time;
                    $Request->installmentsNumber = $request->installmentsNumber;
                    $Request->monthlyInstallment = $request->monthlyInstallment;
                    $Request->advSalaryAmount = $request->amount;
                    $Request->created_by = auth()->user()->id;
                    $Request->insurance_classes_id = $request->ins_class;
                    $Request->baladyCardType = $request->baladyType;
                    $Request->resCardEditType = $request->resEditType;
                    $Request->workInjuriesDate = $request->workInjuriesDate;
                    $Request->contract_main_reason_id = $request->main_reason;
                    $Request->contract_sub_reason_id = $request->sub_reason;
                    $Request->visa_number = $request->visa_number;
                    $Request->atmCardType = $request->atmType;
                    $Request->authorized_entity = $request->authorized_entity;
                    $Request->commissioner_info = $request->commissioner_info;
                    $Request->trip_type = $request->trip_type;
                    $Request->Take_off_location = $request->Take_off_location;
                    $Request->destination = $request->destination;
                    $Request->weight_of_furniture = $request->weight_of_furniture;
                    $Request->date_of_take_off = $request->date_of_take_off;
                    $Request->time_of_take_off = $request->time_of_take_off;
                    $Request->return_date = $request->return_date_of_trip;


                    $Request->job_title_id = $request->job_title;
                    $Request->specialization_id = $request->profession;
                    $Request->nationality_id = $request->nationlity;
                    $Request->number_of_salary_inquiry = $request->number_of_salary_inquiry;

                    $Request->sale_project_id = $request->project_name;
                    $Request->interview_date = $request->interview_date;
                    $Request->interview_time = $request->interview_time;
                    $Request->interview_place = $request->interview_place;

                    $Request->residenceRenewalDuration = $request->residenceRenewalDuration;






                    $Request->save();



                    if ($attachmentPath) {
                        RequestAttachment::create([
                            'request_id' => $Request->id,
                            'file_path' => $attachmentPath,
                        ]);
                    }
                    if ($Request) {
                        $process = null;
                        if ($userType == 'worker') {


                            $procedure = WkProcedure::where('business_id', $business_id)
                                //     ->where('request_type_id', $request->type)->where('start', 1)->whereIn('department_id', $departmentIds)->first();
                                ->where('request_type_id', $request->type)->where('start', 1)->first();

                            // if ($createdBy_type == 'manager' || $createdBy_type == 'admin') {

                            //     $nextProcedure = WkProcedure::where('business_id', $business_id)->where('request_type_id', $request->type)
                            //         ->where('department_id', $procedure->next_department_id)->first();


                            //     $process =   RequestProcess::create([
                            //         'started_department_id' => $departmentIds[0],
                            //         'request_id' => $Request->id,
                            //         'procedure_id' => $nextProcedure ? $nextProcedure->id : null,
                            //         'status' => 'pending',

                            //     ]);
                            //     if ($nextProcedure->action_type = 'task') {
                            //         $procedureTasks = ProcedureTask::where('procedure_id', $nextProcedure->id)->get();
                            //         foreach ($procedureTasks as $task) {
                            //             $requestTasks = new RequestProcedureTask();
                            //             $requestTasks->request_id = $Request->id;
                            //             $requestTasks->procedure_task_id = $task->id;
                            //             $requestTasks->save();
                            //         }
                            //     }
                            // } else {


                            $process = RequestProcess::create([
                                'started_department_id' => $departmentIds[0],
                                'note' => $request->note,
                                'request_id' => $Request->id,
                                'procedure_id' => $procedure ? $procedure->id : null,
                                'status' => 'pending',

                            ]);
                            //    }
                        } else {
                            $department_id = User::where('id', $userId)->first()->essentials_department_id;

                            if ($createdBy_type == 'employee' || ($createdBy_type == 'manager' &&  $createdBy_department !=  $department_id) || ($createdBy_type == 'admin' && !(in_array($department_id, $departmentIds)))) {

                                $superior_dep =  RequestsType::where('id', $request->type)->first()->goes_to_superior;
                                if ($superior_dep) {
                                    if ($department_id) {
                                        $process = RequestProcess::create([
                                            'started_department_id' => $departmentIds[0],
                                            'note' => $request->note,
                                            'request_id' => $Request->id,
                                            'superior_department_id' => $department_id,
                                            'status' => 'pending'
                                        ]);
                                    } else {
                                        RequestAttachment::where('request_id', $Request->id)->delete();
                                        $Request->delete();
                                        if (count($request->user_id) == 1) {
                                            $output = [
                                                'success' => 0,
                                                'msg' => __('request.this_user_has_not_department'),
                                            ];
                                            return redirect()->back()->withErrors([$output['msg']]);
                                        }
                                    }
                                } else {

                                    $procedure = WkProcedure::Where('request_type_id', $request->type)->where('business_id', $business_id)->where('start', '1')->first();
                                    error_log('****************************');
                                    error_log($procedure->id);
                                    error_log('****************************');

                                    if (!$procedure) {
                                        $output = [
                                            'success' => false,
                                            'msg' => __('request.no_procedure_found'),
                                        ];
                                        return redirect()->back()->withErrors([$output['msg']]);
                                    }
                                    if ((in_array($procedure->department_id, $departmentIds))) {
                                        $nextProcedure = WkProcedure::Where('request_type_id', $request->type)->where('business_id', $business_id)->where('department_id', $procedure->next_department_id)->first();
                                        if ($nextProcedure) {
                                            $process = RequestProcess::create([
                                                'started_department_id' => $departmentIds[0],
                                                'request_id' => $Request->id,
                                                'note' => $request->note,
                                                'procedure_id' => $nextProcedure->id,
                                                'status' => 'pending'
                                            ]);

                                            if ($nextProcedure->action_type = 'task') {
                                                $procedureTasks = ProcedureTask::where('procedure_id', $nextProcedure->id)->get();
                                                foreach ($procedureTasks as $task) {
                                                    $requestTasks = new RequestProcedureTask();
                                                    $requestTasks->request_id = $Request->id;
                                                    $requestTasks->procedure_task_id = $task->id;
                                                    $requestTasks->save();
                                                }
                                            }
                                        } else {
                                            $output = [
                                                'success' => false,
                                                'msg' => __('request.no_next_department_to_go_there'),
                                            ];
                                            return redirect()->back()->withErrors([$output['msg']]);
                                        }
                                    } else {
                                        $process = RequestProcess::create([
                                            'started_department_id' => $departmentIds[0],
                                            'note' => $request->note,
                                            'request_id' => $Request->id,
                                            'procedure_id' => $procedure->id,
                                            'status' => 'pending'
                                        ]);
                                    }
                                }
                            } else if (($createdBy_type == 'admin' && (in_array($department_id, $departmentIds))) || ($createdBy_type == 'manager' && (in_array($createdBy_department, $departmentIds)))) {

                                $procedure = WkProcedure::Where('request_type_id', $request->type)->where('business_id', $business_id)->where('start', '1')->first();
                                if (!$procedure) {
                                    $output = [
                                        'success' => false,
                                        'msg' => __('request.no_procedure_found'),
                                    ];
                                    return redirect()->back()->withErrors([$output['msg']]);
                                }
                                if ((in_array($procedure->department_id, $departmentIds))) {
                                    $nextProcedure = WkProcedure::Where('request_type_id', $request->type)->where('business_id', $business_id)->where('department_id', $procedure->next_department_id)->first();
                                    if ($nextProcedure) {
                                        $process = RequestProcess::create([
                                            'started_department_id' => $departmentIds[0],
                                            'note' => $request->note,
                                            'request_id' => $Request->id,
                                            'procedure_id' => $nextProcedure->id,
                                            'status' => 'pending'
                                        ]);

                                        if ($nextProcedure->action_type = 'task') {
                                            $procedureTasks = ProcedureTask::where('procedure_id', $nextProcedure->id)->get();
                                            foreach ($procedureTasks as $task) {
                                                $requestTasks = new RequestProcedureTask();
                                                $requestTasks->request_id = $Request->id;
                                                $requestTasks->procedure_task_id = $task->id;
                                                $requestTasks->save();
                                            }
                                        }
                                    } else {
                                        $output = [
                                            'success' => false,
                                            'msg' => __('request.no_next_department_to_go_there'),
                                        ];
                                        return redirect()->back()->withErrors([$output['msg']]);
                                    }
                                } else {
                                    $process = RequestProcess::create([
                                        'started_department_id' => $departmentIds[0],
                                        'request_id' => $Request->id,
                                        'note' => $request->note,
                                        'procedure_id' => $procedure->id,
                                        'status' => 'pending'
                                    ]);
                                }
                            }
                        }


                        if (!$process) {

                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                        }
                    } else {

                        $success = 0;
                    }
                } else {
                    continue;
                }
            }

            if ($success) {
                $this->makeToDo($Request, $business_id);
                $output = [
                    'success' => 1,
                    'msg' => __('messages.added_success'),
                ];
                return redirect()->back()->with('success', $output['msg']);
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('messages.something_went_wrong'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log($e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
    }

    public function saveAttachment(Request $request, $requestId)
    {
        error_log(json_encode($request->all()));

        if ($request->has('attachments')) {
            $hasValidAttachment = false;

            foreach ($request->attachments as $attachment) {
                if (isset($attachment['file']) && isset($attachment['name'])) {
                    $file = $attachment['file'];
                    $attachmentPath = $file->store('/requests_attachments');

                    RequestAttachment::create([
                        'request_id' => $requestId,
                        'added_by' => auth()->user()->id,
                        'name' => $attachment['name'],
                        'file_path' => $attachmentPath,
                    ]);

                    $hasValidAttachment = true;
                }
            }

            if ($hasValidAttachment) {
                $output = [
                    'status' => 'success',
                    'msg' => __('messages.saved_successfully'),
                ];
            } else {
                $output = [
                    'status' => 'error',
                    'msg' => __('request.please_add_valid_file_and_name_before_saving'),
                ];
            }
        } else {
            $output = [
                'status' => 'error',
                'msg' => __('request.please_add_afile_before_saved'),
            ];
        }

        return response()->json($output);
    }



    public function makeToDo($request, $business_id)
    {


        $created_by = $request->created_by;
        $userCompanyId = User::where('id', $request->related_to)->first()->company_id;
        $request_type = RequestsType::where('id', $request->request_type_id)->first()->type;
        $input['business_id'] = $business_id;
        $input['company_id'] = $userCompanyId;
        $input['created_by'] = $created_by;
        $input['task'] = "طلب جديد";
        $input['date'] = Carbon::now();
        $input['priority'] = 'high';
        $input['description'] = $request_type;
        $input['status'] = !empty($input['status']) ? $input['status'] : 'new';

        $process = RequestProcess::where('request_id', $request->id)->latest()->first();
        $users = [];



        $acessRoleCompany = AccessRoleCompany::where('company_id', $userCompanyId)->pluck('access_role_id')->toArray();

        $rolesFromAccessRoles = AccessRole::whereIn('id', $acessRoleCompany)->pluck('role_id')->toArray();

        if ($process->superior_department_id) {
            $viewRequestPermission = $this->getViewRequestsPermission($process->superior_department_id);
            if ($viewRequestPermission) {
                $permission_id = Permission::with('roles')->where('name', $viewRequestPermission)->first();
                $rolesIds = $permission_id->roles->pluck('id')->toArray();
                $users = User::whereHas('roles', function ($query) use ($rolesIds, $rolesFromAccessRoles) {
                    $query->whereIn('id', $rolesIds)->whereIn('id', $rolesFromAccessRoles);
                })->where('essentials_department_id', $process->superior_department_id);
            }
        } else {
            $procedure = $process->procedure_id;
            error_log($procedure);
            $department_id = WKProcedure::where('id', $procedure)->first()->department_id;
            $viewRequestPermission = $this->getViewRequestsPermission($department_id);
            error_log(json_encode($viewRequestPermission));
            if ($viewRequestPermission) {
                $permission_id = Permission::with('roles')->where('name', $viewRequestPermission)->first();
                $rolesIds = $permission_id->roles->pluck('id')->toArray();
                error_log(json_encode($rolesIds));
                $users = User::whereHas('roles', function ($query) use ($rolesIds,  $rolesFromAccessRoles) {
                    $query->whereIn('id', $rolesIds)->whereIn('id', $rolesFromAccessRoles);
                })->where('essentials_department_id', $department_id);
            }
        }



        $input['task_id'] = $request->request_no;

        $to_dos = ToDo::create($input);
        $usersData = $users->get();

        $to_dos->users()->sync($usersData);


        $user_ids = $users->pluck('id')->toArray();
        $to =  $users->select([DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, '')) as full_name")])
            ->pluck('full_name')->toArray();
        if (!empty($user_ids)) {
            $to = [];
            $userName = User::where('id', $request->related_to)->select([DB::raw("CONCAT(COALESCE(users.first_name, ''),' ', COALESCE(users.last_name, '')) as full_name")])
                ->pluck('full_name')->toArray()[0];
            $sentNotification = SentNotification::create([
                'via' => 'dashboard',
                'type' => 'GeneralManagementNotification',
                'title' =>  $input['task'],
                'msg' => __('request.' . $request_type) . ' ' . $userName,
                'sender_id' => auth()->user()->id,
                'to' => json_encode($to),
            ]);
            // $details = new stdClass();
            // $details->title =  $input['task'];
            // $details->message = $request_type;

            foreach ($user_ids as $user_id) {
                SentNotificationsUser::create([
                    'sent_notifications_id' => $sentNotification->id,
                    'user_id' => $user_id,
                ]);
                // User::where('id', $user_id)->first()?->notify(new GeneralNotification($details, false, true));
            }
        }
    }


    ////// change request status /////////////////// 
    public function changeRequestStatus(Request $request)
    {



        if ($request->request_id) {

            $first_step = RequestProcess::where('request_id', $request->request_id)
                ->where('status', 'pending')
                ->whereNull('sub_status')
                ->first();

            if ($first_step) {
                if ($first_step->procedure_id !== null) {
                    return $this->changeRequestStatusAfterProcedure($request);
                } elseif ($first_step->superior_department_id !== null) {
                    return $this->changeRequestStatusBeforProcedure($request);
                }
            }
        }


        if ($request->request_ids) {
            $requestIds = explode(',', $request->request_ids);

            foreach ($requestIds as $request_id) {

                $first_step = RequestProcess::where('request_id', $request_id)
                    ->where('status', 'pending')
                    ->whereNull('sub_status')
                    ->first();

                if ($first_step) {

                    $request->merge(['request_id' => $request_id]);

                    if ($first_step->procedure_id !== null) {
                        $this->changeRequestStatusAfterProcedure($request);
                    } elseif ($first_step->superior_department_id !== null) {
                        $this->changeRequestStatusBeforProcedure($request);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'msg' => __('lang_v1.updated_success'),
        ]);
    }

    private function changeRequestStatusAfterProcedure($request)
    {

        try {
            $requestProcess = RequestProcess::where('request_id', $request->request_id)
                ->where('status', 'pending')
                ->where('sub_status', null)
                ->first();



            $procedure = WkProcedure::where('id', $requestProcess->procedure_id)->first();

            $procedure_business_id = $procedure->business_id;
            $can_reject = $procedure->can_reject;
            $peivious_note = $requestProcess->note;

            if ($can_reject == 0 && $request->status == 'rejected') {
                $output = [
                    'success' => false,
                    'msg' => __('request.this_department_cant_reject_this_request'),
                ];
                return $output;
            }

            $requestProcess->status = $request->status;
            $requestProcess->reason = $request->reason ?? null;
            $requestProcess->status_changed_at = carbon::now();
            $requestProcess->note = $request->note ?? null;
            $requestProcess->updated_by = auth()->user()->id;
            $requestProcess->save();

            if ($request->status == 'approved') {
                if ($procedure && $procedure->end == 1) {
                    $requestProcess->request->status = 'approved';
                    $cancelContracttypes = RequestsType::where('type', 'cancleContractRequest')->pluck('id')->toArray();
                    $exittypes = RequestsType::whereIn('type', ['exitRequest', 'return_request', 'leavesAndDepartures'])->pluck('id')->toArray();

                    if (!in_array($requestProcess->request->request_type_id, $cancelContracttypes)) {
                        if (in_array($requestProcess->request->request_type_id, $exittypes)) {
                            $travel = EssentialsEmployeeTravelCategorie::where('employee_id', $requestProcess->request->related_to)->first();
                            if (!$travel) {
                                $requestProcess->request->is_done = 1;
                            }
                        } else {
                            $requestProcess->request->is_done = 1;
                        }
                        $requestProcess->request->save();
                    }

                    $types = RequestsType::where('type', 'leavesAndDepartures')->pluck('id')->toArray();
                    if (in_array($requestProcess->request->request_type_id, $types)) {
                        $startDate = Carbon::parse($requestProcess->request->start_date);
                        $endDate = Carbon::parse($requestProcess->request->end_date);
                        $daysDifference = $startDate->diffInDays($endDate) + 1;

                        $leaveBalance = UserLeaveBalance::where([
                            'user_id' => $requestProcess->request->related_to,
                            'essentials_leave_type_id' => $requestProcess->request->essentials_leave_type_id,
                        ])->first();

                        $leaveBalance->amount -= $daysDifference;
                        $leaveBalance->save();
                    }
                } else {

                    $nextDepartmentId = $procedure->next_department_id;
                    $visitedProcedures = RequestProcess::where('request_id', $requestProcess->request_id)
                        ->pluck('procedure_id')
                        ->filter()
                        ->toArray();

                    $nextProcedure = WkProcedure::where('department_id', $nextDepartmentId)
                        ->where('request_type_id', $requestProcess->request->request_type_id)
                        ->where('business_id', $procedure_business_id)
                        ->whereNotIn('id', $visitedProcedures)
                        ->first();


                    if ($nextProcedure) {
                        $newRequestProcess = new RequestProcess();
                        $newRequestProcess->request_id = $requestProcess->request_id;
                        $newRequestProcess->started_department_id = $requestProcess->started_department_id;
                        $newRequestProcess->procedure_id = $nextProcedure->id;
                        $newRequestProcess->status = 'pending';
                        $newRequestProcess->note = $peivious_note . ',' . $request->note ?? null;
                        $newRequestProcess->save();

                        if ($nextProcedure->action_type == 'task') {
                            $procedureTasks = ProcedureTask::where('procedure_id', $nextProcedure->id)->get();
                            foreach ($procedureTasks as $task) {
                                $requestTasks = new RequestProcedureTask();
                                $requestTasks->request_id = $requestProcess->request_id;
                                $requestTasks->procedure_task_id = $task->id;
                                $requestTasks->save();
                            }
                        }
                    }

                    // $business_id = request()->session()->get('user.business_id');
                    $userRequest = UserRequest::where('id', $requestProcess->request_id)->first();
                    $this->makeToDo($userRequest, $procedure_business_id);
                }
            }

            if ($request->status == 'rejected') {
                $requestProcess->request->status = 'rejected';
                $requestProcess->status = 'rejected';
                $requestProcess->note =  $peivious_note . ',' . $request->note ?? null;
                $requestProcess->save();


                $requestProcess->request->save();
            }

            $requestProcess->request->is_new = 0;
            $requestProcess->request->save();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    private function changeRequestStatusBeforProcedure($request)
    {


        try {
            $process = RequestProcess::where('request_id', $request->request_id)->where('status', 'pending')->whereNull('sub_status')->first();
            $userRequest = UserRequest::where('id', $request->request_id)->first();
            $peivious_note = $process->note;
            $currentDepartment = $process->superior_department_id;
            $process->status = $request->status;
            $process->reason = $request->reason ?? null;
            $process->status_changed_at = carbon::now();
            $process->note = $request->note ?? null;
            $process->updated_by = auth()->user()->id;
            $process->save();

            if ($request->status  == 'rejected') {
                $process->request->status = 'rejected';
                $process->status = 'rejected';
                $process->note =  $peivious_note . ',' . $request->note ?? null;
                $process->save();
                $process->request->save();
            }


            if ($request->status == 'approved') {
                $procedure = WkProcedure::where('request_type_id', $userRequest->request_type_id)->first();
                if ($currentDepartment != $procedure->department_id) {
                    $action_type = $procedure->action_type;
                    $newRequestProcess = new RequestProcess();
                    $newRequestProcess->started_department_id = $process->started_department_id;
                    $newRequestProcess->request_id = $process->request_id;
                    $newRequestProcess->procedure_id = $procedure->id;
                    $newRequestProcess->status = 'pending';
                    $newRequestProcess->note = $peivious_note . ',' . $request->note ?? null;
                    $newRequestProcess->save();
                    if ($action_type == 'task') {
                        $procedureTask = ProcedureTask::where('procedure_id', $procedure->id)->get();
                        foreach ($procedureTask as $task) {
                            $request_tasks = new RequestProcedureTask();
                            $request_tasks->request_id = $process->request_id;
                            $request_tasks->procedure_task_id = $task->id;
                            $request_tasks->save();
                        }
                    }
                } else {

                    $nextDepartmentId = $procedure->next_department_id;

                    $nextProcedure = WkProcedure::where('department_id', $nextDepartmentId)
                        ->where('business_id', $procedure->business_id)
                        ->where('request_type_id', $procedure->request_type_id)
                        ->first();
                    if ($nextProcedure) {
                        $action_type = $nextProcedure->action_type;
                        $newRequestProcess = new RequestProcess();
                        $newRequestProcess->started_department_id = $process->started_department_id;
                        $newRequestProcess->request_id = $process->request_id;
                        $newRequestProcess->procedure_id = $nextProcedure->id;
                        $newRequestProcess->status = 'pending';
                        $newRequestProcess->note = $peivious_note . ',' . $request->note ?? null;
                        $newRequestProcess->save();
                        if ($action_type == 'task') {
                            $procedureTask = ProcedureTask::where('procedure_id', $nextProcedure->id)->get();
                            foreach ($procedureTask as $task) {
                                $request_tasks = new RequestProcedureTask();
                                $request_tasks->request_id = $process->request_id;
                                $request_tasks->procedure_task_id = $task->id;
                                $request_tasks->save();
                            }
                        }
                    } else {
                        $process->request->status = 'approved';
                        $process->request->save();
                    }
                }


                $userRequest = UserRequest::where('id',  $process->request_id)->first();
                $this->makeToDo($userRequest, $procedure->business_id);
            }

            $process->request->is_new = 0;
            $process->request->save();

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



    ////// view request /////////////////// 
    public function viewRequest($id)
    {

        $request = UserRequest::with([
            'related_to_user',
            'created_by_user',
            'process.procedure.department',
            'attachments'
        ])->where('id', $id)->first();

        if (!$request) {
            return response()->json(['error' => 'Request not found'], 404);
        }

        if ($request->related_to_user->user_type == "worker") {
            return $this->viewWorkerRequest($request);
        } else {
            return $this->viewEmployeeRequest($request);
        }
    }

    private function viewWorkerRequest($request)
    {
        $allRequestTypes = RequestsType::pluck('type', 'id');
        $type = $allRequestTypes[$request->request_type_id];
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $firstStep = RequestProcess::where('id', $request->process[0]->id)->first();
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->pluck('reason', 'id');
        $sub_main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'sub_main')->pluck('sub_reason', 'id');

        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'escape_time' => $request->escape_time,
            'advSalaryAmount' => $request->advSalaryAmount,
            'monthlyInstallment' => $request->monthlyInstallment,
            'installmentsNumber' => $request->installmentsNumber,
            'baladyCardType' => $request->baladyCardType,
            'workInjuriesDate' => $request->workInjuriesDate,
            'resCardEditType' => $request->resCardEditType,
            'contract_main_reason_id' => $request->contract_main_reason_id ? $main_reasons[$request->contract_main_reason_id] : null,
            'contract_sub_reason_id' => $request->contract_sub_reason_id ?  $sub_main_reasons[$request->contract_sub_reason_id] : null,
            'visa_number' => $request->visa_number,
            'atmCardType' => trans("request.{$request->atmCardType}"),
            'residenceRenewalDuration' => trans("request.{$request->residenceRenewalDuration}"),
            'insurance_classes_id' => $request->insurance_classes_id,
            'status' => trans("request.{$request->status}"),
            'type' => trans("request.{$type}"),
            'started_depatment' => [
                'id' => $firstStep->started_department_id,
                'name' => $firstStep->started_department_id ? $departments[$firstStep->started_department_id] : null,
            ],
            'created_at' => carbon::parse($request->created_at)->format('y:m:d h:m:i'),
            'updated_at' => carbon::parse($request->updated_at)->format('y:m:d h:m:i'),
        ];

        $workflow = [];
        $currentStep = WkProcedure::where('id', $request->process[0]->procedure_id)->first();
        $visitedProcedures = [];
        while ($currentStep && !$currentStep->end) {
            if (in_array($currentStep->id, $visitedProcedures)) {

                break;
            }
            $visitedProcedures[] = $currentStep->id;

            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $currentStep->next_department_id)->first())->name,
            ];

            // Find the next step
            $nextSteps = WkProcedure::where('request_type_id', $request->request_type_id)
                ->where('business_id', $currentStep->business_id)
                ->where('department_id', $currentStep->next_department_id)
                ->get();

            $currentStep = null;
            foreach ($nextSteps as $step) {
                if (!in_array($step->id, $visitedProcedures)) {
                    $currentStep = $step;
                    break;
                }
            }
        }

        if ($currentStep && $currentStep->end == 1) {

            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                // 'procedure_id' =>  $currentStep->id ?? null,
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => null,
            ];
        }

        $isDone = UserRequest::where('id', $request->id)->first()->is_done;
        $workflow[] = [
            'id' => null,

            'status' => $isDone ? 'approved' : '',
            'department' => $isDone ? trans('request.done') : trans('request.not_yet_done'),
        ];
        //  error_log(json_encode($workflow));
        $attachments = null;
        if ($request->attachments) {
            $attachments = $request->attachments->map(function ($attachment) {
                return [
                    'request_id' => $attachment->request_id,
                    'name' => $attachment->name,
                    'file_path' => $attachment->file_path,
                    'created_at' => $attachment->created_at,
                ];
            });
        }
        $companies = Company::all()->pluck('name', 'id');
        $userInfo = [
            'worker_id' => $request->related_to_user->id,
            'user_type' => trans("request.{$request->related_to_user->user_type}"),
            'nationality' => optional(DB::table('essentials_countries')->where('id', $request->related_to_user->nationality_id)->first())->nationality,
            'assigned_to' => $this->getContactLocation($request->related_to_user->assigned_to),
            'worker_full_name' => $request->related_to_user->first_name . ' ' . $request->related_to_user->last_name,
            'id_proof_number' => $request->related_to_user->id_proof_number,
            'company' => $companies[$request->related_to_user->company_id] ?? null,
            'admission_date' => $this->getAdmissionDate($request->related_to_user),
            'contract_end_date' => optional(DB::table('essentials_employees_contracts')->where('employee_id', $request->related_to_user->id)->first())->contract_end_date,
            'eqama_end_date' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->related_to_user->id)->where('type', 'residence_permit')->where('is_active', 1)->first())->expiration_date,
            'passport_number' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->related_to_user->id)->where('type', 'passport')->where('is_active', 1)->first())->number,
        ];

        $createdUserInfo = [
            'created_user_id' => $request->created_by_user->id,
            'user_type' => $request->created_by_user->user_type,
            'nationality_id' => $request->created_by_user->nationality_id,
            'created_user_full_name' => $request->created_by_user->first_name . ' ' . $request->created_by_user->last_name,
            'id_proof_number' => $request->created_by_user->id_proof_number,
        ];

        $followupProcesses = [];
        $seenProcedureIds = [];

        $sortedProcesses = $request->process->sortByDesc(function ($process) {
            return $process->procedure_id . '-' . $process->updated_at;
        });

        foreach ($sortedProcesses as $process) {
            if (in_array($process->procedure_id, $seenProcedureIds)) {
                continue;
            }

            $seenProcedureIds[] = $process->procedure_id;

            $processInfo = [
                'id' => $process->id,
                'status' => trans("request.{$process->status}"),
                'procedure_id' => $process->procedure_id,
                'is_returned' => $process->is_returned,
                'updated_by' => $this->getFullName($process->updated_by),
                'status_changed_at' => $process->status_changed_at,
                'reason' => $process->reason,
                'status_note' => $process->note,
                'department' => [
                    'id' => optional($process->procedure?->department)->id,
                    'name' => optional($process->procedure?->department)->name,
                ],
            ];
            error_log(json_encode($processInfo));
            $followupProcesses[] = $processInfo;
        }
        //error_log(json_encode($followupProcesses));
        $result = [
            'request_info' => $requestInfo,
            'user_info' => $userInfo,
            'created_user_info' => $createdUserInfo,
            'followup_processes' => $followupProcesses,
            'workflow' => $workflow,
            'attachments' => $attachments,
        ];

        return response()->json($result);
    }



    private function viewEmployeeRequest($request)
    {
        $allRequestTypes = RequestsType::pluck('type', 'id');
        $type = $allRequestTypes[$request->request_type_id];
        $firstStep = RequestProcess::where('id', $request->process[0]->id)->first();
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->pluck('reason', 'id');
        $sub_main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'sub_main')->pluck('sub_reason', 'id');

        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'escape_time' => $request->escape_time,
            'advSalaryAmount' => $request->advSalaryAmount,
            'monthlyInstallment' => $request->monthlyInstallment,
            'installmentsNumber' => $request->installmentsNumber,
            'baladyCardType' => $request->baladyCardType,
            'workInjuriesDate' => $request->workInjuriesDate,
            'resCardEditType' => $request->resCardEditType,
            'contract_main_reason_id' => $request->contract_main_reason_id ? $main_reasons[$request->contract_main_reason_id] : null,
            'contract_sub_reason_id' => $request->contract_sub_reason_id ?  $sub_main_reasons[$request->contract_sub_reason_id] : null,
            'visa_number' => $request->visa_number,
            'atmCardType' => trans("request.{$request->atmCardType}"),
            'residenceRenewalDuration' => trans("request.{$request->residenceRenewalDuration}"),
            'insurance_classes_id' => $request->insurance_classes_id,
            'status' => trans("request.{$request->status}"),
            'type' => trans("request.{$type}"),
            'started_depatment' => [
                'id' => $firstStep->started_department_id,
                'name' => $departments[$firstStep->started_department_id],
            ],
            'created_at' => carbon::parse($request->created_at)->format('y:m:d h:m:i'),
            'updated_at' => carbon::parse($request->updated_at)->format('y:m:d h:m:i'),
        ];

        $workflow = [];

        // $firstProcedure = WkProcedure::where('id', $firstStep->procedure_id)->first();
        $user = User::find($request->related_to);
        $business_id = ($user && $user->company_id == 2) ? 2 : 1;
        $firstProcedure = WkProcedure::where('request_type_id', $request->request_type_id)->where('business_id', $business_id)->where('start', 1)->first();
        $visitedProcedures = [];

        if ($firstStep->superior_department_id) {
            $workflow[] = [
                'id' => null,
                'process_id' => $firstStep->id,
                'status' => $firstStep->status,
                'department' => optional(DB::table('essentials_departments')->where('id', $firstStep->superior_department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure?->department_id)->first())->name,
            ];
        }

        $requestproceduretype = $request->request_type_id;
        $requestprocedurebusiness =  $business_id;

        if ($firstStep->superior_department_id == $firstProcedure->department_id) {
            $firstProcedures = WkProcedure::where('request_type_id', $requestproceduretype)
                ->where('business_id', $requestprocedurebusiness)
                ->get();

            if (count($firstProcedures) <= 1) {
                $firstProcedure = null;
            } else {
                $firstProcedure = $firstProcedures[1];
            }
        }

        while ($firstProcedure && !$firstProcedure->end) {
            if (in_array($firstProcedure->id, $visitedProcedures)) {
                // Break the loop if the procedure has already been visited to prevent infinite loop
                break;
            }
            $visitedProcedures[] = $firstProcedure->id;

            $workflow[] = [
                'id' => $firstProcedure->id,
                'process_id' => $this->getProcessIdForStep($request, $firstProcedure),
                'status' => $this->getProcessStatusForStep($request, $firstProcedure),
                'department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure->department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure->next_department_id)->first())->name,
            ];

            // Find the next step
            $nextSteps = WkProcedure::where('request_type_id', $requestproceduretype)
                ->where('business_id', $requestprocedurebusiness)
                ->where('department_id', $firstProcedure->next_department_id)
                ->get();

            $firstProcedure = null;
            foreach ($nextSteps as $step) {
                if (!in_array($step->id, $visitedProcedures)) {
                    $firstProcedure = $step;
                    break;
                }
            }
        }

        if ($firstProcedure && $firstProcedure->end == 1) {
            $workflow[] = [
                'id' => $firstProcedure->id,
                'process_id' => $this->getProcessIdForStep($request, $firstProcedure),
                'status' => $this->getProcessStatusForStep($request, $firstProcedure),
                'department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure->department_id)->first())->name,
                'next_department' => null,
            ];
        }

        $isDone = UserRequest::where('id', $request->id)->first()->is_done;
        $workflow[] = [
            'id' => null,
            'status' => $isDone ? 'approved' : '',
            'department' => $isDone ? trans('request.done') : trans('request.not_yet_done'),
        ];

        $attachments = null;
        if ($request->attachments) {
            $attachments = $request->attachments->map(function ($attachment) {
                return [
                    'request_id' => $attachment->request_id,
                    'name' => $attachment->name,
                    'file_path' => $attachment->file_path,
                    'created_at' => $attachment->created_at,
                ];
            });
        }

        $userInfo = [
            'worker_id' => $request->related_to_user->id,
            'user_type' => trans("request.{$request->related_to_user->user_type}"),
            'nationality' => optional(DB::table('essentials_countries')->where('id', $request->related_to_user->nationality_id)->first())->nationality,
            'assigned_to' => $this->getContactLocation($request->related_to_user->assigned_to),
            'worker_full_name' => $request->related_to_user->first_name . ' ' . $request->related_to_user->last_name,
            'id_proof_number' => $request->related_to_user->id_proof_number,
            'company' => $companies[$request->related_to_user->company_id] ?? null,
            'admission_date' => $this->getAdmissionDate($request->related_to_user),
            'contract_end_date' => optional(DB::table('essentials_employees_contracts')->where('employee_id', $request->related_to_user->id)->first())->contract_end_date,
            'eqama_end_date' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->related_to_user->id)->where('type', 'residence_permit')->where('is_active', 1)->first())->expiration_date,
            'passport_number' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->related_to_user->id)->where('type', 'passport')->where('is_active', 1)->first())->number,
        ];

        $createdUserInfo = [
            'created_user_id' => $request->created_by_user->id,
            'user_type' => $request->created_by_user->user_type,
            'nationality_id' => $request->created_by_user->nationality_id,
            'created_user_full_name' => $request->created_by_user->first_name . ' ' . $request->created_by_user->last_name,
            'id_proof_number' => $request->created_by_user->id_proof_number,
        ];

        $followupProcesses = [];
        foreach ($request->process as $process) {
            if ($process->superior_department_id) {
                $processInfo = [
                    'id' => $process->id,
                    'status' => trans("request.{$process->status}"),
                    'superior_department_id' => $process->superior_department_id ?? null,
                    'is_returned' => $process->is_returned,
                    'updated_by' => $this->getFullName($process->updated_by),
                    'status_changed_at' => carbon::parse($process->status_changed_at)->format('y-m-d h:m:i'),
                    'reason' => $process->reason,
                    'status_note' => $process->note,
                    'department' => [
                        'id' => $process->superior_department_id,
                        'name' => $departments[$process->superior_department_id],
                    ],
                ];
            } else {
                $processInfo = [
                    'id' => $process->id,
                    'status' => trans("request.{$process->status}"),
                    'procedure_id' => $process->procedure_id ?? null,
                    'superior_department_id' => $process->superior_department_id ?? null,
                    'is_returned' => $process->is_returned,
                    'updated_by' => $this->getFullName($process->updated_by),
                    'status_changed_at' => carbon::parse($process->status_changed_at)->format('y:m:d h:m:i'),
                    'reason' => $process->reason,
                    'status_note' => $process->note,
                    'department' => [
                        'id' => $process->procedure->department->id,
                        'name' => $process->procedure->department->name,
                    ],
                ];
            }

            $followupProcesses[] = $processInfo;
        }

        $result = [
            'request_info' => $requestInfo,
            'user_info' => $userInfo,
            'created_user_info' => $createdUserInfo,
            'followup_processes' => $followupProcesses,
            'workflow' => $workflow,
            'attachments' => $attachments,
        ];

        return response()->json($result);
    }



    ////// return Request ////////////////

    public function returnRequest(Request $request)
    {
        try {
            if ($request->requestId && $request->requestId != null) {
                $this->processReturnRequest($request->requestId, $request->reason);
            }

            if ($request->requestIds) {
                $requestIds = explode(',', $request->requestIds);
                foreach ($requestIds as $requestId) {
                    $this->processReturnRequest($requestId, $request->reason);
                }
            }

            $output = [
                'success' => true,
                'msg' => __('request.returned_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return response()->json($output);
    }

    private function processReturnRequest($requestId, $reason)
    {
        $requestProcess = RequestProcess::where('request_id', $requestId)
            ->where('status', 'pending')
            ->whereNull('sub_status')
            ->first();

        if ($requestProcess) {


            $procedure = WkProcedure::find($requestProcess->procedure_id);
            if ($procedure && $procedure->can_return == 0) {
                return;
            }
            $peivious_note = $requestProcess->note;
            $userRequest = $requestProcess->request_id;
            $firstStep = RequestProcess::where('request_id', $userRequest)->first();
            $goes_to_superior = RequestsType::where('id', $procedure->request_type_id)->first()->goes_to_superior;



            if ($procedure) {

                $departmentId = $procedure->department_id;
                $nameDepartment = EssentialsDepartment::where('id', $departmentId)->first()->name;

                $newProcedure = WkProcedure::where('next_department_id', $departmentId)
                    ->where('request_type_id', $procedure->request_type_id)
                    ->first();

                if ($newProcedure) {
                    $requestProcess->update([
                        'procedure_id' => $newProcedure->id,
                        'status' => 'pending',
                        'is_returned' => 1,
                        'note' => $peivious_note . ', ' . __('request.returned_by') . " " . $nameDepartment . ' , ' . __('request.reason') . ": " . $reason,
                    ]);
                } else {
                    if ($procedure->request_owner_type == 'employee' && $goes_to_superior == 1) {
                        $requestProcess->update([
                            'procedure_id' => null,
                            'superior_department_id' => $firstStep->superior_department_id,
                            'status' => 'pending',
                            'is_returned' => 1,
                            'note' => $peivious_note . ', ' . __('request.returned_by') . " " . $nameDepartment . ' , ' . __('request.reason') . ": " . $reason,
                        ]);
                    } else {
                        $requestProcess->update([
                            'procedure_id' => null,
                            'superior_department_id' => $firstStep->started_department_id,
                            'status' => 'pending',
                            'is_returned' => 1,
                            'note' => $peivious_note . ', ' . __('request.returned_by') . " " . $nameDepartment . ' , ' . __('request.reason') . ": " . $reason,
                        ]);
                    }
                }
            }
        }
    }




    ////// operational functions /////////////////// 
    private function getTypePrefix($request_type_id)
    {
        $prefix = RequestsType::where('id', $request_type_id)->first()->prefix;
        return $prefix;
    }

    protected function validateLeaveRequirements($request, $userId, $count)
    {

        $leave_type = EssentialsLeaveType::where('id', $request->leaveType)->first();
        $work_duration = EssentialsEmployeeAppointmet::where('employee_id', $userId)->where('is_active', 1)->first();

        if (!$work_duration) {
            return null;
        }

        $due_date = $leave_type->due_date;
        $start_date = new DateTime($work_duration->start_from);
        $current_date = new DateTime();
        $interval = $start_date->diff($current_date);
        $months_from_start = ($interval->y * 12) + $interval->m + ($interval->d > 0 ? 1 : 0);

        if ($months_from_start < $due_date) {
            if ($count == 1) {
                $output = [
                    'success' => false,
                    'msg' => __('messages.cant add request because not enough months of service'),
                ];
                return $output;
            } else {
                return null;
            }
        }

        return true;
    }


    public function generateRequestNo($request_type_id)
    {
        $type = RequestsType::where('id', $request_type_id)->first()->type;
        $RequestsTypes = RequestsType::where('type', $type)->pluck('id')->toArray();

        $latestRecord = UserRequest::whereIn('request_type_id', $RequestsTypes)->orderBy('request_no', 'desc')->first();

        if ($latestRecord) {
            $latestRefNo = $latestRecord->request_no;
            $prefix = $this->getTypePrefix($request_type_id);
            $numericPart = (int)substr($latestRefNo, strlen($prefix));
            $numericPart++;
            $input['request_no'] = $prefix . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
        } else {
            $input['request_no'] = $this->getTypePrefix($request_type_id) . '0001';
        }

        return $input['request_no'];
    }

    private function getProcessIdForStep($request, $step)
    {
        return optional($request->process->where('procedure_id', $step->id)->sortByDesc('created_at')->first())->id;
    }
    private function getProcedureIdForStep($request, $step)
    {
        return optional($request->process->where('procedure_id', $step->id)->sortByDesc('created_at')->first())->id;
    }
    private function getProcessStatusForStep($request, $step)
    {
        return optional($request->process->where('procedure_id', $step->id)->sortByDesc('created_at')->first())->status;
    }


    private function getFullName($userId)
    {
        $user = User::find($userId);

        if ($user) {
            return $user->first_name . ' ' . $user->last_name;
        }

        return null;
    }
    private function getContactLocation($id)
    {
        $contact = SalesProject::find($id);

        if ($contact) {
            return $contact->name;
        }

        return null;
    }
    private function getAdmissionDate($user)
    {

        $admission = EssentialsAdmissionToWork::where('employee_id', $user->id)->where('is_active', 1)->first();
        if ($admission) {
            error_log($admission->admissions_date);
            return $admission->admissions_date;
        }

        return null;
    }

    public function getTypeById($selectedId)
    {
        $type = RequestsType::where('id', $selectedId)->first()->type;
        return response()->json(['type' => $type]);
    }

    public function getSubReasons(Request $request)
    {

        $mainReason = $request->input('main_reason');

        $subReasons = DB::table('essentails_reason_wishes')->where('main_reson_id', $mainReason)
            ->select('id', 'sub_reason as name')
            ->get();
        return response()->json(['sub_reasons' => $subReasons]);
    }

    public function getNonSaudiUsers(Request $request)
    {

        $workerIds = array_keys($request->input('users', []));

        $saudiUsers = User::whereNot('id_proof_name', 'national_id')->whereIn('id', $workerIds)->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->pluck('full_name', 'id');

        return response()->json(['users' => $saudiUsers]);
    }
    public function getUnsignedWorkers(Request $request)
    {

        $workerIds = array_keys($request->input('users', []));

        $workers = User::whereNull('assigned_to')->where('user_type', 'worker')->whereIn('id', $workerIds)->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->pluck('full_name', 'id');

        return response()->json(['workers' => $workers]);
    }

    public function updateStatus(Request $request)
    {

        $task = RequestProcedureTask::find($request->taskId);
        if (!$task) {
            return response()->json(['success' => false], 404);
        }
        $task->isDone = $request->isDone;
        $task->save();
        $request_id = $task->request_id;
        $anotherTask = RequestProcedureTask::where('request_id', $request_id)->where('isDone', '0')->first();

        if (!$anotherTask) {

            $requestProcess = RequestProcess::where('request_id',  $request_id)->where('status', 'pending')->where('sub_status', null)->first();

            $requestProcess->status = 'approved';
            $requestProcess->updated_by = auth()->user()->id;

            $requestProcess->save();


            $procedure = WkProcedure::find($requestProcess->procedure_id);


            if ($procedure && $procedure->end == 1) {
                $requestProcess->request->status = 'approved';
                $cancelContracttypes = RequestsType::where('type', 'cancleContractRequest')->pluck('id')->toArray();
                $exittypes = RequestsType::whereIn('type', ['exitRequest', 'return_request', 'leavesAndDepartures'])->pluck('id')->toArray();

                if (!in_array($requestProcess->request->request_type_id, $cancelContracttypes)) {
                    if (in_array($requestProcess->request->request_type_id, $exittypes)) {

                        $travel = EssentialsEmployeeTravelCategorie::where('employee_id', $requestProcess->request->related_to)->first();
                        error_log($travel);
                        if (!$travel) {
                            $requestProcess->request->is_done = 1;
                        }
                    } else {
                        $requestProcess->request->is_done = 1;
                    }
                    $requestProcess->request->save();
                }
            } else {
                $nextDepartmentId = $procedure->next_department_id;
                $nextProcedure = WkProcedure::where('department_id', $nextDepartmentId)
                    ->where('request_type_id', $requestProcess->request->request_type_id)
                    ->first();

                if ($nextProcedure) {
                    $newRequestProcess = new RequestProcess();

                    $newRequestProcess->request_id = $requestProcess->request_id;
                    $newRequestProcess->started_department_id = $requestProcess->started_department_id;
                    $newRequestProcess->procedure_id = $nextProcedure->id;
                    $newRequestProcess->status = 'pending';
                    $newRequestProcess->save();

                    if ($nextProcedure->action_type = 'task') {
                        $procedureTasks = ProcedureTask::where('procedure_id', $nextProcedure->id)->get();
                        foreach ($procedureTasks as $task) {
                            $requestTasks = new RequestProcedureTask();
                            $requestTasks->request_id = $requestProcess->request_id;
                            $requestTasks->procedure_task_id = $task->id;
                            $requestTasks->save();
                        }
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }

    //test for requests process escalations
    public function test()
    {

        $requests = RequestProcess::join('wk_procedures', 'request_processes.procedure_id', '=', 'wk_procedures.id')
            ->join('procedure_escalations', 'procedure_escalations.procedure_id', '=', 'wk_procedures.id')
            ->select('request_processes.id as request_id', 'wk_procedures.id as procedure_id', 'wk_procedures.department_id as department')
            ->whereNull('request_processes.sub_status')
            ->whereRaw('TIMESTAMPDIFF(HOUR, request_processes.created_at, NOW()) >= procedure_escalations.escalates_after')
            ->get();

        foreach ($requests as $request) {
            $requestProcess = RequestProcess::find($request->request_id);
            if ($requestProcess->is_escalated == '0') {

                $requestProcess->update(['is_escalated' => 1]);
                $nameDepartment = EssentialsDepartment::where('id', $request->department)->first()->name;
                $escalateRequest = new RequestProcess();
                $escalateRequest->request_id = $requestProcess->request_id;
                $escalateRequest->procedure_id = $requestProcess->procedure_id;
                $escalateRequest->status = 'pending';
                $escalateRequest->sub_status = 'escalateRequest';
                $escalateRequest->note = __('followup::lang.escalated_from') . " " . $nameDepartment;
                $escalateRequest->save();
            }
        }
        return 'success';
    }
    //only for workcard operations
    public function viewRequestsOperations()
    {
        $can_show_request = auth()
            ->user()
            ->can('essentials.show_workcards_request');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%حكومية%')
            ->pluck('id')
            ->toArray();

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $allRequestTypes = RequestsType::pluck('type', 'id');
        if ($is_admin  || auth()->user()->can('essentials.view_requests_operations')) {

            $types = RequestsType::whereIn('type', ['exitRequest', 'returnRequest', 'leavesAndDepartures', 'escapeRequest'])->pluck('id')->toArray();

            $procedures = WkProcedure::whereIn('department_id', $departmentIds)->pluck('id')->toArray();

            $tasks = Task::whereIn('request_type_id', $types)->pluck('id')->toArray();

            $procedure_tasks = ProcedureTask::whereIn('task_id', $tasks)->whereIn('procedure_id', $procedures)->pluck('id')->toArray();

            $requests = UserRequest::whereIn('request_type_id', $types)->pluck('id')->toArray();

            //   $request_procedure_tasks = RequestProcedureTask::whereIn('request_id', $requests)->whereIn('procedure_task_id', $procedure_tasks)->where('isDone', 0)->pluck('request_id')->toArray();

            $requestsProcess =  RequestProcedureTask::whereIn('request_id', $requests)->whereIn('procedure_task_id', $procedure_tasks)->where('isDone', 0)->join('requests', 'requests.id', 'request_procedure_tasks.request_id')->select([
                'requests.request_no',
                'requests.id',
                'requests.request_type_id',
                'requests.created_at',
                'requests.status',
                'requests.note as note',

                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'users.id_proof_number',

                'users.status as userStatus',
                'request_procedure_tasks.id as request_procedure_task',

            ])->leftJoin('users', 'users.id', '=', 'requests.related_to')


                ->whereIn('requests.related_to', $userIds)
                ->where('users.status', 'active');


            if (request()->ajax()) {
                return DataTables::of($requestsProcess ?? [])
                    ->editColumn('created_at', function ($row) {
                        return Carbon::parse($row->created_at);
                    })
                    ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                        if ($row->request_type_id) {
                            return $allRequestTypes[$row->request_type_id];
                        }
                    })->addColumn('view', function ($row) use ($is_admin, $can_show_request) {
                        $buttonsHtml = '';

                        if ($is_admin || $can_show_request) {
                            $buttonsHtml .= '<button class="btn btn-success btn-sm btn-view-request-details" data-request-id="' . $row->id . '">' . trans('request.view_request_details') . '</button>';
                            $buttonsHtml .= '<button class="btn btn-xs btn-view-activities" style="background-color: #6c757d; color: white;" data-request-id="' . $row->id . '">' . trans('request.view_activities') . '</button>';
                        }
                        return $buttonsHtml;
                    })
                    ->rawColumns(['view'])
                    ->make(true);
            }

            return view('request.requests_operations');
        }
    }

    public function finish_operation($id)
    {

        try {
            $request_procedure_task = RequestProcedureTask::find($id);
            $userRequest = UserRequest::find($request_procedure_task->request_id);


            if (!$userRequest) {
                return ['success' => false, 'msg' => __('messages.not_found')];
            }
            $type = RequestsType::where('id', $userRequest->request_type_id)->first()->type;

            if ($type == 'exitRequest') {
                $operation_type = 'final_visa';
                $sub_status = 'final_exit';
            }
            if ($type == 'returnRequest') {
                $operation_type = 'return_visa';
                $sub_status = 'return_exit';
            }
            if ($type == 'escapeRequest') {
                $operation_type = 'absent_report';
                $sub_status = 'escape';
            }
            if ($type == 'leavesAndDepartures') {
                $operation_type = 'vacation_report';
                $sub_status = 'vacation';
            }

            $user = User::find($userRequest->related_to);
            if (!$user) {
                return ['success' => false, 'msg' => __('messages.user_not_found')];
            }
            $user->update([
                'status' => 'inactive',
                'sub_status' => $sub_status,
                'allow_login' => '0',
                'updated_by' => auth()->user()->id
            ]);
            $appointment = EssentialsEmployeeAppointmet::where('employee_id', $userRequest->related_to)->where('is_active', '1')->first();
            if ($appointment) {
                $appointment->update([
                    'is_active' => '0'
                ]);
            }

            $work = EssentialsAdmissionToWork::where('employee_id', $userRequest->related_to)->where('is_active', '1')->first();
            if ($work) {
                $work->update([
                    'is_active' => '0'
                ]);
            }

            if ($type == 'exitRequest' || $type == 'returnRequest' || $type == 'escapeRequest') {
                DB::table('essentails_employee_operations')->insert([
                    'operation_type' => $operation_type,
                    'employee_id' => $userRequest->related_to,
                    'start_date' => $userRequest->start_date,
                    'end_date' =>  $userRequest->end_date,
                ]);
            }
            $request_procedure_task->update([
                'isDone' => '1'
            ]);

            $requestProcess = RequestProcess::where('request_id', $userRequest->id)->where('status', 'pending')->where('sub_status', null)->first();

            $requestProcess->status = 'approved';
            $requestProcess->updated_by = auth()->user()->id;

            $requestProcess->save();


            $procedure = WkProcedure::find($requestProcess->procedure_id);


            if ($procedure && $procedure->end == 1) {
                $requestProcess->request->status = 'approved';
                if ($type == 'escapeRequest') {
                    $requestProcess->request->is_done = 1;
                }
                if ($type == 'exitRequest' || $type == 'returnRequest') {
                    $travel = EssentialsEmployeeTravelCategorie::where('employee_id', $userRequest->related_to)->first();
                    if (!$travel) {
                        $requestProcess->request->is_done = 1;
                    }
                }

                $requestProcess->request->save();
            } else {
                $nextDepartmentId = $procedure->next_department_id;
                $nextProcedure = WkProcedure::where('department_id', $nextDepartmentId)
                    ->where('request_type_id', $requestProcess->request->request_type_id)
                    ->first();

                if ($nextProcedure) {
                    $newRequestProcess = new RequestProcess();

                    $newRequestProcess->request_id = $requestProcess->request_id;
                    $newRequestProcess->started_department_id = $requestProcess->started_department_id;
                    $newRequestProcess->procedure_id = $nextProcedure->id;
                    $newRequestProcess->status = 'pending';
                    $newRequestProcess->save();

                    if ($nextProcedure->action_type = 'task') {
                        $procedureTasks = ProcedureTask::where('procedure_id', $nextProcedure->id)->get();
                        foreach ($procedureTasks as $task) {
                            $requestTasks = new RequestProcedureTask();
                            $requestTasks->request_id = $requestProcess->request_id;
                            $requestTasks->procedure_task_id = $task->id;
                            $requestTasks->save();
                        }
                    }
                }
            }




            $output = [
                'success' => true,
                'msg' => __('lang_v1.finished_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
        }

        return $output;
    }



    public function getViewRequestsPermission($department)
    {
        $departments = [
            'followup' => ['names' => ['%متابعة%'], 'permission' => 'followup.view_followup_requests'],
            'accounting' => ['names' => ['%حاسب%', '%مالي%'], 'permission' => 'accounting.view_accounting_requests'],
            'workcard' => ['names' => ['%حكومية%'], 'permission' => 'essentials.view_workcards_request'],
            'hr' => ['names' => ['%بشرية%'], 'permission' => 'essentials.view_HR_requests'],
            'employee_affairs' => ['names' => ['%موظف%'], 'permission' => 'essentials.view_employees_affairs_requests'],
            'insurance' => ['names' => ['%تأمين%'], 'permission' => 'essentials.crud_insurance_requests'],
            'payroll' => ['names' => ['%رواتب%'], 'permission' => 'essentials.view_payroll_requests'],
            'housing' => ['names' => ['%سكن%'], 'permission' => 'housingmovements.crud_htr_requests'],
            'international_relations' => ['names' => ['%دولي%'], 'permission' => 'internationalrelations.view_ir_requests'],
            'legal' => ['names' => ['%قانوني%'], 'permission' => 'legalaffairs.view_legalaffairs_requests'],
            'sales' => ['names' => ['%مبيعات%'], 'permission' => 'sales.view_sales_requests'],
            'ceo' => ['names' => ['%تنفيذ%'], 'permission' => 'ceomanagment.view_CEO_requests'],
            'general' => ['names' => ['%مجلس%', '%عليا%'], 'permission' => 'generalmanagement.view_president_requests']
        ];

        foreach ($departments as $dept => $info) {
            $deptIds = EssentialsDepartment::where(function ($query) use ($info) {
                foreach ($info['names'] as $name) {
                    $query->orWhere('name', 'like', $name);
                }
            })->pluck('id')->toArray();

            if (in_array($department, $deptIds)) {

                return $info['permission'];
            }
        }

        return null;
    }


    public function getRequest($id)
    {
        $request = UserRequest::with(['requestType', 'related_to_user'])->find($id);

        if ($request) {
            return response()->json([
                'request' => $request,
                'requestType' => $request->requestType,
                'related_to_user' => $request->related_to_user,
            ]);
        } else {
            return response()->json(['error' => 'Request not found'], 404);
        }
    }

    public function updateRequest(Request $request, $id)
    {


        $userRequest = UserRequest::findOrFail($id);


        $startDate = $request->start_date ?? $request->escape_date ?? $request->exit_date;
        $end_date = $request->end_date ?? $request->return_date;
        $today = Carbon::today();
        $type = RequestsType::where('id', $userRequest->request_type_id)->first()->type;

        if ($startDate && $type != 'escapeRequest') {
            $startDateCarbon = Carbon::parse($startDate);
            if ($startDateCarbon->lt($today)) {
                $message = __('request.time_is_gone');
                return redirect()->back()->withErrors([$message]);
            }
            if ($end_date) {
                $endDateCarbon = Carbon::parse($end_date);
                if ($startDateCarbon->gt($endDateCarbon)) {
                    $message = __('request.start_date_after_end_date');
                    return redirect()->back()->withErrors([$message]);
                }
            }
        }

        if ($type == 'cancleContractRequest' && !empty($request->main_reason)) {
            $contract = EssentialsEmployeesContract::where('employee_id', $userRequest->related_to)->firstOrFail();
            if (is_null($contract->wish_id)) {
                $output = [
                    'success' => false,
                    'msg' => __('request.no_wishes_found'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }
            if (now()->diffInMonths($contract->contract_end_date) > 1) {
                $output = [
                    'success' => false,
                    'msg' => __('request.contract_expired'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }
        }

        if ($type == 'leavesAndDepartures' && is_null($request->leaveType)) {
            $output = [
                'success' => false,
                'msg' => __('request.please_select_the_type_of_leave'),
            ];
            return redirect()->back()->withErrors([$output['msg']]);
        }

        if ($type == "leavesAndDepartures") {
            $leaveBalance = UserLeaveBalance::where([
                'user_id' => $userRequest->related_to,
                'essentials_leave_type_id' => $request->leaveType,
            ])->first();

            if (!$leaveBalance || $leaveBalance->amount == 0) {
                $messageKey = !$leaveBalance ? 'this_user_cant_ask_for_leave_request' : 'this_user_has_not_enough_leave_balance';
                $message = __("request.$messageKey");
                DB::rollBack();
                return redirect()->back()->withErrors([$message]);
            } else {
                $startDate = Carbon::parse($startDate);
                $endDate = Carbon::parse($end_date);
                $daysRequested = $startDate->diffInDays($endDate) + 1;

                if ($daysRequested > $leaveBalance->amount) {
                    $message = __("request.this_user_has_not_enough_leave_balance");
                    DB::rollBack();
                    return redirect()->back()->withErrors([$message]);
                }
            }
        }


        $userRequest->start_date = $startDate;
        $userRequest->end_date = $end_date;
        $userRequest->reason = $request->reason;
        $userRequest->note = $request->note;
        $userRequest->essentials_leave_type_id = $request->leaveType;
        $userRequest->escape_time = $request->escape_time;
        $userRequest->installmentsNumber = $request->installmentsNumber;
        $userRequest->monthlyInstallment = $request->monthlyInstallment;
        $userRequest->advSalaryAmount = $request->amount;
        $userRequest->updated_by = auth()->user()->id;
        $userRequest->insurance_classes_id = $request->ins_class;
        $userRequest->baladyCardType = $request->baladyType;
        $userRequest->resCardEditType = $request->resEditType;
        $userRequest->workInjuriesDate = $request->workInjuriesDate;
        $userRequest->contract_main_reason_id = $request->main_reason;
        $userRequest->contract_sub_reason_id = $request->sub_reason;
        $userRequest->visa_number = $request->visa_number;
        $userRequest->atmCardType = $request->atmType;
        $userRequest->authorized_entity = $request->authorized_entity;
        $userRequest->commissioner_info = $request->commissioner_info;
        $userRequest->trip_type = $request->trip_type;
        $userRequest->Take_off_location = $request->Take_off_location;
        $userRequest->destination = $request->destination;
        $userRequest->weight_of_furniture = $request->weight_of_furniture;
        $userRequest->date_of_take_off = $request->date_of_take_off;
        $userRequest->time_of_take_off = $request->time_of_take_off;
        $userRequest->return_date = $request->return_date_of_trip;

        $userRequest->job_title_id = $request->job_title;
        $userRequest->specialization_id = $request->profession;
        $userRequest->nationality_id = $request->nationlity;
        $userRequest->number_of_salary_inquiry = $request->number_of_salary_inquiry;

        $userRequest->sale_project_id = $request->project_name;
        $userRequest->interview_date = $request->interview_date;
        $userRequest->interview_time = $request->interview_time;
        $userRequest->interview_place = $request->interview_place;

        $userRequest->residenceRenewalDuration = $request->residenceRenewalDuration;



        $userRequest->save();


        $output = [
            'success' => 1,
            'msg' => __('request.updated_success'),
        ];
        return redirect()->back()->with('success', $output['msg']);
    }
    public function deleteRequest($id)
    {
        error_log($id);
        try {
            DB::beginTransaction();



            RequestProcess::where('request_id', $id)->delete();
            RequestProcedureTask::where('request_id', $id)->delete();
            RequestAttachment::where('request_id', $id)->delete();
            UserRequest::where('id', $id)->delete();
            DB::commit();


            $output = [
                'success' => true,
                'msg' => __('lang_v1.deleted_success'),
            ];
        } catch (\Exception $e) {
            DB::rollback();
            error_log($e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return $output;
    }


    public function fetchUsersByType(Request $request)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->whereNot('user_type', 'customer')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $type = $request->get('type');
        $requestType = RequestsType::find($type);
        error_log($requestType);
        if (!$requestType) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $for = $requestType->for;
        $user_type = $requestType->user_type ?? null;
        $query = User::query();

        $nationalityIds = EssentialsCountry::where('nationality', 'LIKE', '%سعودي%')->pluck('id');
        if ($user_type) {
            $isCitizen = $user_type === 'citizen';
            $isResident = $user_type === 'resident';

            if ($isCitizen) {
                $query->whereIn('nationality_id', $nationalityIds);
            } elseif ($isResident) {
                $query->whereNotIn('nationality_id', $nationalityIds);
            }
        }

        $userTypes = match ($for) {
            'worker' => ['worker'],
            'employee' => ['employee', 'manager', 'department_head'],
            default => [],
        };

        if ($requestType->type == 'residenceIssue') {
            $users = DB::table('users')->whereNotNull('border_no')
                ->join('companies', 'users.company_id', '=', 'companies.id')
                ->select('users.id', DB::raw("CONCAT(
                    COALESCE(users.first_name, ''), ' ', 
                    COALESCE(users.last_name, ''), ' - ', 
                    COALESCE(users.border_no, ''), ' - ', 
                    COALESCE(companies.name, '')
                ) as full_name"))
                ->where(function ($query) use ($userTypes) {
                    $query->where('users.status', 'active')
                        ->orWhere(function ($subQuery) use ($userTypes) {
                            $subQuery->where('users.status', 'inactive')
                                ->whereIn('users.sub_status', ['vacation', 'escape', 'return_exit']);
                        });
                })
                ->whereIn('users.user_type', $userTypes)->whereIn('users.id', $userIds)
                ->pluck('full_name', 'users.id');
        } else {
            $users = DB::table('users')
                ->join('companies', 'users.company_id', '=', 'companies.id')
                ->select('users.id', DB::raw("CONCAT(
            COALESCE(users.first_name, ''), ' ', 
            COALESCE(users.last_name, ''), ' - ', 
            COALESCE(users.id_proof_number, ''), ' - ', 
            COALESCE(companies.name, '')
        ) as full_name"))
                ->where(function ($query) use ($userTypes) {
                    $query->where('users.status', 'active')
                        ->orWhere(function ($subQuery) use ($userTypes) {
                            $subQuery->where('users.status', 'inactive')
                                ->whereIn('users.sub_status', ['vacation', 'escape', 'return_exit']);
                        });
                })
                ->whereIn('users.user_type', $userTypes)->whereIn('users.id', $userIds)
                ->pluck('full_name', 'users.id');
        }



        // $users = $query->whereIn('user_type', $userTypes)
        //     ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), ' - ', COALESCE(id_proof_number, '')) as full_name"))
        //     ->pluck('full_name', 'id');

        return response()->json(['users' => $users]);
    }
}
