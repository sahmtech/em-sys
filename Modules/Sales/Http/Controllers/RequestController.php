<?php

namespace Modules\Sales\Http\Controllers;

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

class RequestController extends Controller
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

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'followup'))) {
            abort(403, 'Unauthorized action.');
        }

        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            abort(403, 'Unauthorized action.');
        }

        $ContactsLocation = SalesProject::all()->pluck('name', 'id');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
        $department = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%مبيعات%')
            ->first();
      
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');

        $requestsProcess = null;
     
        if ($department) {
            $department = $department->id;
            $user_businesses_ids = Business::pluck('id')->unique()->toArray();
            $user_projects_ids = SalesProject::all('id')->unique()->toArray();
            if (!$is_admin) {
                $userProjects = [];
                $userBusinesses = [];
                $roles = auth()->user()->roles;
                foreach ($roles as $role) {

                    $accessRole = AccessRole::where('role_id', $role->id)->first();

                    $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                    $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                    $userProjects = array_merge($userProjects, $userProjectsForRole);
                    $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                }
                $user_projects_ids = array_unique($userProjects);
                $user_businesses_ids = array_unique($userBusinesses);
            }
            $requestsProcess = FollowupWorkerRequest::select([
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
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->where('user_type', 'worker')
                ->where('department_id', $department)->where(function ($query) use ($user_businesses_ids, $user_projects_ids) {
                    $query->where(function ($query2) use ($user_businesses_ids) {
                        $query2->whereIn('users.business_id', $user_businesses_ids)->whereIn('user_type', ['employee', 'manager']);
                    })->orWhere(function ($query3) use ($user_projects_ids) {
                        $query3->where('user_type', 'worker')->whereIn('assigned_to', $user_projects_ids);
                    });
                });
        }
        else {
            $output = ['success' => false,
            'msg' => __('sales::lang.please_add_the_Sales_department'),
                ];
            return redirect()->action([\Modules\Sales\Http\Controllers\SalesController::class, 'index'])->with('status', $output);
        }
        if (request()->ajax()) {


            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {

                    return Carbon::parse($row->created_at);
                })
                ->editColumn('assigned_to', function ($row) use ($ContactsLocation) {
                    $item = $ContactsLocation[$row->assigned_to] ?? '';

                    return $item;
                })
                ->editColumn('status', function ($row) {
                    $status = '';
                
                    if ($row->status == 'pending') {
                        $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                            . $this->statuses[$row->status]['name'] . '</span>';
                        
                        if (auth()->user()->can('crudExitRequests')) {
                            $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                        }
                    } elseif (in_array($row->status, ['approved', 'rejected'])) {
                        $status = trans('followup::lang.' . $row->status);
                    }
                
                    return $status;
                })

                ->rawColumns(['status'])


                ->make(true);
        }
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $workers = User::where('user_type', 'worker')->whereIn('assigned_to', $user_projects_ids)->select(
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''),
         ' - ',COALESCE(id_proof_number,'')) as full_name")
        )->pluck('full_name', 'id');


        $statuses = $this->statuses;

        return view('sales::requests.allRequest')->with(compact('workers', 'statuses', 'main_reasons', 'classes', 'leaveTypes'));
    }

   
    
    public function changeStatus(Request $request)
    {

        try {
            $input = $request->only(['status', 'reason', 'note', 'request_id']);
            
            $requestProcess = FollowupWorkerRequestProcess::where('worker_request_id',$input['request_id'])->first();
   
            $procedure=EssentialsWkProcedure::where('id',$requestProcess->procedure_id)->first()->can_reject;

            if($procedure == 0 && $input['status']=='rejected'){
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.this_department_cant_reject_this_request'),
                ];
                return $output;
            }


            $requestProcess = FollowupWorkerRequestProcess::where('worker_request_id', $input['request_id'])->first();
           

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
                return redirect()->route('sales.requests')->withErrors([$output['msg']]);
            }


            if (is_null($contract->wish_id)) {
                $output = [
                    'success' => false,
                    'msg' => __('followup::lang.no_wishes_found'),
                ];
                return redirect()->route('sales.requests')->withErrors([$output['msg']]);
            }

            $contractEndDate = Carbon::parse($contract->contract_end_date);
            $todayDate = Carbon::now();

            if ($todayDate->diffInMonths($contractEndDate) > 1) {
                $output = [
                    'success' => false,
                    'msg' => __('followup::lang.contract_expired'),
                ];
                return redirect()->route('sales.requests')->withErrors([$output['msg']]);
            }
        }
        $procedure = EssentialsWkProcedure::where('type', $request->type)->get();
        if ($procedure->count() == 0) {
            $output = [
                'success' => false,
                'msg' => __('followup::lang.this_type_has_not_procedure'),
            ];
            return redirect()->route('sales.requests')->withErrors([$output['msg']]);
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
            return redirect()->route('sales.requests')->with('success', $output['msg']);
        } else {
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->route('sales.requests')->withErrors([$output['msg']]);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
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
            'cancleContractRequest' => 'con',
            'WarningRequest' => 'WR'
        ];

        return $typePrefixMap[$type];
    }
    public function returnReq(Request $request)
    {
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