<?php
namespace Modules\Essentials\Http\Controllers\Api;

use App\Request as UserRequest;
use App\RequestAttachment;
use App\RequestProcess;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\UserLeaveBalance;

class ApiEssentialsRequestsController extends ApiController
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;
    protected $requestUtil;
    protected $statuses;
    protected $statuses2;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->middleware('localization');
        $this->moduleUtil  = $moduleUtil;
        $this->requestUtil = $requestUtil;
        $this->statuses    = [
            'approved' => [
                'name'  => __('api.approved'),
                'class' => 'bg-green',
            ],
            'rejected' => [
                'name'  => __('fapi.rejected'),
                'class' => 'bg-red',
            ],
            'pending'  => [
                'name'  => __('api.pending'),
                'class' => 'bg-yellow',
            ],
        ];
        $this->statuses2 = [
            'approved' => [
                'name'  => __('api.approved'),
                'class' => 'bg-green',
            ],

            'pending'  => [
                'name'  => __('api.pending'),
                'class' => 'bg-yellow',
            ],
        ];
    }

    public function storeApiRequest(Request $request, )
    {
        $user        = User::where('id', Auth::user()->id)->first();
        $requestType = $request->requestType ?? null;
        try {

            $attachmentPath = $request->attachment ? $request->attachment->store('/requests_attachments') : null;
            $startDate      = $request->start_date ?? $request->escape_date ?? $request->exit_date;
            $end_date       = $request->end_date ?? $request->return_date;
            $today          = Carbon::today();
            if ($startDate) {
                $startDateCarbon = Carbon::parse($startDate);
                if ($startDateCarbon->lt($today)) {
                    return new CommonResource([
                        'msg' => "تاريخ البداية يجب أن يكون لاحق",
                    ]);
                }
                if ($end_date) {

                    $endDateCarbon = Carbon::parse($end_date);

                    if ($startDateCarbon->gt($endDateCarbon)) {
                        return new CommonResource([
                            'msg' => "تاريخ البداية يجب أن يسبق تاريخ النهاية",
                        ]);
                    }
                }
            }

            if (! $requestType || $requestType == null) {
                $type_id = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'employee')->first()->id;

                $isExists = UserRequest::where('related_to', $user->id)->where('request_type_id', $type_id)->where('status', 'pending')->first();
                if ($isExists) {
                    return new CommonResource([
                        'msg' => "يوجد طلب سابق قيد المعالجة",
                    ]);
                } else {
                    $leaveBalance = UserLeaveBalance::where([
                        'user_id'                  => $user->id,
                        'essentials_leave_type_id' => $request->leaveTypeId,
                    ])->first();

                    if (! $leaveBalance || $leaveBalance->amount == 0) {

                        return new CommonResource([
                            'msg' => "ليس لديك رصيد كاف",
                        ]);
                    } else {

                        $startDate     = Carbon::parse($startDate);
                        $endDate       = Carbon::parse($end_date);
                        $daysRequested = $startDate->diffInDays($endDate) + 1;

                        if ($daysRequested > $leaveBalance->amount) {
                            return new CommonResource([
                                'msg' => "ليس لديك رصيد كاف",
                            ]);
                        }
                    }
                    $Request = new UserRequest;

                    $Request->request_no               = $this->requestUtil->generateRequestNo($type_id);
                    $Request->related_to               = $user->id;
                    $Request->request_type_id          = $type_id;
                    $Request->start_date               = $startDate;
                    $Request->end_date                 = $endDate;
                    $Request->reason                   = $request->reason;
                    $Request->note                     = $request->note;
                    $Request->attachment               = $attachmentPath;
                    $Request->essentials_leave_type_id = $request->leaveTypeId;
                    $Request->created_by               = $user->id;
                    $Request->save();

                    if ($attachmentPath) {
                        RequestAttachment::create([
                            'request_id' => $Request->id,
                            'file_path'  => $attachmentPath,
                        ]);
                    }
                    if ($Request) {
                        $process = null;

                        $department_id = $user->essentials_department_id;

                        if ($department_id) {
                            $process = RequestProcess::create([
                                'started_department_id'  => $department_id,
                                'request_id'             => $Request->id,
                                'superior_department_id' => $department_id,
                                'status'                 => 'pending',
                            ]);
                        } else {

                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                            return new CommonResource([
                                'msg' => "يجب أن ينتمي الموظف لاحد الأقسام",
                            ]);
                        }

                        if (! $process) {

                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                        }

                        return new CommonResource([
                            'msg' => "تم رفع الطلب بنجاح",
                        ]);
                    }
                    return new CommonResource([
                        'msg' => "تعذر رفع الطلب، حاول مجددا في وقت لاحق",
                    ]);
                }
            } else {
                $type = RequestsType::where('id', $requestType)->first()->type;
                if ($type == 'cancleContractRequest' && ! empty($request->main_reason)) {

                    $contract = EssentialsEmployeesContract::where('employee_id', $user->id)->firstOrFail();
                    if (is_null($contract->wish_id)) {
                        return new CommonResource([
                            'msg' => "يجب تحديد رغبة",
                        ]);
                    }
                    if (now()->diffInMonths($contract->contract_end_date) > 1) {
                        return new CommonResource([
                            'msg' => "العقد منتهي",
                        ]);
                    }
                }

                $isExists = UserRequest::where('related_to', $user->id)->where('request_type_id', $requestType)->where('status', 'pending')->first();
                if ($isExists) {
                    return new CommonResource([
                        'msg' => "يوجد طلب سابق قيد المعالجة",
                    ]);
                } else {
                    if ($type == "exitRequest") {

                        $startDate = DB::table('essentials_employees_contracts')->where('employee_id', $user->id)->first()->contract_end_date ?? null;
                    }
                    $Request = new UserRequest;

                    $Request->request_no              = $this->requestUtil->generateRequestNo($requestType);
                    $Request->related_to              = $user->id;
                    $Request->request_type_id         = $requestType;
                    $Request->start_date              = $startDate;
                    $Request->end_date                = $end_date;
                    $Request->reason                  = $request->reason;
                    $Request->note                    = $request->note;
                    $Request->attachment              = $attachmentPath;
                    $Request->escape_time             = $request->escape_time;
                    $Request->installmentsNumber      = $request->installmentsNumber;
                    $Request->monthlyInstallment      = $request->monthlyInstallment;
                    $Request->advSalaryAmount         = $request->amount;
                    $Request->created_by              = $user->id;
                    $Request->insurance_classes_id    = $request->ins_class;
                    $Request->baladyCardType          = $request->baladyType;
                    $Request->resCardEditType         = $request->resEditType;
                    $Request->workInjuriesDate        = $request->workInjuriesDate;
                    $Request->contract_main_reason_id = $request->main_reason;
                    $Request->contract_sub_reason_id  = $request->sub_reason;
                    $Request->visa_number             = $request->visa_number;
                    $Request->atmCardType             = $request->atmType;
                    $Request->save();
                    if ($attachmentPath) {
                        RequestAttachment::create([
                            'request_id' => $Request->id,
                            'file_path'  => $attachmentPath,
                        ]);
                    }
                    if ($Request) {
                        $process = null;

                        $department_id = $user->essentials_department_id;

                        if ($department_id) {
                            $process = RequestProcess::create([
                                'started_department_id'  => $department_id,
                                'request_id'             => $Request->id,
                                'superior_department_id' => $department_id,
                                'status'                 => 'pending',
                            ]);
                        } else {

                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                            return new CommonResource([
                                'msg' => "يجب أن ينتمي الموظف لاحد الأفسام",
                            ]);
                        }

                        if (! $process) {

                            RequestAttachment::where('request_id', $Request->id)->delete();
                            $Request->delete();
                        }
                        return new CommonResource([
                            'msg' => "تم رفع الطلب بنجاح",
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log($e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg'     => __('messages.something_went_wrong'),
            ];
            return $this->otherExceptions($e);
        }
    }

    public function getRequestTypes()
    {
        $allRequestTypes = RequestsType::with('requestsTypesField')->where('for', 'employee')->whereNot('type', 'leavesAndDepartures')->get();
        $res             = [];
        foreach ($allRequestTypes as $tmp) {

            $res[] = [
                'id'              => $tmp->id,
                'name'            => __('request.' . $tmp->type),
                'required_fields' => $tmp->requestsTypesField->required_fields ?? '',
                'optional_fields' => $tmp->requestsTypesField->optional_fields ?? '',
            ];
        }
        return new CommonResource([
            'request_types' => $res,
        ]);
    }

    public function getMyLeaves()
    {

        if (! $this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $user             = Auth::user();
            $leaveRequestType = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'employee')->first()->id;

            $requests = UserRequest::with('leaveType')->select([

                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'request_processes.note as note',
                'wk_procedures.department_id as department_id',
                'users.id_proof_number',
                'requests.*',
            ])

                ->leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
                ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'requests.related_to')
                ->where('requests.request_type_id', $leaveRequestType)
                ->where('users.id', $user->id)->get();

            $business_id = $user->business_id;

            $leaves = UserLeaveBalance::with(['leave_type' => function ($query) use ($business_id) {
                $query->where('business_id', $business_id)
                    ->select(['id', 'leave_type', 'duration', 'max_leave_count']);
            }])->where('user_id', $user->id)->get();

            $statistics = [];
            foreach ($leaves as $leave) {
                $count        = $requests->where('leave_type_id', $leave->essentials_leave_type_id)->count();
                $statistics[] = [
                    'leave_type_id'     => $leave->essentials_leave_type_id,
                    'leave_type'        => $leave?->leave_type?->leave_type ?? '',
                    'max_leave_count'   => (int) ($leave->amount),
                    'taken_leave_count' => $count,
                ];
            }
            $requestsArr = [];
            foreach ($requests as $request) {

                $startDate = Carbon::parse($request->start_date);
                $endDate   = Carbon::parse($request->start_date);
                error_log($startDate);
                error_log($endDate);
                $duration      = $startDate->diffInDays($endDate);
                $requestsArr[] = [
                    "id"              => $request->id,
                    "request_no"      => $request->request_no,
                    "duration"        => $duration,
                    "type"            => json_decode($request)->leave_type?->leave_type ?? '',
                    "user"            => $request->user,
                    "status"          => __('api.' . $request['status']),
                    "note"            => $request->note,
                    "reason"          => $request->reason,
                    "department_id"   => $request->department_id,
                    "id_proof_number" => $request->id_proof_number,
                    'start_date'      => $request->start_date,
                    'end_date'        => $request->end_date,
                ];
            }

            $res = [
                'statistics' => $statistics,
                'requests'   => $requestsArr,
            ];

            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function getMyRequests()
    {

        if (! $this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $status_filter = request()->status;
            $user          = Auth::user();

            // $requests = FollowupWorkerRequest::select([
            //     'followup_worker_requests.request_no',
            //     'followup_worker_requests.id',
            //     'followup_worker_requests.type as type',
            //     DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            //     'followup_worker_requests.created_at',
            //     'followup_worker_requests_process.status',
            //     'followup_worker_requests_process.status_note as note',
            //     'followup_worker_requests.reason',
            //     'essentials_wk_procedures.department_id as department_id',
            //     'users.id_proof_number',
            //     'followup_worker_requests.start_date',
            //     'followup_worker_requests.end_date',

            // ])
            //     ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
            //     ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            //     ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            //     ->where('users.id', $user->id);
            $leaveRequestType = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'employee')->first()->id;
            // dd($leaveRequestType);

            $requests = UserRequest::with('requestType')
                ->select([
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'request_processes.note as note',
                    'wk_procedures.department_id as department_id',
                    'users.id_proof_number',
                    'requests.*',
                ])
                ->leftJoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
                ->leftJoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'requests.related_to')
                ->whereNot('requests.request_type_id', $leaveRequestType)
                ->where('users.id', $user->id);
            // dd($requests->get());

            if ($status_filter) {
                $requests = $requests->where('requests.status', $status_filter); // Correct table/column reference
            }
            // dd($requests->get());

            $requests = $requests->get();

            $result = [];
            foreach ($requests as $request) {
                $request['status'] = __('api.' . $request['status']);
                $type              = json_decode($request)->request_type->type;
                $request['type']   = __('api.' . $type);
                $result[]          = [
                    'request_no'      => $request->request_no,
                    'id'              => $request->id,
                    'type'            => $request['type'],
                    'user'            => $request->user,
                    'created_at'      => $request->created_at,
                    'status'          => $request->status,
                    'note'            => $request->note,
                    'reason'          => $request->reason,
                    'department_id'   => $request->department_id,
                    'id_proof_number' => $request->id_proof_number,
                ];
            }
            $res = [
                'requests' => $result,
            ];

            return new CommonResource($res);
        } catch (\Exception $e) {

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
}
