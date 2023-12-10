<?php

namespace Modules\FollowUp\Http\Controllers;

use App\Business;
use App\User;
use App\ContactLocation;
use Modules\Sales\Entities\SalesProject;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsEmployeesContract;

class FollowUpRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
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

    public function changeStatus(Request $request)
    {


        try {
            $input = $request->only(['status', 'reason', 'note', 'request_id']);

            $requestProcess = FollowupWorkerRequestProcess::where('id', $input['request_id'])->first();

            $requestProcess->status = $input['status'];
            $requestProcess->reason = $input['reason'] ?? null;
            $requestProcess->status_note = $input['note'] ?? null;
            $requestProcess->updated_by = auth()->user()->id;

            $requestProcess->save();

            if ($input['status'] == 'approved') {
                $procedure = EssentialsWkProcedure::find($requestProcess->procedure_id);


                if ($procedure && $procedure->end == 1) {
                    $requestProcess->followupWorkerRequest->status = 'approved';
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


    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module')) && !$is_admin) {
            abort(403, 'Unauthorized action.');
        }
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $query = User::where('business_id', $business_id)->where('users.user_type', '=', 'worker');
        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $workers = $all_users->pluck('full_name', 'id');
        return view('followup::requests.create')->with(compact('workers', 'leaveTypes'));
    }

    public function getSubReasons(Request $request)
    {

        $mainReason = $request->input('main_reason');

        $subReasons = DB::table('essentails_reason_wishes')->where('main_reson_id', $mainReason)
            ->select('id', 'sub_reason as name')
            ->get();
        return response()->json(['sub_reasons' => $subReasons]);
    }

    public function store(Request $request)
    {

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
                    'msg' => __('followup::lang.no_contract_found'),
                ];
                return redirect()->route('allRequests')->withErrors([$output['msg']]);
            }


            if (is_null($contract->wish_id)) {
                $output = [
                    'success' => false,
                    'msg' => __('followup::lang.no_wishes_found'),
                ];
                return redirect()->route('allRequests')->withErrors([$output['msg']]);
            }

            $contractEndDate = Carbon::parse($contract->contract_end_date);
            $todayDate = Carbon::now();

            if ($todayDate->diffInMonths($contractEndDate) > 1) {
                $output = [
                    'success' => false,
                    'msg' => __('followup::lang.contract_expired'),
                ];
                return redirect()->route('allRequests')->withErrors([$output['msg']]);
            }
        }
        $procedure = EssentialsWkProcedure::where('type', $request->type)->get();
        if ($procedure->count() == 0) {
            $output = [
                'success' => false,
                'msg' => __('followup::lang.this_type_has_not_procedure'),
            ];
            return redirect()->route('allRequests')->withErrors([$output['msg']]);
        }
        $success = 1;

        foreach ($request->worker_id as $workerId) {
            if ($workerId !== null) {
                if ($request->type == "exitRequest") {
                    $startDate = DB::table('essentials_employees_contracts')->where('employee_id', $workerId)->first()->contract_end_date;
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
                    $process = FollowupWorkerRequestProcess::create([
                        'worker_request_id' => $workerRequest->id,
                        'procedure_id' => $this->getProcedureIdForType($request->type),
                        'status' => 'pending',
                        'reason' => null,
                        'status_note' => null,
                    ]);

                    if (!$process) {

                        $workerRequest->delete();
                        // $output = [
                        //     'success' => 0,
                        //     'msg' => __('messages.something_went_wrong'),
                        // ];
                        // return redirect()->route('allRequests')->withErrors([$output['msg']]);
                        $success = 0;
                    }
                } else {

                    $success = 0;
                    // $output = [
                    //     'success' => 0,
                    //     'msg' => __('messages.something_went_wrong'),
                    // ];
                    // return redirect()->route('allRequests')->withErrors([$output['msg']]);
                }
            }
        }
        if ($success) {
            $output = [
                'success' => 1,
                'msg' => __('sales::lang.operationOrder_added_success'),
            ];
            return redirect()->route('allRequests')->with('success', $output['msg']);
        } else {
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->route('allRequests')->withErrors([$output['msg']]);
        }
    }


    private function getProcedureIdForType($type)
    {

        $procedure = EssentialsWkProcedure::where('type', $type)->where('start', 1)->first();

        return $procedure ? $procedure->id : null;
    }

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
            'cancleContractRequest' => 'con'

        ];

        return $typePrefixMap[$type];
    }

    public function requests()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $crud_requests = auth()->user()->can('followup.view_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $ContactsLocation = SalesProject::all()->pluck('name', 'id');
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');
        $requestsProcess = null;

        if ($department) {
            $department = $department->id;

            $requestsProcess = FollowupWorkerRequest::select([
                'followup_worker_requests.request_no',
                'followup_worker_requests.id',
                'followup_worker_requests.type as type',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.created_at',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.status_note as note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.department_id as department_id',
                'users.id_proof_number',
                'users.assigned_to'

            ])
                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->where('user_type', 'worker')
                ->where('department_id', $department);
        }

        // if (!$is_admin) {
        //     $userProjects = UserProject::where('user_id', auth()->user()->id)->pluck('contact_location_id')->unique()->toArray();

        //     $requestsProcess = $requestsProcess->whereIn('users.assigned_to', $userProjects);
          
        // }
        if (request()->ajax()) {


            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
                })
                ->editColumn('assigned_to', function ($row) use ($ContactsLocation) {
                    $item = $ContactsLocation[$row->assigned_to] ?? '';

                    return $item;
                })



                ->make(true);
        }
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $query = User::where('business_id', $business_id)->where('users.user_type', '=', 'worker');
        //  $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))->get();
        $all_users = $query->select(
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
         ' - ',COALESCE(id_proof_number,'')) as full_name")
        )->get();

        $workers = $all_users->pluck('full_name', 'id');


        return view('followup::requests.allRequest')->with(compact('workers', 'main_reasons', 'classes', 'leaveTypes'));
    }

    public function search(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $query = User::where('business_id', $business_id)
            ->where('user_type', 'worker')
            ->where(function ($query) use ($request) {
                $query->where('first_name', 'LIKE', '%' . $request->q . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $request->q . '%')
                    ->orWhere('id_proof_number', 'LIKE', '%' . $request->q . '%');
            });

        $results = $query->select('id',  DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name"))
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'text' => $user->full_name,
                ];
            });

        return response()->json(['results' => $results]);
    }



    public function viewRequest($id)
    {
        $request = FollowupWorkerRequest::with([
            'user', 'createdUser', 'followupWorkerRequestProcess.procedure.department'
        ])
            ->find($id);

        if (!$request) {
            return response()->json(['error' => 'Request not found'], 404);
        }

        // Extracting information
        $requestInfo = [
            'request_no' => $request->request_no,
            'status' => trans("followup::lang.{$request->status}"),
            'type' => trans("followup::lang.{$request->type}"),
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ];
        $workflow = [];
        $currentStep = EssentialsWkProcedure::where('type', $request->type)->where('start', 1)->first();

        while ($currentStep && $currentStep->end != 1) {

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
            'workflow' => $workflow
        ];

        return response()->json($result);
    }

    private function getProcessIdForStep($request, $step)
    {
        return optional($request->followupWorkerRequestProcess->where('procedure_id', $step->id)->first())->id;
    }

    private function getProcessStatusForStep($request, $step)
    {
        return optional($request->followupWorkerRequestProcess->where('procedure_id', $step->id)->first())->status;
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

    public function exitRequestIndex()
    {

        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;

            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',

                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department)->where('followup_worker_requests.type', 'exitRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudExitRequests')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'exitRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.exitRequestIndex')->with(compact('statuses'));
    }
    public function filteredRequests()
    {
        $business_id = request()->session()->get('user.business_id');
        $filter = request()->query('filter');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();
        $requestsProcess = null;

        if ($department) {
            $department = $department->id;

            $requestsProcess = FollowupWorkerRequest::select([
                'followup_worker_requests.request_no',
                'followup_worker_requests.type as type',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.created_at',
                'followup_worker_requests.status',
                'followup_worker_requests.note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.department_id as department_id'
            ])
                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->where('user_type', 'worker')
                ->where('department_id', $department);
        }
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

                ->make(true);
        }

        return view('followup::requests.custom_filtered_requests')->with(compact('pageName'));
    }
    public function returnRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {

            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',

                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'returnRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudReturnRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'returnRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.returnRequestIndex')->with(compact('statuses'));
    }

    public function escapeRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.created_at',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.escape_time',
                    'followup_worker_requests.advSalaryAmount',
                    'followup_worker_requests.monthlyInstallment',
                    'followup_worker_requests.installmentsNumber',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',

                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'escapeRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudEscapeRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'escapeRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.escapeRequestIndex')->with(compact('statuses'));
    }

    public function advanceSalaryIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at as created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.advSalaryAmount',
                    'followup_worker_requests.monthlyInstallment',
                    'followup_worker_requests.installmentsNumber',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',

                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department)->where('followup_worker_requests.type', 'advanceSalary');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudAdvanceSalary')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'advanceSalary')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.advanceSalaryIndex')->with(compact('statuses'));
    }
    public function leavesAndDeparturesIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',


                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'leavesAndDepartures');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudLeavesAndDepartures')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })

                ->rawColumns(['status'])
                ->make(true);
        }

        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'leavesAndDepartures')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.leavesAndDeparturesIndex')->with(compact('statuses'));
    }

    public function atmCardIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'followup_worker_requests.atmCardType',


                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'atmCard');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudAtmCard')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'atmCard')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.atmCardIndex')->with(compact('statuses'));
    }

    public function residenceRenewalIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',

                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'residenceRenewal');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudResidenceRenewal')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'residenceRenewal')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.residenceRenewalIndex')->with(compact('statuses'));
    }

    public function residenceCardIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',

                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'residenceCard');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudResidenceCard')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'residenceCard')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }
        return view('followup::requests.residenceCardIndex')->with(compact('statuses'));
    }

    public function workerTransferIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }
        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',

                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'workerTransfer');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudWorkerTransfer')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'workerTransfer')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }

        return view('followup::requests.workerTransferIndex')->with(compact('statuses'));
    }

    public function chamberRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'followup_worker_requests.visa_number',


                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'chamberRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudChamberRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'chamberRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }

        return view('followup::requests.chamberRequestIndex')->with(compact('statuses'));
    }

    public function mofaRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'followup_worker_requests.visa_number',



                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'mofaRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudMofaRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'mofaRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }

        return view('followup::requests.mofaRequestIndex')->with(compact('statuses'));
    }

    public function insuranceUpgradeRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();

        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');

        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'followup_worker_requests.insurance_classes_id as insurance_class',


                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'insuranceUpgradeRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudMofaRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->editColumn('insurance_class', function ($row) use ($classes) {
                    $item = $classes[$row->insurance_class] ?? '';

                    return $item;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'insuranceUpgradeRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }

        return view('followup::requests.insuranceUpgradeRequestIndex')->with(compact('statuses'));
    }

    public function baladyCardRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'followup_worker_requests.insurance_classes_id as insurance_class',
                    'followup_worker_requests.baladyCardType as cardType',



                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'baladyCardRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudMofaRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'baladyCardRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }

        return view('followup::requests.baladyCardRequestIndex')->with(compact('statuses'));
    }

    public function residenceEditRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'followup_worker_requests.insurance_classes_id as insurance_class',
                    'followup_worker_requests.baladyCardType as cardType',
                    'followup_worker_requests.resCardEditType as resEditType',




                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'residenceEditRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudMofaRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'residenceEditRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }

        return view('followup::requests.residenceEditRequestIndex')->with(compact('statuses'));
    }
    public function workInjuriesRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();


        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'followup_worker_requests.insurance_classes_id as insurance_class',
                    'followup_worker_requests.baladyCardType as cardType',
                    'followup_worker_requests.resCardEditType as resEditType',
                    'followup_worker_requests.workInjuriesDate as workInjuriesDate',





                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'workInjuriesRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudMofaRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'workInjuriesRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }

        return view('followup::requests.workInjuriesRequestIndex')->with(compact('statuses'));
    }
    public function cancleContractRequestIndex()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%متابعة%')
            ->first();

        $reasons = DB::table('essentails_reason_wishes')->pluck('reason', 'id');
        if (request()->ajax()) {
            $requestsProcess = null;
            if ($department) {
                $department = $department->id;

                $requestsProcess = FollowupWorkerRequestProcess::where('followup_worker_requests_process.status', 'pending')->select([
                    'followup_worker_requests_process.id as id',
                    'followup_worker_requests_process.worker_request_id',
                    'followup_worker_requests_process.procedure_id',
                    'followup_worker_requests_process.status',
                    'followup_worker_requests_process.reason',
                    'followup_worker_requests_process.status_note',
                    'followup_worker_requests_process.created_at',
                    'followup_worker_requests_process.updated_at',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'followup_worker_requests.id as request_id',
                    'followup_worker_requests.request_no',
                    'followup_worker_requests.worker_id',
                    'followup_worker_requests.type',
                    'followup_worker_requests.start_date',
                    'followup_worker_requests.end_date',
                    'followup_worker_requests.note',
                    'followup_worker_requests.reason',
                    'essentials_wk_procedures.id as procedure_id',
                    'essentials_wk_procedures.type as procedure_type',
                    'essentials_wk_procedures.department_id as department_id',
                    'essentials_wk_procedures.can_return',
                    'essentials_wk_procedures.start as start',
                    'followup_worker_requests.insurance_classes_id as insurance_class',
                    'followup_worker_requests.baladyCardType as cardType',
                    'followup_worker_requests.resCardEditType as resEditType',
                    'followup_worker_requests.workInjuriesDate as workInjuriesDate',
                    'followup_worker_requests.contract_main_reason_id as main_reason',
                    'followup_worker_requests.contract_sub_reason_id as sub_reason',






                ])
                    ->join('followup_worker_requests', 'followup_worker_requests.id', '=', 'followup_worker_requests_process.worker_request_id')
                    ->join('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                    ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                    ->where('department_id', $department);
                $requestsProcess = $requestsProcess->where('followup_worker_requests.type', 'cancleContractRequest');
            }

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('status', function ($row) {

                    $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                        . $this->statuses[$row->status]['name'] . '</span>';
                    if (auth()->user()->can('crudMofaRequest')) {
                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->editColumn('main_reason', function ($row) use ($reasons) {
                    $item = $reasons[$row->main_reason] ?? '';

                    return $item;
                })
                ->editColumn('sub_reason', function ($row) use ($reasons) {
                    $item = $reasons[$row->sub_reason] ?? '';

                    return $item;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
        if ($department) {
            $department = $department->id;
            $pros = EssentialsWkProcedure::where('department_id', $department)->where('type', 'cancleContractRequest')->first();
            if ($pros) {
                $can_reject = $pros->can_reject;
                $can_reject = $can_reject ?? 0;
                $statuses = $can_reject == 1 ? $this->statuses : $this->statuses2;
            } else {
                $statuses = $this->statuses;
            }
        }

        return view('followup::requests.cancleContractRequestIndex')->with(compact('statuses'));
    }
    public function returnReq(Request $request)
    {
        $can_return_request = auth()->user()->can('followup.return_request');
        if (!$can_return_request) {
            abort(403, 'Unauthorized action.');
        }

        try {

            $requestId = $request->input('requestId');

            $requestProcess = FollowupWorkerRequestProcess::find($requestId);

            if ($requestProcess) {

                $procedure = EssentialsWkProcedure::find($requestProcess->procedure_id);

                if ($procedure) {

                    $departmentId = $procedure->department_id;

                    $nameDepartment = EssentialsDepartment::where('id', $departmentId)->first()->name;
                    $newProcedure = EssentialsWkProcedure::where('next_department_id', $departmentId)
                        ->where('type', $procedure->type)
                        ->first();


                    if ($newProcedure) {

                        $requestProcess->update([
                            'procedure_id' => $newProcedure->id,
                            'status' => 'pending',
                            'is_returned' => 1,
                            'updated_by' => auth()->user()->id,
                            'status_note' => __('followup::lang.returned_by') . " " . $nameDepartment,



                        ]);

                        //  return response()->json(['success' => true, 'msg' => 'Request returned successfully']);
                        $output = [
                            'success' => true,
                            'msg' => __('followup::lang.returned_successfully'),
                        ];
                    } else {
                        $output = [
                            'success' => false,
                            'msg' => __('followup::lang.there_is_no_department_to_return_for'),
                        ];
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
}
