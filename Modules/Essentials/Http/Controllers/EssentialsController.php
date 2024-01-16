<?php

namespace Modules\Essentials\Http\Controllers;

use App\Charts\CommonChart;
use App\Utils\ModuleUtil;
use App\User;
use App\ContactLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsLeave;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentailsEmployeeOperation;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Sales\Entities\SalesProject;
use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleCompany;
use App\AccessRoleProject;
use App\Business;
use App\Company;
use Modules\Essentials\Entities\EssentialsInsuranceClass;

use DB;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class EssentialsController extends Controller
{

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $business_id = request()->session()->get('user.business_id');


        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $num_employee_staff = User::whereIn('id', $userIds)->count();
        $num_workers = User::whereIn('id', $userIds)->where('user_type', 'like', '%worker%')->count();
        $num_employees = User::whereIn('id', $userIds)->where('user_type', 'like', '%employee%')->count();
        $num_managers = User::whereIn('id', $userIds)->where('user_type', 'like', '%manager%')->count();

        $chart = new CommonChart;
        $colors = [
            '#E75E82', '#37A2EC', '#FACD56', '#5CA85C', '#605CA8',
            '#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce',
            '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'
        ];
        $labels = [__('user.worker'), __('user.employee'), __('user.manager')];
        $values = [$num_workers, $num_employees, $num_managers];
        $chart->labels($labels)
            ->options($this->__chartOptions())
            ->dataset(__('user.employee_staff'), 'pie', $values)
            ->color($colors);

        return view('essentials::index', compact('chart', 'num_employee_staff', 'num_workers', 'num_employees', 'num_managers'));
    }

    public function word_cards_dashboard()
    {

        $business_id = request()->session()->get('user.business_id');



        $expiryDateThreshold = Carbon::now()->addDays(15)->toDateString();
        $sixtyday = Carbon::now()->addDays(60)->toDateString();

        $last15_expire_date_residence = EssentialsOfficialDocument::where('type', 'residence_permit')
            ->where('expiration_date', '<=', $expiryDateThreshold)
            ->count();

        $today = Carbon::now()->toDateString();


        $all_ended_residency_date = EssentialsOfficialDocument::with(['employee'])->where('type', 'residence_permit')
            ->whereDate('expiration_date', '<=',  $today)->count();

        $escapeRequest = FollowupWorkerRequest::with('user')->where('type', 'escapeRequest')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->where('end_date', '<=', $sixtyday)
            ->count();


        $vacationrequest = FollowupWorkerRequest::with('user')->where('type', 'leavesAndDepartures')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->count();

        $final_visa = EssentailsEmployeeOperation::where('operation_type', 'final_visa')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->count();


        $late_vacation = FollowupWorkerRequest::with(['user'])
            ->where('type', 'leavesAndDepartures')
            ->where('type', 'returnRequest')
            ->whereHas('user', function ($query) {

                $query->where('status', 'vecation');
            })
            ->where('end_date', '<', now())
            ->count();

        $business_id = request()->session()->get('user.business_id');

        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!$crud_requests) {
            //temp  abort(403, 'Unauthorized action.');
        }

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
        $departmentIds = EssentialsDepartment::whereIn('business_id', $user_businesses_ids)
            ->where('name', 'LIKE', '%بشرية%')
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
                ->whereIn('department_id', $departmentIds)->where('followup_worker_requests_process.sub_status', null);
        } else {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.you_have_no_access_role'),
            ];
            return redirect()->route('home')->with('status', $output);
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




        return view('essentials::work_cards_index')
            ->with(compact(
                'last15_expire_date_residence',
                'all_ended_residency_date',
                'escapeRequest',
                'vacationrequest',
                'final_visa',
                'late_vacation'
            ));
    }


    private function __chartOptions()
    {
        return [
            'plotOptions' => [
                'pie' => [
                    'allowPointSelect' => true,
                    'cursor' => 'pointer',
                    'dataLabels' => [
                        'enabled' => false
                    ],
                    'showInLegend' => true,
                ],
            ],
        ];
    }

    public function getLeaves(){
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $business_id = request()->session()->get('user.business_id');
            $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
            if (!$is_admin) {
                $userIds = [];
                $userIds = $this->moduleUtil->applyAccessRole();
            }
        
            $departmentIds = EssentialsDepartment::where('business_id',  $business_id)
            ->where('name', 'LIKE', '%موظف%')
            ->pluck('id')->toArray();

        $requestsProcess = null;

        if (!empty($departmentIds)) {

            $requestsProcess = FollowupWorkerRequest::select([
                'followup_worker_requests.request_no','followup_worker_requests.type',
                'followup_worker_requests.id',
                'followup_worker_requests.worker_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.start_date',
                'followup_worker_requests_process.status','followup_worker_requests_process.worker_request_id',
                'followup_worker_requests_process.procedure_id',   'followup_worker_requests_process.sub_status',
      
            ])
                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                ->whereIn('department_id', $departmentIds)
                ->where('followup_worker_requests_process.sub_status', null)
                ->whereIn('followup_worker_requests.worker_id', $userIds)->where('followup_worker_requests.type', 'leavesAndDepartures');
        }

        if (request()->ajax()) {


            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {

                    return Carbon::parse($row->created_at);
                })
            
                ->editColumn('status', function ($row) {
                    return trans('followup::lang.' . $row->status) ?? '';
                })

                ->rawColumns(['status'])


                ->make(true);
        }
    }

    public function getLeaveStatusData()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $rawLeaveStatusData = FollowupWorkerRequest::whereIn('worker_id', $userIds)->where('type', 'leavesAndDepartures')
            ->select(DB::raw('status, COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();


        $leaveStatusData = [];
        foreach ($rawLeaveStatusData as $status => $count) {
            $translatedLabel = trans('lang_v1.' . $status);
            $leaveStatusData[$translatedLabel] = $count;
        }

        $data = [
            'labels' => array_keys($leaveStatusData),
            'values' => array_values($leaveStatusData),
        ];


        return response()->json($data);
    }



    public function getContractStatusData()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $totalContracts = EssentialsEmployeesContract::whereIn('employee_id', $userIds)->count();

        $expiredContractsByEndDate = EssentialsEmployeesContract::whereIn('employee_id', $userIds)->whereNotNull('contract_end_date')
            ->whereDate('contract_end_date', '<', now())
            ->count();

        $expiredContractsByProbation = EssentialsEmployeesContract::whereIn('employee_id', $userIds)->whereNotNull('probation_period')
            ->where(function ($query) {
                $query->whereNull('contract_end_date')
                    ->orWhere(function ($endDateSubquery) {
                        $endDateSubquery->whereNotNull('contract_start_date')
                            ->whereRaw('DATE_ADD(contract_start_date, INTERVAL probation_period MONTH) < NOW()');
                    });
            })
            ->count();

        $data = [
            'labels' => [
                __('essentials::lang.expired_contracts'),
                __('essentials::lang.remaining_contracts'),
            ],
            'values' => [
                ($totalContracts > 0) ? ($expiredContractsByEndDate / $totalContracts * 100) : 0,
                ($totalContracts > 0) ? ($expiredContractsByProbation / $totalContracts * 100) : 0,
            ],
        ];

        return response()->json($data);
    }





    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        return view('essentials::edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy()
    {
    }
}
