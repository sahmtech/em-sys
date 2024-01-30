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


        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
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

    public function hr_department_employees()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_essentials_hr_view_department_employees = auth()->user()->can('essentials.hr_view_department_employees');


        if (!($is_admin || $can_essentials_hr_view_department_employees)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->where('name', 'LIKE', '%بشرية%')
                    ->orWhere('name', 'LIKE', '%حكومية%')
                    ->orWhere('name', 'LIKE', '%موظف%');
            })
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds)->where('is_active', 1);
        })->select([
            'users.*',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"),
            'users.id_proof_number',
        ]);
        if (request()->ajax()) {

            return Datatables::of($users)

                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'full_name',
                    function ($row) {
                        return $row->full_name;
                    }
                )
                ->addColumn(
                    'id_proof_number',
                    function ($row) {
                        return $row->id_proof_number;
                    }
                )
                ->addColumn(
                    'appointment',
                    function ($row) {
                        return $row->appointment?->profession->name ?? '';
                    }
                )


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''))  like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['id', 'full_name', 'id_proof_number', 'appointment'])
                ->make(true);
        }

        return view('essentials::hr_department_employees');
    }
    public function work_cards_department_employees()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_work_cards_view_department_employees = auth()->user()->can('essentials.work_cards_view_department_employees');


        if (!($is_admin || $can_work_cards_view_department_employees)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->where('name', 'LIKE', '%بشرية%')
                    ->orWhere('name', 'LIKE', '%حكومية%')
                    ->orWhere('name', 'LIKE', '%موظف%');
            })
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds)->where('is_active', 1);
        })->select([
            'users.*',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"),
            'users.id_proof_number',
        ]);
        if (request()->ajax()) {

            return Datatables::of($users)

                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'full_name',
                    function ($row) {
                        return $row->full_name;
                    }
                )
                ->addColumn(
                    'id_proof_number',
                    function ($row) {
                        return $row->id_proof_number;
                    }
                )
                ->addColumn(
                    'appointment',
                    function ($row) {
                        return $row->appointment?->profession->name ?? '';
                    }
                )


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''))  like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['id', 'full_name', 'id_proof_number', 'appointment'])
                ->make(true);
        }

        return view('essentials::work_cards_department_employees');
    }
    public function employee_affairs_department_employees()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_employee_affairs_view_department_employees = auth()->user()->can('essentials.employee_affairs_view_department_employees');


        if (!($is_admin || $can_employee_affairs_view_department_employees)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->where('name', 'LIKE', '%بشرية%')
                    ->orWhere('name', 'LIKE', '%حكومية%')
                    ->orWhere('name', 'LIKE', '%موظف%');
            })
            ->pluck('id')->toArray();

        $users = User::whereIn('id', $userIds)->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds)->where('is_active', 1);
        })->select([
            'users.*',
            DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,'')) as full_name"),
            'users.id_proof_number',
        ]);
        if (request()->ajax()) {

            return Datatables::of($users)

                ->addColumn(
                    'id',
                    function ($row) {
                        return $row->id;
                    }
                )
                ->addColumn(
                    'full_name',
                    function ($row) {
                        return $row->full_name;
                    }
                )
                ->addColumn(
                    'id_proof_number',
                    function ($row) {
                        return $row->id_proof_number;
                    }
                )
                ->addColumn(
                    'appointment',
                    function ($row) {
                        return $row->appointment?->profession->name ?? '';
                    }
                )


                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''))  like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })

                ->rawColumns(['id', 'full_name', 'id_proof_number', 'appointment'])
                ->make(true);
        }

        return view('essentials::employee_affairs_department_employees');
    }

    public function word_cards_dashboard()
    {

        $business_id = request()->session()->get('user.business_id');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }


        $expiryDateThreshold = Carbon::now()->addDays(15)->toDateString();
        $sixtyday = Carbon::now()->addDays(60)->toDateString();

        $last15_expire_date_residence = EssentialsOfficialDocument::whereIn('employee_id', $userIds)->where('type', 'residence_permit')
            ->where('expiration_date', '<=', $expiryDateThreshold)
            ->count();

        $today = Carbon::now()->toDateString();


        $all_ended_residency_date = EssentialsOfficialDocument::whereIn('employee_id', $userIds)->with(['employee'])->where('type', 'residence_permit')
            ->whereDate('expiration_date', '<=',  $today)->count();

        $escapeRequest = FollowupWorkerRequest::whereIn('worker_id', $userIds)->with('user')->where('type', 'escapeRequest')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->where('end_date', '<=', $sixtyday)
            ->count();


        $vacationrequest = FollowupWorkerRequest::whereIn('worker_id', $userIds)->with('user')->where('type', 'leavesAndDepartures')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->count();

        $final_visa = EssentailsEmployeeOperation::whereIn('employee_id', $userIds)->where('operation_type', 'final_visa')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->count();


        $late_vacation = FollowupWorkerRequest::whereIn('worker_id', $userIds)->with(['user'])
            ->where('type', 'leavesAndDepartures')
            ->where('type', 'returnRequest')
            ->whereHas('user', function ($query) {

                $query->where('status', 'vecation');
            })
            ->where('end_date', '<', now())
            ->count();



        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%حكومية%')
            ->pluck('id')->toArray();

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
                ->whereIn('users.id', $userIds)
                ->whereIn('department_id', $departmentIds)->where('followup_worker_requests_process.sub_status', null);
        }

        if (request()->ajax()) {



            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
                })

                ->editColumn('status', function ($row) {

                    $status = trans('followup::lang.' . $row->status) ?? '';


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

    public function getLeaves()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $business_id = request()->session()->get('user.business_id');
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
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
                'followup_worker_requests.request_no', 'followup_worker_requests.type',
                'followup_worker_requests.id',
                'followup_worker_requests.worker_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.start_date',
                'followup_worker_requests_process.status', 'followup_worker_requests_process.worker_request_id',
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
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
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

        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
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
