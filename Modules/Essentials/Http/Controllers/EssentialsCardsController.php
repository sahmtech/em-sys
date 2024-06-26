<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use App\Category;
use App\Company;
use App\Request as UserRequest;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\Essentials\Entities\EssentailsEmployeeOperation;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsResidencyHistory;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsWorkCard;
use Modules\Sales\Entities\salesContractItem;
use Modules\Sales\Entities\SalesProject;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class EssentialsCardsController extends Controller
{
    protected $moduleUtil;

    protected $requestUtil;

    public function __construct(
        ModuleUtil $moduleUtil,
        RequestUtil $requestUtil
    ) {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;
    }

    public function calculateFees($selectedValue)
    {
        switch ($selectedValue) {
            case '3':
                return 2425;
            case '6':
                return 4850;
            case '9':
                return 7275;
            case '12':
                return 9700;
            default:
                return 0;
        }
    }

    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1');
        $responsible_client = null;

        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {
                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)
                        ->pluck('company_id')
                        ->toArray();
                }
            }
        }

        $all_users = User::whereIn('id', $userIds)
            ->where(function ($query) {
                $query->whereNotNull('users.border_no')
                    ->orWhere('users.id_proof_name', 'eqama');
            })
            ->whereIn('users.user_type', ['worker', 'employee'])
            ->where('nationality_id', '!=', 5)
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),' - ',COALESCE(users.id_proof_number,'')) as full_name"),
                'users.id'
            )
            ->whereNotIn('users.id', function ($query) {
                $query->select('employee_id')->from('essentials_work_cards');
            })
            ->get();

        $employees = $all_users->pluck('full_name', 'id');

        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
        ];

        $companies = Company::pluck('name', 'id');
        $card = EssentialsWorkCard::whereIn('employee_id', $userIds)
            ->where('is_active', 1)
            ->with(['user', 'user.OfficialDocument'])
            ->select(
                'id',
                'employee_id',
                'workcard_duration',
                'work_card_no as card_no',
                'fees as passport_fees',
                'work_card_fees as work_card_fees',
                'other_fees',
                'Payment_number as Payment_number'
            );

        if (!empty($request->input('project'))) {
            $card->whereHas('user.assignedTo', function ($query) use ($request) {
                $query->where('id', $request->input('project'));
            });
        }

        $query = User::whereIn('id', $userIds);
        $all_users = $query
            ->select(
                'id',
                DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name")
            )
            ->get();
        $name_in_charge_choices = $all_users->pluck('full_name', 'id');

        if (request()->ajax()) {
            return Datatables::of($card)
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' . $row->id . '" />';
                })
                ->editColumn('company_name', function ($row) {
                    return $row->user->company?->name ?? '';
                })
                ->editColumn('fixnumber', function ($row) {
                    return $row->user->company?->documents
                        ?->where('licence_type', 'COMMERCIALREGISTER')
                        ->first()->unified_number ?? '';
                })
                ->editColumn('user', function ($row) {
                    return $row->user->first_name . ' ' . $row->user->mid_name . ' ' . $row->user->last_name ?? '';
                })
                ->editColumn('project', function ($row) {
                    return $row->user->assignedTo
                        ? $row->user->assignedTo->name ?? __('essentials::lang.management')
                        : __('essentials::lang.management');
                })
                ->addColumn('responsible_client', function ($row) use ($name_in_charge_choices) {
                    $names = null;
                    $userIds = json_decode($row->user->assignedTo?->assigned_to, true) ?? [];

                    if ($userIds) {
                        $lastUserId = end($userIds);
                        foreach ($userIds as $user_id) {
                            $names .= $name_in_charge_choices[$user_id] ?? 'Management';
                            if ($user_id !== $lastUserId) {
                                $names .= ', ';
                            }
                        }
                    }

                    if ($names === null && $row->user->assignedTo === null) {
                        $names = __('essentials::lang.management');
                    }

                    return $names;
                })
                ->editColumn('proof_number', function ($row) {
                    $residencePermitDocument = $row->user->OfficialDocument->where('type', 'residence_permit')->first();

                    if ($residencePermitDocument) {
                        return $residencePermitDocument->number;
                    } elseif ($row->user->id_proof_number) {
                        return $row->user->id_proof_number;
                    } elseif ($row->user->border_no) {
                        return $row->user->border_no;
                    } else {
                        return '';
                    }
                })
                ->addColumn('r_expiration_date', function ($row) {
                    $residencePermitDocument = $row->user->OfficialDocument()->where('type', 'residence_permit')->where('is_active', 1)->latest('created_at')->first();
                    return $residencePermitDocument ? $residencePermitDocument->expiration_date : '';
                })
                ->editColumn('nationality', function ($row) {
                    return $row->user->country?->nationality ?? '';
                })
                ->filter(function ($query) use ($request) {
                    if (!empty($request->input('full_name'))) {
                        $query->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$request->input('full_name')}%"]);
                    }
                })
                ->filterColumn('proof_number', function ($query, $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->where('users.id_proof_number', 'like', "%{$keyword}%")
                            ->orWhereHas('OfficialDocument', function ($query) use ($keyword) {
                                $query->where('type', 'residence_permit')
                                    ->where('number', 'like', "%{$keyword}%");
                            })
                            ->orWhere('users.border_no', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action', 'profession', 'nationality', 'checkbox'])
                ->make(true);
        }

        $sales_projects = SalesProject::pluck('name', 'id');

        $proof_numbers = User::whereIn('users.id', $userIds)
            ->where('users.user_type', 'worker')
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),' - ',COALESCE(users.id_proof_number,'')) as full_name"),
                'users.id'
            )
            ->get();

        return view('essentials::cards.index')->with(
            compact('sales_projects', 'proof_numbers', 'employees', 'companies', 'durationOptions')
        );
    }


    public function work_cards_all_requests()
    {
        $business_id = request()
            ->session()
            ->get('user.business_id');
        $can_change_status = auth()
            ->user()
            ->can('essentials.workcards_requests_change_status');
        $can_return_request = auth()
            ->user()
            ->can('essentials.return_workcards_request');
        $can_show_request = auth()
            ->user()
            ->can('essentials.show_workcards_request');

        $departmentIds = EssentialsDepartment::where(
            'business_id',
            $business_id
        )
            ->where('name', 'LIKE', '%حكومية%')
            ->pluck('id')
            ->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_governmental_dep'),
            ];

            return redirect()
                ->back()
                ->with('status', $output);
        }

        $ownerTypes = ['worker'];

        return $this->requestUtil->getRequests(
            $departmentIds,
            $ownerTypes,
            'essentials::cards.allrequest',
            $can_change_status,
            $can_return_request,
            $can_show_request
        );
    }

    public function storeRequest(Request $request)
    {
        $business_id = request()
            ->session()
            ->get('user.business_id');

        $departmentIds = EssentialsDepartment::where(
            'business_id',
            $business_id
        )
            ->where('name', 'LIKE', '%حكومية%')
            ->pluck('id')
            ->toArray();

        return $this->requestUtil->storeRequest($request, $departmentIds);
    }

    public function work_cards_operation(Request $request)
    {
        $business_id = request()
            ->session()
            ->get('user.business_id');
        $is_admin = auth()
            ->user()
            ->hasRole('Admin#1')
            ? true
            : false;

        $can_show_employee_profile = auth()
            ->user()
            ->can('essentials.show_employee_profile');
        $permissionName = 'essentials.view_profile_picture';

        if (!Permission::where('name', $permissionName)->exists()) {
            $permission = new Permission(['name' => $permissionName]);
            $permission->save();
        } else {
            $permission = Permission::where('name', $permissionName)->first();
        }

        $appointments = EssentialsEmployeeAppointmet::all()->pluck(
            'profession_id',
            'employee_id'
        );

        $categories = Category::all()->pluck('name', 'id');
        $departments = EssentialsDepartment::all()->pluck('name', 'id');

        $contract_types = EssentialsContractType::all()->pluck('type', 'id');
        $nationalities = EssentialsCountry::nationalityForDropdown();

        $professions = EssentialsProfession::all()->pluck('name', 'id');

        $contract = EssentialsEmployeesContract::all()->pluck(
            'contract_end_date',
            'id'
        );

        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')
            ->toArray();
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

        $users = User::with('assignedTo')
            ->whereIn('users.id', $userIds)
            ->where('users.is_cmmsn_agnt', 0)
            ->where('users.nationality_id', '!=', 5)
            ->where(function ($query) {
                $query->where('users.status', 'active')
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('users.status', 'inactive')
                            ->whereIn('users.sub_status', ['vacation', 'escape', 'return_exit']);
                    });
            })

            ->leftJoin('essentials_employee_appointmets', function ($join) {
                $join
                    ->on(
                        'essentials_employee_appointmets.employee_id',
                        '=',
                        'users.id'
                    )
                    ->where('essentials_employee_appointmets.is_active', 1);
            })

            ->leftJoin('essentials_admission_to_works', function ($join) {
                $join
                    ->on(
                        'essentials_admission_to_works.employee_id',
                        '=',
                        'users.id'
                    )
                    ->where('essentials_admission_to_works.is_active', 1);
            })

            ->leftJoin('essentials_employees_contracts', function ($join) {
                $join
                    ->on(
                        'essentials_employees_contracts.employee_id',
                        '=',
                        'users.id'
                    )
                    ->where('essentials_employees_contracts.is_active', 1);
            })
            ->leftJoin(
                'essentials_countries',
                'essentials_countries.id',
                '=',
                'users.nationality_id'
            )

            ->select([
                'users.id as id',
                'users.emp_number',
                'users.profile_image',
                'users.username',
                'users.business_id',
                'users.user_type',
                'users.assigned_to',
                DB::raw(
                    "CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''),' ', COALESCE(users.last_name, '')) as full_name"
                ),
                'users.id_proof_number',
                DB::raw(
                    "COALESCE(essentials_countries.nationality, '') as nationality"
                ),

                'essentials_admission_to_works.admissions_date as admissions_date',
                'essentials_employees_contracts.contract_end_date as contract_end_date',
                'users.email',
                'users.allow_login',
                'users.contact_number',
                'users.essentials_department_id',
                'users.status',
                'users.essentials_salary',
                'users.total_salary',
                'essentials_employee_appointmets.profession_id as profession_id',
            ])

            ->orderby('id', 'desc');

        //dd($users->where('users.id', 5586)->first()->assigned_to);
        if (!empty($request->input('project'))) {

            $users->where('assigned_to', $request->input('project'));
        }

        if (
            !empty($request->input('proof_numbers')) &&
            $request->input('proof_numbers') != 'all'
        ) {
            $users
                ->whereIn('users.id', $request->input('proof_numbers'))
                ->first();
        }

        if (!empty($request->input('status-select'))) {
            $users->where('users.status', $request->input('status'))->first();
        }

        if (!empty($request->input('business'))) {
            $users
                ->where('users.business_id', $request->input('business'))
                ->first();
        }

        if (!empty($request->input('nationality'))) {
            $users
                ->where('users.nationality_id', $request->input('nationality'))
                ->first();
            error_log('111');
        }
        if (request()->ajax()) {
            return Datatables::of($users)

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" name="tblChk[]" class="tblChk" data-id="' .
                        $row->id .
                        '" />';
                })

                ->addColumn('total_salary', function ($row) {
                    return $row->calculateTotalSalary();
                })

                ->addColumn('project', function ($row) {
                    return $row->assignedTo->name ?? " ";
                })

                ->editColumn('essentials_department_id', function ($row) use (
                    $departments
                ) {
                    $item = $departments[$row->essentials_department_id] ?? '';

                    return $item;
                })

                ->addColumn('profession', function ($row) use (
                    $appointments,
                    $professions
                ) {
                    $professionId = $appointments[$row->id] ?? '';

                    $professionName = $professions[$professionId] ?? '';

                    return $professionName;
                })

                ->addColumn('view', function ($row) use (
                    $is_admin,
                    $can_show_employee_profile
                ) {
                    $html = '';
                    if ($is_admin || $can_show_employee_profile) {
                        $html =
                            '<a href="' .
                            route('operations_show_employee', [
                                'id' => $row->id,
                            ]) .
                            '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye"></i> ' .
                            __('messages.view') .
                            '</a>';
                    }

                    return $html;
                })

                ->filterColumn('full_name', function ($query, $keyword) {
                    $query
                        ->where('first_name', $keyword)
                        ->orWhere('last_name', $keyword);
                })

                ->filterColumn('nationality', function ($query, $keyword) {
                    $query->whereRaw(
                        "COALESCE(essentials_countries.nationality, '')  like ?",
                        ["%{$keyword}%"]
                    );
                })

                ->filterColumn('admissions_date', function ($query, $keyword) {
                    $query->whereRaw('admissions_date  like ?', [
                        "%{$keyword}%",
                    ]);
                })

                ->filterColumn('contract_end_date', function (
                    $query,
                    $keyword
                ) {
                    $query->whereRaw('contract_end_date  like ?', [
                        "%{$keyword}%",
                    ]);
                })

                ->filterColumn('profession', function ($query, $keyword) {
                    $query->whereHas('appointment.profession', function (
                        $subQuery
                    ) use ($keyword) {
                        $subQuery->where('name', 'like', '%' . $keyword . '%');
                    });
                })

                ->rawColumns([
                    'user_type',
                    'business_id',
                    'action',
                    'profession',
                    'view',
                    'checkbox',
                ])
                ->make(true);
        }
        $companies = Company::whereIn('id', $companies_ids)->pluck(
            'name',
            'id'
        );

        $countries = EssentialsCountry::forDropdown();
        $spacializations = EssentialsSpecialization::all()->pluck('name', 'id');

        $status = [
            'active' => 'active',
            'inactive' => 'inactive',
            'terminated' => 'terminated',
            'vecation' => 'vecation',
        ];

        $offer_prices = Transaction::where([
            ['transactions.type', '=', 'sell'],
            ['transactions.status', '=', 'approved'],
        ])
            ->leftJoin(
                'sales_contracts',
                'transactions.id',
                '=',
                'sales_contracts.offer_price_id'
            )
            ->whereNull('sales_contracts.offer_price_id')
            ->pluck('transactions.ref_no', 'transactions.id');
        $items = salesContractItem::pluck('name_of_item', 'id');

        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
        ];
        $companies = Company::pluck('name', 'id');

        $proof_numbers = User::whereIn('users.id', $userIds)
            ->where('users.nationality_id', '!=', 5)
            ->where('users.status', 'active')
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
        ' - ',COALESCE(users.id_proof_number,'')) as full_name"),
                'users.id'
            )
            ->get();
        $sales_projects = SalesProject::pluck('name', 'id');

        return view('essentials::cards.operations')->with(
            compact(
                'sales_projects',
                'durationOptions',
                'companies',
                'proof_numbers',
                'contract_types',
                'nationalities',
                'professions',
                'countries',
                'spacializations',
                'status',
                'offer_prices',
                'items',
                'companies'
            )
        );
    }

    public function operations_show_employee($id, Request $request)
    {
        $is_admin = auth()
            ->user()
            ->hasRole('Admin#1')
            ? true
            : false;
        $can_show_employee = auth()
            ->user()
            ->can('essentials.show_employee_operation');
        $business_id = request()
            ->session()
            ->get('user.business_id');
        $documents = null;

        if (!($is_admin || $can_show_employee)) {
            return redirect()
                ->route('home')
                ->with('status', [
                    'success' => false,
                    'msg' => __('message.unauthorized'),
                ]);
        }

        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')
            ->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        if (!in_array($id, $userIds)) {
            return redirect()
                ->back()
                ->with('status', [
                    'success' => false,
                    'msg' => __('essentials::lang.user_not_found'),
                ]);
        }

        $user = User::with([
            'contactAccess',
            'OfficialDocument',
            'proposal_worker',
        ])
            ->select(
                '*',
                DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as full_name")
            )
            ->find($id);

        if ($user->user_type == 'employee') {
            $documents = $user->OfficialDocument;
        } elseif ($user->user_type == 'worker') {
            if (!empty($user->proposal_worker_id)) {
                $officialDocuments = $user->OfficialDocument;
                $workerDocuments = $user->proposal_worker?->worker_documents;

                $documents = $officialDocuments->merge($workerDocuments);
            } else {
                $documents = $user->OfficialDocument;
            }
        }

        $dataArray = [];
        if (!empty($user->bank_details)) {
            $dataArray = json_decode($user->bank_details, true)['bank_name'];
        }

        $bank_name = EssentialsBankAccounts::where('id', $dataArray)->value(
            'name'
        );
        $admissions_to_work = EssentialsAdmissionToWork::where(
            'employee_id',
            $user->id
        )
            ->where('is_active', 1)
            ->latest('created_at')
            ->first();

        $Qualification = EssentialsEmployeesQualification::where(
            'employee_id',
            $user->id
        )->first();

        $Contract = EssentialsEmployeesContract::where('employee_id', $user->id)
            ->where('is_active', 1)
            ->latest('created_at')
            ->first();

        $professionId = EssentialsEmployeeAppointmet::where(
            'employee_id',
            $user->id
        )
            ->where('is_active', 1)
            ->latest('created_at')
            ->value('profession_id');

        if ($professionId !== null) {
            $profession =
                EssentialsProfession::find($professionId)?->name ?? ' ';
        } else {
            $profession = '';
        }

        $user->profession = $profession;
        $view_partials = $this->moduleUtil->getModuleData(
            'moduleViewPartials',
            ['view' => 'manage_user.show', 'user' => $user]
        );

        $query = User::whereIn('id', $userIds);
        $all_users = $query
            ->select(
                'id',
                DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name")
            )
            ->get();
        $users = $all_users->pluck('full_name', 'id');
        $activities = Activity::forSubject($user)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $nationalities = EssentialsCountry::nationalityForDropdown();
        $nationality_id = $user->nationality_id;
        $nationality = '';
        if (!empty($nationality_id)) {
            $nationality = EssentialsCountry::select('nationality')
                ->where('id', '=', $nationality_id)
                ->first();
        }

        return view('essentials::cards.show_emp')->with(
            compact(
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
                'documents'
            )
        );
    }

    public function expired_residencies()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');

        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');

        $residencies = EssentialsOfficialDocument::with(['employee'])
            ->where(
                'type',
                'residence_permit'
            )
            ->whereIn('employee_id', $userIds)
            ->whereBetween('expiration_date', [
                now(),
                now()
                    ->addDays(15)
                    ->endOfDay(),
            ])
            ->where('is_active', 1)
            ->latest('created_at');


        if (request()->ajax()) {
            return DataTables::of($residencies)
                ->addColumn('worker_name', function ($row) {
                    return $row->employee?->first_name .
                        ' ' .

                        $row->employee->mid_name . ' ' . $row->employee->last_name;
                })
                ->addColumn('residency', function ($row) {
                    return $row->number;
                })
                ->addColumn('gender', function ($row) {
                    return $row->employee->gender ?? " ";
                })
                ->addColumn('customer_name', function ($row) {

                    if ($row->employee->user_type == 'employee' || $row->employee->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->employee->assignedTo?->contact
                            ->supplier_business_name ?? null;
                    }
                })
                ->addColumn('project', function ($row) {
                    if ($row->employee->user_type == 'employee' || $row->employee->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->employee->assignedTo?->contact
                            ->salesProjects()->first()->name ?? null;
                    }
                })
                ->addColumn('end_date', function ($row) {
                    return $row->expiration_date;
                })
                ->addColumn('nationality', function ($row) {
                    return optional($row->employee->country)->nationality ?? ' ';
                })
                ->addColumn('company_name', function ($row) {
                    return optional($row->employee->company)->name ?? ' ';
                })
                ->addColumn('dob', function ($row) {
                    return $row->employee->dob ?? '';
                })
                ->addColumn('passport_number', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument()
                        ->where('type', 'passport')
                        ->where('is_active', 1)
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->number ?? ' ';
                    } else {
                        return ' ';
                    }
                })
                ->addColumn('passport_expire_date', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument()
                        ->where('type', 'passport')
                        ->where('is_active', 1)
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })->addColumn('profession', function ($row) use ($appointments, $job_titles) {
                    $professionId = $appointments[$row->employee->id] ?? '';

                    $professionName = $job_titles[$professionId] ?? '';

                    return $professionName;
                })
                ->addColumn('border_no', function ($row) {
                    return $row->employee->border_no ?? ' ';
                })


                ->addColumn('action', 'border_no', 'nationality', 'profession', 'passport_expire_date', 'passport_number', 'dob', 'company_name')

                ->removeColumn('id')
                ->rawColumns([
                    'worker_name',
                    'residency',
                    'project',
                    'end_date',
                    'action',
                ])
                ->make(true);
        }

        return view('essentials::cards.expired_residencies');
    }

    public function all_expired_residencies()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $today = today()->format('Y-m-d');
        $job_titles = EssentialsProfession::where('type', 'job_title')->pluck('name', 'id');
        $appointments = EssentialsEmployeeAppointmet::all()->pluck('profession_id', 'employee_id');

        $all_expired_residencies = EssentialsOfficialDocument::with(['employee'])
            ->where('type', 'residence_permit')
            ->where('is_active', 1)
            ->whereIn('employee_id', $userIds)

            ->whereDate('expiration_date', '<', $today)
            ->orderBy('id', 'desc')
            ->latest('created_at');

        if (request()->ajax()) {
            return DataTables::of($all_expired_residencies)
                ->addColumn('worker_name', function ($row) {
                    return $row->employee?->first_name .
                        ' ' .

                        $row->employee->mid_name . ' ' . $row->employee->last_name;
                })
                ->addColumn('residency', function ($row) {
                    return $row->number;
                })
                ->addColumn('gender', function ($row) {
                    return $row->employee->gender ?? " ";
                })
                ->addColumn('customer_name', function ($row) {

                    if ($row->employee->user_type == 'employee' || $row->employee->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->employee->assignedTo?->contact
                            ->supplier_business_name ?? null;
                    }
                })
                ->addColumn('project', function ($row) {
                    if ($row->employee->user_type == 'employee' || $row->employee->user_type == 'manager') {
                        return __('essentials::lang.management');
                    } else {
                        return $row->employee->assignedTo?->contact
                            ->salesProjects()->first()->name ?? null;
                    }
                })
                ->addColumn('end_date', function ($row) {
                    return $row->expiration_date;
                })
                ->addColumn('nationality', function ($row) {
                    return optional($row->employee->country)->nationality ?? ' ';
                })
                ->addColumn('company_name', function ($row) {
                    return optional($row->employee->company)->name ?? ' ';
                })
                ->addColumn('dob', function ($row) {
                    return $row->employee->dob ?? '';
                })
                ->addColumn('passport_number', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument()
                        ->where('type', 'passport')
                        ->where('is_active', 1)
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->number ?? ' ';
                    } else {
                        return ' ';
                    }
                })
                ->addColumn('passport_expire_date', function ($row) {
                    $passportDocument = $row->employee->OfficialDocument()
                        ->where('type', 'passport')
                        ->where('is_active', 1)
                        ->first();
                    if ($passportDocument) {

                        return optional($passportDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })->addColumn('profession', function ($row) use ($appointments, $job_titles) {
                    $professionId = $appointments[$row->employee->id] ?? '';

                    $professionName = $job_titles[$professionId] ?? '';

                    return $professionName;
                })
                ->addColumn('border_no', function ($row) {
                    return $row->employee->border_no ?? ' ';
                })


                ->addColumn('action', 'border_no', 'nationality', 'profession', 'passport_expire_date', 'passport_number', 'dob', 'company_name')

                ->removeColumn('id')
                ->rawColumns([
                    'worker_name',
                    'residency',
                    'project',
                    'end_date',
                    'action',
                ])
                ->make(true);
        }

        return view('essentials::cards.all_expired_residencies');
    }

    public function late_for_vacation()
    {
        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')
            ->toArray();
        $is_admin = auth()
            ->user()
            ->hasRole('Admin#1')
            ? true
            : false;
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $late_vacation = [];
        $type = RequestsType::where('type', 'leavesAndDepartures')
            ->where('for', 'employee')
            ->first();
        if ($type) {
            $late_vacation = UserRequest::with(['related_to_user'])
                ->whereIn('related_to', $userIds)
                ->where('request_type_id', $type->id)
                ->whereHas('related_to_user', function ($query) {
                    $query->where('status', 'vecation');
                })
                ->where('end_date', '<', now())
                ->select('end_date');
        }

        if (request()->ajax()) {
            return DataTables::of($late_vacation)
                ->addColumn('worker_name', function ($row) {
                    return $row->user->first_name . ' ' . $row->user->mid_name . ' ' . $row->user->last_name ?? "";
                })

                ->addColumn('eqama', function ($row) {
                    return $row->user->id_proof_number ?? "";
                })
                ->addColumn('project', function ($row) {
                    return $row->user->assignedTo?->contact
                        ->supplier_business_name ?? null;
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->user->assignedTo?->contact
                        ->supplier_business_name ?? null;
                })
                ->addColumn('end_date', function ($row) {
                    return $row->end_date;
                })
                ->addColumn('action', function ($row) {
                    $html = '';

                    return $html;
                })

                ->removeColumn('id')
                ->rawColumns([
                    'worker_name',
                    'project',
                    'end_date',
                    'customer_name',
                    'action',
                ])
                ->make(true);
        }

        return view('essentials::cards.late_for_vacation');
    }

    public function final_visa()
    {
        $final_visa = EssentailsEmployeeOperation::with('user')
            ->where('operation_type', 'final_visa')
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'worker');
            })
            ->select('end_date', 'employee_id');

        if (request()->ajax()) {
            return DataTables::of($final_visa)
                ->addColumn('worker_name', function ($row) {
                    return $row->user?->first_name . ' ' . $row->user?->mid_name
                        . ' ' . $row->user?->last_name ??
                        '';
                })
                ->addColumn('eqama', function ($row) {
                    return $row->user->id_proof_number ?? "";
                })

                ->addColumn('project', function ($row) {
                    return $row->user?->assignedTo?->contact
                        ?->supplier_business_name ?? null;
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->user?->assignedTo?->contact
                        ->supplier_business_name ?? null;
                })
                ->addColumn('end_date', function ($row) {
                    return $row->end_date;
                })

                ->removeColumn('id')
                ->rawColumns([
                    'worker_name',
                    'residency',
                    'project',
                    'end_date',
                    'action',
                ])
                ->make(true);
        }

        return view('essentials::cards.final_visa_index');
    }

    public function post_return_visa_data(Request $request)
    {
        try {
            $requestData = $request->only([
                'start_date',
                'end_date',
                'worker_id',
            ]);

            $commonStartDate = $requestData['start_date'];
            $commonEndDate = $requestData['end_date'];

            $jsonData = [];

            foreach ($requestData['worker_id'] as $index => $workerId) {
                $jsonObject = [
                    'employee_id' => $workerId,
                    'start_date' => $commonStartDate,
                    'end_date' => $commonEndDate,
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            \Log::info('JSON Data: ' . $jsonData);

            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);

                DB::beginTransaction();

                foreach ($selectedData as $data) {
                    $operation = DB::table(
                        'essentails_employee_operations'
                    )->insert([
                        'operation_type' => 'return_visa',
                        'employee_id' => $data['employee_id'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                    ]);
                }

                DB::commit();

                $output = [
                    'success' => 1,
                    'msg' => __('lang_v1.added_success'),
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.no_data_received'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency(
                'File:' .
                    $e->getFile() .
                    'Line:' .
                    $e->getLine() .
                    'Message:' .
                    $e->getMessage()
            );

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return response()->json($output);
    }

    public function post_final_visa_data(Request $request)
    {
        try {
            $requestData = $request->only(['end_date', 'worker_id']);

            $commonEndDate = $requestData['end_date'];

            $jsonData = [];

            foreach ($requestData['worker_id'] as $index => $workerId) {
                $jsonObject = [
                    'employee_id' => $workerId,

                    'end_date' => $commonEndDate,
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            \Log::info('JSON Data: ' . $jsonData);

            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);

                DB::beginTransaction();

                foreach ($selectedData as $data) {
                    $operation = DB::table(
                        'essentails_employee_operations'
                    )->insert([
                        'operation_type' => 'final_visa',
                        'employee_id' => $data['employee_id'],
                        'end_date' => $data['end_date'],
                    ]);

                    $user = User::where('id', $data['employee_id'])->first();

                    $user->status = 'inactive';
                    $user->sub_status = 'final_visa';
                    $user->save();
                }

                DB::commit();

                $output = [
                    'success' => 1,
                    'msg' => __('lang_v1.added_success'),
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.no_data_received'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency(
                'File:' .
                    $e->getFile() .
                    'Line:' .
                    $e->getLine() .
                    'Message:' .
                    $e->getMessage()
            );

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return response()->json($output);
    }

    public function post_absent_report_data(Request $request)
    {
        try {
            $requestData = $request->only(['end_date', 'worker_id']);

            $commonEndDate = $requestData['end_date'];

            $jsonData = [];

            foreach ($requestData['worker_id'] as $index => $workerId) {
                $jsonObject = [
                    'employee_id' => $workerId,

                    'end_date' => $commonEndDate,
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            \Log::info('JSON Data: ' . $jsonData);

            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);

                DB::beginTransaction();

                foreach ($selectedData as $data) {
                    $operation = DB::table(
                        'essentails_employee_operations'
                    )->insert([
                        'operation_type' => 'absent_report',
                        'employee_id' => $data['employee_id'],
                        'end_date' => $data['end_date'],
                    ]);
                    $user = user::where('id', $data['employee_id'])->first();
                    $user->status = 'inactive';
                    $user->sub_status = 'absent_report';
                    $user->save();
                }

                DB::commit();

                $output = [
                    'success' => 1,
                    'msg' => __('lang_v1.added_success'),
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.no_data_received'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency(
                'File:' .
                    $e->getFile() .
                    'Line:' .
                    $e->getLine() .
                    'Message:' .
                    $e->getMessage()
            );

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return response()->json($output);
    }

    public function work_cards_vaction_requests()
    {
        $business_id = request()
            ->session()
            ->get('user.business_id');

        $can_change_status = auth()
            ->user()
            ->can('essentials.workcards_requests_change_status');
        $can_return_request = auth()
            ->user()
            ->can('essentials.return_workcards_request');
        $can_show_request = auth()
            ->user()
            ->can('essentials.show_workcards_request');
        $departmentIds = EssentialsDepartment::where(
            'business_id',
            $business_id
        )
            ->where('name', 'LIKE', '%دولي%')
            ->pluck('id')
            ->toArray();

        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_governmental_dep'),
            ];

            return redirect()
                ->back()
                ->with('status', $output);
        }

        $ownerTypes = ['employee', 'manager'];

        return $this->requestUtil->getRequests(
            $departmentIds,
            $ownerTypes,
            'essentials::cards.allrequest',
            $can_change_status,
            $can_return_request,
            $can_show_request
        );
    }

    public function residencyreports(Request $request)
    {
        $sales_projects = SalesProject::pluck('name', 'id');
        $is_admin = auth()
            ->user()
            ->hasRole('Admin#1')
            ? true
            : false;

        $userIds = User::whereNot('user_type', 'admin')
            ->pluck('id')
            ->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $proof_numbers = User::whereIn('id', $userIds)
            ->where('users.user_type', 'worker')
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,''),
            ' - ',COALESCE(users.id_proof_number,'')) as full_name"),
                'users.id'
            )
            ->get();

        $report = EssentialsResidencyHistory::whereIn('worker_id', $userIds)
            ->with(['worker'])
            ->select('*');
        if (
            !empty($request->input('proof_numbers')) &&
            $request->input('proof_numbers') != 'all'
        ) {
            $report->whereHas('worker', function ($query) use ($request) {
                $query->whereIn('id', $request->input('proof_numbers'));
            });
        }
        if ($request->ajax()) {
            return Datatables::of($report)
                ->editColumn('user', function ($row) {
                    return $row->worker->first_name .
                        ' ' .
                        $row->worker->mid_name .
                        ' ' .
                        $row->worker->last_name ??
                        '';
                })
                ->make(true);
        }

        return view('essentials::cards.reports.residenceReport')->with(
            compact('sales_projects', 'proof_numbers')
        );
    }

    public function getSelectedRowsData(Request $request)
    {
        $selectedRows = $request->input('selectedRows');
        $data = EssentialsWorkCard::whereIn('id', $selectedRows)
            ->with(['user', 'user.assignedTo', 'user.OfficialDocument'])

            ->select(
                'id',
                'employee_id',
                'work_card_no as card_no',
                'fees as passport_fees',
                'work_card_fees as work_card_fees',
                'workcard_duration',
                'Payment_number as Payment_number'
            )
            ->get();

        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
        ];

        foreach ($data as $row) {
            $doc = $row->user
                ->OfficialDocument()
                ->where('type', 'residence_permit')
                ->where('is_active', 1)
                ->latest('created_at')
                ->first();

            $row->expiration_date = $doc ? $doc->expiration_date : null;
            $row->number = $doc ? $doc->number : null;

            $uni_number = $row->user->company?->documents
                ?->where('licence_type', 'COMMERCIALREGISTER')
                ->first();
            $row->fixnumber = $uni_number ? $uni_number->unified_number : null;
        }

        return response()->json([
            'data' => $data,
            'durationOptions' => $durationOptions,
        ]);
    }

    public function postRenewData(Request $request)
    {
        try {
            $requestData = $request->only([
                'id',
                'employee_id',
                'number',
                'expiration_date',
                'renew_duration',
                'fees',
                'passportfees',
                'Payment_number',
            ]);

            $jsonData = [];

            foreach ($requestData['id'] as $index => $workerId) {
                $jsonObject = [
                    'id' => $requestData['id'][$index],
                    'employee_id' => $requestData['employee_id'][$index],
                    'number' => $requestData['number'][$index],
                    'expiration_date' => $requestData['expiration_date'][$index],
                    'renew_duration' => $requestData['renew_duration'][$index],
                    'work_card_fees' => $requestData['fees'][$index],
                    'fees' => $requestData['passportfees'][$index],
                    'Payment_number' => $requestData['Payment_number'][$index],
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);
                $invalidPaymentNumber = false;

                foreach ($selectedData as $data) {
                    if ($data['Payment_number'] && strlen($data['Payment_number']) !== 14) {
                        $invalidPaymentNumber = true;
                        break; // Break the loop if any payment number is invalid
                    }
                }
                if ($invalidPaymentNumber) {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.payment_number_invalid'),
                    ];
                } else {
                    foreach ($selectedData as $data) {


                        $exist_card = EssentialsWorkCard::where(
                            'is_active',
                            1
                        )->find($data['id']);


                        $renewStartDate = Carbon::parse($data['expiration_date']);
                        $renewEndDate = $renewStartDate->addMonths(
                            $data['renew_duration']
                        );
                        $new_fees = $this->calculateFees($data['renew_duration']);

                        if ($exist_card) {
                            $exist_card->is_active = 0;
                            $exist_card->save();
                        }

                        $new_card = new EssentialsWorkCard();
                        $lastrecord = EssentialsWorkCard::where('is_active', 1)
                            ->orderBy('work_card_no', 'desc')
                            ->first();

                        if ($lastrecord) {
                            $lastEmpNumber = (int) substr(
                                $lastrecord->work_card_no,
                                3
                            );
                            $nextNumericPart = $lastEmpNumber + 1;
                            $new_card['work_card_no'] =
                                'WC' .
                                str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
                        } else {
                            $new_card['work_card_no'] = 'WC' . '000';
                        }
                        $new_card->is_active = 1;
                        $new_card->fees = $data['fees'];
                        $new_card->work_card_fees = $data['work_card_fees'];
                        $new_card->Payment_number = $data['Payment_number'];
                        $new_card->workcard_duration =
                            $data['renew_duration'] +
                            $exist_card['workcard_duration'];
                        $new_card->employee_id = $data['employee_id'];
                        $new_card->start_date = $data['expiration_date'];
                        $new_card->end_date = $renewEndDate;
                        $new_card->save();

                        if (
                            $data['number'] != null &&
                            $data['expiration_date'] != null
                        ) {
                            $existingDocument = EssentialsOfficialDocument::where(
                                'type',
                                'residence_permit'
                            )
                                ->where('employee_id', $data['employee_id'])
                                ->where('is_active', 1)
                                ->latest('created_at')
                                ->first();

                            if ($existingDocument) {
                                $existingDocument->is_active = 0;
                                $existingDocument->save();
                            }

                            $newDocument = new EssentialsOfficialDocument();
                            $newDocument->type = 'residence_permit';
                            $newDocument->employee_id = $data['employee_id'];
                            $newDocument->number = $data['number'];
                            $newDocument->expiration_date = $renewEndDate;
                            $newDocument->issue_date = now();
                            $newDocument->is_active = 1;
                            $newDocument->save();
                        }


                        $output = [
                            'success' => 1,
                            'msg' => __('essentials::lang.card_renew_sucessfully'),
                        ];
                    }
                }
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.no_data_received'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency(
                'File:' .
                    $e->getFile() .
                    'Line:' .
                    $e->getLine() .
                    'Message:' .
                    $e->getMessage()
            );

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function getOperationSelectedworkcardData(Request $request)
    {
        $selectedRows = $request->input('selectedRows');
        $data = User::whereIn('id', $selectedRows)
            ->select('*')
            ->orderby('id', 'desc')
            ->get();

        foreach ($data as $row) {
            $doc = $row
                ->OfficialDocument()
                ->where('type', 'residence_permit')
                ->where('is_active', 1)
                ->latest('created_at')
                ->first();

            $row->expiration_date = $doc ? $doc->expiration_date : null;
            $row->number = $doc ? $doc->number : null;

            $row->name = $row->first_name . ' ' . $row->last_name;
            $uni_number = $row->company?->documents
                ?->where('licence_type', 'COMMERCIALREGISTER')
                ->first();
            $row->fixnumber = $uni_number ? $uni_number->unified_number : null;
        }

        $durationOptions = [
            '3' => __('essentials::lang.3_months'),
            '6' => __('essentials::lang.6_months'),
            '9' => __('essentials::lang.9_months'),
            '12' => __('essentials::lang.12_months'),
        ];

        return response()->json([
            'data' => $data,
            'durationOptions' => $durationOptions,
        ]);
    }

    public function postOperationRenewData(Request $request)
    {
        try {
            $requestData = $request->only([
                'id',
                'number',
                'expiration_date',
                'renew_duration',
                'fees',
                'passportfees',
                'Payment_number',
            ]);

            $jsonData = [];

            foreach ($requestData['id'] as $index => $workerId) {
                $jsonObject = [
                    'id' => $requestData['id'][$index],
                    'number' => $requestData['number'][$index],
                    'expiration_date' => $requestData['expiration_date'][$index],
                    'renew_duration' => $requestData['renew_duration'][$index],
                    'work_card_fees' => $requestData['fees'][$index],
                    'fees' => $requestData['passportfees'][$index],
                    'Payment_number' => $requestData['Payment_number'][$index],
                ];

                $jsonData[] = $jsonObject;
            }

            $jsonData = json_encode($jsonData);

            if (!empty($jsonData)) {
                $selectedData = json_decode($jsonData, true);
                $invalidPaymentNumber = false;

                foreach ($selectedData as $data) {
                    if ($data['Payment_number'] && strlen($data['Payment_number']) !== 14) {
                        $invalidPaymentNumber = true;
                        break; // Break the loop if any payment number is invalid
                    }
                }
                if ($invalidPaymentNumber) {
                    $output = [
                        'success' => 0,
                        'msg' => __('essentials::lang.payment_number_invalid'),
                    ];
                } else {
                    foreach ($selectedData as $data) {
                        $user = User::find($data['id']);
                        if (
                            $user &&
                            //  is_null($user->border_no) &&
                            is_null($data['number']) &&
                            is_null($data['expiration_date'])
                        ) {
                            return [
                                'success' => false,
                                'msg' => __(
                                    'essentials::lang.user_info_eqama_not_completed'
                                ),
                            ];
                        }

                        $exist_card = EssentialsWorkCard::where(
                            'employee_id',
                            $data['id']
                        )
                            ->where('is_active', 1)
                            ->first();

                        $renewStartDate = Carbon::parse($data['expiration_date']);
                        $renewEndDate = $renewStartDate->addMonths(
                            $data['renew_duration']
                        );
                        $new_fees = $this->calculateFees($data['renew_duration']);
                        $new_duration = null;
                        if ($exist_card) {
                            $exist_card->is_active = 0;
                            $exist_card->save();

                            $new_duration =
                                $data['renew_duration'] +
                                $exist_card['workcard_duration'];
                        } else {
                            $new_duration = $data['renew_duration'];
                        }

                        $new_card = new EssentialsWorkCard();
                        $lastrecord = EssentialsWorkCard::where('is_active', 1)
                            ->orderBy('work_card_no', 'desc')
                            ->first();

                        if ($lastrecord) {
                            $lastEmpNumber = (int) substr(
                                $lastrecord->work_card_no,
                                3
                            );
                            $nextNumericPart = $lastEmpNumber + 1;
                            $new_card['work_card_no'] =
                                'WC' .
                                str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
                        } else {
                            $new_card['work_card_no'] = 'WC' . '000';
                        }

                        $new_card->is_active = 1;
                        $new_card->fees = $data['fees'];
                        $new_card->work_card_fees = $data['work_card_fees'];
                        $new_card->Payment_number = $data['Payment_number'];
                        $new_card->workcard_duration = $new_duration;
                        $new_card->employee_id = $data['id'];
                        $new_card->start_date = $data['expiration_date'];
                        $new_card->end_date = $renewEndDate;
                        $new_card->save();

                        if (
                            $data['number'] != null &&
                            $data['expiration_date'] != null
                        ) {
                            $existingDocument = EssentialsOfficialDocument::where(
                                'type',
                                'residence_permit'
                            )
                                ->where('employee_id', $data['id'])
                                ->where('is_active', 1)
                                ->latest('created_at')
                                ->first();

                            if ($existingDocument) {
                                $existingDocument->is_active = 0;
                                $existingDocument->save();
                            }

                            $newDocument = new EssentialsOfficialDocument();
                            $newDocument->type = 'residence_permit';
                            $newDocument->employee_id = $data['id'];
                            $newDocument->number = $data['number'];
                            $newDocument->expiration_date = $renewEndDate;
                            $newDocument->issue_date = now();
                            $newDocument->is_active = 1;
                            $newDocument->save();
                        }
                    }

                    $output = [
                        'success' => 1,
                        'msg' => __('essentials::lang.card_renew_sucessfully'),
                    ];
                }
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.no_data_received'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency(
                'File:' .
                    $e->getFile() .
                    'Line:' .
                    $e->getLine() .
                    'Message:' .
                    $e->getMessage()
            );

            $output = ['success' => 0, 'msg' => $e->getMessage()];
        }

        return $output;
    }

    public function getResidencyData(Request $request)
    {
        $employeeId = $request->input('employee_id');

        $residencyData = User::where('users.id', '=', $employeeId)
            ->join(
                'essentials_official_documents as doc',
                'doc.employee_id',
                '=',
                'users.id'
            )->where('doc.is_active', 1)
            ->select(
                'doc.id',
                'users.border_no as border_no',
                'users.id_proof_number as residency_no',
                'doc.expiration_date as residency_end_date'
            )
            ->first();

        return response()->json($residencyData);
    }

    public function get_responsible_data(Request $request)
    {
        $employeeId = $request->get('employeeId');

        $userType = User::where('id', $employeeId)->value('user_type');

        if ($userType !== 'worker') {
            $user = User::with('company')
                ->where('id', $employeeId)
                ->first();
            $usercompany = Company::where('id', $user->company->id)
                ->select('id', 'name')
                ->first();
            if ($usercompany) {
                $company = [
                    'id' => $usercompany->id,
                    'name' => $usercompany->name,
                ];
            } else {
                $company = [
                    'id' => null,
                    'name' => null,
                ];
            }

            return response()->json([
                'all_responsible_users' => [],
                'responsible_client' => [],

                'company' => $company,
            ]);
        } else {
            $projects = User::with(['assignedTo'])->find($employeeId);

            $assignedProject = $projects->assignedTo;
            $projectName = $assignedProject->name ?? '';
            $projectId = $assignedProject->id ?? '';

            if ($assignedProject != null) {
                $all_responsible_users = [
                    'id' => $projectId,
                    'name' => $projectName,
                ];
            } else {
                $all_responsible_users = [];
            }

            $query = User::where('users.user_type', 'employee');
            $all_users = $query
                ->select(
                    'id',
                    DB::raw(
                        "CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"
                    )
                )
                ->get();
            $name_in_charge_choices = $all_users->pluck('full_name', 'id');

            $userIds = json_decode($projects->assignedTo?->assigned_to, true);
            $assignedresponibleClient = [];

            if ($userIds) {
                foreach ($userIds as $user_id) {
                    $assignedresponibleClient[] = [
                        'id' => $user_id,
                        'name' => $name_in_charge_choices[$user_id],
                    ];
                }
            }

            $user = User::with('company')
                ->where('id', $employeeId)
                ->first();
            $usercompany = Company::where('id', $user->company->id)
                ->select('id', 'name')
                ->first();
            if ($usercompany) {
                $company = [
                    'id' => $usercompany->id,
                    'name' => $usercompany->name,
                ];
            } else {
                $company = [
                    'id' => null,
                    'name' => null,
                ];
            }

            return response()->json([
                'all_responsible_users' => $all_responsible_users,
                'responsible_client' => $assignedresponibleClient,
                'company' => $company,
            ]);
        }
    }

    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function store(Request $request)
    {

        try {
            $data = $request->only([
                'Residency_no',
                'workcard_duration_input',
                'Payment_number',
                'passport_fees_input',
                'work_card_fees',
                'other_fees',
                'employee_id',
            ]);

            if ($request->input('Payment_number') != null && strlen($request->input('Payment_number')) !== 14) {
                $output = [
                    'success' => 0,
                    'msg' => __('essentials::lang.payment_number_invalid'),
                ];
            } elseif (
                $request->input('Residency_no') == null &&
                $request->input('Residency_end_date') == null &&
                $request->input('border_no') == null
            ) {
                $output = [
                    'success' => 0,
                    'msg' => __(
                        'essentials::lang.user_info_eqama_not_completed'
                    ),
                ];
            } else {
                $data['employee_id'] = (int) $request->input('employee_id');
                $data['fees'] = $request->input('passport_fees_input');
                $data['work_card_fees'] = $request->input('work_card_fees');
                $data['other_fees'] = $request->input('other_fees');
                $data['workcard_duration'] = (int) $request->input(
                    'workcard_duration_input'
                );
                $data['is_active'] = 1;
                $lastrecord = EssentialsWorkCard::orderBy(
                    'work_card_no',
                    'desc'
                )->first();

                if ($lastrecord) {
                    $lastEmpNumber = (int) substr($lastrecord->work_card_no, 3);
                    $nextNumericPart = $lastEmpNumber + 1;
                    $data['work_card_no'] =
                        'WC' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
                } else {
                    $data['work_card_no'] = 'WC' . '000';
                }

                EssentialsWorkCard::create($data);

                $output = [
                    'success' => 1,
                    'msg' => __('essentials::lang.card_added_sucessfully'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency(
                'File:' .
                    $e->getFile() .
                    'Line:' .
                    $e->getLine() .
                    'Message:' .
                    $e->getMessage()
            );

            error_log(
                'File:' .
                    $e->getFile() .
                    'Line:' .
                    $e->getLine() .
                    'Message:' .
                    $e->getMessage()
            );
            $output = [
                'success' => 0,
                'msg' => __('messeages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
