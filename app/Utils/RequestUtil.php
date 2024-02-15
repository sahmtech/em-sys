<?php

namespace App\Utils;

use App\Request as UserRequest;
use App\RequestProcess;
use App\RequestAttachment;
use App\User;
use Carbon\Carbon;
use App\Utils\ModuleUtil;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Modules\CEOManagment\Entities\WkProcedure;
use Modules\CEOManagment\Entities\RequestsType;


use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;


use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Sales\Entities\SalesProject;


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
    public function getRequests($departmentIds, $ownerTypes, $view, $can_change_status, $can_return_request, $can_show_request, $departmentIdsForGeneralManagment = [])
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
        $users = User::whereIn('id', $userIds)->whereIn('user_type', $ownerTypes)->where('status', '!=', 'inactive')->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->pluck('full_name', 'id');
        $saleProjects = SalesProject::all()->pluck('name', 'id');

        $requestsProcess = null;
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')->groupBy('request_id');

        $requestsProcess = UserRequest::select([
            'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.reason',

            'process.id as process_id', 'process.status', 'process.note as note',  'process.procedure_id as procedure_id', 'process.superior_department_id as superior_department_id',

            'wk_procedures.department_id as department_id', 'wk_procedures.can_return','wk_procedures.start as start',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number', 'users.assigned_to',

        ])
            ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                $join->on('requests.id', '=', 'latest_process.request_id');
            })
            ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
            // ->leftJoin('request_processes as process', 'process.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')
            ->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('process.superior_department_id', $departmentIds)
                    ->orWhereIn('process.started_department_id', $departmentIds);
            })


            ->whereIn('requests.related_to', $userIds)->whereNull('process.sub_status')
            ->where('users.status', '!=', 'inactive')->orderBy('requests.id', 'DESC');


        if (request()->ajax()) {

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                    return $allRequestTypes[$row->request_type_id];
                })
                ->editColumn('assigned_to', function ($row) use ($saleProjects) {

                    if ($row->assigned_to) {
                        return $saleProjects[$row->assigned_to];
                    } else {
                        return '';
                    }
                })
                ->editColumn('status', function ($row)  use ($is_admin, $can_change_status, $departmentIds, $departmentIdsForGeneralManagment) {
                    $status = trans('request.' . $row->status);
                    // $procedureStart = WkProcedure::where('id', $row->procedure_id)->first();

                    // if ($procedureStart) {
                    //   if ($procedureStart->start != 1 || ($procedureStart->start == 1 && $procedureStart->request_owner_type == "employee") ) {

                    if ($departmentIdsForGeneralManagment) {


                        if ($row->status == 'pending' && in_array($row->department_id, $departmentIdsForGeneralManagment)) {
                            if ($is_admin || $can_change_status) {
                                $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">' . __($this->statuses[$row->status]['name']) . '</span>';
                                $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                            }
                        }
                    } else {
                        if ($is_admin || $can_change_status) {
                            if ($row->status == 'pending' && (in_array($row->department_id, $departmentIds) || in_array($row->superior_department_id, $departmentIds))) {
                                $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                                    . __($this->statuses[$row->status]['name']) . '</span>';
                                $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                            }
                        }
                    }
                    return $status;
                })
                ->editColumn('can_return', function ($row) use ($is_admin, $can_return_request, $can_show_request,$departmentIds, $departmentIdsForGeneralManagment) {
                    $buttonsHtml = '';
                    if ($departmentIdsForGeneralManagment) {
                        if ($row->can_return == 1 && $row->status == 'pending' && in_array($row->department_id, $departmentIdsForGeneralManagment) && $row->start !='1') {
                            if ($is_admin || $can_return_request) {
                                $buttonsHtml .= '<button class="btn btn-danger btn-sm btn-return" data-request-id="' . $row->process_id . '">' . trans('request.return_the_request') . '</button>';
                            }
                        }
                    } else {
                        if ($row->can_return == 1 && $row->status == 'pending' && in_array($row->department_id, $departmentIds) && $row->start !='1' ) {
                         

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
        return view($view)->with(compact('users', 'requestTypes', 'statuses', 'main_reasons', 'classes', 'leaveTypes'));
    }


    ////// store request /////////////////// 
    public function storeRequest($request, $departmentIds)
    {

        $business_id = request()->session()->get('user.business_id');
        $attachmentPath = null;

        if (isset($request->attachment) && !empty($request->attachment)) {
            $attachmentPath = $request->attachment->store('/requests_attachments');
        }

        if (is_null($request->start_date) && !empty($request->escape_date)) {
            $startDate = $request->escape_date;
        } elseif (is_null($request->start_date) && !empty($request->exit_date)) {
            $startDate = $request->exit_date;
        } else {
            $startDate = $request->startDate;
        }


        if (is_null($request->end_date) && !empty($request->return_date)) {

            $end_date = $request->return_date;
        } else {
            $end_date = $request->end_date;
        }
        if ($request->type == 'cancleContractRequest' && !empty($request->main_reason)) {

            $contract = EssentialsEmployeesContract::where('employee_id', $request->worker_id)->first();

            if (!$contract) {
                $output = [
                    'success' => false,
                    'msg' => __('request.no_contract_found'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }


            if (is_null($contract->wish_id)) {
                $output = [
                    'success' => false,
                    'msg' => __('request.no_wishes_found'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }

            $contractEndDate = Carbon::parse($contract->contract_end_date);
            $todayDate = Carbon::now();

            if ($todayDate->diffInMonths($contractEndDate) > 1) {
                $output = [
                    'success' => false,
                    'msg' => __('request.contract_expired'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }
        }

        $requestTypeFor = RequestsType::where('id', $request->type)->first()->for;
        $requestType = RequestsType::where('id', $request->type)->first()->type;

        $success = 1;


        foreach ($request->user_id as $userId) {

            if ($userId !== null) {
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


                    if ($request->type == "exitRequest") {
                        $startDate = DB::table('essentials_employees_contracts')->where('employee_id', $userId)->first()->contract_end_date ?? null;
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


                    if (isset($request->attachment) && !empty($request->attachment)) {
                        $attach = RequestAttachment::create([
                            'request_id' => $Request->id,
                            'file_path' => $attachmentPath,

                        ]);
                    }
                    if ($Request) {
                        $process = null;
                        if ($userType == 'worker') {
                            $procedure = WkProcedure::where('business_id', $business_id)
                                ->where('request_type_id', $request->type)->where('start', 1)->whereIn('department_id', $departmentIds)->first();


                            $process = RequestProcess::create([
                                'started_department_id' => $procedure->department_id,
                                'request_id' => $Request->id,
                                'procedure_id' => $procedure ? $procedure->id : null,
                                'status' => 'pending'
                            ]);

                            //     $nextProcedure = WkProcedure::where('business_id', $business_id)->where('type', $request->type)
                            //     ->where('department_id', $procedure->next_department_id)->first()->id;

                            //     RequestProcess::create([
                            //     'request_id' => $Request->id,
                            //     'procedure_id' => $nextProcedure ? $nextProcedure : null,
                            //     'status' => 'pending',
                            //     'reason' => null,
                            //     'status_note' => null,
                            // ]);
                        } else {
                            $department_id = User::where('id', $userId)->first()->essentials_department_id;
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
                        }


                        if (!$process) {
                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                            // $success = 0;
                        }
                    } else {

                        $success = 0;
                    }
                }
            }
        }

        if ($success) {
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

            // $first_step = RequestProcess::where('request_id', $request->request_id)->where('status', 'pending')->where('sub_status', null)->first();

            //$requestProcess = RequestProcess::where('request_id', $request->request_id)->where('status', 'pending')->where('sub_status', null)->get()[1];

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
                    // $first_step->status = 'approved';
                    // $first_step->save();
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
                    }
                }
            }
            if ($request->status  == 'rejected') {
                $requestProcess->request->status = 'rejected';
                // $first_step->status = 'rejected';
                // $first_step->save();
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

                    $newRequestProcess = new RequestProcess();
                    $newRequestProcess->started_department_id = $process->started_department_id;
                    $newRequestProcess->request_id = $process->request_id;
                    $newRequestProcess->procedure_id = $procedure->id;
                    $newRequestProcess->status = 'pending';
                    $newRequestProcess->save();
                } else {

                    $nextDepartmentId = $procedure->next_department_id;

                    $nextProcedure = WkProcedure::where('department_id', $nextDepartmentId)
                        ->where('request_type_id', $procedure->request_type_id)
                        ->first();
                    if ($nextProcedure) {
                        $newRequestProcess = new RequestProcess();
                        $newRequestProcess->started_department_id = $process->started_department_id;
                        $newRequestProcess->request_id = $process->request_id;
                        $newRequestProcess->procedure_id = $nextProcedure->id;
                        $newRequestProcess->status = 'pending';
                        $newRequestProcess->save();
                    } else {
                        $process->request->status = 'approved';
                        $process->request->save();
                    }
                }
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
        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'status' => trans("request.{$request->status}"),
            'type' => trans("request.{$type}"),
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
            'status' => $isDone?'approved':'',
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
            $processInfo = [
                'id' => $process->id,
                'status' => trans("request.{$process->status}"),
                'procedure_id' => $process->procedure_id,
                'is_returned' => $process->is_returned,
                'updated_by' =>  $this->getFullName($process->updated_by),
                'reason' => $process->reason,
                'status_note' => $process->note,
                'department' => [
                    'id' => $process->procedure->department->id,
                    'name' => $process->procedure->department->name,
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
        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'status' => trans("request.{$request->status}"),
            'type' => trans("request.{$type}"),
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ];
        $workflow = [];

        $firstStep = RequestProcess::where('id', $request->process[0]->id)->first();

        $firstProcedure = WkProcedure::where('request_type_id', $request->request_type_id)->first();

        $workflow[] = [
            'id' => null,
            'process_id' =>  $firstStep->id,
            'status' => $firstStep->status,
            'department' => optional(DB::table('essentials_departments')->where('id', $firstStep->superior_department_id)->first())->name,
            'next_department' => optional(DB::table('essentials_departments')->where('id', $firstProcedure->department_id)->first())->name,
        ];
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
            'status' => $isDone?'approved':'',
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

        $departments = EssentialsDepartment::all()->pluck('name', 'id');
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

    private function generateRequestNo($request_type_id)
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
        return optional($request->process->where('procedure_id', $step->id)->first())->id;
    }

    private function getProcessStatusForStep($request, $step)
    {
        return optional($request->process->where('procedure_id', $step->id)->first())->status;
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
}
