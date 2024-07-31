<?php

namespace Modules\Essentials\Http\Controllers;

use App\AccessRoleBusiness;
use App\AccessRoleCompany;
use App\AccountTransaction;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Company;
use App\ContactLocation;
use App\Events\TransactionPaymentAdded;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsLeave;
use Modules\Essentials\Entities\EssentialsUserSalesTarget;
use Modules\Essentials\Entities\PayrollGroup;
use Modules\Essentials\Notifications\PayrollNotification;
use Modules\Essentials\Utils\EssentialsUtil;
use Modules\Sales\Entities\SalesProject;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\RequestUtil;
use App\Utils\NewArrivalUtil;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsBankAccounts;
use App\TimesheetUser;
use App\TimesheetGroup;
use App\AccessRole;
use Modules\CEOManagment\Entities\RequestsType;
use App\AccessRoleRequest;

class PayrollController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    protected $essentialsUtil;

    protected $commonUtil;

    protected $transactionUtil;

    protected $businessUtil;

    protected $requestUtil;


    protected $newArrivalUtil;


    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil, NewArrivalUtil $newArrivalUtil, EssentialsUtil $essentialsUtil, Util $commonUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->essentialsUtil = $essentialsUtil;
        $this->commonUtil = $commonUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->requestUtil = $requestUtil;
        $this->newArrivalUtil = $newArrivalUtil;
    }

    public function dashboard()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_access_payrolls_management = auth()->user()->can('essentials.payrolls_management');

        if (!($is_admin || $can_access_payrolls_management)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        return view('essentials::payroll.dashboard');
    }

    public function list_of_employess()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_payrolls_indexWorkerEmpProjects = auth()->user()->can('essentials.payroll_list_of_emp');
        $can_view_worker_project = auth()->user()->can('essentials.view_worker_project');
        $can_view_salary_info = auth()->user()->can('essentials.view_salary_info');

        if (!($is_admin || $can_payrolls_indexWorkerEmpProjects)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        $contacts_fillter = ['none' => __('messages.undefined')] + SalesProject::all()->pluck('name', 'id')->toArray();
        $user_types = [
            "employee" => __('essentials::lang.user_type.employee'),
            "worker" => __('essentials::lang.user_type.worker'),
            "remote_employee" => __('essentials::lang.user_type.remote_employee'),
        ];
        $bank_names = EssentialsBankAccounts::all()->pluck('name', 'id');
        error_log($bank_names[10]);
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        $companies_ids = Company::whereNotIn('id', [2, 7])->pluck('id')->toArray();
        $companies = Company::whereIn('id', $companies_ids)->pluck('name', 'id');

        $employee_ids = User::with('contract');
        $projects_ids = SalesProject::all()->pluck('name', 'id')->toArray();


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
            ->whereIn('company_id', $companies_ids)
            ->with(['assignedTo'])
            ->whereIn('user_type', ['worker', 'employee'])
            ->where('users.status', '!=', 'inactive')
            ->leftjoin('sales_projects', 'sales_projects.id', '=', 'users.assigned_to')
            ->with(['country', 'contract', 'OfficialDocument']);

        $users->select(
            'users.*',
            'users.id as worker_id',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ',COALESCE(users.mid_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            'sales_projects.name as contact_name'
        )
            ->orderBy('users.id', 'desc')
            ->groupBy('users.id');

        if (!empty(request()->input('user_type')) && request()->input('user_type') !== 'all') {
            $user_type = request()->input('user_type');
            if ($user_type == "worker") {
                $employee_ids = $employee_ids->whereIn('company_id', $companies_ids)->where('user_type', 'worker');
            } elseif ($user_type == "employee" || $user_type == "remote_employee") {
                $employee_ids = $employee_ids->whereIn('company_id', $companies_ids)->where('user_type', 'employee');
            }
            if ($user_type == "remote_employee") {
                $remote_id = EssentialsContractType::where('type', 'LIKE', '%بعد%')->first()?->id;
                $employee_ids = $employee_ids->whereHas('contract', function ($query) use ($remote_id) {
                    $query->where('contract_type_id', $remote_id);
                });
            }
            $employee_ids = $employee_ids->pluck('id')->toArray();
            $users =  $users->whereIn('users.id',  $employee_ids);
        }
        $requestcompanyIds = request()->input('company', []);
        // $requestprojectIds = request()->input('project_name', []);

        if (!empty($requestcompanyIds)) {
            if (is_array($requestcompanyIds)) {
                $users->whereIn('users.company_id', $requestcompanyIds);
            } else {
                $users->where('users.company_id', $requestcompanyIds);
            }
        }

        // if (!empty($requestprojectIds)) {
        //     if (is_array($requestprojectIds)) {
        //         $users->whereIn('users.assigned_to', $requestprojectIds);
        //     } else {
        //         $users->where('users.assigned_to', $requestprojectIds);
        //     }
        // }

        if (!empty(request()->input('company')) && request()->input('company') !== 'all') {
            $users =  $users->where('users.company_id', request()->input('company'));
        }
        if (!empty(request()->input('department_id')) && request()->input('department_id') !== 'all') {
            $users =  $users->where('users.essentials_department_id', request()->input('department_id'));
        }

        if (!empty(request()->input('project_name')) && request()->input('project_name') !== 'all') {
            if (request()->input('project_name') == 'none') {
                $users = $users->whereNull('users.assigned_to');
            } else {
                $users = $users->where('users.assigned_to', request()->input('project_name'));
            }
        }



        if (request()->ajax()) {

            return DataTables::of($users)
                ->addColumn('number', function ($user) {
                    return $user->emp_number ?? ' ';
                })

                ->addColumn('residence_permit', function ($user) {
                    return $user->id_proof_number ?? ' ';
                })

                ->addColumn('residence_permit_expiration', function ($user) {
                    $residencePermitDocument = $user->OfficialDocument
                        ->where('type', 'residence_permit')
                        ->first();
                    if ($residencePermitDocument) {

                        return optional($residencePermitDocument)->expiration_date ?? ' ';
                    } else {

                        return ' ';
                    }
                })
                ->addColumn('border_no', function ($user) {
                    return $user->border_no ?? ' ';
                })

                ->addColumn('nationality', function ($user) {
                    return optional($user->country)->nationality ?? ' ';
                })


                ->addColumn('bank_code', function ($user) {

                    $bank_details = json_decode($user->bank_details);
                    return $bank_details->bank_code ?? ' ';
                })

                ->addColumn('bank_name', function ($user) use ($bank_names) {
                    $bank_details = json_decode($user->bank_details);
                    $bank_name = $bank_names[$bank_details->bank_code] ?? '';
                    return  $bank_name;
                })


                ->addColumn('company_name', function ($user) {
                    return optional($user->company)->name ?? ' ';
                })


                ->addColumn('contact_name', function ($user) {

                    return $user->assignedTo->name ?? '';
                })

                ->addColumn('project_assigner', function ($user) {
                    return $user->assignedTo->contact->name ?? '';
                })
                ->addColumn('salary_voucher', function ($user) {
                    $voucherStatus = $user->salary_voucher ?? '';
                    $buttonText = ($voucherStatus == 1) ? __("essentials::lang.yes") : __("essentials::lang.no");
                    $button = '<button class="btn btn-sm btn-primary edit-salary-voucher" data-user-id="' . $user->id . '" data-toggle="modal" data-target="#editVoucherModal">' . $buttonText . '</button>';
                    return $button;
                })



                ->addColumn(
                    'actions',
                    function ($row)  use ($is_admin, $can_view_worker_project, $can_view_salary_info) {
                        $html = '';

                        if ($is_admin || $can_view_worker_project) {
                            if ($row->assigned_to) {
                                $html .= '&nbsp; <button class="btn btn-xs btn-primary view_worker_project" id="view_worker_project" data-href="' . route('payrolls.view_worker_project', ['id' => $row->id]) . '" data-worker-id="' . $row->id . '"><i class="glyphicon glyphicon-eye"></i> ' . __('messages.view_project') . '</button>';
                            } else {
                                $html .= "&nbsp; ";
                            }
                        }

                        if ($is_admin || $can_view_salary_info) {


                            $html .= '&nbsp; <button class="btn btn-xs btn-success view_salary_info" id="view_salary_info" data-href="' . route('payrolls.view_salary_info', ['id' => $row->id]) . '" data-worker-id="' . $row->id . '"><i class="glyphicon glyphicon-eye"></i> ' . __('essentials::lang.view_salary_info') . '</button>';
                        }

                        return $html;
                    }
                )

                ->filterColumn('worker', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('residence_permit', function ($query, $keyword) {
                    $query->whereRaw("id_proof_number like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['contact_name', 'salary_voucher', 'actions', 'worker_id', 'company_name',  'worker',  'nationality', 'residence_permit_expiration', 'residence_permit',])
                ->make(true);
        }
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        return view('essentials::payroll.list_of_employess')
            ->with(compact('companies', 'contacts_fillter', 'user_types', 'departments'));
    }


    public function viewWorkerProject(Request $request)
    {
        $userId = $request->input('worker_id');
        $user = User::with('assignedTo', 'contract')->find($userId);
        $project = $user->assignedTo;
        $contact_location_name = "";
        if ($project) {
            $contact_location = ContactLocation::where('sales_project_id', $project->id)->first() ?? '';
            if ($contact_location) {
                $contact_location_name =
                    $contact_location->name;
            }
        }

        $contract_start_date = $user->contract->contract_start_date ?? null;
        $contract_end_date = $user->contract->contract_end_date ?? null;
        $project = json_decode(SalesProject::with('followupUserAccessProjects')->where('id', $project->id)
            ->first());
        $followup_user_access_projects = $project->followup_user_access_projects;
        $followup_project = $followup_user_access_projects[0];
        $followup_user_id = $followup_project->user_id;
        $project_manager = User::find($followup_user_id);
        $project_manager_name = $project_manager->first_name . " " . $project_manager->last_name;

        $data = [
            'project' => $project,
            'contract_start_date' => $contract_start_date,
            'contract_end_date' => $contract_end_date,
            'contact_location' => $contact_location_name,
            'project_manager' => $project_manager_name,
        ];
        return response()->json(['data' => $data]);
    }


    public function viewSalaryInfo(Request $request)
    {
        $userId = $request->input('worker_id');
        $user = User::with(['userAllowancesAndDeductions.essentialsAllowanceAndDeduction', 'essentialsUserShifts.shift'])->where('id', $userId)->first();
        $housing_allowance = 0;
        $transportation_allowance = 0;
        $other_allowance = 0;

        $allowances = json_decode($user)->user_allowances_and_deductions ?? [];

        foreach ($allowances as $allowance) {
            $allowance_dsc =   $allowance?->essentials_allowance_and_deduction?->description;
            if ((stripos($allowance_dsc, 'سكن') !== false) || (stripos($allowance_dsc, 'house') !== false)) {
                $housing_allowance = $allowance->amount;
            } elseif ((stripos($allowance_dsc, 'نقل') !== false) || (stripos($allowance_dsc, 'مواصلات') !== false) || (stripos($allowance_dsc, 'transport') !== false)) {
                $transportation_allowance = $allowance->amount;
            } else if (stripos($allowance_dsc, 'other') !== false) {
                $other_allowance += floatval($allowance->amount ?? "0");
            }
        }
        $salary = $user->essentials_salary;

        $data = [
            'user_id' => $user->id,
            'work_days' => 30,
            'salary' => number_format($user->essentials_salary, 0, '.', ''),
            'housing_allowance' => number_format($housing_allowance, 0, '.', ''),
            'transportation_allowance' => number_format($transportation_allowance, 0, '.', ''),
            'other_allowance' => number_format($other_allowance, 0, '.', ''),
            'total' => number_format($salary, 0, '.', ''),
        ];
        return response()->json(['data' => $data]);
    }

    public function updateSalaryInfo(Request $request)
    {

        $userId = $request->input('user_id');
        $updatedSalaryData = $request->except('_token', 'user_id');


        $user = User::with('userAllowancesAndDeductions.essentialsAllowanceAndDeduction')->find($userId);
        if ($user) {
            foreach ($user->userAllowancesAndDeductions as $allowance) {
                if ($allowance->allowance_deduction_id == 1) {
                    error_log($updatedSalaryData['housing_allowance']);
                    $allowance->amount = $updatedSalaryData['housing_allowance'];
                    $allowance->save();
                }

                if ($allowance->allowance_deduction_id == 2) {
                    $allowance->amount = $updatedSalaryData['transportation_allowance'];
                    $allowance->save();
                }

                if ($allowance->allowance_deduction_id == 6) {
                    error_log($updatedSalaryData['other_allowance']);
                    $allowance->amount = $updatedSalaryData['other_allowance'];
                    $allowance->save();
                }
            }
            $user->essentials_salary = $updatedSalaryData['salary'];
            $user->total_salary = $updatedSalaryData['total'];
            $user->save();
            return response()->json(['message' => 'Salary data updated successfully', $user], 200);
        }
    }

    public function updateVoucherStatus(Request $request)
    {
        $userId = $request->input('user_id');
        $status = $request->input('status');
        error_log($userId);



        $user = User::find($userId);
        $user->salary_voucher = $status;
        $user->save();

        return response()->json(['status' => ucfirst($status)]);
    }
    public function index()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

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
        $companies = Company::whereIn('id',  $companies_ids)->pluck('name', 'id')->toArray();

        $projects = SalesProject::all()->pluck('name', 'id')->toArray();
        $employees = User::where('user_type', 'employee')->whereIn('id', $userIds)->select(
            'users.*',
            DB::raw("CONCAT(COALESCE(users.first_name, ''),  ' ', COALESCE(users.last_name, '')) as name"),
        )->pluck('name', 'id')->toArray();
        $user_types = [
            "employee" => __('essentials::lang.user_type.employee'),
            "worker" => __('essentials::lang.user_type.worker'),
            "remote_employee" => __('essentials::lang.user_type.remote_employee'),
        ];
        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        return view('essentials::payroll.index')->with(compact('projects', 'companies', 'employees', 'user_types', 'departments'));
    }
    public function requests()
    {
        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('essentials.change_payroll_request_status');
        $can_return_request = auth()->user()->can('essentials.return_payroll_request');
        $can_show_request = auth()->user()->can('essentials.show_payroll_request');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%رواتب%')
            ->pluck('id')->toArray();
        if (empty($departmentIds)) {
            $output = [
                'success' => false,
                'msg' => __('essentials::lang.there_is_no_payroll_dep'),
            ];
            return redirect()->back()->with('status', $output);
        }

        $ownerTypes = ['employee', 'manager', 'worker'];
        $roles = DB::table('roles')
            ->where('name', 'LIKE', '%رواتب%')->pluck('id')->toArray();
        $access_roles = AccessRole::whereIn('role_id', $roles)->pluck('id')->toArray();
        $requests = AccessRoleRequest::whereIn('access_role_id', $access_roles)->pluck('request_id')->toArray();
        $requestsTypes = RequestsType::whereIn('id', $requests)->pluck('id')->toArray();

        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'essentials::payroll.payrollRequests', $can_change_status, $can_return_request, $can_show_request, $requestsTypes);
    }

    public function storePayrollRequest(Request $request)
    {


        $business_id = request()->session()->get('user.business_id');

        $departmentIds = EssentialsDepartment::where('name', 'LIKE', '%رواتب%')
            ->pluck('id')->toArray();

        return $this->requestUtil->storeRequest($request, $departmentIds);
    }
    // public function getEmployeesBasedOnCompany(Request $request)
    // {

    //     $employees =  User::whereIn('user_type', ['employee', 'manager'])
    //         ->whereIn('users.company_id', $request->company_id)->whereNot('status', 'inactive')
    //         ->select(
    //             'users.*',
    //             DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''), ' ', COALESCE(users.last_name, '')) as name"),
    //         )->pluck('name', 'id')->toArray();


    //     return [
    //         'success' => true,
    //         'msg' => __('lang_v1.success'),
    //         'employees' => $employees,
    //     ];
    // }

    public function payrollsGroupIndex()
    {
        try {
            $user = User::find(auth()->user()->id);
            $is_admin = $user->hasRole('Admin#1');

            $payrolls = TimesheetGroup::where('timesheet_groups.is_approved_by_accounting', 1)
                ->select([
                    'timesheet_groups.id',
                    'timesheet_groups.name',
                    'timesheet_groups.project_id',
                    'timesheet_groups.payment_status',
                    'timesheet_groups.is_invoice_issued',
                    'timesheet_groups.is_payrolls_issued',
                    'timesheet_groups.status',
                    'timesheet_groups.total',
                    'timesheet_groups.created_at',
                    'timesheet_groups.created_by',
                    'timesheet_groups.accounting_approved_by',
                ]);

            $all_users = User::where('status', '!=', 'inactive')
                ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
                ->get();
            $users = $all_users->pluck('full_name', 'id');
            $projects = SalesProject::pluck('name', 'id');

            return DataTables::of($payrolls)
                ->addColumn('action', function ($row) use ($user, $is_admin) {
                    $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                    if ($is_admin || $user->can('essentials.show_payroll_timesheet')) {
                        $html .= '<li><a href="' . route('payroll.agentTimeSheet.showTimeSheet', ['id' => $row->id]) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';
                    }
                    if ($row->is_payrolls_issued == 0) {
                        $html .= '<li><a href="' . route('payroll.agentTimeSheet.issuePayrolls', ['id' => $row->id]) . '"><i class="fa fa-check" aria-hidden="true"></i> ' . __('lang_v1.issue payrolls') . '</a></li>';
                    }
                    $html .= '</ul></div>';
                    return $html;
                })
                ->editColumn('total', '<span class="display_currency" data-currency_symbol="true">{{$total}}</span>')
                ->editColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s');
                })
                ->editColumn('created_by', function ($row) use ($users) {
                    return $users[$row->created_by];
                })
                ->editColumn('project_id', function ($row) use ($projects) {
                    return $projects[$row->project_id] ?? '';
                })
                ->editColumn('status', function ($row) {
                    return __('lang_v1.' . $row->status);
                })
                ->editColumn(
                    'payment_status',
                    '<span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
                        </span>'
                )
                ->editColumn('accounting_approved_by', function ($row) use ($users) {
                    return $users[$row->accounting_approved_by] ?? '';
                })
                ->rawColumns(['created_by', 'payment_status', 'accounting_approved_by', 'action', 'total', 'status'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
        // $business_id = request()->session()->get('user.business_id');
        // $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        // $companies_ids = Company::pluck('id')->toArray();
        // $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        // if (!$is_admin) {
        //     $userIds = [];
        //     $userIds = $this->moduleUtil->applyAccessRole();

        //     $companies_ids = [];
        //     $roles = auth()->user()->roles;
        //     foreach ($roles as $role) {

        //         $accessRole = AccessRole::where('role_id', $role->id)->first();

        //         if ($accessRole) {
        //             $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
        //         }
        //     }
        // }
        // $payroll_groups = PayrollGroup::whereIn('essentials_payroll_groups.company_id', $companies_ids)->where('u.id', auth()->user()->id)
        //     ->leftjoin('users as u', 'u.id', '=', 'essentials_payroll_groups.created_by')
        //     ->leftJoin('business_locations as BL', 'essentials_payroll_groups.location_id', '=', 'BL.id')
        //     ->select(
        //         'essentials_payroll_groups.id as id',
        //         'essentials_payroll_groups.name as name',
        //         'essentials_payroll_groups.status as status',
        //         'essentials_payroll_groups.created_at as created_at',
        //         DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
        //         'essentials_payroll_groups.payment_status as payment_status',
        //         'essentials_payroll_groups.gross_total as gross_total',
        //         'BL.name as location_name'
        //     );

        // if (request()->ajax()) {
        //     return Datatables::of($payroll_groups)
        //         ->addColumn(
        //             'action',
        //             function ($row) {
        //                 // $html = '<div class="btn-group">
        //                 //         <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
        //                 //             data-toggle="dropdown" aria-expanded="false">' .
        //                 //     __('messages.actions') .
        //                 //     '<span class="caret"></span><span class="sr-only">Toggle Dropdown
        //                 //             </span>
        //                 //         </button>
        //                 //         <ul class="dropdown-menu dropdown-menu-right" role="menu">';


        //                 // $html .= '<li>
        //                 //         <a href="' . route('payrolls.show', ['id' => $row->id]) . '" target="_blank">
        //                 //                 <i class="fa fa-eye" aria-hidden="true"></i> '
        //                 //     . __('messages.view') .
        //                 //     '</a>
        //                 //     </li>';

        //                 // if ($row->status == 'draft') {
        //                 //     $html .= '<li>
        //                 //             <a href="' . route('payrolls.edit', ['id' => $row->id]) . '" target="_blank">
        //                 //                     <i class="fas fa-edit" aria-hidden="true"></i> '
        //                 //         . __('messages.edit') .
        //                 //         '</a>
        //                 //         </li>';
        //                 // }



        //                 // $html .= '<li><a href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'destroy'], [$row->id]) . '" class="delete-payroll"><i class="fa fa-trash" aria-hidden="true"></i> ' . __('messages.delete') . '</a></li>';




        //                 // if ($row->status == 'final' && $row->payment_status != 'paid') {
        //                 //     $html .= '<li>
        //                 //         <a href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'addPayment'], [$row->id]) . '" target="_blank">
        //                 //                 <i class="fas fa-money-check" aria-hidden="true"></i> '
        //                 //         . __('purchase.add_payment') .
        //                 //         '</a>
        //                 //     </li>';
        //                 // }



        //                 // $html .= '</ul></div>';
        //                 $html = '<a href="' . route('payrolls.show', ['id' => $row->id, 'type' => 'group']) . '" target="_blank">
        //                      <i class="fa fa-eye" aria-hidden="true"></i> '
        //                     . __('messages.view') .
        //                     '</a>';
        //                 return $html;
        //             }
        //         )
        //         ->editColumn('status', function ($row) {
        //             if ($row->status == "draft") {
        //                 return __('essentials::lang.draft_payroll');
        //             } elseif ($row->status == "final") {
        //                 return __('essentials::lang.final_payroll');
        //             } else {
        //                 return "";
        //             }
        //         })
        //         ->editColumn('created_at', '
        //         {{@format_datetime($created_at)}}
        //     ')
        //         ->editColumn('gross_total', '
        //         @format_currency($gross_total)
        //     ')
        //         ->editColumn('location_name', '
        //         @if(!empty($location_name))
        //             {{$location_name}}
        //         @else
        //             {{__("report.all_locations")}}
        //         @endif
        //     ')
        //         ->editColumn(
        //             'payment_status',
        //             '<span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
        //             </span>
        //             '
        //         )
        //         ->filterColumn('added_by', function ($query, $keyword) {
        //             $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
        //         })
        //         ->removeColumn('id')
        //         ->rawColumns(['action', 'added_by', 'created_at', 'status', 'gross_total', 'payment_status', 'location_name'])
        //         ->make(true);
        //   }

    }
    public function issuePayrolls($id)
    {
        try {
            $timesheet = TimeSheetGroup::with('timesheetUsers')->where('id', $id)->first();

            if (!$timesheet) {
                throw new \Exception('Timesheet group not found');
            }

            $translatedTimeSheetFor = __('agent.payroll_for');
            $employees_details = $timesheet->timesheetUsers;

            DB::beginTransaction();


            $payroll_group_data = [
                'business_id' => $timesheet->business_id,
                'timesheet_group_id' => $timesheet->id,
                //
                'company_id' => $timesheet->company_id,
                'name' => $translatedTimeSheetFor . ' ' . $timesheet->timesheet_date,
                'status' => $timesheet->status,
                'gross_total' => $timesheet->total,
                'created_by' => auth()->user()->id,
            ];


            $payroll_group = PayrollGroup::create($payroll_group_data);


            $transaction_ids = [];


            foreach ($employees_details as $employee_details) {

                $payroll = [
                    'expense_for' => $employee_details['id'],
                    'transaction_date' => $timesheet->timesheet_date,
                    'business_id' => $timesheet->business_id,
                    'created_by' => auth()->user()->id,
                    'type' => 'payroll',
                    'payment_status' => 'due',
                    'status' => $timesheet->status,
                    'total_before_tax' => $employee_details['final_salary'] ?? 0,
                    'essentials_amount_per_unit_duration' => $employee_details['monthly_cost'],
                    'final_total' => $employee_details['final_salary'] ?? 0,
                ];

                // Get allowances and deductions
                $allowances_and_deductions = $this->getAllowanceAndDeductionJson($employee_details);
                $payroll['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'];
                $payroll['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'];

                // Generate reference number
                $ref_count = $this->moduleUtil->setAndGetReferenceCount('payroll');
                if (empty($payroll['ref_no'])) {
                    $settings = request()->session()->get('business.essentials_settings');
                    $settings = !empty($settings) ? json_decode($settings, true) : [];
                    $prefix = !empty($settings['payroll_ref_no_prefix']) ? $settings['payroll_ref_no_prefix'] : '';
                    $payroll['ref_no'] = $this->moduleUtil->generateReferenceNumber('payroll', $ref_count, null, $prefix);
                }


                $transaction = Transaction::create($payroll);
                $transaction_ids[] = $transaction->id;
            }


            $payroll_group->payrollGroupTransactions()->sync($transaction_ids);


            DB::commit();

            $timesheet->is_payrolls_issued = 1;
            $timesheet->save();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('payrolls.index')->with('status', $output);
    }




    public function payrollsIndex()
    {
        $user = User::find(auth()->user()->id);
        $is_admin = $user->hasRole('Admin#1');

        $companies_ids = Company::pluck('id')->toArray();

        if (!$is_admin) {

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }
        $companies = Company::whereIn('id',  $companies_ids)->pluck('name', 'id')->toArray();

        $projects = SalesProject::all()->pluck('name', 'id')->toArray();
        $payrolls = TimesheetGroup::where('timesheet_groups.is_approved_by_accounting', 1)
            ->select([
                'timesheet_groups.id',
                'timesheet_groups.name',
                'timesheet_groups.project_id',
                'timesheet_groups.is_invoice_issued',
                'timesheet_groups.is_payrolls_issued',
                'timesheet_groups.status',
                'timesheet_groups.total',
                'timesheet_groups.created_at',
                'timesheet_groups.created_by',
                'timesheet_groups.accounting_approved_by',
            ]);
        error_log(json_encode($payrolls));
        $all_users = User::where('status', '!=', 'inactive')
            ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"))
            ->get();
        $users = $all_users->pluck('full_name', 'id');
        $projects = SalesProject::pluck('name', 'id');

        return DataTables::of($payrolls)
            ->addColumn('action', function ($row) use ($user, $is_admin) {
                $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' .
                    __('messages.actions') .
                    '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                if ($is_admin || $user->can('essentials.show_payroll_timesheet')) {
                    $html .= '<li><a href="' . route('payroll.agentTimeSheet.showTimeSheet', ['id' => $row->id]) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';
                }

                $html .= '</ul></div>';
                return $html;
            })
            ->editColumn('total', '<span class="display_currency" data-currency_symbol="true">{{$total}}</span>')
            ->editColumn('created_at', function ($row) {
                return \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s');
            })
            ->editColumn('created_by', function ($row) use ($users) {
                return $users[$row->created_by];
            })
            ->editColumn('project_id', function ($row) use ($projects) {
                return $projects[$row->project_id] ?? '';
            })
            ->editColumn('status', function ($row) {
                return __('lang_v1.' . $row->status);
            })
            ->editColumn('accounting_approved_by', function ($row) use ($users) {
                return $users[$row->accounting_approved_by] ?? '';
            })
            ->rawColumns(['created_by', 'accounting_approved_by', 'action', 'total', 'status'])
            ->make(true);
    }




    // public function create()
    // {
    //     return request()->all();
    //     $companies_ids = request()->input('companies');
    //     $projects_ids = request()->input('projects');
    //     $user_type = request()->input('user_type');
    //     $employee_ids = User::with('contract');

    //     if ($user_type == "worker") {
    //         $employee_ids = $employee_ids->whereIn('company_id', $companies_ids)->whereIn('assigned_to', $projects_ids)->where('user_type', 'worker');
    //     } elseif ($user_type == "employee" || $user_type == "remote_employee") {
    //         $employee_ids = $employee_ids->whereIn('company_id', $companies_ids)->where('user_type', 'employee');
    //     }
    //     if ($user_type == "remote_employee") {
    //         $remote_id = EssentialsContractType::where('type', 'LIKE', '%بعد%')->first()?->id;
    //         $employee_ids = $employee_ids->whereHas('contract', function ($query) use ($remote_id) {
    //             $query->where('contract_type_id', $remote_id);
    //         });
    //     }
    //     $employee_ids = $employee_ids->whereNot('status', 'inactive')->pluck('id')->toArray();

    //     $month_year = request()->input('month_year');
    //     $employees = User::with(['appointment.profession', 'assignedTo', 'essentialsUserShifts.shift', 'transactions', 'userAllowancesAndDeductions.essentialsAllowanceAndDeduction'])
    //         ->whereIn('users.id',  $employee_ids)
    //         ->select(
    //             'users.*',
    //             'users.id as user_id',
    //             DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),
    //             'users.id_proof_number',
    //             'users.essentials_pay_period',
    //             'users.essentials_salary',
    //             'users.essentials_pay_period as wd',
    //         )->get();

    //     $businesses = Business::pluck('name', 'id',);
    //     $currentDateTime = Carbon::now('Asia/Riyadh');
    //     $month = $currentDateTime->month;
    //     $year = $currentDateTime->year;
    //     $start_of_month = $currentDateTime->copy()->startOfMonth();
    //     $end_of_month = $currentDateTime->copy()->endOfMonth();
    //     $payrolls = [];
    //     foreach ($employees as $worker) {
    //         $housing_allowance = 0;
    //         $transportation_allowance = 0;
    //         $other_allowance = 0;
    //         $allowances = json_decode($worker)->user_allowances_and_deductions ?? [];
    //         foreach ($allowances as $allowance) {
    //             $allowance_dsc =   $allowance?->essentials_allowance_and_deduction?->description;
    //             if ((stripos($allowance_dsc, 'سكن') !== false) || (stripos($allowance_dsc, 'house') !== false)) {
    //                 $housing_allowance = $allowance->amount;
    //             } elseif ((stripos($allowance_dsc, 'نقل') !== false) || (stripos($allowance_dsc, 'مواصلات') !== false) || (stripos($allowance_dsc, 'transport') !== false)) {
    //                 $transportation_allowance = $allowance->amount;
    //             } else {
    //                 $other_allowance += floatval($allowance->amount ?? "0");
    //             }
    //         }
    //         $salary = $worker->essentials_salary + $other_allowance;
    //         if ($worker->user_type == "worker") {

    //             $project_name = $worker->assignedTo?->name ?? '';
    //         }
    //         if ($worker->user_type == "employee") {
    //             $profession = $worker->appointment?->profession?->name ?? '';
    //         }
    //         $payrolls[] = [
    //             'id' => $worker->user_id,
    //             'name' => $worker->name ?? '',
    //             'nationality' => User::find($worker->id)->country?->nationality ?? '',
    //             'identity_card_number' => $worker->id_proof_number ?? '',
    //             'project_name' => $project_name ?? '',
    //             'region' => $region ?? '',
    //             'profession' => $profession ?? '',
    //             'work_days' => 30,
    //             'salary' => number_format($worker->essentials_salary, 0, '.', ''),
    //             'housing_allowance' => number_format($housing_allowance, 0, '.', ''),
    //             'transportation_allowance' => number_format($transportation_allowance, 0, '.', ''),
    //             'other_allowance' => number_format($other_allowance, 0, '.', ''),
    //             'total' => number_format($salary, 0, '.', ''),
    //             'violations' => 0,
    //             'absence' => 0,
    //             'late' => 0,
    //             'late_deduction' => 0,
    //             'absence_deduction' => 0,
    //             'other_deductions' => 0,
    //             'loan' => 0,
    //             'total_deduction' => 0,
    //             'over_time_hours' => 0,
    //             'over_time_hours_addition' => 0,
    //             'additional_addition' => 0,
    //             'other_additions' => 0,
    //             'total_additions' => 0,
    //             'final_salary' => 0,
    //             'payment_method' => '',
    //             'notes' => '',
    //         ];
    //     }

    //     $date = (Carbon::createFromFormat('m/Y', request()->input('month_year') ?? Carbon::now()->format('m/Y')))->format('F Y');
    //     $transaction_date = request()->input('month_year');
    //     $group_name = __('essentials::lang.payroll_for_month', ['date' => $date]);
    //     $action = 'create';

    //     return view('essentials::payroll.create')->with(compact('user_type', 'employee_ids', 'group_name', 'date', 'transaction_date', 'month_year', 'payrolls', 'action'));
    // }


    public function create()
    {
        $companies_ids = request()->input('companies', []);
        $projects_ids = request()->input('projects', []);
        $departments_ids = request()->input('departments', []);
        $user_type = request()->input('user_type');
        $month_year = request()->input('month_year');

        $employee_ids = User::with('contract');

        if ($user_type == "worker") {
            $employee_ids = $employee_ids->whereIn('company_id', $companies_ids)->whereIn('assigned_to', $projects_ids)->where('user_type', 'worker');
        } elseif ($user_type == "employee" || $user_type == "remote_employee") {
            $employee_ids = $employee_ids->whereIn('users.essentials_department_id', $departments_ids)->whereIn('company_id', $companies_ids)->where('user_type', 'employee');
        }
        if ($user_type == "remote_employee") {
            $remote_id = EssentialsContractType::where('type', 'LIKE', '%بعد%')->first()?->id;
            $employee_ids = $employee_ids->whereHas('contract', function ($query) use ($remote_id) {
                $query->where('contract_type_id', $remote_id);
            });
        }
        $employee_ids = $employee_ids->whereNot('status', 'inactive')->pluck('id')->toArray();

        $employees = User::with([
            'appointment.profession',
            'assignedTo',
            'essentialsUserShifts.shift',
            'transactions',
            'userAllowancesAndDeductions.essentialsAllowanceAndDeduction'
        ])
            ->whereIn('users.id', $employee_ids)

            ->select(
                'users.*',
                'users.id as user_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),
                'users.id_proof_number',
                'users.essentials_pay_period',
                'users.essentials_salary',
                'users.essentials_pay_period as wd'
            )->get();

        if ($user_type == "worker") {
            $timesheet_users = TimesheetUser::whereIn('user_id', $employee_ids)->get();
        } else {
            $timesheet_users = collect([]);
        }

        $businesses = Business::pluck('name', 'id');
        $currentDateTime = Carbon::now('Asia/Riyadh');
        $month = $currentDateTime->month;
        $year = $currentDateTime->year;
        $start_of_month = $currentDateTime->copy()->startOfMonth();
        $end_of_month = $currentDateTime->copy()->endOfMonth();
        $payrolls = [];

        foreach ($employees as $worker) {
            $housing_allowance = 0;
            $transportation_allowance = 0;
            $other_allowance = 0;
            $timesheet = $timesheet_users->where('user_id', $worker->id)->first();
            if ($worker->user_type == "worker" && $timesheet) {
                $housing_allowance = $timesheet->housing;
                $transportation_allowance = $timesheet->transport;
                $other_allowance = $timesheet->other_allowances;
                $monthly_cost = $timesheet->monthly_cost;
                $work_days = $timesheet->work_days;
                $absence_days = $timesheet->absence_days;
                $absence_amount = $timesheet->absence_amount;
                $over_time_hours = $timesheet->over_time_hours;
                $over_time_amount = $timesheet->over_time_amount;
                $other_deduction = $timesheet->other_deduction;
                $other_addition = $timesheet->other_addition;
                $salary = $timesheet->cost_2;
                $invoice_value = $timesheet->invoice_value;
                $vat = $timesheet->vat;
                $total = $timesheet->total;
                $basic = $timesheet->basic;
                $total_salary = $timesheet->total_salary;
                $deductions = $timesheet->deductions;
                $additions = $timesheet->additions;
                $final_salary = $timesheet->final_salary;
                $project_name = $timesheet->project_id;
            } else {
                $allowances = json_decode($worker)->user_allowances_and_deductions ?? [];
                foreach ($allowances as $allowance) {
                    $allowance_dsc = $allowance?->essentials_allowance_and_deduction?->description;
                    if ((stripos($allowance_dsc, 'سكن') !== false) || (stripos($allowance_dsc, 'house') !== false)) {
                        $housing_allowance = number_format($allowance->amount, 0, '.', '');
                    } elseif ((stripos($allowance_dsc, 'نقل') !== false) || (stripos($allowance_dsc, 'مواصلات') !== false) || (stripos($allowance_dsc, 'transport') !== false)) {
                        $transportation_allowance = number_format($allowance->amount, 0, '.', '');
                    } else {
                        $other_allowance += floatval($allowance->amount ?? "0");
                    }
                }
                $salary = number_format($worker->essentials_salary + $other_allowance, 0, '.', '');
                $monthly_cost = number_format($worker->essentials_salary, 0, '.', '');
                $work_days = 30; // Assuming 30 days in a month for now
                $absence_days = 0;
                $absence_amount = 0;
                $over_time_hours = 0;
                $over_time_amount = 0;
                $other_deduction = 0;
                $other_addition = 0;
                $invoice_value = null;
                $vat = null;
                $total = null;
                $basic = null;
                $total_salary = null;
                $deductions = 0;
                $additions = 0;
                $final_salary = null;
                $project_name = $worker->assignedTo?->name ?? '';
            }

            $profession = $worker->appointment?->profession?->name ?? '';

            $payrolls[] = [
                'id' => $worker->user_id,
                'name' => $worker->name ?? '',
                'nationality' => User::find($worker->id)->country?->nationality ?? '',
                'identity_card_number' => $worker->id_proof_number ?? '',
                'project_name' => $project_name ?? '',
                'region' => '',
                'profession' => $profession ?? '',
                'work_days' => $work_days,
                'salary' => $monthly_cost,
                'housing_allowance' => $housing_allowance,
                'transportation_allowance' => $transportation_allowance,
                'other_allowance' => $other_allowance,
                'total' => $salary,
                'violations' => 0,
                'absence' => $absence_days,
                'late' => 0,
                'late_deduction' => 0,
                'absence_deduction' => $absence_amount,
                'other_deductions' => $other_deduction,
                'loan' => 0,
                'total_deduction' => $other_deduction,
                'over_time_hours' => $over_time_hours,
                'over_time_hours_addition' => $over_time_amount,
                'additional_addition' => 0,
                'other_additions' => $additions,
                'total_additions' => $additions,
                'final_salary' => $final_salary,
                'payment_method' => '',
                'notes' => '',
            ];
        }

        $date = (Carbon::createFromFormat('m/Y', $month_year ?? Carbon::now()->format('m/Y')))->format('F Y');
        $transaction_date = $month_year;
        $group_name = __('essentials::lang.payroll_for_month', ['date' => $date]);
        $action = 'create';

        return view('essentials::payroll.create')->with(compact('user_type', 'employee_ids', 'group_name', 'date', 'transaction_date', 'month_year', 'payrolls', 'action'));
    }

    public function store(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $business_id = $user->business_id ?? 1;
        $company_id = $user->company_id ?? 1;
        try {
            DB::beginTransaction();
            $payroll_group['business_id'] = $business_id;
            $payroll_group['company_id'] = $company_id;
            $payroll_group['name'] = $request->input('payroll_group_name');
            $payroll_group['status'] = $request->input('payroll_group_status');
            $payroll_group['gross_total'] = $request->input('total_payrolls');
            $payroll_group['created_by'] = auth()->user()->id;
            $payroll_group = PayrollGroup::create($payroll_group);
            $transaction_date = Carbon::createFromFormat('m/Y', $request->transaction_date)->format('Y-m-d H:i:s');

            //ref_no,
            $transaction_ids = [];
            $employees_details = $request->payrolls;
            foreach ($employees_details as $employee_details) {
                error_log($employee_details['final_salary'] ?? 1263761253761);
                $payroll['expense_for'] = $employee_details['id'];
                $payroll['transaction_date'] = $transaction_date;
                $payroll['business_id'] = $business_id;
                $payroll['created_by'] = auth()->user()->id;
                $payroll['type'] = 'payroll';
                $payroll['payment_status'] = 'due';
                $payroll['status'] = $request->input('payroll_group_status');
                $payroll['total_before_tax'] = $employee_details['final_salary'] ?? 0;
                $payroll['essentials_amount_per_unit_duration'] = $employee_details['salary'];

                $allowances_and_deductions = $this->getAllowanceAndDeductionJson($employee_details);
                $payroll['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'];
                $payroll['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'];
                $payroll['final_total'] = $employee_details['final_salary'] ?? 0;
                //Update reference count
                $ref_count = $this->moduleUtil->setAndGetReferenceCount('payroll');

                //Generate reference number
                if (empty($payroll['ref_no'])) {
                    $settings = request()->session()->get('business.essentials_settings');
                    $settings = !empty($settings) ? json_decode($settings, true) : [];
                    $prefix = !empty($settings['payroll_ref_no_prefix']) ? $settings['payroll_ref_no_prefix'] : '';
                    $payroll['ref_no'] = $this->moduleUtil->generateReferenceNumber('payroll', $ref_count, null, $prefix);
                }
                unset(
                    $payroll['allowance_names'],
                    $payroll['allowance_types'],
                    $payroll['allowance_percent'],
                    $payroll['allowance_amounts'],
                    $payroll['deduction_names'],
                    $payroll['deduction_types'],
                    $payroll['deduction_percent'],
                    $payroll['deduction_amounts'],
                    $payroll['total']
                );

                $transaction = Transaction::create($payroll);
                $transaction_ids[] = $transaction->id;

                // if ($notify_employee && $payroll_group->status == 'final') {
                //     $transaction->action = 'created';
                //     $transaction->transaction_for->notify(new PayrollNotification($transaction));
                // }
            }

            $payroll_group->payrollGroupTransactions()->sync($transaction_ids);

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('payrolls.index')->with('status', $output);
    }

    private function getAllowanceAndDeductionJson($payroll)
    {
        $allowance_types = [];
        $allowance_names_array = [];
        $allowance_percent_array = [];
        $allowance_amounts = [];


        if (isset($payroll['over_time_hours_addition']) && $payroll['over_time_hours_addition'] != 0) {
            $allowance_names_array[] = 'وقت إضافي';
            $allowance_amounts[] = $payroll['over_time_hours_addition'];
            $allowance_percent_array[] = 0;
            $allowance_types[] = 'fixed';
        }
        if (isset($payroll['additional_addition']) && $payroll['additional_addition'] != 0) {
            $allowance_names_array[] = 'مبلغ إضافي';
            $allowance_amounts[] = $payroll['additional_addition'];
            $allowance_percent_array[] = 0;

            $allowance_types[] = 'fixed';
        }
        if (isset($payroll['other_additions']) && $payroll['other_additions'] != 0) {
            $allowance_names_array[] = 'استحفافات إخرى';
            $allowance_amounts[] = $payroll['other_additions'];
            $allowance_percent_array[] = 0;
            $allowance_types[] = 'fixed';
        }


        $deduction_types = [];
        $deduction_names_array = [];
        $deduction_percents_array = [];
        $deduction_amounts = [];

        if ($payroll['violations'] != 0) {
            $deduction_names_array[] = 'مخالفات';
            $deduction_amounts[] = $this->moduleUtil->num_uf($payroll['violations']);
            $deduction_percents_array[] = 0;
            $deduction_types[] = 'fixed';
        }
        if ($payroll['absence'] != 0) {
            $deduction_names_array[] = 'غياب';
            $deduction_amounts[] = $this->moduleUtil->num_uf($payroll['absence']);
            $deduction_percents_array[] = 0;
            $deduction_types[] = 'fixed';
        }
        if ($payroll['late'] != 0) {
            $deduction_names_array[] = 'تأخير';
            $deduction_amounts[] = $this->moduleUtil->num_uf($payroll['late']);
            $deduction_percents_array[] = 0;
            $deduction_types[] = 'fixed';
        }
        if ($payroll['other_deductions'] != 0) {
            $deduction_names_array[] = 'خصومات أخرى';
            $deduction_amounts[] = $this->moduleUtil->num_uf($payroll['other_deductions']);
            $deduction_percents_array[] = 0;
            $deduction_types[] = 'fixed';
        }
        if ($payroll['loan'] != 0) {
            $deduction_names_array[] = 'سلف';
            $deduction_amounts[] = $this->moduleUtil->num_uf($payroll['loan']);
            $deduction_percents_array[] = 0;
            $deduction_types[] = 'fixed';
        }

        $output['essentials_allowances'] = json_encode([
            'allowance_names' => $allowance_names_array,
            'allowance_amounts' => $allowance_amounts,
            'allowance_types' => $allowance_types,
            'allowance_percents' => $allowance_percent_array,
        ]);
        $output['essentials_deductions'] = json_encode([
            'deduction_names' => $deduction_names_array,
            'deduction_amounts' => $deduction_amounts,
            'deduction_types' => $deduction_types,
            'deduction_percents' => $deduction_percents_array,
        ]);

        return $output;
    }


    public function show($id, $type)
    {


        if ($type == 'group') {
            $payroll_group = PayrollGroup::find($id);
            $payroll_group_transactions = $payroll_group->payrollGroupTransactions;
            $transaction_date = $payroll_group_transactions->first()->transaction_date;
            $date = Carbon::parse($transaction_date)->format('F Y');
            $month_year = $date;
            $employee_ids = [];

            foreach ($payroll_group_transactions as $payroll_group_transaction) {
                $employee_ids[] = $payroll_group_transaction->expense_for;
            }
            $usersArr = User::whereIn('id', $employee_ids)->select([
                'users.*',

                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),

            ])->get();
            $user = $usersArr->first();
            $user_type = $user->user_type;
            $remote_id = EssentialsContractType::where('type', 'LIKE', '%بعد%')->first()?->id;
            if ($user->contract?->contract_type_id == $remote_id) {
                $user_type =  "remote_employee";
            }

            $businesses = Business::pluck('name', 'id',);

            $payrolls = [];


            // return  json_decode($payroll_group_transactions[3]->essentials_allowances);
            foreach ($payroll_group_transactions as $payroll_group_transaction) {


                $user = $usersArr->where('id', $payroll_group_transaction->expense_for)->first();
                $housing_allowance = 0;
                $transportation_allowance = 0;
                $other_allowance = 0;
                $allowances = json_decode($user)->user_allowances_and_deductions ?? [];
                foreach ($allowances as $allowance) {
                    $allowance_dsc =   $allowance->essentials_allowance_and_deduction->description;
                    if ((stripos($allowance_dsc, 'سكن') !== false) || (stripos($allowance_dsc, 'house') !== false)) {
                        $housing_allowance = $allowance->amount;
                    } elseif ((stripos($allowance_dsc, 'نقل') !== false) || (stripos($allowance_dsc, 'مواصلات') !== false) || (stripos($allowance_dsc, 'transport') !== false)) {
                        $transportation_allowance = $allowance->amount;
                    } else {
                        $other_allowance += floatval($allowance->amount ?? "0");
                    }
                }

                $salary = $user->essentials_salary + $other_allowance;
                $essentials_allowances = json_decode($payroll_group_transaction->essentials_allowances);
                $over_time_hours = 0;
                $additional_addition = 0;
                $other_additions = 0;
                foreach ($essentials_allowances->allowance_names as $index => $allowance) {
                    if ((stripos($allowance, 'وقت') !== false)) {
                        $over_time_hours = $essentials_allowances->allowance_amounts[$index];
                    }
                    if ((stripos($allowance, 'مبلغ') !== false)) {
                        $additional_addition = $essentials_allowances->allowance_amounts[$index];
                    }
                    if ((stripos($allowance, 'خرى') !== false)) {
                        $other_additions = $essentials_allowances->allowance_amounts[$index];
                    }
                }



                $essentials_deductions = json_decode($payroll_group_transaction->essentials_deductions);
                $violations = 0;
                $absence = 0;
                $other_deductions = 0;
                $loan = 0;
                $late = 0;
                foreach ($essentials_deductions->deduction_names as $index => $deduction) {
                    if ((stripos($deduction, 'مخالف') !== false)) {
                        $violations = $essentials_deductions->deduction_amounts[$index];
                    }
                    if ((stripos($deduction, 'غياب') !== false)) {
                        $absence = $essentials_deductions->deduction_amounts[$index];
                    }
                    if ((stripos($deduction, 'تأخير') !== false)) {
                        $late = $essentials_deductions->deduction_amounts[$index];
                    }
                    if ((stripos($deduction, 'خرى') !== false)) {
                        $other_deductions = $essentials_deductions->deduction_amounts[$index];
                    }
                    if ((stripos($deduction, 'سلف') !== false)) {
                        $loan = $essentials_deductions->deduction_amounts[$index];
                    }
                }
                if ($user->user_type == "worker") {

                    $project_name = $user->assignedTo?->name ?? '';
                }
                if ($user->user_type == "employee") {
                    $profession = $user->appointment?->profession?->name ?? '';
                }
                $payrolls[] = [
                    'id' => $user->id,
                    'name' =>  $user->name ?? '',
                    'nationality' => $user->country?->nationality ?? '',
                    'identity_card_number' => $user->id_proof_number ?? '',
                    'project_name' => $project_name ?? '',
                    'region' => $region ?? '',
                    'profession' => $profession ?? '',
                    'work_days' => 30,
                    'salary' => number_format($payroll_group_transaction->essentials_amount_per_unit_duration, 0, '.', ''),
                    'housing_allowance' => number_format($housing_allowance, 0, '.', ''),
                    'transportation_allowance' => number_format($transportation_allowance, 0, '.', ''),
                    'other_allowance' => number_format($other_allowance, 0, '.', ''),
                    'total' => number_format($salary, 0, '.', ''),
                    'violations' =>   $violations,
                    'absence' => $absence,
                    'late' =>  $late,
                    'late_deduction' => 0,
                    'absence_deduction' => 0,
                    'other_deductions' => $other_deductions,
                    'loan' => $loan,
                    'total_deduction' => 0,
                    'over_time_hours' => $over_time_hours,
                    'over_time_hours_addition' => 0,
                    'additional_addition' => $additional_addition,
                    'other_additions' => $other_additions,
                    'total_additions' => 0,
                    'final_salary' => $payroll_group_transaction->final_total,
                    'payment_method' => '',
                    'notes' => '',
                ];
            }

            $group_name = __('essentials::lang.payroll_for_month', ['date' => $date]);
            $action = 'edit';
            return view('essentials::payroll.show')->with(compact('user_type', 'employee_ids', 'group_name', 'transaction_date', 'date', 'month_year', 'payrolls', 'action'));
        } elseif ($type == "single") {

            $payroll_group_transactions = Transaction::find($id);

            $transaction_date = $payroll_group_transactions->transaction_date;
            $date = Carbon::parse($transaction_date)->format('F Y');
            $month_year = $date;
            $employee_ids =  $payroll_group_transactions->expense_for;
            $user = User::with('contract')->where('id', $employee_ids)->select([
                'users.*',
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),

            ])->first();

            $user_type = $user->user_type;
            $remote_id = EssentialsContractType::where('type', 'LIKE', '%بعد%')->first()?->id;
            if ($user->contract->contract_type_id == $remote_id) {
                $user_type =  "remote_employee";
            }



            $businesses = Business::pluck('name', 'id',);

            $payrolls = [];





            $housing_allowance = 0;
            $transportation_allowance = 0;
            $other_allowance = 0;
            $allowances = json_decode($user)->user_allowances_and_deductions ?? [];
            foreach ($allowances as $allowance) {
                $allowance_dsc =   $allowance->essentials_allowance_and_deduction->description;
                if ((stripos($allowance_dsc, 'سكن') !== false) || (stripos($allowance_dsc, 'house') !== false)) {
                    $housing_allowance = $allowance->amount;
                } elseif ((stripos($allowance_dsc, 'نقل') !== false) || (stripos($allowance_dsc, 'مواصلات') !== false) || (stripos($allowance_dsc, 'transport') !== false)) {
                    $transportation_allowance = $allowance->amount;
                } else {
                    $other_allowance += floatval($allowance->amount ?? "0");
                }
            }
            $salary = $user->essentials_salary + $other_allowance;
            $essentials_allowances = json_decode($payroll_group_transactions->essentials_allowances);
            $over_time_hours = 0;
            $additional_addition = 0;
            $other_additions = 0;
            foreach ($essentials_allowances->allowance_names as $index => $allowance) {
                if ((stripos($allowance, 'وقت') !== false)) {
                    $over_time_hours = $essentials_allowances->allowance_amounts[$index];
                }
                if ((stripos($allowance, 'مبلغ') !== false)) {
                    $additional_addition = $essentials_allowances->allowance_amounts[$index];
                }
                if ((stripos($allowance, 'خرى') !== false)) {
                    $other_additions = $essentials_allowances->allowance_amounts[$index];
                }
            }



            $essentials_deductions = json_decode($payroll_group_transactions->essentials_deductions);
            $violations = 0;
            $absence = 0;
            $other_deductions = 0;
            $loan = 0;
            $late = 0;
            foreach ($essentials_deductions->deduction_names as $index => $deduction) {
                if ((stripos($deduction, 'مخالف') !== false)) {
                    $violations = $essentials_deductions->deduction_amounts[$index];
                }
                if ((stripos($deduction, 'غياب') !== false)) {
                    $absence = $essentials_deductions->deduction_amounts[$index];
                }
                if ((stripos($deduction, 'تأخير') !== false)) {
                    $late = $essentials_deductions->deduction_amounts[$index];
                }
                if ((stripos($deduction, 'خرى') !== false)) {
                    $other_deductions = $essentials_deductions->deduction_amounts[$index];
                }
                if ((stripos($deduction, 'سلف') !== false)) {
                    $loan = $essentials_deductions->deduction_amounts[$index];
                }
            }
            if ($user->user_type == "worker") {

                $project_name = $user->assignedTo?->name ?? '';
            }
            if ($user->user_type == "employee") {
                $profession = $user->appointment?->profession?->name ?? '';
            }
            $payrolls[] = [
                'id' => $user->id,
                'name' =>  $user->name ?? '',
                'nationality' => $user->country?->nationality ?? '',
                'identity_card_number' => $user->id_proof_number ?? '',
                'project_name' => $project_name ?? '',
                'region' => $region ?? '',
                'profession' => $profession ?? '',
                'work_days' => 30,
                'salary' => number_format($payroll_group_transactions->essentials_amount_per_unit_duration, 0, '.', ''),
                'housing_allowance' => number_format($housing_allowance, 0, '.', ''),
                'transportation_allowance' => number_format($transportation_allowance, 0, '.', ''),
                'other_allowance' => number_format($other_allowance, 0, '.', ''),
                'total' => number_format($salary, 0, '.', ''),
                'violations' =>   $violations,
                'absence' => $absence,
                'late' =>  $late,
                'late_deduction' => 0,
                'absence_deduction' => 0,
                'other_deductions' => $other_deductions,
                'loan' => $loan,
                'total_deduction' => 0,
                'over_time_hours' => $over_time_hours,
                'over_time_hours_addition' => 0,
                'additional_addition' => $additional_addition,
                'other_additions' => $other_additions,
                'total_additions' => 0,
                'final_salary' => $payroll_group_transactions->final_total,
                'payment_method' => '',
                'notes' => '',
            ];


            $group_name = __('essentials::lang.payroll_for_month', ['date' => $date]);
            $action = 'edit';
            $user_name = $user->name;
            return view('essentials::payroll.show')->with(compact('user_type', 'user_name', 'employee_ids', 'group_name', 'transaction_date', 'date', 'month_year', 'payrolls', 'action'));
        }
    }

    public function edit($id)
    {
        $payroll_group = PayrollGroup::find($id);
        $payroll_group_transactions = $payroll_group->payrollGroupTransactions;
        $transaction_date = $payroll_group_transactions->first()->transaction_date;
        $date = Carbon::parse($transaction_date)->format('F Y');
        $month_year = $date;
        $employee_ids = [];

        foreach ($payroll_group_transactions as $payroll_group_transaction) {
            $employee_ids[] = $payroll_group_transaction->expense_for;
        }
        $usersArr = User::whereIn('id', $employee_ids)->select([
            'users.*',
            DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),

        ])->get();


        $businesses = Business::pluck('name', 'id',);

        $payrolls = [];


        // return  json_decode($payroll_group_transactions[3]->essentials_allowances);
        foreach ($payroll_group_transactions as $payroll_group_transaction) {


            $user = $usersArr->where('id', $payroll_group_transaction->expense_for)->first();
            $housing_allowance = 0;
            $transportation_allowance = 0;
            $other_allowance = 0;
            $allowances = json_decode($user)->user_allowances_and_deductions ?? [];
            foreach ($allowances as $allowance) {
                $allowance_dsc =   $allowance->essentials_allowance_and_deduction->description;
                if ((stripos($allowance_dsc, 'سكن') !== false) || (stripos($allowance_dsc, 'house') !== false)) {
                    $housing_allowance = $allowance->amount;
                } elseif ((stripos($allowance_dsc, 'نقل') !== false) || (stripos($allowance_dsc, 'مواصلات') !== false) || (stripos($allowance_dsc, 'transport') !== false)) {
                    $transportation_allowance = $allowance->amount;
                } else {
                    $other_allowance += floatval($allowance->amount ?? "0");
                }
            }
            $salary = $user->essentials_salary + $other_allowance;
            $essentials_allowances = json_decode($payroll_group_transaction->essentials_allowances);
            $over_time_hours = 0;
            $additional_addition = 0;
            $other_additions = 0;
            foreach ($essentials_allowances->allowance_names as $index => $allowance) {
                if ((stripos($allowance, 'وقت') !== false)) {
                    $over_time_hours = $essentials_allowances->allowance_amounts[$index];
                }
                if ((stripos($allowance, 'مبلغ') !== false)) {
                    $additional_addition = $essentials_allowances->allowance_amounts[$index];
                }
                if ((stripos($allowance, 'خرى') !== false)) {
                    $other_additions = $essentials_allowances->allowance_amounts[$index];
                }
            }



            $essentials_deductions = json_decode($payroll_group_transaction->essentials_deductions);
            $violations = 0;
            $absence = 0;
            $other_deductions = 0;
            $loan = 0;
            foreach ($essentials_deductions->deduction_names as $index => $deduction) {
                if ((stripos($deduction, 'مخالف') !== false)) {
                    $violations = $essentials_deductions->deduction_amounts[$index];
                }
                if ((stripos($deduction, 'غياب') !== false)) {
                    $absence = $essentials_deductions->deduction_amounts[$index];
                }
                if ((stripos($deduction, 'خرى') !== false)) {
                    $other_deductions = $essentials_deductions->deduction_amounts[$index];
                }
                if ((stripos($deduction, 'سلف') !== false)) {
                    $loan = $essentials_deductions->deduction_amounts[$index];
                }
            }
            $payrolls[] = [
                'id' => $user->id,
                'name' =>  $user->name ?? '',
                'nationality' => $user->country?->nationality ?? '',
                'identity_card_number' => $user->id_proof_number ?? '',
                'profession' => '',
                'work_days' => 30,
                'salary' => number_format($payroll_group_transaction->essentials_amount_per_unit_duration, 0, '.', ''),
                'housing_allowance' => number_format($housing_allowance, 0, '.', ''),
                'transportation_allowance' => number_format($transportation_allowance, 0, '.', ''),
                'other_allowance' => number_format($other_allowance, 0, '.', ''),
                'total' => number_format($salary, 0, '.', ''),
                'violations' =>   $violations,
                'absence' => $absence,
                'absence_deduction' => 0,
                'other_deductions' => $other_deductions,
                'loan' => $loan,
                'total_deduction' => 0,
                'over_time_hours' => $over_time_hours,
                'over_time_hours_addition' => 0,
                'additional_addition' => $additional_addition,
                'other_additions' => $other_additions,
                'total_additions' => 0,
                'final_salary' => $payroll_group_transaction->final_total,
                'payment_method' => '',
                'notes' => '',
            ];
        }

        $group_name = __('essentials::lang.payroll_for_month', ['date' => $date]);
        $action = 'edit';

        return view('essentials::payroll.create')->with(compact('employee_ids', 'group_name', 'transaction_date', 'date', 'month_year', 'payrolls', 'action'));
    }




    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');



        try {
            $input = $request->only(['essentials_duration', 'essentials_amount_per_unit_duration', 'final_total', 'essentials_duration_unit']);

            $input['essentials_amount_per_unit_duration'] = $this->moduleUtil->num_uf($input['essentials_amount_per_unit_duration']);
            $input['total_before_tax'] = $input['final_total'];

            //get pay componentes
            $payroll['allowance_names'] = $request->input('allowance_names');
            $payroll['allowance_types'] = $request->input('allowance_types');
            $payroll['allowance_percent'] = $request->input('allowance_percent');
            $payroll['allowance_amounts'] = $request->input('allowance_amounts');
            $payroll['deduction_names'] = $request->input('deduction_names');
            $payroll['deduction_types'] = $request->input('deduction_types');
            $payroll['deduction_percent'] = $request->input('deduction_percent');
            $payroll['deduction_amounts'] = $request->input('deduction_amounts');
            $payroll['final_total'] = $request->input('final_total');

            $allowances_and_deductions = $this->getAllowanceAndDeductionJson($payroll);
            $input['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'];
            $input['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'];

            DB::beginTransaction();
            $payroll = Transaction::where('business_id', $business_id)
                ->where('type', 'payroll')
                ->findOrFail($id);

            $payroll->update($input);

            $payroll->action = 'updated';
            $payroll->transaction_for->notify(new PayrollNotification($payroll));

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index'])->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');



        if (request()->ajax()) {
            try {
                $payroll_group = PayrollGroup::where('business_id', $business_id)
                    ->with(['payrollGroupTransactions'])
                    ->findOrFail($id);

                DB::beginTransaction();
                if ($payroll_group->status == 'draft') {
                    $transaction_ids = $payroll_group->payrollGroupTransactions->pluck('id')->toArray();
                    //delete all account tranactions
                    AccountTransaction::whereIn('transaction_id', $transaction_ids)->delete();
                    //delete all transaction payments
                    TransactionPayment::whereIn('transaction_id', $transaction_ids)->delete();

                    $payroll_group->payrollGroupTransactions()->delete();
                    $payroll_group->delete();
                }

                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function getAllowanceAndDeductionRow(Request $request)
    {
        if ($request->ajax()) {
            $employee = $request->input('employee_id');
            $type = $request->input('type');

            $ad_row = view('essentials::payroll.allowance_and_deduction_row')
                ->with(compact('type', 'employee'))
                ->render();

            return $ad_row;
        }
    }

    public function payrollGroupDatatable(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $user_businesses_ids = Business::pluck('id')->unique()->toArray();

        if (!$is_admin) {
            $userProjects = [];
            $userBusinesses = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                    $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                }
            }
            $user_businesses_ids = array_unique($userBusinesses);
        }
        if ($request->ajax()) {
            $payroll_groups = PayrollGroup::whereIn('essentials_payroll_groups.business_id', $user_businesses_ids)
                ->leftjoin('users as u', 'u.id', '=', 'essentials_payroll_groups.created_by')
                ->leftJoin('business_locations as BL', 'essentials_payroll_groups.location_id', '=', 'BL.id')
                ->select(
                    'essentials_payroll_groups.id as id',
                    'essentials_payroll_groups.name as name',
                    'essentials_payroll_groups.status as status',
                    'essentials_payroll_groups.created_at as created_at',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
                    'essentials_payroll_groups.payment_status as payment_status',
                    'essentials_payroll_groups.gross_total as gross_total',
                    'BL.name as location_name'
                );

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $payroll_groups->where(function ($q) use ($permitted_locations) {
                    $q->whereIn('essentials_payroll_groups.location_id', $permitted_locations)
                        ->orWhereNull('essentials_payroll_groups.location_id');
                });
            }

            return Datatables::of($payroll_groups)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                        data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                        if (auth()->user()->hasRole("Admin#1") || auth()->user()->can('essentials.show_payroll')) {
                            $html .= '<li>
                                    <a href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'viewPayrollGroup'], [$row->id]) . '" target="_blank">
                                            <i class="fa fa-eye" aria-hidden="true"></i> '
                                . __('messages.view') .
                                '</a>
                                </li>';
                        }
                        if (auth()->user()->hasRole("Admin#1")  || auth()->user()->can('essentials.update_payroll')) {
                            $html .= '<li>
                                        <a href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'getEditPayrollGroup'], [$row->id]) . '" target="_blank">
                                                <i class="fas fa-edit" aria-hidden="true"></i> '
                                . __('messages.edit') .
                                '</a>
                                    </li>';
                        }

                        if (auth()->user()->hasRole("Admin#1") || auth()->user()->can('essentials.delete_payroll') && $row->status == 'draft') {
                            $html .= '<li><a href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'destroy'], [$row->id]) . '" class="delete-payroll"><i class="fa fa-trash" aria-hidden="true"></i> ' . __('messages.delete') . '</a></li>';
                        }



                        if ($row->status == 'final' && $row->payment_status != 'paid') {
                            $html .= '<li>
                                    <a href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'addPayment'], [$row->id]) . '" target="_blank">
                                            <i class="fas fa-money-check" aria-hidden="true"></i> '
                                . __('purchase.add_payment') .
                                '</a>
                                </li>';
                        }


                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->editColumn('status', '
                    @lang("sale.".$status)
                ')
                ->editColumn('created_at', '
                    {{@format_datetime($created_at)}}
                ')
                ->editColumn('gross_total', '
                    @format_currency($gross_total)
                ')
                ->editColumn('location_name', '
                    @if(!empty($location_name))
                        {{$location_name}}
                    @else
                        {{__("report.all_locations")}}
                    @endif
                ')
                ->editColumn(
                    'payment_status',
                    '<span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
                        </span>
                        '
                )
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'added_by', 'created_at', 'status', 'gross_total', 'payment_status', 'location_name'])
                ->make(true);
        }
    }

    public function viewPayrollGroup($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $user_businesses_ids = Business::pluck('id')->unique()->toArray();

        if (!$is_admin) {
            $userProjects = [];
            $userBusinesses = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                    $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                }
            }
            $user_businesses_ids = array_unique($userBusinesses);
        }
        $payroll_group = PayrollGroup::whereIn('business_id', $user_businesses_ids)
            ->with(['payrollGroupTransactions', 'payrollGroupTransactions.transaction_for', 'businessLocation', 'business'])
            ->findOrFail($id);

        $payrolls = [];
        $month_name = null;
        $year = null;
        foreach ($payroll_group->payrollGroupTransactions as $transaction) {

            //payroll info
            if (empty($month_name) && empty($year)) {
                $transaction_date = \Carbon::parse($transaction->transaction_date);
                $month_name = $transaction_date->format('F');
                $year = $transaction_date->format('Y');
            }

            //transaction info
            $payrolls[$transaction->expense_for]['transaction_id'] = $transaction->id;
            $payrolls[$transaction->expense_for]['final_total'] = $transaction->final_total;
            $payrolls[$transaction->expense_for]['payment_status'] = $transaction->payment_status;

            //get employee info
            $payrolls[$transaction->expense_for]['employee'] = $transaction->transaction_for->user_full_name;
            $payrolls[$transaction->expense_for]['bank_details'] = json_decode($transaction->transaction_for->bank_details, true);
        }

        return view('essentials::payroll.view_payroll_group')
            ->with(compact('payroll_group', 'month_name', 'year', 'payrolls'));
    }

    public function getEditPayrollGroup($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $user_businesses_ids = Business::pluck('id')->unique()->toArray();

        if (!$is_admin) {
            $userProjects = [];
            $userBusinesses = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                    $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                }
            }
            $user_businesses_ids = array_unique($userBusinesses);
        }
        $payroll_group = PayrollGroup::whereIn('business_id', $user_businesses_ids)
            ->with(['payrollGroupTransactions', 'payrollGroupTransactions.transaction_for', 'businessLocation'])
            ->findOrFail($id);

        //payroll location
        $location = $payroll_group->businessLocation;

        $payrolls = [];
        $transaction_date = null;
        $month_name = null;
        $year = null;
        foreach ($payroll_group->payrollGroupTransactions as $transaction) {

            //payroll info
            if (empty($transaction_date) && empty($month_name) && empty($year)) {
                $transaction_date = \Carbon::parse($transaction->transaction_date);
                $month_name = $transaction_date->format('F');
                $year = $transaction_date->format('Y');
                $start_date = \Carbon::parse($transaction->transaction_date);
                $end_date = \Carbon::parse($start_date)->lastOfMonth();
            }
            //transaction info
            $payrolls[$transaction->expense_for]['transaction_id'] = $transaction->id;

            //get employee info
            $payrolls[$transaction->expense_for]['name'] = $transaction->transaction_for->user_full_name ?? '';
            $payrolls[$transaction->expense_for]['staff_note'] = $transaction->staff_note;
            $payrolls[$transaction->expense_for]['essentials_amount_per_unit_duration'] = $transaction->essentials_amount_per_unit_duration;
            $payrolls[$transaction->expense_for]['essentials_duration'] = $transaction->essentials_duration;
            $payrolls[$transaction->expense_for]['essentials_duration_unit'] = $transaction->essentials_duration_unit;
            $payrolls[$transaction->expense_for]['total_leaves'] = $this->essentialsUtil->getTotalLeavesForGivenDateOfAnEmployee($business_id, $transaction->expense_for, $start_date->format('Y-m-d'), $end_date->format('Y-m-d'));
            $payrolls[$transaction->expense_for]['total_days_worked'] = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee($business_id, $transaction->expense_for, $start_date, $end_date);

            //get total work duration of employee(attendance)
            $payrolls[$transaction->expense_for]['total_work_duration'] = $this->essentialsUtil->getTotalWorkDuration('hour', $transaction->expense_for, $business_id, $start_date->format('Y-m-d'), $end_date->format('Y-m-d'));

            //get earnings employee
            $allowances = !empty($transaction->essentials_allowances) ? json_decode($transaction->essentials_allowances, true) : [];

            if (empty($allowances['allowance_names']) && empty($allowances['allowance_amounts'])) {
                $allowances['allowance_names'][] = '';
                $allowances['allowance_amounts'][] = 0;
                $allowances['allowance_types'][] = 'fixed';
                $allowances['allowance_percents'][] = '';
            }
            $payrolls[$transaction->expense_for]['allowances'] = $allowances;

            //get deductions of employee
            $deductions = !empty($transaction->essentials_deductions) ? json_decode($transaction->essentials_deductions, true) : [];

            if (empty($deductions['deduction_names']) && empty($deductions['deduction_amounts'])) {
                $deductions['deduction_names'][] = '';
                $deductions['deduction_amounts'][] = 0;
                $deductions['deduction_types'][] = 'fixed';
                $deductions['deduction_percents'][] = '';
            }

            $payrolls[$transaction->expense_for]['deductions'] = $deductions;
        }

        $action = 'edit';

        return view('essentials::payroll.create')
            ->with(compact('month_name', 'transaction_date', 'year', 'payrolls', 'payroll_group', 'action', 'location'));
    }

    public function getUpdatePayrollGroup(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');


        try {
            $transaction_date = $request->input('transaction_date');
            $payrolls = $request->input('payrolls');
            $notify_employee = !empty($request->input('notify_employee')) ? 1 : 0;

            $payroll_group_id = $request->input('payroll_group_id');
            $pg_input['name'] = $request->input('payroll_group_name');
            $pg_input['status'] = $request->input('payroll_group_status');
            $pg_input['gross_total'] = $this->transactionUtil->num_uf($request->input('total_gross_amount'));

            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $user_businesses_ids = Business::pluck('id')->unique()->toArray();

            if (!$is_admin) {
                $userProjects = [];
                $userBusinesses = [];
                $roles = auth()->user()->roles;
                foreach ($roles as $role) {

                    $accessRole = AccessRole::where('role_id', $role->id)->first();

                    if ($accessRole) {
                        $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                        $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                    }
                }
                $user_businesses_ids = array_unique($userBusinesses);
            }

            DB::beginTransaction();
            $payroll_group = PayrollGroup::whereIn('business_id', $user_businesses_ids)
                ->findOrFail($payroll_group_id);

            $payroll_group->update($pg_input);

            foreach ($payrolls as $key => $payroll) {
                $transaction_id = $payroll['transaction_id'];

                $payroll['total_before_tax'] = $payroll['final_total'];
                $payroll['essentials_amount_per_unit_duration'] = $this->moduleUtil->num_uf($payroll['essentials_amount_per_unit_duration']);

                $allowances_and_deductions = $this->getAllowanceAndDeductionJson($payroll);
                $payroll['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'];
                $payroll['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'];

                unset($payroll['allowance_names'], $payroll['allowance_types'], $payroll['allowance_percent'], $payroll['allowance_amounts'], $payroll['deduction_names'], $payroll['deduction_types'], $payroll['deduction_percent'], $payroll['deduction_amounts'], $payroll['total'], $payroll['transaction_id']);

                $payroll_trans = Transaction::where('business_id', $business_id)
                    ->where('type', 'payroll')
                    ->find($transaction_id);

                if (!empty($payroll_trans)) {
                    $payroll_trans->update($payroll);

                    if ($notify_employee && $payroll_group->status == 'final') {
                        $payroll_trans->action = 'updated';
                        $payroll_trans->transaction_for->notify(new PayrollNotification($payroll_trans));
                    }
                }
            }
            DB::commit();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index'])->with('status', $output);
    }

    public function addPayment($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $user_businesses_ids = Business::pluck('id')->unique()->toArray();

        if (!$is_admin) {
            $userProjects = [];
            $userBusinesses = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                    $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                }
            }
            $user_businesses_ids = array_unique($userBusinesses);
        }
        $payroll_group = PayrollGroup::whereIn('business_id', $user_businesses_ids)
            ->with(['payrollGroupTransactions', 'payrollGroupTransactions.transaction_for', 'businessLocation', 'business'])
            ->findOrFail($id);

        $payrolls = [];
        $month_name = null;
        $year = null;
        foreach ($payroll_group->payrollGroupTransactions as $transaction) {

            //payroll info
            if (empty($month_name) && empty($year)) {
                $transaction_date = \Carbon::parse($transaction->transaction_date);
                $month_name = $transaction_date->format('F');
                $year = $transaction_date->format('Y');
            }

            //transaction info
            $payrolls[$transaction->expense_for]['transaction_id'] = $transaction->id;
            $payrolls[$transaction->expense_for]['final_total'] = $transaction->final_total;
            $payrolls[$transaction->expense_for]['payment_status'] = $transaction->payment_status;
            $payrolls[$transaction->expense_for]['paid_on'] = \Carbon::now();

            //get employee info
            $payrolls[$transaction->expense_for]['employee'] = $transaction->transaction_for->user_full_name;
            $payrolls[$transaction->expense_for]['employee_id'] = $transaction->transaction_for->id;
            $payrolls[$transaction->expense_for]['bank_details'] = json_decode($transaction->transaction_for->bank_details, true);
        }

        $payment_types = $this->transactionUtil->payment_types();
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);

        return view('essentials::payroll.pay_payroll_group')
            ->with(compact('payroll_group', 'month_name', 'year', 'payrolls', 'payment_types', 'accounts'));
    }

    public function postAddPayment(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        try {
            $payments = $request->input('payments');
            $payroll_group_id = $request->input('payroll_group_id');

            $user_businesses_ids = Business::pluck('id')->unique()->toArray();

            if (!$is_admin) {
                $userProjects = [];
                $userBusinesses = [];
                $roles = auth()->user()->roles;
                foreach ($roles as $role) {

                    $accessRole = AccessRole::where('role_id', $role->id)->first();

                    if ($accessRole) {
                        $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

                        $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
                    }
                }
                $user_businesses_ids = array_unique($userBusinesses);
            }

            foreach ($payments as $employee_id => $payment) {
                $transaction = Transaction::whereIn('business_id', $user_businesses_ids)->findOrFail($payment['transaction_id']);
                $transaction_before = $transaction->replicate();
                if ($transaction->payment_status != 'paid' && !empty($payment['final_total']) && !empty($payment['method'])) {
                    $input['method'] = $payment['method'];
                    $input['card_number'] = $payment['card_number'];
                    $input['card_holder_name'] = $payment['card_holder_name'];
                    $input['card_transaction_number'] = $payment['card_transaction_number'];
                    $input['card_type'] = $payment['card_type'];
                    $input['card_month'] = $payment['card_month'];
                    $input['card_year'] = $payment['card_year'];
                    $input['card_security'] = $payment['card_security'];
                    $input['cheque_number'] = $payment['cheque_number'];
                    $input['bank_account_number'] = $payment['bank_account_number'];
                    $input['business_id'] = $business_id;
                    $input['paid_on'] = $this->transactionUtil->uf_date($payment['paid_on'], true);
                    $input['transaction_id'] = $payment['transaction_id'];
                    $input['amount'] = $this->transactionUtil->num_uf($payment['final_total']);
                    $input['created_by'] = auth()->user()->id;

                    if ($input['method'] == 'custom_pay_1') {
                        $input['transaction_no'] = $payment['transaction_no_1'];
                    } elseif ($input['method'] == 'custom_pay_2') {
                        $input['transaction_no'] = $payment['transaction_no_2'];
                    } elseif ($input['method'] == 'custom_pay_3') {
                        $input['transaction_no'] = $payment['transaction_no_3'];
                    }

                    if (!empty($payment['account_id']) && $input['method'] != 'advance') {
                        $input['account_id'] = $payment['account_id'];
                    }

                    DB::beginTransaction();
                    $ref_count = $this->transactionUtil->setAndGetReferenceCount('purchase_payment');
                    // Generate reference number
                    $input['payment_ref_no'] = $this->transactionUtil->generateReferenceNumber('purchase_payment', $ref_count);

                    $tp = TransactionPayment::create($input);
                    $input['transaction_type'] = $transaction->type;
                    event(new TransactionPaymentAdded($tp, $input));

                    //update payment status
                    $payment_status = $this->transactionUtil->updatePaymentStatus($input['transaction_id']);
                    $transaction->payment_status = $payment_status;
                    $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);
                    DB::commit();

                    //unset transaction type after insert data
                    unset($input['transaction_type']);
                }
            }

            $this->_updatePayrollGroupPaymentStatus($payroll_group_id, $business_id);

            $output = [
                'success' => true,
                'msg' => __('purchase.payment_added_success'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index'])->with('status', $output);
    }

    protected function _updatePayrollGroupPaymentStatus($payroll_group_id, $business_id)
    {
        $payroll_group = PayrollGroup::with(['payrollGroupTransactions'])
            ->findOrFail($payroll_group_id);

        $total_transaction = count($payroll_group->payrollGroupTransactions);
        $total_paid = $payroll_group->payrollGroupTransactions->where('payment_status', 'paid')->count();
        $total_due = $payroll_group->payrollGroupTransactions->where('payment_status', '!=', 'paid')->count();

        if ($total_transaction == $total_paid) {
            $payment_status = 'paid';
        } elseif ($total_transaction == $total_due) {
            $payment_status = 'due';
        } else {
            $payment_status = 'partial';
        }

        $payroll_group->payment_status = $payment_status;
        $payroll_group->save();
    }

    /**
     * List payrolls & pay components
     * of an user
     *
     * @return Response
     */
    public function getMyPayrolls(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');


        if ($request->ajax()) {
            $payrolls = $this->essentialsUtil->getPayrollQuery($business_id);

            $payrolls->where('transactions.expense_for', auth()->user()->id);

            return Datatables::of($payrolls)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<a href="#" data-href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'show'], [$row->id]) . '" data-container=".view_modal" class="btn-modal btn-info btn btn-sm">
                            <i class="fa fa-eye" aria-hidden="true"></i> '
                            . __('messages.view') .
                            '</a>';

                        return $html;
                    }
                )
                ->addColumn('transaction_date', function ($row) {
                    $transaction_date = \Carbon::parse($row->transaction_date);

                    return $transaction_date->format('F Y');
                })
                ->editColumn('final_total', '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>')
                ->editColumn(
                    'payment_status',
                    '<span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
                        </span>'
                )
                ->removeColumn('id')
                ->rawColumns(['action', 'final_total', 'payment_status'])
                ->make(true);
        }

        $pay_components = EssentialsAllowanceAndDeduction::join('essentials_user_allowance_and_deductions as EUAD', 'essentials_allowances_and_deductions.id', '=', 'EUAD.allowance_deduction_id')
            ->where('essentials_allowances_and_deductions.business_id', $business_id)
            ->where('EUAD.user_id', auth()->user()->id)
            ->get();

        return view('essentials::payroll.partials.user_payrolls')
            ->with(compact('pay_components'));
    }

    public function getEmployeesBasedOnLocation(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');


        try {
            $location_id = $request->get('location_id');

            $employees = $this->__getEmployeesByLocation($business_id, $location_id);

            //dynamically generate dropdown
            $employees_html = view('essentials::payroll.partials.employee_dropdown')
                ->with(compact('employees'))
                ->render();
            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
                'employees_html' => $employees_html,
            ];
        } catch (Exception $e) {
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    private function __getEmployeesByLocation($business_id, $location_id = null)
    {
        $query = User::where('business_id', $business_id);

        if (!empty($location_id)) {
            $query->where('location_id', $location_id);
        } else {
            $query->whereNull('location_id');
        }

        $users = $query->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
        ' - ',COALESCE(id_proof_number,'')) as  full_name"))->get();

        $employees = $users->pluck('full_name', 'id')->toArray();

        return $employees;
    }



    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return Response
    //  */
    // public function index()
    // {
    //     $business_id = request()->session()->get('user.business_id');
    //     $can_view_all_payroll = auth()->user()->can('essentials.view_all_payroll');

    //     $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
    //     $user_businesses_ids = Business::pluck('id')->unique()->toArray();

    //     if (!$is_admin) {
    //         $userProjects = [];
    //         $userBusinesses = [];
    //         $roles = auth()->user()->roles;
    //         foreach ($roles as $role) {

    //             $accessRole = AccessRole::where('role_id', $role->id)->first();

    //             if ($accessRole) {
    //                 $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

    //                 $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
    //             }
    //         }
    //         $user_businesses_ids = array_unique($userBusinesses);
    //     }

    //     if (request()->ajax()) {
    //         $payrolls = $this->essentialsUtil->getPayrollQuery($user_businesses_ids);

    //         if ($can_view_all_payroll) {
    //             if (!empty(request()->input('user_id'))) {
    //                 $payrolls->where('transactions.expense_for', request()->input('user_id'));
    //             }

    //             if (!empty(request()->input('designation_id'))) {
    //                 $payrolls->where('dsgn.id', request()->input('designation_id'));
    //             }

    //             if (!empty(request()->input('department_id'))) {
    //                 $payrolls->where('dept.id', request()->input('department_id'));
    //             }
    //         }

    //         if (!$can_view_all_payroll) {
    //             $payrolls->where('transactions.expense_for', auth()->user()->id);
    //         }

    //         if (!empty(request()->input('location_id'))) {
    //             $payrolls->where('u.location_id', request()->input('location_id'));
    //         }

    //         $permitted_locations = auth()->user()->permitted_locations();
    //         if ($permitted_locations != 'all') {
    //             $payrolls->where(function ($q) use ($permitted_locations) {
    //                 $q->whereIn('epg.location_id', $permitted_locations)
    //                     ->orWhereNull('epg.location_id');
    //             });
    //         }

    //         if (!empty(request()->month_year)) {
    //             $month_year_arr = explode('/', request()->month_year);
    //             if (count($month_year_arr) == 2) {
    //                 $month = $month_year_arr[0];
    //                 $year = $month_year_arr[1];

    //                 $payrolls->whereDate('transaction_date', $year . '-' . $month . '-01');
    //             }
    //         }

    //         return Datatables::of($payrolls)
    //             ->addColumn(
    //                 'action',
    //                 function ($row) use ($is_admin, $can_view_all_payroll) {
    //                     if ($is_admin  || $can_view_all_payroll) {
    //                         $html = '<div class="btn-group">
    //                                 <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
    //                                     data-toggle="dropdown" aria-expanded="false">' .
    //                             __('messages.actions') .
    //                             '<span class="caret"></span><span class="sr-only">Toggle Dropdown
    //                                     </span>
    //                                 </button>
    //                                 <ul class="dropdown-menu dropdown-menu-right" role="menu">';

    //                         $html .= '<li><a href="#" data-href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'show'], [$row->id]) . '" data-container=".view_modal" class="btn-modal"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';
    //                     }
    //                     // $html .= '<li><a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->id]) . '" class="view_payment_modal"><i class="fa fa-money"></i> ' . __("purchase.view_payments") . '</a></li>';

    //                     if (empty($row->payroll_group_id) && $row->payment_status != 'paid' && auth()->user()->can('essentials.create_payroll')) {
    //                         $html .= '<li><a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal"><i class="fa fa-money"></i> ' . __('purchase.add_payment') . '</a></li>';
    //                     }

    //                     $html .= '</ul></div>';

    //                     return $html;
    //                 }
    //             )
    //             ->addColumn('transaction_date', function ($row) {
    //                 $transaction_date = \Carbon::parse($row->transaction_date);

    //                 return $transaction_date->format('F Y');
    //             })
    //             ->editColumn('final_total', '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>')
    //             ->filterColumn('user', function ($query, $keyword) {
    //                 $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
    //             })
    //             ->editColumn(
    //                 'payment_status',
    //                 '<a href="{{ action([\App\Http\Controllers\TransactionPaymentController::class, \'show\'], [$id])}}" class="view_payment_modal payment-status-label no-print" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
    //                     </span></a>
    //                     <span class="print_section">{{__(\'lang_v1.\' . $payment_status)}}</span>
    //                     '
    //             )
    //             ->removeColumn('id')
    //             ->rawColumns(['action', 'final_total', 'payment_status'])
    //             ->make(true);
    //     }

    //     $employees = [];
    //     if (auth()->user()->can('essentials.create_payroll')) {
    //         $employees = $this->__getEmployeesByLocation($business_id);
    //     }
    //     $departments = Category::forDropdown($business_id, 'hrm_department');
    //     $designations = Category::forDropdown($business_id, 'hrm_designation');
    //     $locations = BusinessLocation::forDropdown($business_id, true, false, true, true);

    //     return view('essentials::payroll.index')->with(compact('employees', 'departments', 'designations', 'locations'));
    // }


    // /**
    //  * Show the form for creating a new resource.
    //  *
    //  * @return Response
    //  */
    // public function create()
    // {
    //     $business_id = request()->session()->get('user.business_id');

    //     $employee_ids = request()->input('employee_ids');
    //     $month_year_arr = explode('/', request()->input('month_year'));
    //     $location_id = request()->get('primary_work_location');
    //     $month = $month_year_arr[0];
    //     $year = $month_year_arr[1];

    //     $transaction_date = $year . '-' . $month . '-01';

    //     //check if payrolls exists for the month year
    //     $payrolls = Transaction::where('business_id', $business_id)
    //         ->where('type', 'payroll')
    //         ->whereIn('expense_for', $employee_ids)
    //         ->whereDate('transaction_date', $transaction_date)
    //         ->get();

    //     $add_payroll_for = array_diff($employee_ids, $payrolls->pluck('expense_for')->toArray());

    //     if (!empty($add_payroll_for)) {
    //         $location = BusinessLocation::where('business_id', $business_id)
    //             ->find($location_id);

    //         //initialize required data
    //         $start_date = $transaction_date;
    //         $end_date = \Carbon::parse($start_date)->lastOfMonth();
    //         $month_name = $end_date->format('F');

    //         $employees = User::where('business_id', $business_id)
    //             ->find($add_payroll_for);

    //         $payrolls = [];
    //         foreach ($employees as $employee) {

    //             //get employee info
    //             $payrolls[$employee->id]['name'] = $employee->user_full_name;
    //             $payrolls[$employee->id]['essentials_salary'] = $employee->essentials_salary;
    //             $payrolls[$employee->id]['essentials_pay_period'] = $employee->essentials_pay_period;
    //             $payrolls[$employee->id]['total_leaves'] = $this->essentialsUtil->getTotalLeavesForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date->format('Y-m-d'));
    //             $payrolls[$employee->id]['total_days_worked'] = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date);

    //             //get total work duration of employee(attendance)
    //             $payrolls[$employee->id]['total_work_duration'] = $this->essentialsUtil->getTotalWorkDuration('hour', $employee->id, $business_id, $start_date, $end_date->format('Y-m-d'));

    //             //get total earned commission for employee
    //             $business_details = $this->businessUtil->getDetails($business_id);
    //             $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

    //             $commsn_calculation_type = empty($pos_settings['cmmsn_calculation_type']) || $pos_settings['cmmsn_calculation_type'] == 'invoice_value' ? 'invoice_value' : $pos_settings['cmmsn_calculation_type'];

    //             $total_commission = 0;
    //             if ($commsn_calculation_type == 'payment_received') {
    //                 $payment_details = $this->transactionUtil->getTotalPaymentWithCommission($business_id, $start_date, $end_date, null, $employee->id);
    //                 //Get Commision
    //                 $total_commission = $employee->cmmsn_percent * $payment_details['total_payment_with_commission'] / 100;
    //             } else {
    //                 $sell_details = $this->transactionUtil->getTotalSellCommission($business_id, $start_date, $end_date, null, $employee->id);
    //                 $total_commission = $employee->cmmsn_percent * $sell_details['total_sales_with_commission'] / 100;
    //             }

    //             if ($total_commission > 0) {
    //                 $payrolls[$employee->id]['allowances']['allowance_names'][] = __('essentials::lang.sale_commission');
    //                 $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $total_commission;
    //                 $payrolls[$employee->id]['allowances']['allowance_types'][] = 'fixed';
    //                 $payrolls[$employee->id]['allowances']['allowance_percents'][] = 0;
    //             }
    //             $settings = $this->essentialsUtil->getEssentialsSettings();
    //             //get total sales added by the employee
    //             $sale_totals = $this->transactionUtil->getUserTotalSales($business_id, $employee->id, $start_date, $end_date);

    //             $total_sales = !empty($settings['calculate_sales_target_commission_without_tax']) && $settings['calculate_sales_target_commission_without_tax'] == 1 ? $sale_totals['total_sales_without_tax'] : $sale_totals['total_sales'];

    //             //get sales target if exists
    //             $sales_target = EssentialsUserSalesTarget::where('user_id', $employee->id)
    //                 ->where('target_start', '<=', $total_sales)
    //                 ->where('target_end', '>=', $total_sales)
    //                 ->first();

    //             $total_sales_target_commission_percent = !empty($sales_target) ? $sales_target->commission_percent : 0;

    //             $total_sales_target_commission = $this->transactionUtil->calc_percentage($total_sales, $total_sales_target_commission_percent);

    //             if ($total_sales_target_commission > 0) {
    //                 $payrolls[$employee->id]['allowances']['allowance_names'][] = __('essentials::lang.sales_target_commission');
    //                 $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $total_sales_target_commission;
    //                 $payrolls[$employee->id]['allowances']['allowance_types'][] = 'fixed';
    //                 $payrolls[$employee->id]['allowances']['allowance_percents'][] = 0;
    //             }

    //             //get earnings & deductions of employee
    //             $allowances_and_deductions = $this->essentialsUtil->getEmployeeAllowancesAndDeductions($business_id, $employee->id, $start_date, $end_date);
    //             foreach ($allowances_and_deductions as $ad) {
    //                 if ($ad->type == 'allowance') {
    //                     $payrolls[$employee->id]['allowances']['allowance_names'][] = $ad->description;
    //                     $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $ad->amount_type == 'fixed' ? $ad->amount : 0;
    //                     $payrolls[$employee->id]['allowances']['allowance_types'][] = $ad->amount_type;
    //                     $payrolls[$employee->id]['allowances']['allowance_percents'][] = $ad->amount_type == 'percent' ? $ad->amount : 0;
    //                 } else {
    //                     $payrolls[$employee->id]['deductions']['deduction_names'][] = $ad->description;
    //                     $payrolls[$employee->id]['deductions']['deduction_amounts'][] = $ad->amount_type == 'fixed' ? $ad->amount : 0;
    //                     $payrolls[$employee->id]['deductions']['deduction_types'][] = $ad->amount_type;
    //                     $payrolls[$employee->id]['deductions']['deduction_percents'][] = $ad->amount_type == 'percent' ? $ad->amount : 0;
    //                 }
    //             }
    //         }

    //         $action = 'create';

    //         return view('essentials::payroll.create')
    //             ->with(compact('month_name', 'transaction_date', 'year', 'payrolls', 'action', 'location'));
    //     } else {
    //         return redirect()->action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index'])
    //             ->with(
    //                 'status',
    //                 [
    //                     'success' => true,
    //                     'msg' => __('essentials::lang.payroll_already_added_for_given_user'),
    //                 ]
    //             );
    //     }
    // }


    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  Request  $request
    //  * @return Response
    //  */
    // public function store(Request $request)
    // {
    //     // return  $request->all();
    //     $business_id = request()->session()->get('user.business_id');
    //     try {
    //         $transaction_date = $request->input('transaction_date');
    //         $payrolls = $request->input('payrolls');
    //         $notify_employee = !empty($request->input('notify_employee')) ? 1 : 0;
    //         $payroll_group['business_id'] = $business_id;
    //         $payroll_group['name'] = $request->input('payroll_group_name');
    //         $payroll_group['status'] = $request->input('payroll_group_status');
    //         $payroll_group['gross_total'] = $this->transactionUtil->num_uf($request->input('total_gross_amount'));
    //         $payroll_group['location_id'] = $request->input('location_id');
    //         $payroll_group['created_by'] = auth()->user()->id;

    //         DB::beginTransaction();

    //         $payroll_group = PayrollGroup::create($payroll_group);
    //         $transaction_ids = [];
    //         foreach ($payrolls as $key => $payroll) {
    //             $payroll['transaction_date'] = $transaction_date;
    //             $payroll['business_id'] = $business_id;
    //             $payroll['created_by'] = auth()->user()->id;
    //             $payroll['type'] = 'payroll';
    //             $payroll['payment_status'] = 'due';
    //             $payroll['status'] = 'final';
    //             $payroll['total_before_tax'] = $payroll['final_total'];
    //             $payroll['essentials_amount_per_unit_duration'] = $this->moduleUtil->num_uf($payroll['essentials_amount_per_unit_duration']);

    //             $allowances_and_deductions = $this->getAllowanceAndDeductionJson($payroll);
    //             $payroll['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'];
    //             $payroll['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'];

    //             //Update reference count
    //             $ref_count = $this->moduleUtil->setAndGetReferenceCount('payroll');

    //             //Generate reference number
    //             if (empty($payroll['ref_no'])) {
    //                 $settings = request()->session()->get('business.essentials_settings');
    //                 $settings = !empty($settings) ? json_decode($settings, true) : [];
    //                 $prefix = !empty($settings['payroll_ref_no_prefix']) ? $settings['payroll_ref_no_prefix'] : '';
    //                 $payroll['ref_no'] = $this->moduleUtil->generateReferenceNumber('payroll', $ref_count, null, $prefix);
    //             }
    //             unset($payroll['allowance_names'], $payroll['allowance_types'], $payroll['allowance_percent'], $payroll['allowance_amounts'], $payroll['deduction_names'], $payroll['deduction_types'], $payroll['deduction_percent'], $payroll['deduction_amounts'], $payroll['total']);

    //             $transaction = Transaction::create($payroll);
    //             $transaction_ids[] = $transaction->id;

    //             if ($notify_employee && $payroll_group->status == 'final') {
    //                 $transaction->action = 'created';
    //                 $transaction->transaction_for->notify(new PayrollNotification($transaction));
    //             }
    //         }

    //         $payroll_group->payrollGroupTransactions()->sync($transaction_ids);

    //         DB::commit();

    //         $output = [
    //             'success' => true,
    //             'msg' => __('lang_v1.added_success'),
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

    //         $output = [
    //             'success' => false,
    //             'msg' => __('messages.something_went_wrong'),
    //         ];
    //     }

    //     return redirect()->action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index'])->with('status', $output);
    // }


    // private function getAllowanceAndDeductionJson($payroll)
    // {
    //     $allowance_names = $payroll['allowance_names'];
    //     $allowance_types = $payroll['allowance_types'];
    //     $allowance_percents = $payroll['allowance_percent'];
    //     $allowance_names_array = [];
    //     $allowance_percent_array = [];
    //     $allowance_amounts = [];

    //     foreach ($payroll['allowance_amounts'] as $key => $value) {
    //         if (!empty($allowance_names[$key])) {
    //             $allowance_amounts[] = $this->moduleUtil->num_uf($value);
    //             $allowance_names_array[] = $allowance_names[$key];
    //             $allowance_percent_array[] = !empty($allowance_percents[$key]) ? $this->moduleUtil->num_uf($allowance_percents[$key]) : 0;
    //         }
    //     }

    //     $deduction_names = $payroll['deduction_names'];
    //     $deduction_types = $payroll['deduction_types'];
    //     $deduction_percents = $payroll['deduction_percent'];
    //     $deduction_names_array = [];
    //     $deduction_percents_array = [];
    //     $deduction_amounts = [];
    //     foreach ($payroll['deduction_amounts'] as $key => $value) {
    //         if (!empty($deduction_names[$key])) {
    //             $deduction_names_array[] = $deduction_names[$key];
    //             $deduction_amounts[] = $this->moduleUtil->num_uf($value);
    //             $deduction_percents_array[] = !empty($deduction_percents[$key]) ? $this->moduleUtil->num_uf($deduction_percents[$key]) : 0;
    //         }
    //     }

    //     $output['essentials_allowances'] = json_encode([
    //         'allowance_names' => $allowance_names_array,
    //         'allowance_amounts' => $allowance_amounts,
    //         'allowance_types' => $allowance_types,
    //         'allowance_percents' => $allowance_percent_array,
    //     ]);
    //     $output['essentials_deductions'] = json_encode([
    //         'deduction_names' => $deduction_names_array,
    //         'deduction_amounts' => $deduction_amounts,
    //         'deduction_types' => $deduction_types,
    //         'deduction_percents' => $deduction_percents_array,
    //     ]);

    //     return $output;
    // }


    // /**
    //  * Show the specified resource.
    //  *
    //  * @return Response
    //  */
    // public function show($id)
    // {
    //     // $business_id = request()->session()->get('user.business_id');
    //     $query = Transaction::with(['transaction_for', 'payment_lines']);
    //     $business_id = $query->first()->business_id;
    //     // $query = Transaction::where('business_id', $business_id)
    //     //                 ->with(['transaction_for', 'payment_lines']);

    //     if (!auth()->user()->can('essentials.view_all_payroll')) {
    //         $query->where('expense_for', auth()->user()->id);
    //     }
    //     $payroll = $query->findOrFail($id);

    //     $transaction_date = \Carbon::parse($payroll->transaction_date);

    //     $department = Category::where('category_type', 'hrm_department')
    //         ->find($payroll->transaction_for->essentials_department_id);

    //     $designation = Category::where('category_type', 'hrm_designation')
    //         ->find($payroll->transaction_for->essentials_designation_id);

    //     $location = BusinessLocation::where('business_id', $business_id)
    //         ->find($payroll->transaction_for->location_id);

    //     $month_name = $transaction_date->format('F');
    //     $year = $transaction_date->format('Y');
    //     $allowances = !empty($payroll->essentials_allowances) ? json_decode($payroll->essentials_allowances, true) : [];
    //     $deductions = !empty($payroll->essentials_deductions) ? json_decode($payroll->essentials_deductions, true) : [];
    //     $bank_details = json_decode($payroll->transaction_for->bank_details, true);
    //     $payment_types = $this->moduleUtil->payment_types();
    //     $final_total_in_words = $this->commonUtil->numToIndianFormat($payroll->final_total);

    //     $start_of_month = \Carbon::parse($payroll->transaction_date);
    //     $end_of_month = \Carbon::parse($payroll->transaction_date)->endOfMonth();

    //     $leaves = EssentialsLeave::where('business_id', $business_id)
    //         ->where('user_id', $payroll->transaction_for->id)
    //         ->whereDate('start_date', '>=', $start_of_month)
    //         ->whereDate('end_date', '<=', $end_of_month)
    //         ->get();

    //     $total_leaves = 0;
    //     $days_in_a_month = \Carbon::parse($start_of_month)->daysInMonth;
    //     foreach ($leaves as $key => $leave) {
    //         $start_date = \Carbon::parse($leave->start_date);
    //         $end_date = \Carbon::parse($leave->end_date);

    //         $diff = $start_date->diffInDays($end_date);
    //         $diff += 1;
    //         $total_leaves += $diff;
    //     }

    //     $total_days_present = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee(
    //         $business_id,
    //         $payroll->transaction_for->id,
    //         $start_of_month->format('Y-m-d'),
    //         $end_of_month->format('Y-m-d')
    //     );

    //     $total_work_duration = $this->essentialsUtil->getTotalWorkDuration(
    //         'hour',
    //         $payroll->transaction_for->id,
    //         $business_id,
    //         $start_of_month->format('Y-m-d'),
    //         $end_of_month->format('Y-m-d')
    //     );

    //     return view('essentials::payroll.show')
    //         ->with(compact(
    //             'payroll',
    //             'month_name',
    //             'allowances',
    //             'deductions',
    //             'year',
    //             'payment_types',
    //             'bank_details',
    //             'designation',
    //             'department',
    //             'final_total_in_words',
    //             'total_leaves',
    //             'days_in_a_month',
    //             'total_work_duration',
    //             'location',
    //             'total_days_present'
    //         ));
    // }


    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @return Response
    //  */
    // public function edit($id)
    // {
    //     $business_id = request()->session()->get('user.business_id');



    //     $payroll = Transaction::where('business_id', $business_id)
    //         ->with(['transaction_for'])
    //         ->where('type', 'payroll')
    //         ->findOrFail($id);

    //     $transaction_date = \Carbon::parse($payroll->transaction_date);
    //     $month_name = $transaction_date->format('F');
    //     $year = $transaction_date->format('Y');
    //     $allowances = !empty($payroll->essentials_allowances) ? json_decode($payroll->essentials_allowances, true) : [];
    //     $deductions = !empty($payroll->essentials_deductions) ? json_decode($payroll->essentials_deductions, true) : [];

    //     return view('essentials::payroll.edit')->with(compact('payroll', 'month_name', 'allowances', 'deductions', 'year'));
    // }


    // public function addPayment($id)
    // {
    //     $business_id = request()->session()->get('user.business_id');
    //     $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
    //     $user_businesses_ids = Business::pluck('id')->unique()->toArray();

    //     if (!$is_admin) {
    //         $userProjects = [];
    //         $userBusinesses = [];
    //         $roles = auth()->user()->roles;
    //         foreach ($roles as $role) {

    //             $accessRole = AccessRole::where('role_id', $role->id)->first();

    //             if ($accessRole) {
    //                 $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

    //                 $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
    //             }
    //         }
    //         $user_businesses_ids = array_unique($userBusinesses);
    //     }
    //     $payroll_group = PayrollGroup::whereIn('business_id', $user_businesses_ids)
    //         ->with(['payrollGroupTransactions', 'payrollGroupTransactions.transaction_for', 'businessLocation', 'business'])
    //         ->findOrFail($id);

    //     $payrolls = [];
    //     $month_name = null;
    //     $year = null;
    //     foreach ($payroll_group->payrollGroupTransactions as $transaction) {

    //         //payroll info
    //         if (empty($month_name) && empty($year)) {
    //             $transaction_date = \Carbon::parse($transaction->transaction_date);
    //             $month_name = $transaction_date->format('F');
    //             $year = $transaction_date->format('Y');
    //         }

    //         //transaction info
    //         $payrolls[$transaction->expense_for]['transaction_id'] = $transaction->id;
    //         $payrolls[$transaction->expense_for]['final_total'] = $transaction->final_total;
    //         $payrolls[$transaction->expense_for]['payment_status'] = $transaction->payment_status;
    //         $payrolls[$transaction->expense_for]['paid_on'] = \Carbon::now();

    //         //get employee info
    //         $payrolls[$transaction->expense_for]['employee'] = $transaction->transaction_for->user_full_name;
    //         $payrolls[$transaction->expense_for]['employee_id'] = $transaction->transaction_for->id;
    //         $payrolls[$transaction->expense_for]['bank_details'] = json_decode($transaction->transaction_for->bank_details, true);
    //     }

    //     $payment_types = $this->transactionUtil->payment_types();
    //     $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);

    //     return view('essentials::payroll.pay_payroll_group')
    //         ->with(compact('payroll_group', 'month_name', 'year', 'payrolls', 'payment_types', 'accounts'));
    // }

    public function new_arrival_for_workers(Request $request)
    {
        $view = 'essentials::payroll.travelers.index';
        return $this->newArrivalUtil->new_arrival_for_workers($request, $view);
    }

    public function housed_workers_index(Request $request)
    {
        $view = 'essentials::payroll.travelers.partials.housed_workers';
        return $this->newArrivalUtil->housed_workers_index($request, $view);
    }

    public function medicalExamination()
    {
        $view = 'essentials::payroll.travelers.medicalExamination';
        return $this->newArrivalUtil->medicalExamination($view);
    }
    public function SIMCard()
    {
        $view = 'essentials::payroll.travelers.SIMCard';
        return $this->newArrivalUtil->SIMCard($view);
    }
    public function workCardIssuing()
    {
        $view = 'essentials::payroll.travelers.workCardIssuing';
        return $this->newArrivalUtil->workCardIssuing($view);
    }
    public function medicalInsurance()
    {
        $view = 'essentials::payroll.travelers.medicalInsurance';
        return $this->newArrivalUtil->medicalInsurance($view);
    }
    public function bankAccounts()
    {
        $view = 'essentials::payroll.travelers.bankAccounts';
        return $this->newArrivalUtil->bankAccounts($view);
    }
    public function QiwaContracts()
    {
        $view = 'essentials::payroll.travelers.QiwaContracts';
        return $this->newArrivalUtil->QiwaContracts($view);
    }
    public function residencyPrint()
    {
        $view = 'essentials::payroll.travelers.residencyPrint';
        return $this->newArrivalUtil->residencyPrint($view);
    }
    public function residencyDelivery()
    {
        $view = 'essentials::payroll.travelers.residencyDelivery';
        return $this->newArrivalUtil->residencyDelivery($view);
    }
    public function advanceSalaryRequest()
    {
        $view = 'essentials::payroll.travelers.advanceSalaryRequest';
        return $this->newArrivalUtil->advanceSalaryRequest($view);
    }
}
