<?php

namespace Modules\InternationalRelations\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\User;
use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleProject;
use App\Business;
use Modules\Sales\Entities\SalesProject;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use App\ContactLocation;
use Modules\Essentials\Entities\EssentialsInsuranceClass;

class IrRequestController extends Controller
{
    protected $moduleUtil;
    protected $statuses;
    protected $statuses2;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_internationalrelations_requests_change_status = auth()->user()->can('internationalrelations.internationalrelations_requests_change_status');

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%دولي%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_internationalrelations_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $requestTypes = EssentialsWkProcedure::whereIn('department_id', $departmentIds)
            ->where('start', '1')
            ->pluck('type')
            ->mapWithKeys(function ($type) {
                return [$type => __("essentials::lang.$type")];
            })->toArray();

        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
        $users = User::whereIn('id', $userIds)->where('user_type', 'worker')->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->pluck('full_name', 'id');
        $statuses = $this->statuses;

        $requestsProcess = null;

        $requestsProcess = FollowupWorkerRequest::select([
            'followup_worker_requests.request_no','followup_worker_requests_process.id as process_id','followup_worker_requests.id',
            'followup_worker_requests.type as type','followup_worker_requests.created_at','followup_worker_requests_process.status',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            'followup_worker_requests_process.status_note as note','followup_worker_requests.reason','essentials_wk_procedures.department_id as department_id',
            'users.id_proof_number','essentials_wk_procedures.can_return','users.assigned_to','followup_worker_requests_process.procedure_id as procedure_id',
        ])
        ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
        ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
        ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->whereIn('department_id', $departmentIds)
        ->whereIn('followup_worker_requests.worker_id', $userIds)->where('followup_worker_requests_process.sub_status', null);




        if (request()->ajax()) {
            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('status', function ($row)  use ($is_admin, $can_internationalrelations_requests_change_status) {
                    $status = '';
                    $procedureStart=EssentialsWkProcedure::where('id',$row->procedure_id)->first();
                    if($procedureStart->start != 1){
                        if ($is_admin || $can_internationalrelations_requests_change_status) {
                        if ($row->status == 'pending') {
                            $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                                . __($this->statuses[$row->status]['name']) . '</span>';
                            $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                        } elseif (in_array($row->status, ['approved', 'rejected'])) {
                            $status = trans('essentials::lang.' . $row->status);
                        }
                    } else {
                        $status = trans('essentials::lang.' . $row->status);
                    }
                }
                else {
                    $status = trans('essentials::lang.' . $row->status);
                }
                        
                    return $status;
                })

                ->rawColumns(['status'])


                ->make(true);
        }



        return view('internationalrelations::requests.allRequest')->with(compact('users','requestTypes', 'statuses', 'main_reasons', 'classes', 'leaveTypes'));
    }

    public function store(Request $request)
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
                    'msg' => __('essentials::lang.no_contract_found'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }


            if (is_null($contract->wish_id)) {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.no_wishes_found'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }

            $contractEndDate = Carbon::parse($contract->contract_end_date);
            $todayDate = Carbon::now();

            if ($todayDate->diffInMonths($contractEndDate) > 1) {
                $output = [
                    'success' => false,
                    'msg' => __('essentials::lang.contract_expired'),
                ];
                return redirect()->back()->withErrors([$output['msg']]);
            }
        }

        $success = 1;
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%دولي%')
        ->pluck('id')->toArray();
        foreach ($request->worker_id as $workerId) {

            if ($workerId !== null) {
                $business_id = User::where('id', $workerId)->first()->business_id;

                if ($request->type == "exitRequest") {
                    $startDate = DB::table('essentials_employees_contracts')->where('employee_id', $workerId)->first()->contract_end_date ?? null;
                }

                $workerRequest = new FollowupWorkerRequest;

                $workerRequest->request_no = $this->generateRequestNo($request->type);
                $workerRequest->worker_id = $workerId;
                $workerRequest->type = $request->type;
                $workerRequest->start_date = $startDate;
                $workerRequest->end_date = $end_date;
                $workerRequest->reason = $request->reason;
                $workerRequest->note = $request->note;
                $workerRequest->attachment = $attachmentPath;
                $workerRequest->essentials_leave_type_id = $request->leaveType;
                $workerRequest->escape_time = $request->escape_time;
                $workerRequest->installmentsNumber = $request->installmentsNumber;
                $workerRequest->monthlyInstallment = $request->monthlyInstallment;
                $workerRequest->advSalaryAmount = $request->amount;
                $workerRequest->updated_by = auth()->user()->id;
                $workerRequest->insurance_classes_id = $request->ins_class;
                $workerRequest->baladyCardType = $request->baladyType;
                $workerRequest->resCardEditType = $request->resEditType;
                $workerRequest->workInjuriesDate = $request->workInjuriesDate;
                $workerRequest->contract_main_reason_id = $request->main_reason;
                $workerRequest->contract_sub_reason_id = $request->sub_reason;
                $workerRequest->visa_number = $request->visa_number;
                $workerRequest->atmCardType = $request->atmType;
                $workerRequest->save();

                if ($workerRequest) {
                    $procedure =EssentialsWkProcedure::where('business_id', $business_id)
                    ->where('type', $request->type)->where('start', 1)->whereIn('department_id', $departmentIds)->first();
                 

                        $process = FollowupWorkerRequestProcess::create([
                            'worker_request_id' => $workerRequest->id,
                            'procedure_id' => $procedure ? $procedure->id : null,
                            'status' => 'pending',
                            'reason' => null,
                            'status_note' => null,
                        ]);

                        if (!$process) {

                            $workerRequest->delete();

                            $success = 0;
                        }
                    
                    $nextProcedure=EssentialsWkProcedure::where('business_id', $business_id)->where('type', $request->type)
                    ->where('department_id',$procedure->next_department_id)->first()->id;

                    FollowupWorkerRequestProcess::create([
                        'worker_request_id' => $workerRequest->id,
                        'procedure_id' => $nextProcedure ? $nextProcedure : null,
                        'status' => 'pending',
                        'reason' => null,
                        'status_note' => null,
                    ]);
                } else {

                    $success = 0;
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

    public function changeStatus(Request $request)
    {


        try {
            $input = $request->only(['status', 'reason', 'note', 'request_id']);
            $first_step = FollowupWorkerRequestProcess::where('worker_request_id', $input['request_id'])->where('status', 'pending')->where('sub_status', null)->first();
            $requestProcess = FollowupWorkerRequestProcess::where('worker_request_id', $input['request_id'])->where('status', 'pending')->where('sub_status', null)->get()[1];

            $procedure = EssentialsWkProcedure::where('id', $requestProcess->procedure_id)->first()->can_reject;


            if ($procedure == 0 && $input['status'] == 'rejected') {
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.this_department_cant_reject_this_request'),
                ];
                return $output;
            }

            $requestProcess->status = $input['status'];
            $requestProcess->reason = $input['reason'] ?? null;
            $requestProcess->status_note = $input['note'] ?? null;
            $requestProcess->updated_by = auth()->user()->id;

            $requestProcess->save();

            if ($input['status'] == 'approved') {
                $procedure = EssentialsWkProcedure::find($requestProcess->procedure_id);


                if ($procedure && $procedure->end == 1) {
                    $requestProcess->followupWorkerRequest->status = 'approved';
                    $first_step->status = 'approved';
                    $requestProcess->followupWorkerRequest->save();
                } else {
                    $nextDepartmentId = $procedure->next_department_id;
                    $nextProcedure = EssentialsWkProcedure::where('department_id', $nextDepartmentId)
                        ->where('type', $requestProcess->followupWorkerRequest->type)
                        ->first();

                    if ($nextProcedure) {
                        $newRequestProcess = new FollowupWorkerRequestProcess();
                        $newRequestProcess->worker_request_id = $requestProcess->worker_request_id;
                        $newRequestProcess->procedure_id = $nextProcedure->id;
                        $newRequestProcess->status = 'pending';
                        $newRequestProcess->save();
                    }
                }
            }
            if ($input['status'] == 'rejected') {
                $requestProcess->followupWorkerRequest->status = 'rejected';
                $first_step->status = 'rejected';
                $requestProcess->followupWorkerRequest->save();
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
    
   
    

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('internationalrelations::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
   
     

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
   
    private function generateRequestNo($type)
    {
        $latestRecord = FollowupWorkerRequest::where('type', $type)->orderBy('request_no', 'desc')->first();

        if ($latestRecord) {
            $latestRefNo = $latestRecord->request_no;
            $prefix = $this->getTypePrefix($type);
            $numericPart = (int)substr($latestRefNo, strlen($prefix));
            $numericPart++;
            $input['request_no'] = $prefix . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
        } else {
            $input['request_no'] = $this->getTypePrefix($type) . '0001';
        }

        return $input['request_no'];
    }
    private function getTypePrefix($type)
    {

        $typePrefixMap = [
            'exitRequest' => 'ex',
            'returnRequest' => 'ret',
            'leavesAndDepartures' => 'lev',
            'residenceRenewal' => 'resRe',
            'escapeRequest' => 'escRe',
            'advanceSalary' => 'advRe',
            'atmCard' => 'atm',
            'residenceCard' => 'res',
            'workerTransfer' => 'wT',
            'workInjuriesRequest' => 'wIng',
            'residenceEditRequest' => 'resEd',
            'baladyCardRequest' => 'bal',
            'insuranceUpgradeRequest' => 'insUp',
            'mofaRequest' => 'mofa',
            'chamberRequest' => 'ch',
            'cancleContractRequest' => 'con',
            'WarningRequest' => 'WR'
        ];

        return $typePrefixMap[$type];
    }
    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('internationalrelations::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
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

        $departmentIds =  EssentialsDepartment::where('business_id', $business_id)
        ->where('name', 'LIKE', '%دولي%')
        ->pluck('id')->toArray();

         
        if (!empty($departmentIds)) {
            $procedureIds = EssentialsWkProcedure::whereIn('escalates_to', $departmentIds)->pluck('id')->toArray();

            $requestsProcess = FollowupWorkerRequest::where('sub_status', 'escalateRequest')->whereIn('procedure_id', $procedureIds)->select([
                'followup_worker_requests.request_no',
                'followup_worker_requests_process.id as process_id',
                'followup_worker_requests.id',
                'followup_worker_requests.type as type',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.created_at',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.status_note as note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.department_id as department_id',
                'users.id_proof_number',
                'essentials_wk_procedures.can_return',
                'users.assigned_to'

            ])
                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->whereIn('followup_worker_requests.worker_id', $userIds)
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id') ->where('followup_worker_requests_process.status','pending');
        } else {
            $output = [
                'success' => false,
                'msg' => __('internationalrelations::lang.you_have_no_access_role'),
            ];
            return redirect()->action([\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'index'])->with('status', $output);
        }

        if (request()->ajax()) {
         

            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
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

                ->rawColumns(['status'])


                ->make(true);
        }
        $statuses = $this->statuses;
        return view('internationalrelations::requests.escalate_requests')->with(compact('statuses'));
    }


    public function changeEscalateRequestsStatus(Request $request)
    {
        try {
            $input = $request->only(['status', 'reason', 'note', 'request_id']);

            $requestProcesses = FollowupWorkerRequestProcess::where('worker_request_id', $input['request_id'])->where('status','pending')->get();

            foreach ($requestProcesses as $requestProcess) {

                $requestProcess->status = $input['status'];
                $requestProcess->reason = $input['reason'] ?? null;
                $requestProcess->status_note = $input['note'] ?? null;
                $requestProcess->updated_by = auth()->user()->id;

                $requestProcess->save();
            }


            $mainRequest = FollowupWorkerRequest::find($input['request_id']);
            $mainRequest->status = $input['status'];
            $mainRequest->save();

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
    public function viewRequest($id)
    {
      

        $request = FollowupWorkerRequest::with([
            'user', 'createdUser', 'followupWorkerRequestProcess.procedure.department', 'attachments'
        ])->where('id', $id)->first();

        if (!$request) {
            return response()->json(['error' => 'Request not found'], 404);
        }


        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'status' => trans("followup::lang.{$request->status}"),
            'type' => trans("followup::lang.{$request->type}"),
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ];
        $workflow = [];
        $currentStep = EssentialsWkProcedure::where('id', $request->followupWorkerRequestProcess[0]->procedure_id)->first();
   
        while ($currentStep && !$currentStep->end ) {

            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $currentStep->next_department_id)->first())->name,
            ];

            $currentStep = EssentialsWkProcedure::where('type', $request->type)
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
            'worker_id' => $request->user->id,
            'user_type' => trans("followup::lang.{$request->user->user_type}"),
            'nationality' => optional(DB::table('essentials_countries')->where('id', $request->user->nationality_id)->first())->nationality,
            'assigned_to' =>  $this->getContactLocation($request->user->assigned_to),
            'worker_full_name' => $request->user->first_name . ' ' . $request->user->last_name,
            'id_proof_number' => $request->user->id_proof_number,
            'contract_end_date' => optional(DB::table('essentials_employees_contracts')->where('employee_id', $request->user->id)->first())->contract_end_date,
            'eqama_end_date' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->user->id)->where('type', 'residence_permit')->first())->expiration_date,
            'passport_number' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->user->id)->where('type', 'passport')->first())->number,

        ];

        $createdUserInfo = [
            'created_user_id' => $request->createdUser->id,
            'user_type' => $request->createdUser->user_type,
            'nationality_id' => $request->createdUser->nationality_id,
            'created_user_full_name' => $request->createdUser->first_name . ' ' . $request->createdUser->last_name,
            'id_proof_number' => $request->createdUser->id_proof_number,

        ];

        $followupProcesses = [];
        foreach ($request->followupWorkerRequestProcess as $process) {
            $processInfo = [
                'id' => $process->id,
                'status' => trans("followup::lang.{$process->status}"),
                'procedure_id' => $process->procedure_id,
                'is_returned' => $process->is_returned,
                'updated_by' =>  $this->getFullName($process->updated_by),
                'reason' => $process->reason,
                'status_note' => $process->status_note,
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
    private function getProcessIdForStep($request, $step)
    {
        return optional($request->followupWorkerRequestProcess->where('procedure_id', $step->id)->first())->id;
    }
    private function getProcessStatusForStep($request, $step)
    {
        return optional($request->followupWorkerRequestProcess->where('procedure_id', $step->id)->first())->status;
    }
}
