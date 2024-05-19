<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use App\Utils\ModuleUtil;
use App\Utils\NewArrivalUtil;
use App\BusinessLocation;
use App\User;
use App\Category;
use App\Company;
use App\Transaction;
use App\Contact;
use Modules\Sales\Entities\salesContractItem;
use DB;
use Modules\Sales\Entities\SalesProject;
use Spatie\Permission\Models\Permission;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Events\UserCreatedOrModified;
use Carbon\Carbon;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;

use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;
use App\Request as UserRequest;
use App\RequestProcess;
use Modules\CEOManagment\Entities\RequestsType;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeesNotFoundExport;
use Exception;

class EssentialsManageEmployeeController extends Controller
{
    protected $moduleUtil;
    protected $newArrivalUtil;


    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, NewArrivalUtil $newArrivalUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->newArrivalUtil = $newArrivalUtil;
    }

    public function getAmount($salaryType)
    {

        $categories = EssentialsAllowanceAndDeduction::where('id', $salaryType)->select('amount')
            ->first();
        return response()->json($categories);
    }


    public function fetch_user($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $user = User::where('business_id', $business_id)

            ->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */


    public function index(Request $request)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.view') || auth()->user()->can('user.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }


        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');

        $can_show_employee = auth()->user()->can('essentials.show_employee');
        $can_add_employee = auth()->user()->can('essentials.add_employee');
        $can_edit_employee = auth()->user()->can('essentials.edit_employee');
        $can_show_employee_options = auth()->user()->can('essentials.show_employee_options');
        $permissionName = 'essentials.view_profile_picture';


        if (!Permission::where('name', $permissionName)->exists()) {
            $permission = new Permission(['name' => $permissionName]);
            $permission->save();
        } else {

            $permission = Permission::where('name', $permissionName)->first();
        }

        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');
        $appointments2 = EssentialsEmployeeAppointmet::all()->pluck('specialization_id', 'employee_id');
        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $EssentialsProfession = EssentialsProfession::all()->pluck('name', 'id');
        $EssentialsSpecialization = EssentialsSpecialization::all()->pluck('name', 'id');
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $specializations = EssentialsProfession::where('type', 'academic')->pluck('name', 'id');

        // $contract = EssentialsEmployeesContract::all()
        // ->where()->pluck('contract_end_date', 'id');
        $companies = Company::all()->pluck('name', 'id');

        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }

        $users = User::whereIn('users.id', $userIds)
            ->with([
                'userAllowancesAndDeductions',
                'appointment'
            ])
            ->where('users.is_cmmsn_agnt', 0)
            ->where('user_type', '!=', 'worker')

            ->leftJoin('essentials_admission_to_works', function ($join) {
                $join->on('essentials_admission_to_works.employee_id', '=', 'users.id')
                    ->where('essentials_admission_to_works.is_active', 1);
            })
            ->leftJoin('essentials_employees_contracts', function ($join) {
                $join->on('essentials_employees_contracts.employee_id', '=', 'users.id')
                    ->where('essentials_employees_contracts.is_active', 1);
            })
            ->leftJoin('essentials_countries', 'essentials_countries.id', '=', 'users.nationality_id')

            ->select([
                'users.id as id',
                'users.emp_number',
                'users.profile_image',
                'users.username',
                'users.company_id',
                'users.user_type',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''),' ', COALESCE(users.last_name, '')) as full_name"),
                'users.id_proof_number',
                DB::raw("COALESCE(essentials_countries.nationality, '') as nationality"),
                'essentials_admission_to_works.admissions_date as admissions_date',
                'essentials_employees_contracts.contract_end_date as contract_end_date',
                'essentials_employees_contracts.contract_type_id',
                'users.email',
                'users.allow_login',
                'users.contact_number',
                'users.essentials_department_id',
                'users.status',
                'users.essentials_salary',
                'users.total_salary',
            ])
            ->orderBy('id', 'desc');



        if (!empty($request->input('specialization')) && $request->input('specialization') != 'all') {

            $users->whereHas('appointment', function ($query) use ($request) {
                if (!empty($request->input('specialization'))) {
                    $query->where('profession_id', $request->input('specialization'));
                }
            });
        }


        if (!empty($request->input('status')) && $request->input('status') != 'all') {
            $users->where('users.status', $request->input('status'));
        }
        if (!empty($request->input('department')) && $request->input('department') != 'all') {
            error_log($request->input('department'));
            $users->where('users.essentials_department_id', $request->input('department'));
        }
        if (!empty($request->input('contract_type')) && $request->input('contract_type') != 'all') {
            error_log($request->input('contract_type'));
            $users->where('essentials_employees_contracts.contract_type_id', $request->input('contract_type'));
        }

        if (!empty($request->input('company')) && $request->input('company') != 'all') {

            $users->where('users.company_id', $request->input('company'));
        }

        if (!empty($request->input('nationality')) && $request->input('nationality') != 'all') {

            $users->where('users.nationality_id', $request->input('nationality'));
        }
        if (request()->ajax()) {


            return Datatables::of($users)
                ->addColumn('company_id', function ($row)  use ($companies) {
                    $item = $companies[$row->company_id] ?? '';
                    return $item;
                })
                ->addColumn('total_salary', function ($row) {
                    return $row->calculateTotalSalary();
                })

                ->editColumn('essentials_department_id', function ($row) use ($departments) {
                    $item = $departments[$row->essentials_department_id] ?? '';

                    return $item;
                })


                ->addColumn('profession', function ($row) use ($appointments, $job_titles) {
                    $professionId = $appointments[$row->id] ?? '';

                    $professionName = $job_titles[$professionId] ?? '';


                    return $professionName;
                })

                ->addColumn(
                    'action',
                    function ($row)  use ($is_admin, $can_show_employee_options) {
                        if ($is_admin || $can_show_employee_options) {
                            $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' .
                                __('messages.actions') .
                                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li>
                                <a href="#" class="btn-modal1"  data-toggle="modal" data-target="#addQualificationModal"  data-row-id="' . $row->id . '"  data-row-name="' . $row->full_name . '"  data-href=""><i class="fas fa-plus" aria-hidden="true"></i>' . __('essentials::lang.add_qualification') . '</a>
                             
                                </a>
                                </li>';


                            $html .= '<li>
                                    <a href="#" class="btn-modal2"  data-toggle="modal" data-target="#add_doc"  data-row-id="' . $row->id . '"  data-row-name="' . $row->full_name . '"  data-href=""><i class="fas fa-plus" aria-hidden="true"></i>' . __('essentials::lang.add_doc') . '</a>
                                </li>';

                            $html .= '<li>
                                <a class=" btn-modal3" data-toggle="modal" data-target="#addContractModal"><i class="fas fa-plus" aria-hidden="true"></i>' . __('essentials::lang.add_contract') . '</a>
                            </li>';

                            $html .= '</ul></div>';

                            return $html;
                        }
                    }
                )
                ->addColumn('view', function ($row) use ($can_show_employee, $is_admin) {
                    if ($is_admin || $can_show_employee) {
                        $html = '<a href="' . route('showEmployee', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye"></i> ' . __('messages.view') . '</a>';

                        return $html;
                    }
                })

                ->filterColumn('full_name', function ($query, $keyword) {

                    $query->whereRaw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''),' ', COALESCE(users.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('nationality', function ($query, $keyword) {
                    $query->whereRaw("COALESCE(essentials_countries.nationality, '')  like ?", ["%{$keyword}%"]);
                })

                ->filterColumn('id_proof_number', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number  like ?", ["%{$keyword}%"]);
                })

                ->filterColumn('emp_number', function ($query, $keyword) {
                    $query->whereRaw("emp_number  like ?", ["%{$keyword}%"]);
                })

                ->filterColumn('admissions_date', function ($query, $keyword) {
                    $query->whereRaw("admissions_date  like ?", ["%{$keyword}%"]);
                })

                ->filterColumn('contract_end_date', function ($query, $keyword) {
                    $query->whereRaw("contract_end_date  like ?", ["%{$keyword}%"]);
                })


                ->filterColumn('profession', function ($query, $keyword) {
                    $query->whereHas('appointment.profession', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', '%' . $keyword . '%');
                    });
                })
                //->removecolumn('id')
                ->rawColumns(['user_type', 'company_id', 'action', 'profession', 'view'])
                ->make(true);
        }


        $countries = EssentialsCountry::forDropdown();
        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');


        $companies = Company::whereIn('id', $companies_ids)->pluck('name', 'id');
        $status = [
            'active' => 'active',
            'inactive' => 'inactive',
            'terminated' => 'terminated',
            'vecation' => 'vecation',
        ];



        $offer_prices = Transaction::where([['transactions.type', '=', 'sell'], ['transactions.status', '=', 'approved']])
            ->leftJoin('sales_contracts', 'transactions.id', '=', 'sales_contracts.offer_price_id')
            ->whereNull('sales_contracts.offer_price_id')->pluck('transactions.ref_no', 'transactions.id');
        $items = salesContractItem::pluck('name_of_item', 'id');

        return view('essentials::employee_affairs.employee_affairs.index')
            ->with(compact(
                'departments',
                'contract_types',
                'nationalities',
                'specializations',
                'countries',
                'job_titles',
                'status',
                'offer_prices',
                'items',
                'companies',
                'spacializations'
            ));
    }


    public function employee_affairs_dashboard()
    {
        $today = now();
        $endDateThreshold = $today->copy()->addDays(14);

        $today = now();
        $endDateThreshold = $today->copy()->addDays(60);

        $ContactsLocation = SalesProject::all()->pluck('name', 'id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        $business_id = request()->session()->get('user.business_id');
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $probation_period = EssentialsEmployeesContract::whereIn('employee_id', $userIds)->where('probation_period', 3)
            ->where(function ($query) use ($today) {
                $query->whereDate('contract_start_date', '<=', $today)
                    ->orWhereNull('contract_start_date');
            })
            ->whereDate(DB::raw('DATE_ADD(contract_start_date, INTERVAL probation_period MONTH)'), '>', $endDateThreshold)
            ->count();

        $contract_end_date = EssentialsEmployeesContract::whereIn('employee_id', $userIds)->where(function ($query) use ($today) {
            $query->whereDate('contract_start_date', '<=', $today)
                ->orWhereNull('contract_start_date');
        })
            ->whereDate('contract_end_date', '<=', $endDateThreshold)
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
                ->select('end_date')->count();
        }


        $nullCount = User::whereIn('id', $userIds)
            ->with(['essentialsEmployeesInsurance', 'activeAdmission', 'activeAppointmet', 'activeInternationalCertificate', 'activeCarRegistration', 'activeDriversLicense', 'activeNationalId', 'activeIban', 'activeResidencePermit', 'activePassport', 'activeOfficialDocument', 'activeContract'])
            ->where(function ($query) {
                $query->where(function ($q1) {
                    $q1->whereIn('user_type', ['employee', 'manager'])
                        ->where(function ($q2) {
                            $q2->whereDoesntHave('activeContract')
                                ->orWhereDoesntHave('activeOfficialDocument')
                                ->orWhereDoesntHave('activePassport')
                                ->orWhereDoesntHave('activeResidencePermit')
                                ->orWhereDoesntHave('activeIban')
                                ->orWhereDoesntHave('activeNationalId')
                                ->orWhereDoesntHave('activeDriversLicense')
                                ->orWhereDoesntHave('activeCarRegistration')
                                ->orWhereDoesntHave('activeInternationalCertificate')
                                ->orWhereDoesntHave('activeAppointmet')
                                ->orWhereDoesntHave('activeAdmission')
                                ->orWhereDoesntHave('essentialsEmployeesInsurance');
                        });
                })->orWhere(function ($q1) {
                    $q1->where('user_type', 'worker')
                        ->where(function ($q2) {
                            $q2->whereDoesntHave('activeContract')
                                ->orWhereDoesntHave('activeOfficialDocument')
                                ->orWhereDoesntHave('activePassport')
                                ->orWhereDoesntHave('activeResidencePermit')
                                ->orWhereDoesntHave('activeAppointmet')
                                ->orWhereDoesntHave('activeAdmission')
                                ->orWhereDoesntHave('essentialsEmployeesInsurance');
                        });
                });
            })
            ->count();



        $requestsProcess = null;

        $allRequestTypes = RequestsType::pluck('type', 'id');

        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%موظف%')
            ->pluck('id')->toArray();

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
        return view('essentials::employee_affairs.dashboard')
            ->with(compact(
                'probation_period',
                'contract_end_date',
                'late_vacation',
                'nullCount'
            ));
    }


    public function finsish_contract_duration()
    {
        $today = now();
        $endDateThreshold = $today->copy()->addDays(14);
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $probation_period = EssentialsEmployeesContract::whereIn('employee_id', $userIds)->with('user')

            ->where('probation_period', 3)
            ->where(function ($query) use ($today) {
                $query->whereDate('contract_start_date', '<=', $today)
                    ->orWhereNull('contract_start_date');
            })
            ->whereDate(DB::raw('DATE_ADD(contract_start_date, INTERVAL probation_period MONTH)'), '>', $endDateThreshold)
            ->select('contract_end_date', 'employee_id');


        // dd( $residencies->first());

        if (request()->ajax()) {

            return DataTables::of($probation_period)
                ->addColumn(
                    'worker_name',
                    function ($row) {
                        return $row->user?->first_name . ' ' . $row->user?->last_name ?? '';
                    }
                )

                ->addColumn(
                    'project',
                    function ($row) {
                        return $row->user?->assignedTo?->contact?->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->user?->assignedTo?->contact->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'end_date',
                    function ($row) {
                        return $row->contract_end_date;
                    }
                )
                ->addColumn(
                    'action',
                    ''
                    // function ($row) {
                    //     $html = '';
                    //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                    //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                    //     return $html;
                    // }
                )


                ->removeColumn('id')
                ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
                ->make(true);
        }

        return view('essentials::employee_affairs.statistics.finsish_contract_duration');
    }

    public function finish_contracts()
    {
        $today = now();
        $endDateThreshold = $today->copy()->addDays(60);

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $contract_end_date = EssentialsEmployeesContract::whereIn('employee_id', $userIds)->with(['user'])
            ->whereDate('contract_end_date', '<=', $endDateThreshold)
            ->select('contract_end_date', 'employee_id');

        //  dd( $contract_end_date->first());

        if (request()->ajax()) {

            return DataTables::of($contract_end_date)
                ->addColumn(
                    'worker_name',
                    function ($row) {
                        return $row->user?->first_name . ' ' . $row->user?->last_name ?? '';
                    }
                )

                ->addColumn(
                    'project',
                    function ($row) {
                        return $row->user?->assignedTo?->contact?->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->user?->assignedTo?->contact?->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'end_date',
                    function ($row) {
                        return $row->contract_end_date;
                    }
                )
                ->addColumn(
                    'action',
                    ''
                    // function ($row) {
                    //     $html = '';
                    //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                    //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                    //     return $html;
                    // }
                )


                ->removeColumn('id')
                ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
                ->make(true);
        }

        return view('essentials::employee_affairs.statistics.finish_contracts');
    }

    public function uncomplete_profiles()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        $companies_ids = Company::pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {
                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where(
                        'access_role_id',
                        $accessRole->id
                    )
                        ->pluck('company_id')
                        ->toArray();
                }
            }
        }

        $usersWithNullAdmission = User::whereIn('id', $userIds)
            ->with(['essentialsEmployeesInsurance', 'activeAdmission', 'activeAppointmet', 'activeInternationalCertificate', 'activeCarRegistration', 'activeDriversLicense', 'activeNationalId', 'activeIban', 'activeResidencePermit', 'activePassport', 'activeOfficialDocument', 'activeContract'])
            ->where(function ($query) {
                $query->where(function ($q1) {
                    $q1->whereIn('user_type', ['employee', 'manager'])
                        ->where(function ($q2) {
                            $q2->whereDoesntHave('activeContract')
                                ->orWhereDoesntHave('activeOfficialDocument')
                                ->orWhereDoesntHave('activePassport')
                                ->orWhereDoesntHave('activeResidencePermit')
                                ->orWhereDoesntHave('activeIban')
                                ->orWhereDoesntHave('activeNationalId')
                                ->orWhereDoesntHave('activeDriversLicense')
                                ->orWhereDoesntHave('activeCarRegistration')
                                ->orWhereDoesntHave('activeInternationalCertificate')
                                ->orWhereDoesntHave('activeAppointmet')
                                ->orWhereDoesntHave('activeAdmission')
                                ->orWhereDoesntHave('essentialsEmployeesInsurance');
                        });
                })->orWhere(function ($q1) {
                    $q1->where('user_type', 'worker')
                        ->where(function ($q2) {
                            $q2->whereDoesntHave('activeContract')
                                ->orWhereDoesntHave('activeOfficialDocument')
                                ->orWhereDoesntHave('activePassport')
                                ->orWhereDoesntHave('activeResidencePermit')
                                ->orWhereDoesntHave('activeAppointmet')
                                ->orWhereDoesntHave('activeAdmission')
                                ->orWhereDoesntHave('essentialsEmployeesInsurance');
                        });
                });
            });

        if (!empty(request()->input('user_type_filter')) && request()->input('user_type_filter') !== 'all') {
            $usersWithNullAdmission->where('users.user_type', request()->input('user_type_filter'));
        }
        if (!empty(request()->input('company_filter')) && request()->input('company_filter') !== 'all') {
            $usersWithNullAdmission->where('users.company_id', request()->input('company_filter'));
        }
        if (!empty(request()->input('project_filter')) && request()->input('project_filter') !== 'all') {
            $usersWithNullAdmission->where('users.assigned_to', request()->input('project_filter'));
        }
        if (!empty(request()->input('missing_info_filter')) && request()->input('missing_info_filter') !== 'all') {
            $missing_info_filter = request()->input('missing_info_filter');

            if ($missing_info_filter == 'appointment') {
                $usersWithNullAdmission->whereDoesntHave('activeAppointmet');
            } else if ($missing_info_filter == 'admissions_to_work') {
                $usersWithNullAdmission->whereDoesntHave('activeAdmission');
            } else if ($missing_info_filter == 'health_insurance') {
                $usersWithNullAdmission->whereDoesntHave('essentialsEmployeesInsurance');
            }
        }
        if (!empty(request()->input('missing_files_filter')) && request()->input('missing_files_filter') !== 'all') {
            $missing_files_filter = request()->input('missing_files_filter');
            if ($missing_files_filter == 'contract') {
                $usersWithNullAdmission->whereDoesntHave('activeContract');
            }
            if ($missing_files_filter == 'passport') {
                $usersWithNullAdmission->whereDoesntHave('activePassport');
            }
            if ($missing_files_filter == 'residence_permit') {
                $usersWithNullAdmission->whereDoesntHave('activeResidencePermit');
            }
            if ($missing_files_filter == 'Iban') {
                $usersWithNullAdmission->whereIn('user_type', ['employee', 'manager'])->whereDoesntHave('activeIban');
            }
            if ($missing_files_filter == 'national_id') {
                $usersWithNullAdmission->whereIn('user_type', ['employee', 'manager'])->whereDoesntHave('activeNationalId');
            }
            if ($missing_files_filter == 'drivers_license') {
                $usersWithNullAdmission->whereIn('user_type', ['employee', 'manager'])->whereDoesntHave('activeDriversLicense');
            }
            if ($missing_files_filter == 'car_registration') {
                $usersWithNullAdmission->whereIn('user_type', ['employee', 'manager'])->whereDoesntHave('activeCarRegistration');
            }
            if ($missing_files_filter == 'international_certificate') {
                $usersWithNullAdmission->whereIn('user_type', ['employee', 'manager'])->whereDoesntHave('activeInternationalCertificate');
            }
        }

        if (request()->ajax()) {
            return DataTables::of($usersWithNullAdmission)
                ->addColumn(
                    'missings_files',
                    function ($row) {
                        $missings_files = '';
                        try {
                            if ($row->activeContract->is_active == 0) {
                                $missings_files .= __('essentials::lang.contract') . '\n';
                            }
                        } catch (Exception $e) {

                            $missings_files .= __('essentials::lang.contract') . '\n';
                        }
                        if ($row->user_type == 'worker') {

                            try {
                                if ($row->activePassport->is_active == 0) {
                                    $missings_files .= __('essentials::lang.passport') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.passport') . '\n';
                            }
                            try {
                                if ($row->activeResidencePermit->is_active == 0) {
                                    $missings_files .= __('essentials::lang.residence_permit') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.residence_permit') . '\n';
                            }
                            if ($missings_files == '') {
                                try {
                                    if ($row->activeOfficialDocument->first()->is_active == 0) {
                                        $missings_files .= __('essentials::lang.passport') . '\n' . __('essentials::lang.residence_permit') . '\n';
                                    }
                                } catch (Exception $e) {
                                    $missings_files .= __('essentials::lang.passport') . '\n' . __('essentials::lang.residence_permit') . '\n';
                                }
                            }
                        }
                        if ($row->user_type == 'employee' || $row->user_type == 'manager') {
                            try {
                                if ($row->activePassport->is_active == 0) {
                                    $missings_files .= __('essentials::lang.passport') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.passport') . '\n';
                            }
                            try {
                                if ($row->activeResidencePermit->is_active == 0) {
                                    $missings_files .= __('essentials::lang.residence_permit') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.residence_permit') . '\n';
                            }
                            try {
                                if ($row->activeIban->is_active == 0) {
                                    $missings_files .= __('essentials::lang.Iban') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.Iban') . '\n';
                            }
                            try {
                                if ($row->activeNationalId->is_active == 0) {
                                    $missings_files .= __('essentials::lang.national_id') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.national_id') . '\n';
                            }
                            try {
                                if ($row->activeDriversLicense->is_active == 0) {
                                    $missings_files .= __('essentials::lang.drivers_license') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.drivers_license') . '\n';
                            }
                            try {
                                if ($row->activeCarRegistration->is_active == 0) {
                                    $missings_files .= __('essentials::lang.car_registration') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.car_registration') . '\n';
                            }
                            try {
                                if ($row->activeInternationalCertificate->is_active == 0) {
                                    $missings_files .= __('essentials::lang.international_certificate') . '\n';
                                }
                            } catch (Exception $e) {
                                $missings_files .= __('essentials::lang.international_certificate') . '\n';
                            }
                            if ($missings_files == '') {
                                try {
                                    if ($row->activeOfficialDocument->first()->is_active == 0) {
                                        $missings_files .= __('essentials::lang.passport') . '\n' . __('essentials::lang.residence_permit') . '\n' . __('essentials::lang.Iban') . '\n' . __('essentials::lang.national_id') . '\n' . __('essentials::lang.drivers_license') . '\n' . __('essentials::lang.car_registration') . '\n' . __('essentials::lang.international_certificate') . '\n';
                                    }
                                } catch (Exception $e) {
                                    $missings_files .= __('essentials::lang.passport') . '\n' . __('essentials::lang.residence_permit') . '\n' . __('essentials::lang.Iban') . '\n' . __('essentials::lang.national_id') . '\n' . __('essentials::lang.drivers_license') . '\n' . __('essentials::lang.car_registration') . '\n' . __('essentials::lang.international_certificate') . '\n';
                                }
                            }
                        }
                        return $missings_files;
                    }
                )
                ->addColumn(
                    'missings_info',
                    function ($row) {
                        $missings_info = '';
                        try {
                            if ($row->activeAppointmet->is_active == 0) {
                                $missings_info .= __('essentials::lang.appointment') . '\n';
                            }
                        } catch (Exception $e) {
                            $missings_info .= __('essentials::lang.appointment') . '\n';
                        }
                        try {
                            if ($row->activeAdmission->is_active == 0) {
                                $missings_info .= __('essentials::lang.admissions_to_work') . '\n';
                            }
                        } catch (Exception $e) {
                            $missings_info .= __('essentials::lang.admissions_to_work') . '\n';
                        }
                        try {
                            $row->essentialsEmployeesInsurance->id;
                        } catch (Exception $e) {
                            $missings_info .= __('essentials::lang.health_insurance') . '\n';
                        }

                        return  $missings_info;
                    }
                )
                ->addColumn(
                    'worker_name',
                    function ($row) {

                        return $row->first_name . ' ' . $row->mid_name . ' ' . $row->last_name  ?? '';
                    }
                )

                ->addColumn(
                    'project',
                    function ($row) {
                        return $row->assignedTo?->contact?->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'sponsor',
                    function ($row) {

                        $sponsor_company = $row?->essentialsEmployeeAppointmets?->sponsor_company ?? null;
                        $sponsor_name = $row?->essentialsEmployeeAppointmets?->sponsor_name ?? null;


                        $sponsor = '';
                        if ($sponsor_company !== null) {
                            $sponsor = Company::find($sponsor_company)->name;
                        } elseif ($sponsor_name !== null) {
                            $sponsor = $sponsor_name;
                        }

                        return $sponsor;
                    }
                )


                ->addColumn(
                    'action',
                    ''
                    // function ($row) {
                    //     $html = '';
                    //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                    //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                    //     return $html;
                    // }
                )



                ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
                ->make(true);
        }
        $companies = Company::whereIn('id', $companies_ids)->pluck('name', 'id')->toArray();
        $projects =  SalesProject::pluck('name', 'id')->toArray();
        $missing_files = [
            'contract' => __('essentials::lang.contract'),
            'passport' => __('essentials::lang.passport'),
            'residence_permit' => __('essentials::lang.residence_permit'),
            'Iban' => __('essentials::lang.Iban'),
            'national_id' => __('essentials::lang.national_id'),
            'drivers_license' => __('essentials::lang.drivers_license'),
            'car_registration' => __('essentials::lang.car_registration'),
            'international_certificate' => __('essentials::lang.international_certificate')

        ];
        $missing_info = [
            'appointment' => __('essentials::lang.appointment'),
            'admissions_to_work' => __('essentials::lang.admissions_to_work'),
            'health_insurance' => __('essentials::lang.health_insurance'),
        ];
        return view('essentials::employee_affairs.statistics.uncomplete_profies')->with(compact('companies', 'projects', 'missing_files', 'missing_info'));
    }


    public function late_admission()
    {


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $late_vacation = [];
        $type = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'employee')->first();
        if ($type) {
            $late_vacation = UserRequest::with('related_to_user')->whereIn('related_to', $userIds)
                ->where('request_type_id', $type->id)
                ->whereHas('related_to_user', function ($query) {
                    $query->where('status', 'vecation');
                })
                ->where('end_date', '<', now());
        }
        if (request()->ajax()) {

            return DataTables::of($late_vacation)
                ->addColumn(
                    'worker_name',
                    function ($row) {
                        return $row->user->first_name . ' ' . $row->user->last_name ?? '';
                    }
                )

                ->addColumn(
                    'project',
                    function ($row) {
                        return $row->user->assignedTo?->contact?->supplier_business_name ?? null;
                    }
                )
                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->user->assignedTo?->contact->supplier_business_name ?? null;
                    }
                )

                ->addColumn(
                    'customer_name',
                    function ($row) {
                        return $row->user->status ?? null;
                    }
                )

                ->addColumn(
                    'action',
                    ''
                    // function ($row) {
                    //     $html = '';
                    //     $html .= '<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal" data-href="' . route('doc.view', ['id' => $row->id]) . '"><i class="fa fa-eye"></i> ' . __('essentials::lang.view') . '</button>  &nbsp;';
                    //     $html .= '<a  href="' . route('doc.edit', ['id' => $row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a> &nbsp;';
                    //     $html .= '<button class="btn btn-xs btn-danger delete_doc_button" data-href="' . route('offDoc.destroy', ['id' => $row->id]) . '"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';

                    //     return $html;
                    // }
                )


                ->removeColumn('id')
                ->rawColumns(['worker_name', 'residency', 'project', 'end_date', 'action'])
                ->make(true);
        }


        return view('essentials::employee_affairs.statistics.late_vacaction');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');


        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('users', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action([\App\Http\Controllers\ManageUserController::class, 'index']));
        }

        $roles = $this->getRolesArray($business_id);
        $username_ext = $this->moduleUtil->getUsernameExtension();
        // $locations = BusinessLocation::where('business_id', $business_id)
        //     ->Active()
        //     ->get();

        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');

        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $contacts = SalesProject::pluck('name', 'id');

        $blood_types = [
            'A+' => 'A positive (A+).',
            'A-' => 'A negative (A-).',
            'B+' => 'B positive (B+)',
            'B-' => 'B negative (B-).',
            'AB+' => 'AB positive (AB+).',
            'AB-' => 'AB negative (AB-).',
            'O+' => 'O positive (O+).',
            'O-' => 'O positive (O-).',
        ];



        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'academic')->pluck('name', 'id');
        $countries = EssentialsCountry::forDropdown();
        $resident_doc = null;
        $companies = Company::all()->pluck('name', 'id');
        $user = null;
        return view('essentials::employee_affairs.employee_affairs.create')
            ->with(compact(
                'roles',
                'countries',
                'professions',
                'spacializations',
                'nationalities',
                'username_ext',
                'blood_types',
                'contacts',
                'companies',
                'banks',
                'contract_types',
                'form_partials',
                'resident_doc',
                'user'
            ));
    }

    public function updateEmployeeProfilePicture(Request $request, $id)
    {
        try {

            $user = User::find($id);
            if (!$user) {
                throw new \Exception("User not found");
            }

            if ($request->hasFile('profile_picture')) {
                // Handle file upload
                $image = $request->file('profile_picture');
                $profile = $image->store('/profile_images');

                $user->update(['profile_image' => $profile, 'updated_by' => Auth::user()->id]);
                error_log($profile);
            } elseif ($request->input('delete_image') == '1') {
                $oldImage = $user->profile_image;
                if ($oldImage) {
                    Storage::delete($oldImage);
                }
                $user->update(['profile_image' => null, 'updated_by' => Auth::user()->id]);
                // Make sure to reset the delete_image flag in case of future updates
                $request->request->remove('delete_image');
            }

            $output = [
                'success' => 1,
                'msg' => __('user.user_update_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->back()->with('status', $output);
    }


    public function createWorker($id)
    {
        $contact = SalesProject::find($id);
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');


        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('users', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action([\App\Http\Controllers\ManageUserController::class, 'index']));
        }
        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'academic')->pluck('name', 'id');
        $countries = EssentialsCountry::forDropdown();
        $roles = $this->getRolesArray($business_id);
        $username_ext = $this->moduleUtil->getUsernameExtension();
        $locations = BusinessLocation::where('business_id', $business_id)
            ->Active()
            ->get();
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');

        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);
        $nationalities = EssentialsCountry::nationalityForDropdown();



        $blood_types = [
            'A+' => 'A positive (A+).',
            'A-' => 'A negative (A-).',
            'B+' => 'B positive (B+)',
            'B-' => 'B negative (B-).',
            'AB+' => 'AB positive (AB+).',
            'AB-' => 'AB negative (AB-).',
            'O+' => 'O positive (O+).',
            'O-' => 'O positive (O-).',
        ];
        $resident_doc = null;
        $user = null;
        return view('followup::workers.create')
            ->with(compact(
                'roles',
                'nationalities',
                'username_ext',
                'countries',
                'blood_types',
                'spacializations',
                'professions',
                'contact',
                'locations',
                'banks',
                'contract_types',
                'form_partials',
                'resident_doc',
                'user'
            ));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $business_id = request()->session()->get('user.business_id');
        if (!($is_admin || auth()->user()->can('user.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            if (!empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            $request['cmmsn_percent'] = !empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;
            $request['max_sales_discount_percent'] = !is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;
            $request['DocumentTypes'] = $request->input('DocumentTypes');

            $com_id = request()->input('company_id');
            error_log($com_id);
            $emp_number = request()->input('emp_number');
            if ($emp_number) {
                $request['emp_number'] = $emp_number;
            } else {
                $request['emp_number'] = $this->moduleUtil->generateEmpNumber($com_id);
            }

            // $latestRecord = User::where('company_id', $com_id)->orderBy('emp_number', 'desc')
            //     ->first();

            // if ($latestRecord) {
            //     $latestRefNo = $latestRecord->emp_number;
            //     $latestRefNo++;
            //     $request['emp_number'] = str_pad($latestRefNo, 4, '0', STR_PAD_LEFT);
            // } else {

            //     $request['emp_number'] =  $com_id . '001';
            // }



            $existingprofnumber = User::where('id_proof_number', $request->input('id_proof_number'))->first();

            if ($existingprofnumber) {

                $output = [
                    'success' => 0,
                    'msg' => __('essentials::lang.user_with_same_id_proof_number_exists'),
                ];
            } else {
                $user = $this->moduleUtil->createUser($request);
                $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_saved',  'model_instance' => $user, 'request' => $user]);
                $output = [
                    'success' => 1,
                    'msg' => __('user.user_added'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('employees')->with('status', $output);
    }


    public function storeWorker(Request $request)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.create'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            if (!empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            $request['cmmsn_percent'] = !empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;

            $request['max_sales_discount_percent'] = !is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;

            // $business_id = request()->session()->get('user.business_id');

            // $numericPart = (int)substr($business_id, 3);
            // $lastEmployee = User::where('business_id', $business_id)
            //     ->orderBy('emp_number', 'desc')
            //     ->first();

            // if ($lastEmployee) {

            //     $lastEmpNumber = (int)substr($lastEmployee->emp_number, 3);

            //     $nextNumericPart = $lastEmpNumber + 1;

            //     $request['emp_number'] = $business_id . str_pad($nextNumericPart, 6, '0', STR_PAD_LEFT);
            // } else {

            //     $request['emp_number'] =  $business_id . '000';
            // }

            if ($request->input('id_proof_number')) {
                $existingprofnumber = User::where('id_proof_number', $request->input('id_proof_number'))->first();
            }
            if ($request->input('border_no')) {
                $existingBordernumber = User::where('border_no', $request->input('border_no'))->first();
            }



            if ($existingprofnumber || $existingBordernumber) {

                if ($existingprofnumber != null) {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.user_with_same_id_proof_number_exists'),
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.worker_with_same_border_number_exists'),
                    ];
                }
            } else {
                $user = $this->moduleUtil->createUser($request);
                event(new UserCreatedOrModified($user, 'added'));

                $output = [
                    'success' => 1,
                    'msg' => __('user.user_added'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('projects')->with('status', $output);
    }

    public function show($id)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_show_employee = auth()->user()->can('essentials.show_employee');
        $business_id = request()->session()->get('user.business_id');
        // $documents = null;

        if (!($is_admin || $can_show_employee)) {
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



        $user = User::whereIn('users.id', $userIds)
            ->with(['contactAccess', 'OfficialDocument', 'proposal_worker', 'essentials_qualification'])
            ->select('*', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"))
            ->find($id);





        $documents = new Collection();

        if (
            $user && ($user->user_type == 'employee' || $user->user_type == 'manager')
        ) {
            $officialDocuments = $user->OfficialDocument()->where('is_active', 1)->get();

            $contractDoc = $user->contract()->where('is_active', 1)->first();
            if ($contractDoc) {
                $documents->push($contractDoc);
            }


            $qualificationDoc = $user->essentials_qualification()->first();
            if ($qualificationDoc) {
                $documents->push($qualificationDoc);
            }


            if ($officialDocuments !== null) {
                $documents = $documents->merge($officialDocuments); // Merge official documents with other documents
            }
        }


        $dataArray = [];
        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }


        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value('name');
        $admissions_to_work = EssentialsAdmissionToWork::where('employee_id', $user->id)->first();
        $Qualification = EssentialsEmployeesQualification::where('employee_id', $user->id)->first();

        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)
            ->where('status', 'valid')
            ->where('is_active', 1)
            ->first();


        $professionId = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->where('is_active', 1)
            ->value('profession_id');

        $sponsor_company = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->where('is_active', 1)
            ->value('sponsor_company');
        $sponsor_name = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->where('is_active', 1)
            ->value('sponsor_name');


        if ($professionId !== null) {
            $profession = EssentialsProfession::find($professionId)->name;
        } else {
            $profession = "";
        }
        if ($sponsor_company !== null) {
            $sponsor = Company::find($sponsor_company)->name;
        } elseif ($sponsor_name !== null) {
            $sponsor = $sponsor_name;
        } else {
            $sponsor = '';
        }


        $user->profession = $profession;
        $user->sponsor_company = $sponsor;



        $view_partials = $this->moduleUtil->getModuleData(
            'moduleViewPartials',
            ['view' => 'manage_user.show', 'user' => $user]
        );

        $users = User::forDropdown($business_id, false);

        $activities = Activity::forSubject($user)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $nationality_id = $user->nationality_id;
        $nationality = "";
        if (!empty($nationality_id)) {
            $nationality = EssentialsCountry::select('nationality')->where('id', '=', $nationality_id)->first();
        }



        return view('essentials::employee_affairs.employee_affairs.show')->with(compact(
            'user',
            'view_partials',
            'users',
            'activities',
            'bank_name',
            'admissions_to_work',
            'Qualification',
            'Contract',
            'nationalities',
            'nationality',
            'documents',

        ));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function get_connect_camera()
    {
        return view('essentials::employee_affairs.employee_affairs.connect_camera_index');
    }
    public function edit($id)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.update'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = User::with(['contactAccess', 'assignedTo', 'OfficialDocument'])
            ->findOrFail($id);

        $contacts = SalesProject::pluck('name', 'id');
        $countries = EssentialsCountry::forDropdown();
        $projects = SalesProject::pluck('name', 'id');
        $appointments = EssentialsEmployeeAppointmet::select([

            'profession_id', 'sponsor_company'

        ])->where('employee_id', $id)->where('is_active', 1)
            ->first();
        if ($appointments !== null) {
            $user->profession_id = $appointments['profession_id'];
            $user->sponsor = $appointments['sponsor_company'];
        } else {
            $user->profession_id = null;
            $user->sponsor = 'other_suponser';
        }
        $blood_types = [
            'A+' => 'A positive (A+).',
            'A-' => 'A negative (A-).',
            'B+' => 'B positive (B+)',
            'B-' => 'B negative (B-).',
            'AB+' => 'AB positive (AB+).',
            'AB-' => 'AB negative (AB-).',
            'O+' => 'O positive (O+).',
            'O-' => 'O positive (O-).',
        ];

        $idProofName = $user->id_proof_name;
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $roles = $this->getRolesArray($business_id);
        $contact_access = $user->contactAccess->pluck('name', 'id')->toArray();
        $contract_types = EssentialsContractType::all()->pluck('type', 'id');

        $contract = EssentialsEmployeesContract::where('employee_id', '=', $user->id)
            ->where('is_active', 1)
            ->where('status', 'valid')
            ->select('*')
            ->first();
        //dd($contract);


        $allowance_deduction_ids = [];
        if (!empty($user)) {
            $allowance_deduction_ids = EssentialsUserAllowancesAndDeduction::with('essentialsAllowanceAndDeduction')
                ->where('user_id', $user->id)
                ->get();
        }


        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');
        $professions = EssentialsProfession::where('type', 'academic')->pluck('name', 'id');
        $sponsor_company = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->where('is_active', 1)
            ->value('sponsor_company');
        $sponsor_name = EssentialsEmployeeAppointmet::where('employee_id', $user->id)->where('is_active', 1)
            ->value('sponsor_name');
        if ($user->status == 'active') {
            $is_checked_checkbox = true;
        } else {
            $is_checked_checkbox = false;
        }

        if ($sponsor_company !== null) {
            $sponsor = Company::find($sponsor_company)->name;
        } elseif ($sponsor_name !== null) {

            $user->sponsor = 'other_suponser';
            $user->sponsor_name = $sponsor_name;
        } else {
            $user->sponsor = '';
        }

        $locations = BusinessLocation::where('business_id', $business_id)
            ->get();

        $permitted_locations = $user->permitted_locations();
        $username_ext = $this->moduleUtil->getUsernameExtension();
        $banks = EssentialsBankAccounts::all()->pluck('name', 'id');

        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);

        $qualification = EssentialsEmployeesQualification::where('employee_id', $id)->first();

        $resident_doc = EssentialsOfficialDocument::select(['expiration_date', 'number'])->where('employee_id', $id)->where('is_active', 1)
            ->first();
        $officalDocuments = $user->OfficialDocument;

        return view('essentials::employee_affairs.employee_affairs.edit')
            ->with(compact(
                'officalDocuments',
                'projects',
                'contacts',
                'spacializations',
                'qualification',
                'resident_doc',
                'countries',
                'roles',
                'banks',
                'idProofName',
                'user',
                'blood_types',
                'contact_access',
                'is_checked_checkbox',
                'locations',
                'permitted_locations',
                'form_partials',
                'appointments',
                'username_ext',
                'contract_types',
                'contract',
                'nationalities',
                'allowance_deduction_ids',
                'professions'
            ));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('user.update'))) {
            //temp  abort(403, 'Unauthorized action.');
        }
        try {
            $user_data = $request->only([
                'surname', 'first_name', 'last_name', 'email', 'selected_contacts', 'marital_status', 'border_no', 'bank_details',
                'blood_group', 'contact_number', 'fb_link', 'twitter_link', 'social_media_1', 'location_id',
                'social_media_2', 'permanent_address', 'current_address', 'profession', 'specialization',
                'guardian_name', 'custom_field_1', 'custom_field_2', 'nationality', 'contract_type', 'contract_start_date', 'contract_end_date',
                'contract_duration', 'probation_period',
                'is_renewable', 'contract_file', 'essentials_salary', 'essentials_pay_period',
                'salary_type', 'amount', 'can_add_category',
                'travel_ticket_categorie', 'health_insurance', 'selectedData',
                'custom_field_3', 'custom_field_4', 'id_proof_name', 'id_proof_number', 'cmmsn_percent', 'gender', 'essentials_department_id',
                'max_sales_discount_percent', 'family_number', 'alt_number', 'Iban_file', 'emp_number', 'company_id'

            ]);


            $existing_user =  User::findOrFail($id);

            if (!$existing_user) {
                $output = [
                    'success' => 0,
                    'msg' => __('essentials::lang.user_not_found'),
                ];
            } else {
                $is_unique = User::where('id_proof_number', $request->input('id_proof_number'))->first();
                if (!$is_unique || ($is_unique && $is_unique->id == $id)) {

                    if (!isset($user_data['selected_contacts'])) {
                        $user_data['selected_contacts'] = 0;
                    }

                    if ($user_data['emp_number'] == null) {
                        $comp_id = request()->input('company_id');
                        //dd($comp_id);
                        $user_data['emp_number'] = $this->moduleUtil->generateEmpNumber($comp_id);
                    }

                    $user_data['cmmsn_percent'] = !empty($user_data['cmmsn_percent']) ? $this->moduleUtil->num_uf($user_data['cmmsn_percent']) : 0;

                    $user_data['max_sales_discount_percent'] = null;
                    if (!empty($request->input('dob'))) {
                        $user_data['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
                    }
                    if (!empty($request->input('border_no'))) {
                        $user_data['border_no'] = $request->input('border_no');
                    }
                    if (!empty($request->input('nationality'))) {
                        $user_data['nationality_id'] = $request->input('nationality');
                    }
                    if (!empty($request->input('bank_details'))) {
                        $user_data['bank_details'] = json_encode($request->input('bank_details'));
                    }
                    if (!empty($request->input('has_insurance'))) {
                        $user_data['has_insurance'] = json_encode($request->input('has_insurance'));
                    }

                    DB::beginTransaction();



                    $delete_iban_file = $request->delete_iban_file ?? null;
                    if ($delete_iban_file && $delete_iban_file == 1) {

                        $filePath =  !empty($existing_user->bank_details) ? json_decode($existing_user->bank_details, true)['Iban_file'] ?? null : null;
                        if ($filePath) {
                            Storage::delete($filePath);
                        }
                    }

                    if ($request->hasFile('Iban_file')) {
                        $file = $request->file('Iban_file');
                        $path = $file->store('/officialDocuments');
                        $bank_details = $request->input('bank_details');
                        $bank_details['Iban_file'] = $path;
                        $user_data['bank_details'] = json_encode($bank_details);

                        $bankCode = $bank_details['bank_code'];
                        $input = [
                            'number' => $bankCode,
                            'file_path' => $path,
                        ];

                        $Iban_doc = EssentialsOfficialDocument::where('employee_id', $existing_user->id)->where('is_active', 1)->where('type', 'Iban')->first();


                        if ($Iban_doc) {

                            $Iban_doc->update($input);
                        } else {

                            $input['employee_id'] = $existing_user->id;
                            $input['type'] = 'Iban';
                            $input['created_by'] = auth()->user()->id;
                            EssentialsOfficialDocument::create($input);
                        }
                    } elseif ($request->existing_iban_file) {

                        $bank_details = $request->input('bank_details');
                        $bank_details['Iban_file'] = $request->existing_iban_file;
                        $user_data['bank_details'] = json_encode($bank_details);

                        $bankCode = $bank_details['bank_code'];
                        $input = [
                            'number' => $bankCode,
                            'file_path' => $request->existing_iban_file,
                            'updated_by' => auth()->user()->id
                        ];

                        $Iban_doc = EssentialsOfficialDocument::where('employee_id', $existing_user->id)->where('is_active', 1)->where('type', 'Iban')->first();


                        if ($Iban_doc) {

                            $Iban_doc->update($input);
                        }
                    }

                    $user_data['updated_by'] = Auth::user()->id;
                    $existing_user->update($user_data);

                    $deleted_documents = $request->deleted_documents ?? null;
                    $offical_documents_types = $request->offical_documents_type;
                    $offical_documents_choosen_files = $request->offical_documents_choosen_files;
                    $offical_documents_previous_files = $request->offical_documents_previous_files;
                    $files = [];
                    if ($request->hasFile('offical_documents_files')) {
                        $files = $request->file('offical_documents_files');
                    }
                    if ($deleted_documents) {
                        foreach ($deleted_documents as $deleted_document) {
                            $filePath = EssentialsOfficialDocument::where('id', $deleted_document)->first()->file_path;
                            if ($filePath) {
                                Storage::delete($filePath);
                                EssentialsOfficialDocument::where('id', $deleted_document)->update([
                                    'file_path' => Null,
                                    'updated_by' => Auth::user()->id,

                                ]);
                            }
                        }
                    }
                    foreach ($offical_documents_types  as  $index => $offical_documents_type) {
                        if (
                            $offical_documents_type
                        ) {
                            if ($offical_documents_previous_files[$index] && $offical_documents_choosen_files[$index]) {
                                if (isset($files[$index])) {
                                    $filePath = $files[$index]->store('/officialDocuments');

                                    EssentialsOfficialDocument::where('id', $offical_documents_previous_files[$index])->update(['file_path' => $filePath, 'updated_by' => Auth::user()->id]);
                                }
                            } elseif ($offical_documents_choosen_files[$index]) {
                                $document2 = new EssentialsOfficialDocument();
                                $document2->type = $offical_documents_type;
                                $document2->employee_id = $id;
                                $document2->created_by = Auth::user()->id;
                                if (isset($files[$index])) {
                                    $filePath = $files[$index]->store('/officialDocuments');
                                    $document2->file_path = $filePath;
                                }
                                $document2->save();
                            }
                        }
                    }

                    $delete_qualification_file = $request->delete_qualification_file ?? null;
                    if ($request->hasFile('qualification_file')) {

                        $qual = EssentialsEmployeesQualification::where('employee_id', $id)->first();
                        if (!$qual) {
                            $qual = new EssentialsEmployeesQualification();
                            $qual->created_by = Auth::user()->id;
                        }
                        $qual_file = request()->file('qualification_file');
                        $qual_file_path = $qual_file->store('/employee_qualifications');

                        $qual->file_path = $qual_file_path;
                        $qual->updated_by = Auth::user()->id;

                        $qual->save();
                    }
                    if ($delete_qualification_file && $delete_qualification_file == 1) {

                        $filePath = EssentialsEmployeesQualification::where('employee_id', $id)->first()->file_path;
                        if ($filePath) {
                            Storage::delete($filePath);
                            EssentialsEmployeesQualification::where('employee_id', $id)->update([
                                'file_path' => Null, 'updated_by' => Auth::user()->id
                            ]);
                        }
                    }


                    $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_updated', 'model_instance' => $existing_user, 'request' => $user_data]);

                    $this->moduleUtil->activityLog($existing_user, 'edited', null, ['name' => $existing_user->user_full_name]);

                    event(new UserCreatedOrModified($existing_user, 'updated'));

                    $output = [
                        'success' => 1,
                        'msg' => __('user.user_update_success'),
                    ];

                    DB::commit();
                }
                if (($is_unique && $is_unique->id != $id)) {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.user_with_same_id_proof_number_exists'),
                    ];
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->route('showEmployee', ['id' => $id])->with('status', $output);
        //  return redirect()->route('employees')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
    }

    private function getRolesArray($business_id)
    {
        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        foreach ($roles_array as $key => $value) {
            if (!$is_admin && $value == 'Admin#' . $business_id) {
                continue;
            }
            $roles[$key] = str_replace('#' . $business_id, '', $value);
        }
        return $roles;
    }



    public function new_arrival_for_workers(Request $request)
    {
        $view = 'essentials::employee_affairs.travelers.index';
        return $this->newArrivalUtil->new_arrival_for_workers($request, $view);
    }

    public function housed_workers_index(Request $request)
    {
        $view = 'essentials::employee_affairs.travelers.partials.housed_workers';
        return $this->newArrivalUtil->housed_workers_index($request, $view);
    }

    public function medicalExamination()
    {
        $view = 'essentials::employee_affairs.travelers.medicalExamination';
        return $this->newArrivalUtil->medicalExamination($view);
    }
    public function SIMCard()
    {
        $view = 'essentials::employee_affairs.travelers.SIMCard';
        return $this->newArrivalUtil->SIMCard($view);
    }
    public function workCardIssuing()
    {
        $view = 'essentials::employee_affairs.travelers.workCardIssuing';
        return $this->newArrivalUtil->workCardIssuing($view);
    }
    public function medicalInsurance()
    {
        $view = 'essentials::employee_affairs.travelers.medicalInsurance';
        return $this->newArrivalUtil->medicalInsurance($view);
    }
    public function bankAccounts()
    {
        $view = 'essentials::employee_affairs.travelers.bankAccounts';
        return $this->newArrivalUtil->bankAccounts($view);
    }
    public function QiwaContracts()
    {
        $view = 'essentials::employee_affairs.travelers.QiwaContracts';
        return $this->newArrivalUtil->QiwaContracts($view);
    }
    public function residencyPrint()
    {
        $view = 'essentials::employee_affairs.travelers.residencyPrint';
        return $this->newArrivalUtil->residencyPrint($view);
    }
    public function residencyDelivery()
    {
        $view = 'essentials::employee_affairs.travelers.residencyDelivery';
        return $this->newArrivalUtil->residencyDelivery($view);
    }
    public function advanceSalaryRequest()
    {
        $view = 'essentials::employee_affairs.travelers.advanceSalaryRequest';
        return $this->newArrivalUtil->advanceSalaryRequest($view);
    }
}
