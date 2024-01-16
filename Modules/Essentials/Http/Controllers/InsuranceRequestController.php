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
    
    public function index(){  
        
        $business_id = request()->session()->get('user.business_id');
        $can_insurance_requests_change_status =auth()->user()->can('essentials.insurances_requests_change_status');
       
        $ContactsLocation = ContactLocation::all()->pluck('name', 'id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $user_businesses_ids = Business::pluck('id')->unique()->toArray();

        $user_projects_ids = SalesProject::all('id')->unique()->toArray();
        if (!$is_admin) {
            $userProjects = [];
            $userBusinesses = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $userProjectsForRole = AccessRoleProject::where('access_role_id', $accessRole->id)->pluck('sales_project_id')->unique()->toArray();
                    $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                    $userProjects = array_merge($userProjects, $userProjectsForRole);
                    $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                }
            }
            $user_projects_ids = array_unique($userProjects);
            $user_businesses_ids = array_unique($userBusinesses);
        }
        if (empty($user_businesses_ids)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.you_have_no_access_role'),
            ];
            return redirect()->back()->with('status', $output);
        }
        $departmentIds = EssentialsDepartment::whereIn('business_id', $user_businesses_ids)
            ->where('name', 'LIKE', '%تأمين%')
            ->pluck('id')->toArray();
     
        $leaveTypes = EssentialsLeaveType::all()->pluck('leave_type', 'id');
        $classes = EssentialsInsuranceClass::all()->pluck('name', 'id');
        $main_reasons = DB::table('essentails_reason_wishes')->where('reason_type', 'main')->where('employee_type', 'worker')->pluck('reason', 'id');


        $requestsProcess = null;

        if (!empty($departmentIds)) {


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
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                ->whereIn('department_id', $departmentIds)->where('followup_worker_requests_process.sub_status', null)
                ->where('followup_worker_requests_process.status', 'pending');
             
        } else {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_insurance_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        if (!$is_admin) {

            $requestsProcess = $requestsProcess->where(function ($query) use ($user_businesses_ids, $user_projects_ids) {
                $query->where(function ($query2) use ($user_businesses_ids) {
                    $query2->whereIn('users.business_id', $user_businesses_ids)->whereIn('user_type', ['employee', 'manager']);
                })->orWhere(function ($query3) use ($user_projects_ids) {
                    $query3->where('user_type', 'worker')->whereIn('assigned_to', $user_projects_ids);
                });
            });
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
                ->editColumn('status', function ($row ,$can_insurance_requests_change_status ,$is_admin) {
                    $status = '';

                    if($can_insurance_requests_change_status || $is_admin)
                    {
                        if ($row->status == 'pending') {
                            $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                                . __($this->statuses[$row->status]['name']) . '</span>';
    
                          
                                $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                        
                        } elseif (in_array($row->status, ['approved', 'rejected'])) {
                            $status = trans('followup::lang.' . $row->status);
                        }
                    }
                   

                    return $status;
                })

                ->rawColumns(['status'])


                ->make(true);
        }


        $workers = User::with(['userAllowancesAndDeductions'])
        ->where(function ($query) use ($user_businesses_ids, $user_projects_ids) {
            $query->where(function ($query2) use ($user_businesses_ids) {
                $query2->whereIn('users.business_id', $user_businesses_ids)
                       ->whereIn('user_type', ['employee', 'manager', 'worker']);
            })->orWhere(function ($query3) use ($user_projects_ids, $user_businesses_ids) {
                $query3->where('user_type', 'worker')
                       ->whereIn('assigned_to', $user_projects_ids)
                       ->whereIn('users.business_id', $user_businesses_ids);
            });
        })
        ->select(
            'id',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,''), ' - ',COALESCE(id_proof_number,'')) as full_name")
        )
        ->pluck('full_name', 'id');
        $statuses = $this->statuses;


      return view('essentials::requests.insurance_requests')->with(compact('workers', 'statuses', 'main_reasons', 'classes', 'leaveTypes'));
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
                return redirect()->route('insurance_requests')->withErrors([$output['msg']]);
            }


            if (is_null($contract->wish_id)) {
                $output = [
                    'success' => false,
                    'msg' => __('followup::lang.no_wishes_found'),
                ];
                return redirect()->route('insurance_requests')->withErrors([$output['msg']]);
            }

            $contractEndDate = Carbon::parse($contract->contract_end_date);
            $todayDate = Carbon::now();

            if ($todayDate->diffInMonths($contractEndDate) > 1) {
                $output = [
                    'success' => false,
                    'msg' => __('followup::lang.contract_expired'),
                ];
                return redirect()->route('insurance_requests')->withErrors([$output['msg']]);
            }
        }

        $success = 1;

        foreach ($request->worker_id as $workerId) {
            
            if ($workerId !== null) {
                $business_id = User::where('id', $workerId)->first()->business_id;
                error_log($business_id);

                $procedure = EssentialsWkProcedure::where('type', $request->type)->where('business_id', $business_id);
                if ($procedure->count() == 0) {

                    $is_main = Business::where('id', $business_id)->first()->is_main;
                    if ($is_main) {
                        $output = [
                            'success' => false,
                            'msg' => __('followup::lang.this_type_has_not_procedure'),
                        ];
                        return redirect()->route('insurance_requests')->withErrors([$output['msg']]);
                    } else {
                        $parentBusiness = Business::where('id', $business_id)->first()->parent_business_id;
                        $procedure = EssentialsWkProcedure::where('type', $request->type)->where('business_id', $parentBusiness);
                        if ($procedure->count() == 0) {
                            $output = [
                                'success' => false,
                                'msg' => __('followup::lang.this_type_has_not_procedure'),
                            ];
                            return redirect()->route('insurance_requests')->withErrors([$output['msg']]);
                        }
                    }
                }
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


                $procedure = $procedure->where('start', 1)->first();
                if ($workerRequest) {
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
            return redirect()->route('insurance_requests')->with('success', $output['msg']);
        } else {
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->route('insurance_requests')->withErrors([$output['msg']]);
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
