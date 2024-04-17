<?php

namespace App\Utils;

use App\Request as UserRequest;
use App\RequestProcess;
use App\RequestAttachment;
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


use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Sales\Entities\SalesProject;

use Modules\CEOManagment\Entities\Task;



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
    public function getRequests($departmentIds, $ownerTypes, $view, $can_change_status, $can_return_request, $can_show_request, $departmentIdsForGeneralManagment = [], $isFollowup = false)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $allRequestTypes = RequestsType::pluck('type', 'id');

        $requestTypeIds = WkProcedure::distinct()
            ->with('request_type')
            ->whereIn('department_id', $departmentIds)
            ->whereIn('request_owner_type', $ownerTypes)
            ->where('start', '1')
            ->pluck('request_type_id')
            ->toArray();

        $requestTypes = RequestsType::whereIn('id', $requestTypeIds)
            ->get()
            ->mapWithKeys(function ($requestType) {
                return [$requestType->id => $requestType->type];
            })
            ->unique()
            ->toArray();


        $statuses = $this->statuses;
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->whereIn('employee_type', $ownerTypes)->pluck('reason', 'id');

        if ($isFollowup) {
            $is_manager = User::find(auth()->user()->id)->user_type == 'manager';
            if (!($is_admin || $is_manager)) {
                $followupUserAccessProject = FollowupUserAccessProject::where('user_id',  auth()->user()->id)->pluck('sales_project_id');
                $worker_ids = User::whereIn('id', $userIds)->whereIn('assigned_to',  $followupUserAccessProject)->pluck('id')->toArray();
                $userIds = array_intersect($userIds, $worker_ids);
            }
        }
        $users = User::whereIn('id', $userIds)->whereIn('user_type', $ownerTypes)->where('status', '!=', 'inactive')->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->pluck('full_name', 'id');

        $saleProjects = SalesProject::all()->pluck('name', 'id');

        $requestsProcess = null;
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')->whereNull('sub_status')->groupBy('request_id');

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
                    ->where('request_procedure_tasks.request_id', '=', 'requests.id');
            })
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')
            ->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('process.superior_department_id', $departmentIds)
                    ->orWhereIn('process.started_department_id', $departmentIds);
            })


            ->whereIn('requests.related_to', $userIds)
            ->where('users.status', '!=', 'inactive')->groupBy('requests.id');

        $tasksDetails = [];
        if ($requestsProcess) {
            $requests = $requestsProcess->get();


            foreach ($requests as $request) {
                $tasksDetails = DB::table('request_procedure_tasks')
                    ->join('procedure_tasks', 'procedure_tasks.id', '=', 'request_procedure_tasks.procedure_task_id')
                    ->join('tasks', 'tasks.id', '=', 'procedure_tasks.task_id')
                    ->where('request_procedure_tasks.request_id', $request->id)
                    ->select('tasks.description', 'request_procedure_tasks.id', 'tasks.link', 'request_procedure_tasks.isDone')
                    ->get();


                $request->tasksDetails = $tasksDetails;
            }
        }

        if (request()->ajax()) {


            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                    if ($row->request_type_id) {
                        return $allRequestTypes[$row->request_type_id];
                    }
                })
                ->editColumn('assigned_to', function ($row) use ($saleProjects) {
                    if ($row->assigned_to) {
                        return $saleProjects[$row->assigned_to];
                    } else {
                        return '';
                    }
                })

                ->editColumn('status', function ($row) use ($is_admin, $can_change_status, $departmentIds, $tasksDetails, $departmentIdsForGeneralManagment, $statuses) {
                    if ($row->status) {

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
                            if ($tasksDetails) {

                                $status = '<ul style="list-style-type:none; padding-left: 0;">';


                                foreach ($tasksDetails as $taskDetail) {


                                    $checkmark = $taskDetail->isDone ? '&#9989;' : '';

                                    $taskLink = $taskDetail->link;

                                    $status .= "<li><label>";
                                    if ($taskDetail->isDone) {


                                        $status .= "<a href='{$taskLink}' target='_blank' style='color: green; font-size: large; text-decoration: none;'>$checkmark</a>";
                                    } else {

                                        $status .= "<input type='checkbox' disabled>";
                                    }
                                    $status .= " <a href='{$taskLink}' target='_blank'>{$taskDetail->description}</a></label></li>";
                                }

                                $status .= '</ul>';
                            }
                            if ($departmentIdsForGeneralManagment) {
                                if ($row->status == 'pending' && in_array($row->department_id, $departmentIdsForGeneralManagment)) {
                                    if ($tasksDetails) {
                                        $status = '<ul style="list-style-type:none; padding-left: 0;">';

                                        foreach ($tasksDetails as $taskDetail) {
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

                                if ($tasksDetails) {
                                    $status = '<ul style="list-style-type:none; padding-left: 0;">';

                                    foreach ($tasksDetails as $taskDetail) {

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
                        if ($row->can_return == 1 && $row->status == 'pending' && in_array($row->department_id, $departmentIdsForGeneralManagment) && $row->start != '1') {
                            if ($is_admin || $can_return_request) {
                                $buttonsHtml .= '<button class="btn btn-danger btn-sm btn-return" data-request-id="' . $row->process_id . '">' . trans('request.return_the_request') . '</button>';
                            }
                        }
                    } else {
                        if ($row->can_return == 1 && $row->status == 'pending' && in_array($row->department_id, $departmentIds) && $row->start != '1') {


                            if ($is_admin || $can_return_request) {
                                $buttonsHtml .= '<button class="btn btn-danger btn-sm btn-return" data-request-id="' . $row->process_id . '">' . trans('request.return_the_request') . '</button>';
                            }
                        }
                    }
                    if ($is_admin || $can_show_request) {
                        $buttonsHtml .= '<button class="btn btn-primary btn-sm btn-view-request" data-request-id="' . $row->id . '">' . trans('request.view_request') . '</button>';
                    }

                    return $buttonsHtml;
                })

                ->rawColumns(['status', 'request_type_id', 'can_return', 'assigned_to'])


                ->make(true);
        }
        return view($view)->with(compact('users', 'requestTypes', 'statuses', 'main_reasons', 'classes', 'saleProjects', 'leaveTypes'));
    }


    ////// store request /////////////////// 
    public function storeRequest($request, $departmentIds)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $attachmentPath = $request->attachment ? $request->attachment->store('/requests_attachments') : null;
            $startDate = $request->start_date ?? $request->escape_date ?? $request->exit_date;
            $end_date = $request->end_date ?? $request->return_date;
            $today = Carbon::today();

            if ($startDate) {
                $startDateCarbon = Carbon::parse($startDate);
                if ($startDateCarbon->lt($today)) {
                    $message = __('request.time_is_gone');
                    return redirect()->back()->withErrors([$message]);
                }
                if ($end_date) {

                    $endDateCarbon = Carbon::parse($end_date);
                    error_log($endDateCarbon);
                    if ($startDateCarbon->gt($endDateCarbon)) {
                        $message = __('request.start_date_after_end_date');
                        return redirect()->back()->withErrors([$message]);
                    }
                }
            }

            $type = RequestsType::where('id', $request->type)->first()->type;

            if ($type == 'cancleContractRequest' && !empty($request->main_reason)) {

                $contract = EssentialsEmployeesContract::where('employee_id', $request->worker_id)->firstOrFail();
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

                    $business_id = User::where('id', $userId)->first()->business_id;
                    $userType = User::where('id', $userId)->first()->user_type;


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
                                ->where('request_type_id', $request->type)->where('start', 1)->whereIn('department_id', $departmentIds)->first();


                            if ($createdBy_type == 'manager' || $createdBy_type == 'admin') {
                                $nextProcedure = WkProcedure::where('business_id', $business_id)->where('request_type_id', $request->type)
                                    ->where('department_id', $procedure->next_department_id)->first();


                                $process =   RequestProcess::create([
                                    'started_department_id' => $departmentIds[0],
                                    'request_id' => $Request->id,
                                    'procedure_id' => $nextProcedure ? $nextProcedure->id : null,
                                    'status' => 'pending',

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


                                $process = RequestProcess::create([
                                    'started_department_id' => $departmentIds[0],
                                    'request_id' => $Request->id,
                                    'procedure_id' => $procedure ? $procedure->id : null,
                                    'status' => 'pending',

                                ]);
                            }
                        } else {
                            $department_id = User::where('id', $userId)->first()->essentials_department_id;



                            if ($createdBy_type == 'employee' || ($createdBy_type == 'manager' &&  $createdBy_department !=  $department_id) || ($createdBy_type == 'admin' && !(in_array($department_id, $departmentIds)))) {

                                if ($department_id) {
                                    $process = RequestProcess::create([
                                        'started_department_id' => $departmentIds[0],
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
                            } else if (($createdBy_type == 'admin' && (in_array($department_id, $departmentIds))) || ($createdBy_type == 'manager' && (in_array($createdBy_department, $departmentIds)))) {

                                $procedure = WkProcedure::Where('request_type_id', $request->type)->where('start', '1')->first();
                                if (!$procedure) {
                                    $output = [
                                        'success' => false,
                                        'msg' => __('request.no_procedure_found'),
                                    ];
                                    return redirect()->back()->withErrors([$output['msg']]);
                                }
                                if ((in_array($procedure->department_id, $departmentIds))) {
                                    $nextProcedure = WkProcedure::Where('request_type_id', $request->type)->where('department_id', $procedure->next_department_id)->first();
                                    if ($nextProcedure) {
                                        $process = RequestProcess::create([
                                            'started_department_id' => $departmentIds[0],
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
                }
            }

            if ($success) {
                // $this->makeToDo($Request, $business_id);
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

        // $request->validate([
        //     'attachment' => 'required|mimes:pdf,doc,docx|max:2048',
        // ]);
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $attachmentPath = $attachment->store('/requests_attachments');


            RequestAttachment::create([
                'request_id' => $requestId,
                'file_path' => $attachmentPath,

            ]);
            $output = [
                'success' => true,
                'msg' => __('messages.saved_successfully'),
            ];
        } else {
            $output = [
                'success' => false,
                'msg' => __('request.please_add_afile_before_saved'),
            ];
        }
        return redirect()->back()->with('status', $output);

        // return redirect()->back()->with('success', trans('messages.saved_successfully'));
    }

    public function makeToDo($request, $business_id)
    {


        $created_by = $request->created_by;

        $request_type = RequestsType::where('id', $request->request_type_id)->first()->type;
        $input['business_id'] = $business_id;
        $input['company_id'] = $business_id;
        $input['created_by'] = $created_by;
        $input['task'] = "طلب جديد";
        $input['date'] = Carbon::now();
        $input['priority'] = 'high';
        $input['description'] = $request_type;
        $input['status'] = !empty($input['status']) ? $input['status'] : 'new';

        $process = RequestProcess::where('request_id', $request->id)->latest()->first();
        $users = [];
        if ($process->superior_department_id) {
            $users = User::where('essentials_department_id', $process->superior_department_id)->get();
        } else {
            $procedure = $process->procedure_id;
            $department_id = WKProcedure::where('id', $procedure)->first()->department_id;

            $users = User::where('essentials_department_id', $department_id)->get();
        }


        $input['task_id'] = $request->request_no;

        $to_dos = ToDo::create($input);

        $to_dos->users()->sync($users);
        if ($users->isNotEmpty()) {

            \Notification::send($users, new NewTaskNotification($to_dos));
        }
    }



    ////// change request status /////////////////// 
    public function changeRequestStatus(Request $request)
    {
        $first_step = RequestProcess::where('request_id', $request->request_id)->where('status', 'pending')->where('sub_status', null)->first();
        if ($first_step->procedure_id != Null) {
            return $this->changeRequestStatusAfterProcedure($request);
        } elseif ($first_step->superior_department_id != Null) {

            return $this->changeRequestStatusBeforProcedure($request);
        }
    }

    private function changeRequestStatusAfterProcedure($request)
    {
        try {

            $requestProcess = RequestProcess::where('request_id', $request->request_id)->where('status', 'pending')->where('sub_status', null)->first();
            $procedure = WkProcedure::where('id', $requestProcess->procedure_id)->first()->can_reject;


            if ($procedure == 0 && $request->status == 'rejected') {
                $output = [
                    'success' => false,
                    'msg' => __('request.this_department_cant_reject_this_request'),
                ];
                return $output;
            }

            $requestProcess->status =  $request->status;
            $requestProcess->reason =  $request->reason ?? null;
            $requestProcess->note = $request->note  ?? null;
            $requestProcess->updated_by = auth()->user()->id;

            $requestProcess->save();

            if ($request->status == 'approved') {
                $procedure = WkProcedure::find($requestProcess->procedure_id);


                if ($procedure && $procedure->end == 1) {
                    $requestProcess->request->status = 'approved';
                    $requestProcess->request->save();


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
                    $business_id = request()->session()->get('user.business_id');
                    $userRequest = UserRequest::where('id', $requestProcess->request_id)->first();
                    //  $this->makeToDo($userRequest, $business_id);
                }
            }
            if ($request->status  == 'rejected') {
                $requestProcess->request->status = 'rejected';
                $requestProcess->request->save();
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

    private function changeRequestStatusBeforProcedure($request)
    {


        try {
            $process = RequestProcess::where('request_id', $request->request_id)->where('status', 'pending')->whereNull('sub_status')->first();
            $userRequest = UserRequest::where('id', $request->request_id)->first();

            $currentDepartment = $process->superior_department_id;
            $process->status = $request->status;
            $process->reason = $request->reason ?? null;
            $process->note = $request->note  ?? null;
            $process->updated_by = auth()->user()->id;
            $process->save();

            if ($request->status  == 'rejected') {
                $process->request->status = 'rejected';
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
                        ->where('request_type_id', $procedure->request_type_id)
                        ->first();
                    if ($nextProcedure) {
                        $action_type = $nextProcedure->action_type;
                        $newRequestProcess = new RequestProcess();
                        $newRequestProcess->started_department_id = $process->started_department_id;
                        $newRequestProcess->request_id = $process->request_id;
                        $newRequestProcess->procedure_id = $nextProcedure->id;
                        $newRequestProcess->status = 'pending';
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
                $business_id = request()->session()->get('user.business_id');

                $userRequest = UserRequest::where('id',  $process->request_id)->first();
                //  $this->makeToDo($userRequest, $business_id);
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



    ////// view request /////////////////// 
    public function viewRequest($id)
    {

        $request = UserRequest::with([
            'related_to_user', 'created_by_user', 'process.procedure.department', 'attachments'
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
        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'status' => trans("request.{$request->status}"),
            'type' => trans("request.{$type}"),
            'started_depatment' => [
                'id' => $firstStep->started_department_id,
                'name' => $departments[$firstStep->started_department_id],
            ],
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ];
        $workflow = [];
        $currentStep = WkProcedure::where('id', $request->process[0]->procedure_id)->first();

        while ($currentStep && !$currentStep->end) {

            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $currentStep->next_department_id)->first())->name,
            ];

            $currentStep = WkProcedure::where('request_type_id', $request->request_type_id)
                ->where('department_id', $currentStep->next_department_id)
                ->first();
        }

        if ($currentStep && $currentStep->end == 1) {
            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => null,
            ];
        };
        $isDone = UserRequest::where('id', $request->id)->first()->is_done;
        $workflow[] = [
            'id' => null,
            'status' => $isDone ? 'approved' : '',
            'department' => $isDone ? trans('request.done') : trans('request.not_yet_done'),

        ];

        $attachments = null;
        if ($request->attachments) {

            $attachments = $request->attachments->map(function ($attachments) {
                return [
                    'request_id' => $attachments->request_id,
                    'file_path' => $attachments->file_path,
                    'created_at' => $attachments->created_at,
                ];
            });
        }


        $userInfo = [
            'worker_id' => $request->related_to_user->id,
            'user_type' => trans("request.{$request->related_to_user->user_type}"),
            'nationality' => optional(DB::table('essentials_countries')->where('id', $request->related_to_user->nationality_id)->first())->nationality,
            'assigned_to' =>  $this->getContactLocation($request->related_to_user->assigned_to),
            'worker_full_name' => $request->related_to_user->first_name . ' ' . $request->related_to_user->last_name,
            'id_proof_number' => $request->related_to_user->id_proof_number,
            'contract_end_date' => optional(DB::table('essentials_employees_contracts')->where('employee_id', $request->related_to_user->id)->first())->contract_end_date,
            'eqama_end_date' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->related_to_user->id)->where('type', 'residence_permit')->first())->expiration_date,
            'passport_number' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->related_to_user->id)->where('type', 'passport')->first())->number,

        ];

        $createdUserInfo = [
            'created_user_id' => $request->created_by_user->id,
            'user_type' => $request->created_by_user->user_type,
            'nationality_id' => $request->created_by_user->nationality_id,
            'created_user_full_name' => $request->created_by_user->first_name . ' ' . $request->created_by_user->last_name,
            'id_proof_number' => $request->created_by_user->id_proof_number,

        ];

        // $followupProcesses = [];
        // foreach ($request->process as $process) {
        //     $processInfo = [
        //         'id' => $process->id,
        //         'status' => trans("request.{$process->status}"),
        //         'procedure_id' => $process->procedure_id,
        //         'is_returned' => $process->is_returned,
        //         'updated_by' =>  $this->getFullName($process->updated_by),
        //         'reason' => $process->reason,
        //         'status_note' => $process->note,
        //         'department' => [
        //             'id' => $process->procedure->department->id,
        //             'name' => $process->procedure->department->name,
        //         ],

        //     ];
        //     $followupProcesses[] = $processInfo;
        // }
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
                'updated_by' =>  $this->getFullName($process->updated_by),
                'reason' => $process->reason,
                'status_note' => $process->note,
                'department' => [
                    'id' => optional($process->procedure->department)->id,
                    'name' => optional($process->procedure->department)->name,
                ],
            ];

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

    private function viewEmployeeRequest($request)
    {

        $allRequestTypes = RequestsType::pluck('type', 'id');
        $type = $allRequestTypes[$request->request_type_id];
        $firstStep = RequestProcess::where('id', $request->process[0]->id)->first();
        $departments = EssentialsDepartment::all()->pluck('name', 'id');

        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'started_depatment' => [
                'id' => $firstStep->started_department_id,
                'name' => $departments[$firstStep->started_department_id],
            ],

            'status' => trans("request.{$request->status}"),
            'type' => trans("request.{$type}"),
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ];
        $workflow = [];

        $firstStep = RequestProcess::where('id', $request->process[0]->id)->first();

        $firstProcedure = WkProcedure::where('request_type_id', $request->request_type_id)->first();
        if ($firstStep->superior_department_id) {
            $workflow[] = [
                'id' => null,
                'process_id' =>  $firstStep->id,
                'status' => $firstStep->status,
                'department' => optional(DB::table('essentials_departments')->where('id', $firstStep->superior_department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure->department_id)->first())->name,
            ];
        }


        if ($firstStep->superior_department_id == $firstProcedure->department_id) {
            $firstProcedures = WkProcedure::where('request_type_id', $request->request_type_id)->get();

            if (count($firstProcedures) <= 1) {
                $firstProcedure = null;
            } else {
                $firstProcedure = $firstProcedures[1];
            }
        }


        while ($firstProcedure && !$firstProcedure->end) {

            $workflow[] = [
                'id' => $firstProcedure->id,
                'process_id' => $this->getProcessIdForStep($request, $firstProcedure),
                'status' => $this->getProcessStatusForStep($request, $firstProcedure),
                'department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure->department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure->next_department_id)->first())->name,
            ];

            $firstProcedure = WkProcedure::where('request_type_id', $request->request_type_id)
                ->where('department_id', $firstProcedure->next_department_id)
                ->first();
        }



        if ($firstProcedure && $firstProcedure->end == 1) {
            $workflow[] = [
                'id' => $firstProcedure->id,
                'process_id' => $this->getProcessIdForStep($request, $firstProcedure),
                'status' => $this->getProcessStatusForStep($request, $firstProcedure),
                'department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure->department_id)->first())->name,
                'next_department' => null,
            ];
        };
        $isDone = UserRequest::where('id', $request->id)->first()->is_done;
        $workflow[] = [
            'id' => null,
            'status' => $isDone ? 'approved' : '',
            'department' => $isDone ? trans('request.done') : trans('request.not_yet_done'),

        ];

        $attachments = null;
        if ($request->attachments) {

            $attachments = $request->attachments->map(function ($attachments) {
                return [
                    'request_id' => $attachments->request_id,
                    'file_path' => $attachments->file_path,
                    'created_at' => $attachments->created_at,
                ];
            });
        }


        $userInfo = [
            'worker_id' => $request->related_to_user->id,
            'user_type' => trans("request.{$request->related_to_user->user_type}"),
            'nationality' => optional(DB::table('essentials_countries')->where('id', $request->related_to_user->nationality_id)->first())->nationality,
            'assigned_to' =>  $this->getContactLocation($request->related_to_user->assigned_to),
            'worker_full_name' => $request->related_to_user->first_name . ' ' . $request->related_to_user->last_name,
            'id_proof_number' => $request->related_to_user->id_proof_number,
            'contract_end_date' => optional(DB::table('essentials_employees_contracts')->where('employee_id', $request->related_to_user->id)->first())->contract_end_date,
            'eqama_end_date' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->related_to_user->id)->where('type', 'residence_permit')->first())->expiration_date,
            'passport_number' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->related_to_user->id)->where('type', 'passport')->first())->number,

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
                    'updated_by' =>  $this->getFullName($process->updated_by),
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
                    'updated_by' =>  $this->getFullName($process->updated_by),
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

            $requestId = $request->input('requestId'); // id of process 

            $requestProcess = RequestProcess::find($requestId);
            $userRequest = $requestProcess->request_id;
            $firstStep =  RequestProcess::where('request_id', $userRequest)->first();

            if ($requestProcess) {


                $procedure = WkProcedure::find($requestProcess->procedure_id);

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
                            'updated_by' => auth()->user()->id,
                            'note' => __('request.returned_by') . " " . $nameDepartment,

                        ]);

                        //  return response()->json(['success' => true, 'msg' => 'Request returned successfully']);
                        $output = [
                            'success' => true,
                            'msg' => __('request.returned_successfully'),
                        ];
                    } else {
                        if ($procedure->request_owner_type == 'employee') {


                            $requestProcess->update([
                                'procedure_id' => null,
                                'superior_department_id' => $firstStep->superior_department_id,
                                'status' => 'pending',
                                'is_returned' => 1,
                                'updated_by' => auth()->user()->id,
                                'note' => __('request.returned_by') . " " . $nameDepartment,

                            ]);
                            $output = [
                                'success' => true,
                                'msg' => __('request.returned_successfully'),
                            ];
                        } else {
                            $output = [
                                'success' => false,
                                'msg' => __('request.there_is_no_department_to_return_for'),
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }



    ////// operational functions /////////////////// 
    private function getTypePrefix($request_type_id)
    {
        $prefix = RequestsType::where('id', $request_type_id)->first()->prefix;
        return $prefix;
    }

    protected function validateLeaveRequirements($request, $userId, $count)
    {
        error_log($count);
        error_log($userId);
        error_log($request);



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
        error_log($anotherTask);
        if (!$anotherTask) {
            error_log('11111111111111111111111');
            $requestProcess = RequestProcess::where('request_id',  $request_id)->where('status', 'pending')->where('sub_status', null)->first();

            $requestProcess->status = 'approved';
            $requestProcess->updated_by = auth()->user()->id;

            $requestProcess->save();


            $procedure = WkProcedure::find($requestProcess->procedure_id);


            if ($procedure && $procedure->end == 1) {
                $requestProcess->request->status = 'approved';
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
        }

        return response()->json(['success' => true]);
    }


    public function test()  //test for requests process escalations
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

    public function viewRequestsOperations() //only for workcard operations
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $business_id = request()->session()->get('user.business_id');
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%حكومية%')
            ->pluck('id')
            ->toArray();

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $allRequestTypes = RequestsType::pluck('type', 'id');
        if ($is_admin  || auth()->user()->can('essentials.view_requests_operations')) {

            $types = RequestsType::whereIn('type', ['exitRequest', 'returnRequest', 'escapeRequest'])->pluck('id')->toArray();

            $procedures = WkProcedure::whereIn('department_id', $departmentIds)->pluck('id')->toArray();

            $tasks = Task::whereIn('request_type_id', $types)->pluck('id')->toArray();

            $procedure_tasks = ProcedureTask::whereIn('task_id', $tasks)->whereIn('procedure_id', $procedures)->pluck('id')->toArray();

            $requests = UserRequest::whereIn('request_type_id', $types)->pluck('id')->toArray();

            //   $request_procedure_tasks = RequestProcedureTask::whereIn('request_id', $requests)->whereIn('procedure_task_id', $procedure_tasks)->where('isDone', 0)->pluck('request_id')->toArray();

            $requestsProcess =  RequestProcedureTask::whereIn('request_id', $requests)->whereIn('procedure_task_id', $procedure_tasks)->where('isDone', 0)->join('requests', 'requests.id', 'request_procedure_tasks.request_id')->select([
                'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.status', 'requests.note as note',

                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number',

                'users.status as userStatus', 'request_procedure_tasks.id as request_procedure_task',

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
                    })
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
            }
            if ($type == 'returnRequest') {
                $operation_type = 'return_visa';
            }
            if ($type == 'escapeRequest') {
                $operation_type = 'absent_report';
            }


            $user = User::find($userRequest->related_to);
            if (!$user) {
                return ['success' => false, 'msg' => __('messages.user_not_found')];
            }
            $user->update([
                'status' => 'inactive',
                'allow_login' => '0'
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

            DB::table('essentails_employee_operations')->insert([
                'operation_type' => $operation_type,
                'employee_id' => $userRequest->related_to,
                'start_date' => $userRequest->start_date,
                'end_date' =>  $userRequest->end_date,
            ]);
            $request_procedure_task->update([
                'isDone' => '1'
            ]);

            $requestProcess = RequestProcess::where('request_id', $userRequest->id)->where('status', 'pending')->where('sub_status', null)->first();
            // $procedure = WkProcedure::where('id', $requestProcess->procedure_id)->first()->can_reject;


            // if ($procedure == 0 && $request->status == 'rejected') {
            //     $output = [
            //         'success' => false,
            //         'msg' => __('request.this_department_cant_reject_this_request'),
            //     ];
            //     return $output;
            // }

            $requestProcess->status = 'approved';
            $requestProcess->updated_by = auth()->user()->id;

            $requestProcess->save();


            $procedure = WkProcedure::find($requestProcess->procedure_id);


            if ($procedure && $procedure->end == 1) {
                $requestProcess->request->status = 'approved';
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
}