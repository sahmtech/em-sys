<?php

namespace Modules\Accounting\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Business;
use App\TimesheetUser;
use App\User;
use App\TimesheetGroup;
use App\Utils\ModuleUtil;
use Modules\Sales\Entities\SalesProject;
use App\Category;
use Carbon\Carbon;
use DB;
use App\Company;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Session;

class TimeSheetController extends Controller
{
    protected $moduleUtil;



    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {

        $currentUser = auth()->user();
        $is_manager = $currentUser->user_type == 'manager';
        $is_admin = $currentUser->hasRole('Admin#1');

        $userIds = User::where('user_type', '!=', 'admin')->pluck('id')->toArray();

        if (!$is_admin) {

            $userIds = $this->moduleUtil->applyAccessRole();
        }


        // if ($is_admin) {
        $projects = SalesProject::pluck('name', 'id')->toArray();
        // } else {
        //     $projects = FollowupUserAccessProject::where('user_id', $currentUser->id)
        //         ->pluck('sales_project_id')
        //         ->toArray();
        // }


        $worker_ids = User::whereIn('id', $userIds)
            ->whereIn('assigned_to', $projects)
            ->pluck('id')
            ->toArray();

        $userIds = array_intersect($userIds, $worker_ids);

        // Get business ID from session
        $business_id = request()->session()->get('user.business_id');

        // Get workers with concatenated names
        $workers = User::where('user_type', 'worker')
            ->whereIn('users.assigned_to', $userIds)
            ->select(
                'users.id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''), ' ', COALESCE(users.last_name, '')) as worker")
            )
            ->pluck('worker', 'id')
            ->toArray();


        $departments = Category::forDropdown($business_id, 'hrm_department');
        $designations = Category::forDropdown($business_id, 'hrm_designation');


        return view('accounting::custom_views.agents.agent_time_sheet.index', compact('workers', 'departments', 'designations', 'projects'));
    }

    public function create()
    {
        $companies = Company::pluck('name', 'id');
        $project_id = request()->input('projects');
        $employee_ids = request()->input('employee_ids');
        $month_year = request()->input('month_year');
        $workers = User::with(['essentialsUserShifts.shift', 'transactions', 'userAllowancesAndDeductions.essentialsAllowanceAndDeduction'])->where('user_type', 'worker')
            ->whereIn('users.id',  $employee_ids)
            ->select(
                'users.*',
                'users.id as user_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),
                'users.id_proof_number as eqama_number',
                'users.essentials_pay_period',
                'users.essentials_salary as monthly_cost',
                'users.essentials_pay_period as wd',
            )->get();

        $businesses = Business::pluck('name', 'id',);
        $projects = SalesProject::pluck('name', 'id');
        $currentDateTime = Carbon::now('Asia/Riyadh');
        $month = $currentDateTime->month;
        $year = $currentDateTime->year;
        $start_of_month = $currentDateTime->copy()->startOfMonth();
        $end_of_month = $currentDateTime->copy()->endOfMonth();
        $payrolls = [];
        foreach ($workers as $worker) {
            $payrolls[] = [
                'id' => $worker->user_id,
                'name' => $worker->name ?? '',
                'nationality' => User::find($worker->id)->country?->nationality ?? '',
                'company' => $worker->company_id ? $companies[$worker->company_id] ?? '' : '',
                'residency' => $worker->eqama_number ?? '',
                'monthly_cost' => number_format($worker->calculateTotalSalary(), 0, '.', ''),
                'wd' => '30',
                'absence_day' => 0,
                'absence_amount' => '',
                'over_time_h' => 0,
                'over_time' => '',
                'other_deduction' => 0,
                'other_addition' => 0,
                'cost2' => '',
                'invoice_value' => '',
                'vat' => '',
                'total' => '',
                'sponser' => $worker->assigned_to ? $projects[$worker->assigned_to] ?? '' : '',
                'basic' => $worker->monthly_cost ? number_format($worker->monthly_cost, 0, '.', '') : '',
                'housing' => 0,
                'transport' => 0,
                'other_allowances' => 0,
                'total_salary' => '',
                'deductions' => '',
                'additions' => '',
                'final_salary' => '',
            ];
        }

        $date = (Carbon::createFromFormat('m/Y', request()->input('month_year')))->format('F Y');
        $group_name = __('essentials::lang.payroll_for_month', ['date' => $date]);
        $action = 'create';
        return view('accounting::custom_views.agents.agent_time_sheet.payroll_group')->with(compact('employee_ids', 'group_name', 'date', 'project_id', 'month_year', 'payrolls', 'action'));
    }

    public function agentTimeSheetGroups()
    {

        $company_id = Session::get('selectedCompanyId');
        error_log($company_id);
        $user = User::where('id', auth()->user()->id)->first();
        $payrolls = TimesheetGroup::where('timesheet_groups.is_approved', 1)->whereHas('timesheetUsers.user', function ($query) use ($company_id) {
            $query->where('company_id', $company_id)->where('is_approved', 0);
        })
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
                'timesheet_groups.is_approved',
                'timesheet_groups.approved_by',
                'timesheet_groups.is_approved_by_accounting',
                'timesheet_groups.accounting_approved_by',



            ]);

        $all_users = User::where('status', '!=', 'inactive')
            ->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(last_name,'')) as full_name"))
            ->get();
        $users = $all_users->pluck('full_name', 'id');
        $is_admin = $user->hasRole('Admin#1');
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
                if ($is_admin || $user->can('accounting.show_timesheet')) {
                    $html .= '<li><a href="' . route('accounting.agentTimeSheet.showTimeSheet', ['id' => $row->id]) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';
                }
                // if ($row->status == 'draft' && ($is_admin || $user->can('accounting.edit_timesheet'))) {
                //     $html .= '<li><a href="' . route('accounting.agentTimeSheet.editTimeSheet', ['id' => $row->id]) . '"><i class="fa fa-edit" aria-hidden="true"></i> ' . __('messages.edit') . '</a></li>';
                // }
                if ($row->is_approved_by_accounting == 0) {
                    $html .= '<li><a href="' . route('accounting.agentTimeSheet.approvedTimeSheetByAccounting', ['id' => $row->id]) . '"><i class="fa fa-check" aria-hidden="true"></i> ' . __('lang_v1.approve') . '</a></li>';
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
                if ($row->project_id) {
                    return $projects[$row->project_id];
                } else {
                    return '';
                }
            })
            ->editColumn('status', function ($row) {
                return __('lang_v1.' . $row->status);
            })
            ->editColumn('accounting_approved_by', function ($row) use ($users) {
                if ($row->accounting_approved_by) {
                    return $users[$row->accounting_approved_by];
                } else {
                    return '';
                }
            })
            ->rawColumns(['created_by', 'accounting_approved_by', 'action', 'total', 'status'])
            ->make(true);
    }
    public function agentTimeSheetUsers()
    {
        $timesheetUsers = TimesheetUser::join('users as u', 'u.id', '=', 'timesheet_users.user_id')
            ->join('timesheet_groups', 'timesheet_groups.id', '=', 'timesheet_users.timesheet_group_id')
            ->select([
                'u.first_name',
                'u.last_name',
                'u.id_proof_number',
                'timesheet_groups.timesheet_date',
                'timesheet_users.final_salary',
                'timesheet_groups.payment_status',
                'timesheet_groups.name',
                'timesheet_users.id'
            ]);

        return DataTables::of($timesheetUsers)
            ->addColumn('user', function ($row) {
                return $row->first_name . ' ' . $row->last_name;
            })
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

                    $html .= '<li><a href="#" data-href="' . route('agentTimeSheet.showPayroll', ['id' => $row->id]) . '" data-container=".view_modal" class="btn-modal"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';

                    // $html .= '<li><a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->id]) . '" class="view_payment_modal"><i class="fa fa-money"></i> ' . __("purchase.view_payments") . '</a></li>';

                    if (empty($row->payroll_group_id) && $row->payment_status != 'paid' && auth()->user()->can('essentials.create_payroll')) {
                        $html .= '<li><a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal"><i class="fa fa-money"></i> ' . __('purchase.add_payment') . '</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                }
            )
            ->editColumn('payment_status', function ($row) {
                return __('lang_v1.' . $row->payment_status);
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function dealTimeSheet($id)
    {
        try {
            $authUser = auth()->user();
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

            $companies_ids = Company::pluck('id')->toArray();
            if (!$is_admin) {

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


            $timesheetGroup = TimesheetGroup::findOrFail($id);

            $timesheetUsers = TimesheetUser::where('timesheet_group_id', $id)
                ->whereHas('user', function ($query) use ($companies_ids) {
                    $query->whereIn('company_id', $companies_ids);
                })
                ->get();

            foreach ($timesheetUsers as $timesheetUser) {
                $timesheetUser->update([
                    'is_approved' => 1,
                    'approved_by' => $authUser->id,
                ]);
            }

            $hasPendingApprovals = TimesheetUser::where('timesheet_group_id', $id)
                ->where('is_approved', 0)
                ->exists();

            if (!$hasPendingApprovals) {
                $timesheetGroup->update([
                    'is_approved' => 1,
                    'approved_by' => $authUser->id,
                ]);
            }

            return redirect()->route('hrm.agentTimeSheetIndex')->with('status', [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return redirect()->route('accounting.agentTimeSheetIndex')->with('status', [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }

    public function editTimeSheet($id)
    {

        $timesheetGroup = TimesheetGroup::findOrFail($id);
        $project_id = request()->input('projects');
        $timesheetUsers = TimesheetUser::where('timesheet_group_id', $id)->get();
        $employee_ids = $timesheetUsers->pluck('user_id')->toArray();
        $payrolls = [];
        foreach ($timesheetUsers as $user) {
            $user2 = User::where('id', $user->user_id)->first();
            $payrolls[] = [
                'id' => $user->user_id,
                'name' => $user2->first_name . ' '  . $user2->last_name,
                'nationality' => $user2->country->nationality ?? '',
                'residency' => $user->id_proof_number,
                'monthly_cost' => $user->monthly_cost,
                'wd' => $user->work_days,
                'absence_day' => $user->absence_days,
                'absence_amount' => $user->absence_amount,
                'over_time_h' => $user->over_time_hours,
                'over_time' => $user->over_time_amount,
                'other_deduction' => $user->other_deduction,
                'other_addition' => $user->other_addition,
                'cost2' => $user->cost_2,
                'invoice_value' => $user->invoice_value,
                'vat' => $user->vat,
                'total' => $user->total,
                'sponser' => $user->project_id,
                'basic' => $user->basic,
                'housing' => $user->housing,
                'transport' => $user->transport,
                'other_allowances' => $user->other_allowances,
                'total_salary' => $user->total_salary,
                'deductions' => $user->deductions,
                'additions' => $user->additions,
                'final_salary' => $user->final_salary,
            ];
        }

        $date = \Carbon\Carbon::parse($timesheetGroup->created_at)->format('F Y');
        $group_name = __('essentials::lang.payroll_for_month', ['date' => $date]);
        $month_year = \Carbon\Carbon::parse($timesheetGroup->created_at)->format('m/Y');
        $action = 'edit';
        return view('accounting::custom_views.agents.agent_time_sheet.payroll_group')->with(compact('project_id', 'employee_ids', 'group_name', 'id', 'date', 'month_year', 'payrolls', 'action'));
    }

    public function submitTmeSheet(Request $request)
    {
        $business_id = 1;
        $action = $request->input('action'); // Get the action (create or edit)
        $timesheet_group_id = $request->input('timesheet_group_id'); // Get the timesheet group ID for edit action

        try {
            DB::beginTransaction();
            $translatedTimeSheetFor = __('agent.time_sheet_for');
            $timesheet_group_data = [
                'business_id' => $business_id,
                'project_id' => $request->project_id,
                'name' => $translatedTimeSheetFor . ' ' . $request->transaction_date,
                'status' => $request->input('payroll_group_status'),
                'total' => $request->input('total_payrolls'),
                'timesheet_date' => $request->transaction_date,
                'created_by' => auth()->user()->id,
            ];

            if ($action === 'edit' && $timesheet_group_id) {
                // Update existing timesheet group
                $timesheet_group = TimesheetGroup::findOrFail($timesheet_group_id);
                $timesheet_group->update($timesheet_group_data);

                // Delete existing timesheet users
                TimesheetUser::where('timesheet_group_id', $timesheet_group_id)->delete();
            } else {
                // Create new timesheet group
                $timesheet_group = TimesheetGroup::create($timesheet_group_data);
            }

            $payrolls = $request->input('payrolls', []);

            foreach ($payrolls as $payroll) {
                $user = User::find($payroll['id']);

                if ($user) {
                    TimesheetUser::create([
                        'user_id' => $user->id,
                        'timesheet_group_id' => $timesheet_group->id,
                        'nationality_id' => $user->nationality_id,
                        'id_proof_number' => $user->id_proof_number,
                        'monthly_cost' => $payroll['monthly_cost'],
                        'work_days' => $payroll['wd'],
                        'absence_days' => $payroll['absence_day'],
                        'absence_amount' => $payroll['absence_amount'],
                        'over_time_hours' => $payroll['over_time_h'],
                        'over_time_amount' => $payroll['over_time'],
                        'other_deduction' => $payroll['other_deduction'],
                        'other_addition' => $payroll['other_addition'],
                        'cost_2' => $payroll['cost2'],
                        'invoice_value' => $payroll['invoice_value'],
                        'vat' => $payroll['vat'],
                        'total' => $payroll['total'],
                        'project_id' => $payroll['assigned_to'] ?? null,
                        'basic' => $payroll['basic'],
                        'housing' => $payroll['housing'],
                        'transport' => $payroll['transport'],
                        'other_allowances' => $payroll['other_allowances'],
                        'total_salary' => $payroll['total_salary'],
                        'deductions' => $payroll['deductions'],
                        'additions' => $payroll['additions'],
                        'final_salary' => $payroll['final_salary'],
                        'created_by' => auth()->user()->id
                    ]);
                }
            }

            DB::commit();

            $output = [
                'success' => true,
                'msg' => ($action === 'edit') ? __('lang_v1.updated_success') : __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('accounting.agentTimeSheetIndex')->with('status', $output);
    }



    public function showTimeSheet($id)
    {
        $company_id = Session::get('selectedCompanyId');
        $timesheetGroup = TimesheetGroup::findOrFail($id);
        $timesheetUsers = TimeSheetUser::where('timesheet_group_id', $id)
            ->join('users as u', 'u.id', '=', 'timesheet_users.user_id')
            ->where('u.company_id', $company_id)
            ->select([
                'timesheet_users.*',
                'u.first_name',
                'u.mid_name',
                'u.last_name',
                'u.bank_details',

                'u.assigned_to',
                'u.id'
            ])->where('is_approved', 0)
            ->get();

        $timesheetUsers->each(function ($item) {
            $bankDetails = json_decode($item->bank_details, true);
            $item->bank_name = $bankDetails['bank_name'] ?? '';
            $item->branch = $bankDetails['branch'] ?? '';
            $item->iban_number = $bankDetails['iban_number'] ?? '';
            $item->account_holder_name = $bankDetails['account_holder_name'] ?? '';
            $item->account_number = $bankDetails['account_number'] ?? '';
            $item->tax_number = $bankDetails['tax_number'] ?? '';
        });
        $projects = SalesProject::pluck('name', 'id');
        $payrolls = $timesheetUsers->map(function ($user) use ($projects) {
            return [
                'id' => $user->user_id,
                'name' => $user->first_name . ' '  . $user->last_name,
                'nationality' => User::find($user->id)->country?->nationality ?? '',
                'residency' => $user->id_proof_number,
                'monthly_cost' => $user->monthly_cost,
                'wd' => $user->work_days,
                'absence_day' => $user->absence_days,
                'absence_amount' => $user->absence_amount,
                'over_time_h' => $user->over_time_hours,
                'over_time' => $user->over_time_amount,
                'other_deduction' => $user->other_deduction,
                'other_addition' => $user->other_addition,
                'cost2' => $user->cost_2,
                'invoice_value' => $user->invoice_value,
                'vat' => $user->vat,
                'total' => $user->total,
                'sponser' => $user->assigned_to ? $projects[$user->assigned_to] ?? '' : '',
                'basic' => $user->basic,
                'housing' => $user->housing,
                'transport' => $user->transport,
                'other_allowances' => $user->other_allowances,
                'total_salary' => $user->total_salary,
                'deductions' => $user->deductions,
                'additions' => $user->additions,
                'final_salary' => $user->final_salary,
                'bank_name' => $user->bank_name,
                'branch' => $user->branch,
                'iban_number' => $user->iban_number,
                'account_holder_name' => $user->account_holder_name,
                'account_number' => $user->account_number,
                'tax_number' => $user->tax_number,
            ];
        });

        return view('accounting::custom_views.agents.agent_time_sheet.show', compact('timesheetGroup', 'payrolls'));
    }
    public function approvedTimeSheetByAccounting($id)
    {
        try {
            $timesheetGroup = TimesheetGroup::findOrFail($id);
            $timesheetGroup->is_approved_by_accounting = 1;
            $timesheetGroup->accounting_approved_by = auth()->user()->id;
            $timesheetGroup->save();

            return redirect()->route('accounting.agentTimeSheetIndex')->with('status', [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return redirect()->route('accounting.agentTimeSheetIndex')->with('status', [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }
}
