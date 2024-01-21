<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleProject;
use App\Business;
use App\ContactLocation;
use App\Utils\ModuleUtil;
use App\User;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Modules\Sales\Entities\SalesProject;

class InsuranceRequestController extends Controller
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

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $can_change_status = auth()->user()->can('essentials.change_insurance_request_status');

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%تأمين%')
            ->pluck('id')->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_insurance_dep'),
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
        $users = User::whereIn('id', $userIds)->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name"))->pluck('full_name', 'id');
        $statuses = $this->statuses;

        $requestsProcess = null;


        $requestsProcess = FollowupWorkerRequest::select([
            'followup_worker_requests.request_no', 'followup_worker_requests_process.id as process_id', 'followup_worker_requests.id',
            'followup_worker_requests.type as type', 'followup_worker_requests.created_at', 'followup_worker_requests_process.status',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            'followup_worker_requests_process.status_note as note', 'followup_worker_requests.reason', 'essentials_wk_procedures.department_id as department_id',
            'users.id_proof_number', 'essentials_wk_procedures.can_return', 'users.assigned_to', 'followup_worker_requests_process.procedure_id as procedure_id',
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
                ->editColumn('status', function ($row)  use ($is_admin, $can_change_status) {
                    $status = '';
                    $procedureStart = EssentialsWkProcedure::where('id', $row->procedure_id)->first();
                    if ($procedureStart->start != 1) {
                        if ($is_admin || $can_change_status) {
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
                    } else {
                        $status = trans('essentials::lang.' . $row->status);
                    }

                    return $status;
                })

                ->rawColumns(['status'])


                ->make(true);
        }



        return view('essentials::requests.insurance_requests')->with(compact('users', 'requestTypes','statuses', 'main_reasons', 'classes', 'leaveTypes'));
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
        ->where('name', 'LIKE', '%تأمين%')
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
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
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
    public function create()
    {
        return view('essentials::create');
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
    public function show($id)
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
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
}
