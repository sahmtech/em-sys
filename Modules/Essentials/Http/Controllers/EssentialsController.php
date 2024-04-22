<?php

namespace Modules\Essentials\Http\Controllers;

use App\Charts\CommonChart;
use App\Utils\ModuleUtil;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentailsEmployeeOperation;
use Modules\Essentials\Entities\EssentialsDepartment;
use App\Request as UserRequest;
use App\RequestProcess;
use DB;
use Illuminate\Support\Carbon;
use Modules\CEOManagment\Entities\RequestsType;
use Yajra\DataTables\Facades\DataTables;
use Alkoumi\LaravelHijriDate\Hijri;


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

    public function hijriToGregorian(Request $request)
    {
        $hijriDate = explode('/', $request->input('hijriDate')); // Assuming the date format is YYYY/MM/DD
        if (count($hijriDate) == 3) {
            list($year, $month, $day) = $hijriDate;
            $gregorianDate = Hijri::DateToGregorianFromDMY($day, $month, $year);
            $date = Carbon::createFromFormat('Y/m/d', $gregorianDate);

            // Format the date to the desired format
            $gregorianDate = $date->format('d/m/Y');
        } else {
            $gregorianDate = 'Invalid date format';
        }
        error_log($gregorianDate);
        return response()->json([
            'gregorianDate' => $gregorianDate,
        ]);
    }
    public function gregorianToHijri(Request $request)
    {
        error_log($request->input('gregorian'));
        $gregorianDate =  explode('/', $request->input('gregorian'));
        if (count($gregorianDate) == 3) {
            list($day, $month, $year) = $gregorianDate;
            error_log($year);
            error_log($month);
            error_log($day);
            $hijriDate = Hijri::DateFromGregorianDMY($month, $day, $year);
            error_log($hijriDate);
        } else {
            $hijriDate = 'Invalid date format';
        }



        return response()->json(['hijriDate' => $hijriDate]);
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

        $contract_type_id = DB::table('essentials_contract_types')->where('type', 'LIKE', '%بعد%')->first();
        $users = User::whereIn('id', $userIds)->whereHas('appointment', function ($query) use ($departmentIds) {
            $query->whereIn('department_id', $departmentIds)->where('is_active', 1);
        })
            ->whereHas('contract', function ($query) use ($userIds, $contract_type_id) {
                $query->whereIn('employee_id', $userIds)
                    ->where(function ($query) use ($contract_type_id) {
                        $query->where('contract_type_id', '!=', $contract_type_id->id)
                            ->orWhereNull('contract_type_id');
                    });
            })

            ->select([
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


        $last15_expire_date_residence = EssentialsOfficialDocument::where('is_active', 1)->where('type', 'residence_permit')

            ->whereBetween('expiration_date', [now(), now()->addDays(15)->endOfDay()])
            ->count();

        $today = today()->format('Y-m-d');

        $all_ended_residency_date = EssentialsOfficialDocument::where('is_active', 1)->with(['employee'])

            ->where('type', 'residence_permit')
            ->whereDate('expiration_date', '<', $today)
            ->count();

        $escapeRequest = 0;
        $type = RequestsType::where('type', 'escapeRequest')->where('for', 'worker')->first();
        if ($type) {
            $escapeRequest = UserRequest::with(['related_to_user'])->whereIn('related_to', $userIds)
                ->where('request_type_id', $type->id)
                ->whereHas('related_to_user', function ($query) {
                    $query->where('user_type', 'worker');
                })
                ->where('end_date', '<=', $sixtyday)
                ->count();
        }
        $vacationrequest = 0;
        $type2 = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'worker')->first();
        if ($type2) {
            $vacationrequest = UserRequest::with(['related_to_user'])->whereIn('related_to', $userIds)
                ->where('request_type_id', $type2->id)
                ->whereHas('related_to_user', function ($query) {
                    $query->where('user_type', 'worker');
                })->count();
        }


        $final_visa = EssentailsEmployeeOperation::whereIn('employee_id', $userIds)->where('operation_type', 'final_visa')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->count();
        $late_vacation = 0;
        $type = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'employee')->first();
        if ($type) {
            $late_vacation = UserRequest::with(['related_to_user'])->whereIn('related_to', $userIds)
                ->where('request_type_id', $type->id)
                ->whereHas('related_to_user', function ($query) {
                    $query->where('status', 'vecation');
                })
                ->where('end_date', '<', now())
                ->count();
        }

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%حكومية%')
            ->pluck('id')->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_governmental_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $requestsProcess = null;
        $allRequestTypes = RequestsType::pluck('type', 'id');
        $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')
            ->groupBy('request_id');

        $requestsProcess = UserRequest::select([
            'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.reason',

            'process.id as process_id', 'process.status', 'process.note as note',  'process.procedure_id as procedure_id', 'process.superior_department_id as superior_department_id',

            'wk_procedures.department_id as department_id', 'wk_procedures.can_return',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number',

        ])
            ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                $join->on('requests.id', '=', 'latest_process.request_id');
            })
            ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
            // ->leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')
            ->where(function ($query) use ($departmentIds) {
                $query->whereIn('wk_procedures.department_id', $departmentIds)
                    ->orWhereIn('process.superior_department_id', $departmentIds);
            })


            ->whereIn('requests.related_to', $userIds)->whereNull('process.sub_status')
            ->where('users.status', '!=', 'inactive');


        if (request()->ajax()) {

            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                    return $allRequestTypes[$row->request_type_id];
                })
                ->editColumn('status', function ($row) {
                    $status = trans('request.' . $row->status);

                    return $status;
                })

                ->rawColumns(['status', 'request_type_id'])


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

        $allRequestTypes = RequestsType::pluck('type', 'id');
        $types = RequestsType::where('type', 'leavesAndDepartures')
            ->pluck('id')->toArray();

        if ($types) {

            $latestProcessesSubQuery = RequestProcess::selectRaw('request_id, MAX(id) as max_id')
                ->groupBy('request_id');

            $requestsProcess = UserRequest::select([
                'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.reason', 'requests.start_date',

                'process.id as process_id', 'process.status', 'process.note as note',  'process.procedure_id as procedure_id', 'process.superior_department_id as superior_department_id',

                'wk_procedures.department_id as department_id', 'wk_procedures.can_return',

                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number',

            ])
                ->leftJoinSub($latestProcessesSubQuery, 'latest_process', function ($join) {
                    $join->on('requests.id', '=', 'latest_process.request_id');
                })
                ->leftJoin('request_processes as process', 'process.id', '=', 'latest_process.max_id')
                // ->leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
                ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'requests.related_to')
                ->where(function ($query) use ($departmentIds) {
                    $query->whereIn('wk_procedures.department_id', $departmentIds)
                        ->orWhereIn('process.superior_department_id', $departmentIds);
                })


                ->whereIn('requests.related_to', $userIds)->whereNull('process.sub_status')
                ->where('users.status', '!=', 'inactive')->whereIn('requests.request_type_id', $types);


            if (request()->ajax()) {

                return DataTables::of($requestsProcess ?? [])
                    ->editColumn('created_at', function ($row) {
                        return Carbon::parse($row->created_at);
                    })
                    ->editColumn('request_type_id', function ($row) use ($allRequestTypes) {
                        return $allRequestTypes[$row->request_type_id];
                    })
                    ->editColumn('status', function ($row) {
                        $status = trans('request.' . $row->status);

                        return $status;
                    })

                    ->rawColumns(['status', 'request_type_id'])


                    ->make(true);
            }
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
        $rawLeaveStatusData = 0;
        $type = RequestsType::where('type', 'leavesAndDepartures')->pluck('id')->toArray();
        if ($type) {
            $rawLeaveStatusData = UserRequest::with(['related_to_user'])->whereIn('related_to', $userIds)
                ->whereIn('request_type_id', $type)->select(DB::raw('status, COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        }


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
