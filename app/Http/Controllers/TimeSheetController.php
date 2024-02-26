<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Modules\Sales\Entities\SalesProject;
use App\Category;
use App\Transaction;
use Carbon\Carbon;
use DB;
use App\Request as UserRequest;
use Modules\CEOManagment\Entities\RequestsType;
use Illuminate\Http\Request;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsAttendance;
use Yajra\DataTables\Facades\DataTables;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsLeave;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;
use Modules\Essentials\Entities\EssentialsUserSalesTarget;
use Modules\Essentials\Entities\PayrollGroup;
use Modules\Essentials\Entities\PayrollGroupTransaction;
use Modules\Essentials\Utils\EssentialsUtil;
use Modules\FollowUp\Entities\FollowupWorkerRequest;

class TimeSheetController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $commonUtil;

    protected $restUtil;

    protected $essentialsUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        RestaurantUtil $restUtil,
        EssentialsUtil $essentialsUtil,
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
        $this->essentialsUtil = $essentialsUtil;
    }



    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id =  $user->crm_contact_id;
        $projects = SalesProject::where('contact_id', $contact_id)->pluck('name', 'id');
        $can_view_all_payroll = auth()->user()->can('essentials.view_all_payroll');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $user_businesses_ids = Business::pluck('id')->unique()->toArray();

        // if (!$is_admin) {
        //     $userProjects = [];
        //     $userBusinesses = [];
        //     $roles = auth()->user()->roles;
        //     foreach ($roles as $role) {

        //         $accessRole = AccessRole::where('role_id', $role->id)->first();

        //         if ($accessRole) {
        //             $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

        //             $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
        //         }
        //     }
        //     $user_businesses_ids = array_unique($userBusinesses);
        // }

        // if (request()->ajax()) {
        //     $payrolls = $this->essentialsUtil->getPayrollQuery($user_businesses_ids);
        //     error_log(($payrolls->count()));

        //     if (!empty(request()->input('user_id'))) {
        //         $payrolls->where('transactions.expense_for', request()->input('user_id'));
        //     }

        //     if (!empty(request()->input('designation_id'))) {
        //         $payrolls->where('dsgn.id', request()->input('designation_id'));
        //     }

        //     if (!empty(request()->input('department_id'))) {
        //         $payrolls->where('dept.id', request()->input('department_id'));
        //     }


        //     // if (!$can_view_all_payroll) {
        //     //     $payrolls->where('transactions.expense_for', auth()->user()->id);
        //     // }

        //     if (!empty(request()->input('location_id'))) {
        //         $payrolls->where('u.location_id', request()->input('location_id'));
        //     }

        //     // $permitted_locations = auth()->user()->permitted_locations();
        //     // if ($permitted_locations != 'all') {
        //     //     $payrolls->where(function ($q) use ($permitted_locations) {
        //     //         $q->whereIn('epg.location_id', $permitted_locations)
        //     //             ->orWhereNull('epg.location_id');
        //     //     });
        //     // }

        //     if (!empty(request()->month_year)) {
        //         $month_year_arr = explode('/', request()->month_year);
        //         if (count($month_year_arr) == 2) {
        //             $month = $month_year_arr[0];
        //             $year = $month_year_arr[1];

        //             $payrolls->whereDate('transaction_date', $year . '-' . $month . '-01');
        //         }
        //     }

        //     return Datatables::of($payrolls)
        //         ->addColumn(
        //             'action',
        //             function ($row) use ($is_admin, $can_view_all_payroll) {

        //                 $html = '<div class="btn-group">
        //                             <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
        //                                 data-toggle="dropdown" aria-expanded="false">' .
        //                     __('messages.actions') .
        //                     '<span class="caret"></span><span class="sr-only">Toggle Dropdown
        //                                 </span>
        //                             </button>
        //                             <ul class="dropdown-menu dropdown-menu-right" role="menu">';

        //                 $html .= '<li><a href="#" data-href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'show'], [$row->id]) . '" data-container=".view_modal" class="btn-modal"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';

        //                 // $html .= '<li><a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->id]) . '" class="view_payment_modal"><i class="fa fa-money"></i> ' . __("purchase.view_payments") . '</a></li>';

        //                 if (empty($row->payroll_group_id) && $row->payment_status != 'paid' && auth()->user()->can('essentials.create_payroll')) {
        //                     $html .= '<li><a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal"><i class="fa fa-money"></i> ' . __('purchase.add_payment') . '</a></li>';
        //                 }

        //                 $html .= '</ul></div>';

        //                 return $html;
        //             }
        //         )
        //         ->addColumn('transaction_date', function ($row) {
        //             $transaction_date = \Carbon::parse($row->transaction_date);

        //             return $transaction_date->format('F Y');
        //         })
        //         ->editColumn('final_total', '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>')
        //         ->filterColumn('user', function ($query, $keyword) {
        //             $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
        //         })
        //         ->editColumn(
        //             'payment_status',
        //             '<a href="{{ action([\App\Http\Controllers\TransactionPaymentController::class, \'show\'], [$id])}}" class="view_payment_modal payment-status-label no-print" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
        //                 </span></a>
        //                 <span class="print_section">{{__(\'lang_v1.\' . $payment_status)}}</span>
        //                 '
        //         )
        //         ->removeColumn('id')
        //         ->rawColumns(['action', 'final_total', 'payment_status'])
        //         ->make(true);
        // }

        $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
        $workers =  User::where('user_type', 'worker')
            ->whereIn('users.assigned_to', $projectsIds)
            ->select(
                'users.*',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
            )->pluck('worker', 'id')->toArray();

        $departments = Category::forDropdown($business_id, 'hrm_department');
        $designations = Category::forDropdown($business_id, 'hrm_designation');
        // $locations =    BusinessLocation::pluck('name', 'id');
        return view('custom_views.agents.agent_time_sheet.index')->with(compact('workers', 'departments', 'designations', 'projects'));
    }

    private function getAllowanceAndDeductionJson($payroll)
    {
        $allowance_types = [];
        $allowance_names_array = [];
        $allowance_percent_array = [];
        $allowance_amounts = [];


        if ($payroll['over_time'] != 0) {
            $allowance_names_array[] = 'وقت إضافي';
            $allowance_amounts[] = $payroll['over_time'];
            $allowance_percent_array[] = 0;
            $allowance_types[] = 'fixed';
        }
        if ($payroll['other_addition'] != 0) {
            $allowance_names_array[] = 'إضافات إخرى';
            $allowance_amounts[] = $payroll['other_addition'];
            $allowance_percent_array[] = 0;
            $allowance_types[] = 'fixed';
        }


        $deduction_types = [];
        $deduction_names_array = [];
        $deduction_percents_array = [];
        $deduction_amounts = [];

        if ($payroll['absence_amount'] != 0) {
            $deduction_names_array[] = 'غياب';
            $deduction_amounts[] = $this->moduleUtil->num_uf($payroll['absence_amount']);
            $deduction_percents_array[] = 0;
            $deduction_types[] = 'fixed';
        }
        if ($payroll['other_deduction'] != 0) {
            $deduction_names_array[] = 'خصومات أخرى';
            $deduction_amounts[] = $this->moduleUtil->num_uf($payroll['other_deduction']);
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

    public function submitTmeSheet(Request $request)
    {
        $business_id = 1;
        try {
            DB::beginTransaction();
            $payroll_group['business_id'] = $business_id;
            $payroll_group['name'] = $request->input('payroll_group_name');
            $payroll_group['status'] = $request->input('payroll_group_status');
            $payroll_group['gross_total'] = $request->input('total_payrolls');
            $payroll_group['created_by'] = auth()->user()->id;
            $payroll_group = PayrollGroup::create($payroll_group);
            $transaction_date = $request->transaction_date;

            //ref_no,
            $transaction_ids = [];
            $employees_details = $request->payrolls;
            foreach ($employees_details as $employee_details) {
                $payroll['expense_for'] = $employee_details['id'];
                $payroll['transaction_date'] = $transaction_date;
                $payroll['business_id'] = $business_id;
                $payroll['created_by'] = auth()->user()->id;
                $payroll['type'] = 'payroll';
                $payroll['payment_status'] = 'due';
                $payroll['status'] = 'final';
                $payroll['total_before_tax'] = $employee_details['invoice_value'];
                $payroll['essentials_amount_per_unit_duration'] = $employee_details['monthly_cost'];

                $allowances_and_deductions = $this->getAllowanceAndDeductionJson($employee_details);
                $payroll['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'];
                $payroll['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'];
                $payroll['final_total'] = $employee_details['total'];
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

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return redirect()->route('agentTimeSheet.index')->with('status', $output);
    }

    public function payrolls()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id =  $user->crm_contact_id;
        $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
        $workers_ids =  User::where('user_type', 'worker')
            ->whereIn('users.assigned_to', $projectsIds)
            ->pluck('id')->toArray();
        $payrolls = Transaction::whereIn('transactions.expense_for', $workers_ids)->where('type', 'payroll')
            ->join('users as u', 'u.id', '=', 'transactions.expense_for')
            ->leftJoin('categories as dept', 'u.essentials_department_id', '=', 'dept.id')
            ->leftJoin('categories as dsgn', 'u.essentials_designation_id', '=', 'dsgn.id')
            ->leftJoin('essentials_payroll_group_transactions as epgt', 'transactions.id', '=', 'epgt.transaction_id')
            ->leftJoin('essentials_payroll_groups as epg', 'epgt.payroll_group_id', '=', 'epg.id')
            ->select([
                'transactions.id',
                DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                'final_total',
                'transaction_date',
                'ref_no',
                'transactions.payment_status',
                'dept.name as department',
                'dsgn.name as designation',
                'epgt.payroll_group_id',
            ]);
        return Datatables::of($payrolls)
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
            ->addColumn('transaction_date', function ($row) {
                $transaction_date = \Carbon::parse($row->transaction_date);

                return $transaction_date->format('F Y');
            })
            ->editColumn('final_total', '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>')
            ->filterColumn('user', function ($query, $keyword) {
                $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
            })
            ->editColumn(
                'payment_status',
                '<a href="{{ action([\App\Http\Controllers\TransactionPaymentController::class, \'show\'], [$id])}}" class="view_payment_modal payment-status-label no-print" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
                    </span></a>
                    <span class="print_section">{{__(\'lang_v1.\' . $payment_status)}}</span>
                    '
            )
            ->removeColumn('id')
            ->rawColumns(['action', 'final_total', 'payment_status'])
            ->make(true);
    }

    public function showPayroll($id)
    {

        $query = Transaction::with(['transaction_for', 'payment_lines']);
        $business_id = $query->first()->business_id;
        $payroll = $query->findOrFail($id);

        $transaction_date = \Carbon::parse($payroll->transaction_date);

        $department = Category::where('category_type', 'hrm_department')
            ->find($payroll->transaction_for->essentials_department_id);

        $designation = Category::where('category_type', 'hrm_designation')
            ->find($payroll->transaction_for->essentials_designation_id);

        $location = BusinessLocation::where('business_id', $business_id)
            ->find($payroll->transaction_for->location_id);

        $month_name = $transaction_date->format('F');
        $year = $transaction_date->format('Y');
        $allowances = !empty($payroll->essentials_allowances) ? json_decode($payroll->essentials_allowances, true) : [];
        $deductions = !empty($payroll->essentials_deductions) ? json_decode($payroll->essentials_deductions, true) : [];
        $bank_details = json_decode($payroll->transaction_for->bank_details, true);
        $payment_types = $this->moduleUtil->payment_types();
        $final_total_in_words = $this->commonUtil->numToIndianFormat($payroll->final_total);

        $start_of_month = \Carbon::parse($payroll->transaction_date);
        $end_of_month = \Carbon::parse($payroll->transaction_date)->endOfMonth();

        $leaves = EssentialsLeave::where('business_id', $business_id)
            ->where('user_id', $payroll->transaction_for->id)
            ->whereDate('start_date', '>=', $start_of_month)
            ->whereDate('end_date', '<=', $end_of_month)
            ->get();

        $total_leaves = 0;
        $days_in_a_month = \Carbon::parse($start_of_month)->daysInMonth;
        foreach ($leaves as $key => $leave) {
            $start_date = \Carbon::parse($leave->start_date);
            $end_date = \Carbon::parse($leave->end_date);

            $diff = $start_date->diffInDays($end_date);
            $diff += 1;
            $total_leaves += $diff;
        }

        $total_days_present = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee(
            $business_id,
            $payroll->transaction_for->id,
            $start_of_month->format('Y-m-d'),
            $end_of_month->format('Y-m-d')
        );

        $total_work_duration = $this->essentialsUtil->getTotalWorkDuration(
            'hour',
            $payroll->transaction_for->id,
            $business_id,
            $start_of_month->format('Y-m-d'),
            $end_of_month->format('Y-m-d')
        );

        return view('custom_views.agents.agent_time_sheet.show')
            ->with(compact(
                'payroll',
                'month_name',
                'allowances',
                'deductions',
                'year',
                'payment_types',
                'bank_details',
                'designation',
                'department',
                'final_total_in_words',
                'total_leaves',
                'days_in_a_month',
                'total_work_duration',
                'location',
                'total_days_present'
            ));
    }


    public function payrollGroupDatatable(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

            $user_businesses_ids = Business::pluck('id')->unique()->toArray();

            // if (!$is_admin) {
            //     $userProjects = [];
            //     $userBusinesses = [];
            //     $roles = auth()->user()->roles;
            //     foreach ($roles as $role) {

            //         $accessRole = AccessRole::where('role_id', $role->id)->first();

            //         if ($accessRole) {
            //             $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

            //             $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
            //         }
            //     }
            //     $user_businesses_ids = array_unique($userBusinesses);
            // }
            if ($request->ajax()) {
                $payroll_groups = PayrollGroup::whereIn('essentials_payroll_groups.business_id', $user_businesses_ids)->where('u.id', auth()->user()->id)
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

                // $permitted_locations = auth()->user()->permitted_locations();
                // if ($permitted_locations != 'all') {
                //     $payroll_groups->where(function ($q) use ($permitted_locations) {
                //         $q->whereIn('essentials_payroll_groups.location_id', $permitted_locations)
                //             ->orWhereNull('essentials_payroll_groups.location_id');
                //     });
                // }

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


                            $html .= '<li>
                                    <a href="' . route('agentTimeSheet.viewPayrollGroup', ['id' => $row->id]) . '" target="_blank">
                                            <i class="fa fa-eye" aria-hidden="true"></i> '
                                . __('messages.view') .
                                '</a>
                                </li>';


                            $html .= '<li>
                                        <a href="' . route('agentTimeSheet.getEditPayrollGroup', ['id' => $row->id]) . '" target="_blank">
                                                <i class="fas fa-edit" aria-hidden="true"></i> '
                                . __('messages.edit') .
                                '</a>
                                    </li>';



                            $html .= '<li><a href="' . action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'destroy'], [$row->id]) . '" class="delete-payroll"><i class="fa fa-trash" aria-hidden="true"></i> ' . __('messages.delete') . '</a></li>';




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
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
    }


    public function viewPayrollGroup($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $user_businesses_ids = Business::pluck('id')->unique()->toArray();

        // if (!$is_admin) {
        //     $userProjects = [];
        //     $userBusinesses = [];
        //     $roles = auth()->user()->roles;
        //     foreach ($roles as $role) {

        //         $accessRole = AccessRole::where('role_id', $role->id)->first();

        //         if ($accessRole) {
        //             $userBusinessesForRole = AccessRoleBusiness::where('access_role_id', $accessRole->id)->pluck('business_id')->unique()->toArray();

        //             $userBusinesses = array_merge($userBusinesses, $userBusinessesForRole);
        //         }
        //     }
        //     $user_businesses_ids = array_unique($userBusinesses);
        // }
        $payroll_group = PayrollGroup::with(['payrollGroupTransactions', 'payrollGroupTransactions.transaction_for', 'businessLocation', 'business'])
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

        return view('custom_views.agents.agent_time_sheet.view_payroll_group')
            ->with(compact('payroll_group', 'month_name', 'year', 'payrolls'));
    }

    public function getEditPayrollGroup($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $user_businesses_ids = Business::pluck('id')->unique()->toArray();



        ////////////////////////////////////////////////////////////////////////////////////



        $business_id = 1;
        $payroll_group = PayrollGroup::with(['payrollGroupTransactions', 'payrollGroupTransactions.transaction_for', 'businessLocation'])
            ->findOrFail($id);

        // $employee_ids = request()->input('employee_ids');
        // $employee_ids = $request->employee_ids ?? [];
        // $employee_ids = json_decode($employee_ids, true);

        $location_id = request()->get('primary_work_location');


        $transaction_date = $payroll_group->payrollGroupTransactions->first()->transaction_date;
        $start_date = $transaction_date;
        $end_date = \Carbon::parse($start_date)->lastOfMonth();
        //check if payrolls exists for the month year
        // $payrolls = Transaction::where('business_id', $business_id)
        //     ->where('type', 'payroll')
        //     ->whereIn('expense_for', $employee_ids)
        //     ->whereDate('transaction_date', $transaction_date)
        //     ->get();

        $payrolls = $payroll_group->payrollGroupTransactions;
        $add_payroll_for =  $payrolls->pluck('expense_for')->toArray();
        $transactions_id = $payrolls->pluck('id')->toArray();



        $location = BusinessLocation::where('business_id', $business_id)
            ->find($location_id);

        //initialize required data


        $employees = User::where('business_id', $business_id)
            ->find($add_payroll_for);

        $payrolls = [];
        foreach ($employees as $employee) {

            //get employee info
            $payrolls[$employee->id]['name'] = $employee->user_full_name;
            $payrolls[$employee->id]['nationality'] = $employee->country->nationality;
            $payrolls[$employee->id]['id_proof_number'] = $employee->id_proof_number;
            $payrolls[$employee->id]['essentials_salary'] = $employee->essentials_salary;
            $payrolls[$employee->id]['total_salary'] = $employee->total_salary;
            $payrolls[$employee->id]['essentials_pay_period'] = $employee->essentials_pay_period;
            $payrolls[$employee->id]['total_leaves'] = $this->essentialsUtil->getTotalLeavesForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date->format('Y-m-d'));
            $payrolls[$employee->id]['total_days_worked'] = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date);

            //get total work duration of employee(attendance)
            $payrolls[$employee->id]['total_work_duration'] = $this->essentialsUtil->getTotalWorkDuration('hour', $employee->id, $business_id, $start_date, $end_date->format('Y-m-d'));


            //get earnings & deductions of employee
            // $allowances_and_deductions = $this->essentialsUtil->getEmployeeAllowancesAndDeductions($business_id, $employee->id, $start_date, $end_date);
            $allowances_and_deductions = EssentialsUserAllowancesAndDeduction::with('essentialsAllowanceAndDeduction')->where('user_id', $employee->id)->get();
            foreach ($allowances_and_deductions as $ad) {
                if ($ad->essentialsAllowanceAndDeduction->type == 'allowance') {
                    $payrolls[$employee->id]['allowances']['allowance_names'][] = $ad->essentialsAllowanceAndDeduction->description;
                    $payrolls[$employee->id]['allowances']['allowance_amounts'][] =  $ad->amount;
                } else {
                    $payrolls[$employee->id]['deductions']['deduction_names'][] = $ad->essentialsAllowanceAndDeduction->description;
                    $payrolls[$employee->id]['deductions']['deduction_amounts'][] =  $ad->amount;
                }
            }
        }

        $employee_ids = $add_payroll_for;
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
            $worker_transaction = $worker->transactions->whereIn('id', $transactions_id)->first();
            $payrolls[] = [
                'id' => $worker->user_id,
                'name' => $worker->name ?? '',
                'nationality' => User::find($worker->id)->country?->nationality ?? '',
                'residency' => $worker->eqama_number ?? '',
                'monthly_cost' => number_format($worker->calculateTotalSalary(), 0, '.', ''),
                'wd' => '30',
                'absence_day' => 0,
                'absence_amount' => '',
                'over_time_h' => 0,
                'over_time' => '',
                'other_deduction' => 0,
                'other_addition' => 0,
                'cost2' => $worker_transaction->essentials_amount_per_unit_duration,
                'invoice_value' => $worker_transaction->final_total / 1.15,
                'vat' => $worker_transaction->final_total / 0.15,
                'total' => $worker_transaction->final_total,
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

        $date = (Carbon::parse($transaction_date))->format('F Y');
        $group_name = __('essentials::lang.payroll_for_month', ['date' => $date]);
        $action = 'edit';
        return view('custom_views.agents.agent_time_sheet.payroll_group')->with(compact('employee_ids', 'group_name', 'date', 'month_year', 'payrolls', 'action'));
    }


    public function getWorkersBasedOnProject(Request $request)
    {
        $workers = [];
        if ($request->project_id == 'all') {
            $user = User::where('id', auth()->user()->id)->first();
            $contact_id =  $user->crm_contact_id;
            $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
            $workers =  User::where('user_type', 'worker')
                ->whereIn('users.assigned_to', $projectsIds)
                ->select(
                    'users.*',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
                )->pluck('worker', 'id')->toArray();
        } else {
            $workers =  User::where('user_type', 'worker')
                ->where('users.assigned_to', $request->project_id)
                ->select(
                    'users.*',
                    DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.mid_name, ''), ' ', COALESCE(users.last_name, '')) as worker"),
                )->pluck('worker', 'id')->toArray();
        }

        return [
            'success' => true,
            'msg' => __('lang_v1.success'),
            'workers' => $workers,
        ];
    }

    public function getPayrollGroup()
    {
        $employee_ids = request()->input('employee_ids');
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
            );

        $businesses = Business::pluck('name', 'id',);
        $projects = SalesProject::pluck('name', 'id');
        $currentDateTime = Carbon::now('Asia/Riyadh');
        $month = $currentDateTime->month;
        $year = $currentDateTime->year;
        $start_of_month = $currentDateTime->copy()->startOfMonth();
        $end_of_month = $currentDateTime->copy()->endOfMonth();

        if (request()->ajax()) {

            return Datatables::of($workers)
                ->addColumn('name', function ($row) {
                    return $row->name ?? '';
                })
                ->addColumn('eqama_number', function ($row) {
                    return $row->eqama_number ?? '';
                })
                ->addColumn('project', function ($row) use ($projects) {
                    if ($row->assigned_to) {
                        return $projects[$row->assigned_to] ?? '';
                    } else {
                        return '';
                    }
                })
                ->addColumn('nationality', function ($row) {
                    return  User::find($row->id)->country?->nationality ?? '';
                })
                ->addColumn('monthly_cost', function ($row) {
                    return number_format($row->calculateTotalSalary(), 0, '.', '');
                })
                ->addColumn('wd', function ($row) {
                    //essentialsUserShifts()->orderBy('id', 'desc')->first();
                    if ($row->essentials_pay_period) {
                        if ($row->essentials_pay_period == 'month') {
                            return Carbon::now()->daysInMonth;
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('actual_work_days', function ($row) {
                    $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
                    if ($userShift) {
                        $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
                        $holidays = [];
                        $holidayCounts = 0;
                        foreach ($holidays_temp as $holiday_temp) {
                            $holidays[] = strtolower($holiday_temp);
                        }
                        $start = Carbon::now()->startOfMonth();
                        $end = Carbon::now()->endOfMonth();
                        while ($start->lte($end)) {
                            $dayName = strtolower($start->englishDayOfWeek);

                            if (in_array($dayName, $holidays)) {
                                $holidayCounts++;
                            }
                            $start->addDay();
                        }
                        if ($row->essentials_pay_period) {
                            if ($row->essentials_pay_period == 'month') {
                                return Carbon::now()->daysInMonth - $holidayCounts;
                            } else {
                                return '';
                            }
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('daily_work_hours', function ($row) {
                    $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
                    if ($userShift) {
                        $shift = $userShift->shift;
                        $start = Carbon::parse($shift->start_time);
                        $end = Carbon::parse($shift->end_time);
                        $hoursDifference = $end->diffInHours($start);
                        // $result = $hoursDifference . ' ' . __('worker.hours');
                        return $hoursDifference;
                    } else {
                        return '';
                    }
                })
                ->addColumn('absence_day', function ($row) use ($month, $year) {

                    if ($row->wd) {
                        if ($row->wd == 'month') {
                            $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
                            if ($userShift) {
                                $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
                                    ->whereYear('created_at', '=', $year)->get();
                                $actual_work_days = 0;
                                foreach ($attendances as $attendance) {
                                    if ($attendance->status_id == 1) {
                                        $actual_work_days++;
                                    }
                                }
                                $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
                                $holidays = [];
                                $holidayCounts = 0;
                                foreach ($holidays_temp as $holiday_temp) {
                                    $holidays[] = strtolower($holiday_temp);
                                }
                                $start = Carbon::now()->startOfMonth();
                                $end = Carbon::now();
                                while ($start->lte($end)) {
                                    $dayName = strtolower($start->englishDayOfWeek);

                                    if (in_array($dayName, $holidays)) {
                                        $holidayCounts++;
                                    }
                                    $start->addDay();
                                }
                                return Carbon::now()->day - $holidayCounts - $actual_work_days;
                            } else {
                                return  '';
                            }
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('basic', function ($row)  use ($month, $year) {
                    $basic =  $row->monthly_cost;
                    if ($basic) {
                        return number_format($basic, 0, '.', '');
                    } else {
                        return '';
                    }
                })
                ->addColumn('absence_amount', function ($row) use ($month, $year) {

                    if ($row->wd && $row->essentials_pay_period) {
                        if ($row->wd == 'month' && $row->essentials_pay_period == 'month' && $row->monthly_cost) {
                            $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
                            if ($userShift) {
                                $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
                                    ->whereYear('created_at', '=', $year)->get();
                                $actual_work_days = 0;
                                foreach ($attendances as $attendance) {
                                    if ($attendance->status_id == 1) {
                                        $actual_work_days++;
                                    }
                                }
                                $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
                                $holidays = [];
                                $holidayCounts = 0;
                                foreach ($holidays_temp as $holiday_temp) {
                                    $holidays[] = strtolower($holiday_temp);
                                }
                                $start = Carbon::now()->startOfMonth();
                                $end = Carbon::now();
                                while ($start->lte($end)) {
                                    $dayName = strtolower($start->englishDayOfWeek);

                                    if (in_array($dayName, $holidays)) {
                                        $holidayCounts++;
                                    }
                                    $start->addDay();
                                }
                                $absenceDays = Carbon::now()->day - $holidayCounts - $actual_work_days;
                                $basic =  $row->monthly_cost;
                                $dayPay =  $basic /  Carbon::now()->daysInMonth;

                                return ceil($dayPay * $absenceDays);
                            } else {
                                return  '';
                            }
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('cost2', function ($row) use ($month, $year) {
                    $total_before_absent_days = number_format($row->calculateTotalSalary(), 0, '.', '');
                    if ($row->wd && $row->essentials_pay_period) {
                        if ($row->wd == 'month' && $row->essentials_pay_period == 'month' && $row->monthly_cost) {
                            $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
                            if ($userShift) {
                                $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
                                    ->whereYear('created_at', '=', $year)->get();
                                $actual_work_days = 0;
                                foreach ($attendances as $attendance) {
                                    if ($attendance->status_id == 1) {
                                        $actual_work_days++;
                                    }
                                }
                                $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
                                $holidays = [];
                                $holidayCounts = 0;
                                foreach ($holidays_temp as $holiday_temp) {
                                    $holidays[] = strtolower($holiday_temp);
                                }
                                $start = Carbon::now()->startOfMonth();
                                $end = Carbon::now();
                                while ($start->lte($end)) {
                                    $dayName = strtolower($start->englishDayOfWeek);

                                    if (in_array($dayName, $holidays)) {
                                        $holidayCounts++;
                                    }
                                    $start->addDay();
                                }
                                $absenceDays = Carbon::now()->day - $holidayCounts - $actual_work_days;
                                $basic =  $row->monthly_cost;
                                $dayPay =  $basic /  Carbon::now()->daysInMonth;

                                return $total_before_absent_days - ceil($dayPay * $absenceDays);
                            } else {
                                return  '';
                            }
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('over_time_h', function ($row) {
                    return $row->over_time_h ?? '';
                })
                ->addColumn('over_time', function ($row) {
                    return $row->over_time ?? '';
                })
                ->addColumn('other_deduction', function ($row) use ($month, $year) {
                    $other_deductions = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->essentials_deductions;
                    if ($other_deductions) {
                        $deductions = json_decode($other_deductions);

                        $html = '<ul>';
                        foreach ($deductions->deduction_names as $key => $deduction) {
                            $html .= '<li>' . $deduction . ' : ' . $deductions->deduction_amounts[$key] . '</li>';
                        }
                        $html .= '</ul>';
                        return   $html;
                    } else {
                        return '';
                    }
                })
                ->addColumn('other_addition', function ($row) use ($month, $year) {
                    $other_addition = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->essentials_allowances;
                    if ($other_addition) {
                        $additions = json_decode($other_addition);

                        $html = '<ul>';
                        foreach ($additions->allowance_names as $key => $allowance) {
                            $html .= '<li>' . $allowance . ' : ' . $additions->allowance_amounts[$key] . '</li>';
                        }
                        $html .= '</ul>';
                        return   $html;
                    } else {
                        return '';
                    }
                })

                ->addColumn('invoice_value', function ($row) use ($month, $year) {
                    // $total_before_absent_days = number_format($row->calculateTotalSalary(), 0, '.', '');
                    // if ($row->wd && $row->essentials_pay_period) {
                    //     if ($row->wd == 'month' && $row->essentials_pay_period == 'month' && $row->monthly_cost) {
                    //         $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
                    //         if ($userShift) {
                    //             $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
                    //                 ->whereYear('created_at', '=', $year)->get();
                    //             $actual_work_days = 0;
                    //             foreach ($attendances as $attendance) {
                    //                 if ($attendance->status_id == 1) {
                    //                     $actual_work_days++;
                    //                 }
                    //             }
                    //             $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
                    //             $holidays = [];
                    //             $holidayCounts = 0;
                    //             foreach ($holidays_temp as $holiday_temp) {
                    //                 $holidays[] = strtolower($holiday_temp);
                    //             }
                    //             $start = Carbon::now()->startOfMonth();
                    //             $end = Carbon::now();
                    //             while ($start->lte($end)) {
                    //                 $dayName = strtolower($start->englishDayOfWeek);

                    //                 if (in_array($dayName, $holidays)) {
                    //                     $holidayCounts++;
                    //                 }
                    //                 $start->addDay();
                    //             }
                    //             $absenceDays = Carbon::now()->day - $holidayCounts - $actual_work_days;
                    //             $basic =  $row->monthly_cost;
                    //             $dayPay =  $basic /  Carbon::now()->daysInMonth;

                    //             return $total_before_absent_days - ceil($dayPay * $absenceDays);
                    //         } else {
                    //             return  '';
                    //         }
                    //     } else {
                    //         return '';
                    //     }
                    // } else {
                    //     return '';
                    // }
                    $total_before_tax = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->total_before_tax;
                    if ($total_before_tax) {
                        return number_format($total_before_tax, 0, '.', '');
                    } else {
                        $total_before_absent_days = number_format($row->calculateTotalSalary(), 0, '.', '');
                        if ($row->wd && $row->essentials_pay_period) {
                            if ($row->wd == 'month' && $row->essentials_pay_period == 'month' && $row->monthly_cost) {
                                $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
                                if ($userShift) {
                                    $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
                                        ->whereYear('created_at', '=', $year)->get();
                                    $actual_work_days = 0;
                                    foreach ($attendances as $attendance) {
                                        if ($attendance->status_id == 1) {
                                            $actual_work_days++;
                                        }
                                    }
                                    $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
                                    $holidays = [];
                                    $holidayCounts = 0;
                                    foreach ($holidays_temp as $holiday_temp) {
                                        $holidays[] = strtolower($holiday_temp);
                                    }
                                    $start = Carbon::now()->startOfMonth();
                                    $end = Carbon::now();
                                    while ($start->lte($end)) {
                                        $dayName = strtolower($start->englishDayOfWeek);

                                        if (in_array($dayName, $holidays)) {
                                            $holidayCounts++;
                                        }
                                        $start->addDay();
                                    }
                                    $absenceDays = Carbon::now()->day - $holidayCounts - $actual_work_days;
                                    $basic =  $row->monthly_cost;
                                    $dayPay =  $basic /  Carbon::now()->daysInMonth;

                                    return $total_before_absent_days - ceil($dayPay * $absenceDays);
                                } else {
                                    return  '';
                                }
                            } else {
                                return '';
                            }
                        } else {
                            return '';
                        }
                    }
                })
                ->addColumn('vat', function ($row) use ($month, $year) {
                    $tax = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->tax_amount;
                    if ($tax) {
                        return number_format($tax, 0, '.', '');
                    } else {
                        return '';
                    }
                })
                ->addColumn('total', function ($row)  use ($month, $year) {
                    $total = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->final_total;
                    if ($total) {
                        return number_format($total, 0, '.', '');
                    } else {
                        $total = $row->total_salary;
                        if ($total) {
                            return number_format($total, 0, '.', '');
                        } else {
                            $total = $row->monthly_cost;
                            if ($total) {
                                return number_format($total, 0, '.', '');
                            } else {
                                return '';
                            }
                        }
                    }
                })
                ->addColumn('sponser', function ($row)  use ($month, $year, $businesses) {
                    $business_id = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->business_id;
                    if ($business_id) {
                        return  $businesses[$business_id] ?? '';
                    } else {
                        return '';
                    }
                })

                // ->addColumn('housing', function ($row) {
                //     return $row->housing ?? '';
                // })
                // ->addColumn('transport', function ($row) {
                //     return $row->transport ?? '';
                // })
                ->addColumn('other_allowances', function ($row) {
                    return $row->other_allowances ?? '';
                })
                ->addColumn('total_salary', function ($row)  use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->total_salary ?? '';
                })
                ->addColumn('deductions', function ($row) {
                    $userAllowancesAndDeductions = $row->userAllowancesAndDeductions;
                    if ($userAllowancesAndDeductions) {
                        $deduction_arr = [];
                        foreach ($userAllowancesAndDeductions as $userAllowancesAndDeduction) {
                            $deduction = json_decode(json_encode($userAllowancesAndDeduction));
                            if ($deduction->essentials_allowance_and_deduction->type == 'deduction') {
                                $deduction_arr[] = $deduction;
                            }
                        }
                        $deductions = collect($deduction_arr);
                        if (!empty($deductions)) {
                            $html = '<ul>';
                            foreach ($deductions as $deduction) {
                                $html .= '<li>' . $deduction->essentials_allowance_and_deduction->description . ' : ' . number_format($deduction->amount, 0, '.', '') . '</li>';
                            }
                            $html .= '</ul>';
                            return $html;
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('additions', function ($row) {
                    $userAllowancesAndDeductions = $row->userAllowancesAndDeductions;
                    if ($userAllowancesAndDeductions) {
                        $addition_arr = [];
                        foreach ($userAllowancesAndDeductions as $userAllowancesAndDeduction) {
                            $addition = json_decode(json_encode($userAllowancesAndDeduction));
                            if ($addition->essentials_allowance_and_deduction->type == 'allowance') {
                                $addition_arr[] = $addition;
                            }
                        }
                        $additions = collect($addition_arr);
                        if (!empty($additions)) {
                            $html = '<ul>';
                            foreach ($additions as $addition) {
                                $html .= '<li>' . $addition->essentials_allowance_and_deduction->description . ' : ' . number_format($addition->amount, 0, '.', '') . '</li>';
                            }
                            $html .= '</ul>';
                            return $html;
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('final_salary', function ($row) use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->final_salary ?? '';
                })->addColumn('action', function ($row) use ($month, $year) {
                    // $trans = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first();
                    // if ($trans) {
                    //     if ($trans->payment_status == 'paid') {
                    //         return 'paid';
                    //     }
                    //     return  ' <a href="' . route('agentTimeSheet.timeSheet', [$row->id]) . '" class="btn btn-xs btn-primary">' . __("agent.edit_time_sheet") . '</a>';
                    // } else {
                    //     return  ' <a href="' . route('agentTimeSheet.timeSheet', [$row->id]) . '" class="btn btn-xs btn-success">' . __("agent.add_time_sheet") . '</a>';
                    // }
                    return  ' <a href="' . route('agentTimeSheet.timeSheet', [$row->id]) . '" class="btn btn-xs btn-primary">' . __("agent.edit_time_sheet") . '</a>';
                })->rawColumns([
                    'name',
                    'eqama_number',
                    'project',
                    'nationality',
                    'monthly_cost',
                    'wd',
                    'actual_work_days',
                    'daily_work_hours',
                    'absence_day',
                    'absence_amount',
                    'over_time_h',
                    'over_time',
                    'other_deduction',
                    'other_addition',
                    'cost2',
                    'invoice_value',
                    'vat',
                    'total',
                    'sponser',
                    'basic',
                    'housing',
                    'transport',
                    'other_allowances',
                    'total_salary',
                    'deductions',
                    'additions',
                    'final_salary',
                    'action'
                ])->make(true);
        }
    }

    public function create()
    {
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
        return view('custom_views.agents.agent_time_sheet.payroll_group')->with(compact('employee_ids', 'group_name', 'date', 'month_year', 'payrolls', 'action'));

        // } else {
        //     return redirect()->action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index'])
        //         ->with(
        //             'status',
        //             [
        //                 'success' => true,
        //                 'msg' => __('essentials::lang.payroll_already_added_for_given_user'),
        //             ]
        //         );
        // }
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     $user = User::where('id', auth()->user()->id)->first();
    //     $contact_id =  $user->crm_contact_id;
    //     $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
    //     $workers = User::with(['essentialsUserShifts.shift', 'transactions', 'userAllowancesAndDeductions.essentialsAllowanceAndDeduction'])->where('user_type', 'worker')
    //         ->whereIn('assigned_to', $projectsIds)
    //         ->select(
    //             'users.*',
    //             'users.id as user_id',
    //             DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),
    //             'users.id_proof_number as eqama_number',
    //             'users.essentials_pay_period',
    //             'users.essentials_salary as monthly_cost',
    //             'users.essentials_pay_period as wd',
    //             // 'as actual_work_days',
    //             // 'as daily_work_hours',
    //             // 'as absence_day',
    //             // 'as absence_amount',
    //             // 'as over_time_h',
    //             // 'as over_time',
    //             // 'transactions.essentials_allowances as other_deduction',
    //             // 'transactions.essentials_deductions as other_addition',
    //             // 'users.essentials_salary as cost2',
    //             // 'transactions.total_before_tax as invoice_value',
    //             // 'transactions.tax_amount as vat',
    //             // 'transactions.final_total as total',
    //             // 'transactions.business_id as sponser',
    //             // 'transactions.essentials_amount_per_unit_duration as basic',
    //             // 'as housing',
    //             // 'as transport',
    //             // 'as other_allowances',
    //             // 'transactions.total_before_tax as total_salary',
    //             // 'transactions.essentials_deductions as deductions',
    //             // 'transactions.essentials_allowances as additions',
    //             // 'transactions.final_total as final_salary',
    //         );

    //     $businesses = Business::pluck('name', 'id',);
    //     $currentDateTime = Carbon::now('Asia/Riyadh');
    //     $month = $currentDateTime->month;
    //     $year = $currentDateTime->year;
    //     $start_of_month = $currentDateTime->copy()->startOfMonth();
    //     $end_of_month = $currentDateTime->copy()->endOfMonth();
    //     // $temp=$workers->first()->userAllowancesAndDeductions;
    //     // foreach($temp as $t){
    //     //     return json_decode(json_encode($t))->essentials_allowance_and_deduction;
    //     // }
    //     if (request()->ajax()) {

    //         return Datatables::of($workers)
    //             ->addColumn('name', function ($row) {
    //                 return $row->name ?? '';
    //             })
    //             ->addColumn('eqama_number', function ($row) {
    //                 return $row->eqama_number ?? '';
    //             })
    //             ->addColumn('location', function ($row) use ($businesses) {
    //                 if ($row->business_id) {
    //                     return $businesses[$row->business_id] ?? '';
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('nationality', function ($row) {
    //                 return  User::find($row->id)->country?->nationality ?? '';
    //             })
    //             ->addColumn('monthly_cost', function ($row) {
    //                 return number_format($row->calculateTotalSalary(), 0, '.', '');
    //             })
    //             ->addColumn('wd', function ($row) {
    //                 //essentialsUserShifts()->orderBy('id', 'desc')->first();
    //                 if ($row->essentials_pay_period) {
    //                     if ($row->essentials_pay_period == 'month') {
    //                         return Carbon::now()->daysInMonth;
    //                     }
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('actual_work_days', function ($row) {
    //                 $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
    //                 if ($userShift) {
    //                     $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
    //                     $holidays = [];
    //                     $holidayCounts = 0;
    //                     foreach ($holidays_temp as $holiday_temp) {
    //                         $holidays[] = strtolower($holiday_temp);
    //                     }
    //                     $start = Carbon::now()->startOfMonth();
    //                     $end = Carbon::now()->endOfMonth();
    //                     while ($start->lte($end)) {
    //                         $dayName = strtolower($start->englishDayOfWeek);

    //                         if (in_array($dayName, $holidays)) {
    //                             $holidayCounts++;
    //                         }
    //                         $start->addDay();
    //                     }
    //                     if ($row->essentials_pay_period) {
    //                         if ($row->essentials_pay_period == 'month') {
    //                             return Carbon::now()->daysInMonth - $holidayCounts;
    //                         } else {
    //                             return '';
    //                         }
    //                     }
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('daily_work_hours', function ($row) {
    //                 $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
    //                 if ($userShift) {
    //                     $shift = $userShift->shift;
    //                     $start = Carbon::parse($shift->start_time);
    //                     $end = Carbon::parse($shift->end_time);
    //                     $hoursDifference = $end->diffInHours($start);
    //                     // $result = $hoursDifference . ' ' . __('worker.hours');
    //                     return $hoursDifference;
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('absence_day', function ($row) use ($month, $year) {

    //                 if ($row->wd) {
    //                     if ($row->wd == 'month') {
    //                         $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
    //                         if ($userShift) {
    //                             $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
    //                                 ->whereYear('created_at', '=', $year)->get();
    //                             $actual_work_days = 0;
    //                             foreach ($attendances as $attendance) {
    //                                 if ($attendance->status_id == 1) {
    //                                     $actual_work_days++;
    //                                 }
    //                             }
    //                             $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
    //                             $holidays = [];
    //                             $holidayCounts = 0;
    //                             foreach ($holidays_temp as $holiday_temp) {
    //                                 $holidays[] = strtolower($holiday_temp);
    //                             }
    //                             $start = Carbon::now()->startOfMonth();
    //                             $end = Carbon::now();
    //                             while ($start->lte($end)) {
    //                                 $dayName = strtolower($start->englishDayOfWeek);

    //                                 if (in_array($dayName, $holidays)) {
    //                                     $holidayCounts++;
    //                                 }
    //                                 $start->addDay();
    //                             }
    //                             return Carbon::now()->day - $holidayCounts - $actual_work_days;
    //                         } else {
    //                             return  '';
    //                         }
    //                     } else {
    //                         return '';
    //                     }
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('basic', function ($row)  use ($month, $year) {
    //                 $basic =  $row->monthly_cost;
    //                 if ($basic) {
    //                     return number_format($basic, 0, '.', '');
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('absence_amount', function ($row) use ($month, $year) {

    //                 if ($row->wd && $row->essentials_pay_period) {
    //                     if ($row->wd == 'month' && $row->essentials_pay_period == 'month' && $row->monthly_cost) {
    //                         $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
    //                         if ($userShift) {
    //                             $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
    //                                 ->whereYear('created_at', '=', $year)->get();
    //                             $actual_work_days = 0;
    //                             foreach ($attendances as $attendance) {
    //                                 if ($attendance->status_id == 1) {
    //                                     $actual_work_days++;
    //                                 }
    //                             }
    //                             $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
    //                             $holidays = [];
    //                             $holidayCounts = 0;
    //                             foreach ($holidays_temp as $holiday_temp) {
    //                                 $holidays[] = strtolower($holiday_temp);
    //                             }
    //                             $start = Carbon::now()->startOfMonth();
    //                             $end = Carbon::now();
    //                             while ($start->lte($end)) {
    //                                 $dayName = strtolower($start->englishDayOfWeek);

    //                                 if (in_array($dayName, $holidays)) {
    //                                     $holidayCounts++;
    //                                 }
    //                                 $start->addDay();
    //                             }
    //                             $absenceDays = Carbon::now()->day - $holidayCounts - $actual_work_days;
    //                             $basic =  $row->monthly_cost;
    //                             $dayPay =  $basic /  Carbon::now()->daysInMonth;

    //                             return ceil($dayPay * $absenceDays);
    //                         } else {
    //                             return  '';
    //                         }
    //                     } else {
    //                         return '';
    //                     }
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('cost2', function ($row) use ($month, $year) {
    //                 $total_before_absent_days = number_format($row->calculateTotalSalary(), 0, '.', '');
    //                 if ($row->wd && $row->essentials_pay_period) {
    //                     if ($row->wd == 'month' && $row->essentials_pay_period == 'month' && $row->monthly_cost) {
    //                         $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
    //                         if ($userShift) {
    //                             $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
    //                                 ->whereYear('created_at', '=', $year)->get();
    //                             $actual_work_days = 0;
    //                             foreach ($attendances as $attendance) {
    //                                 if ($attendance->status_id == 1) {
    //                                     $actual_work_days++;
    //                                 }
    //                             }
    //                             $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
    //                             $holidays = [];
    //                             $holidayCounts = 0;
    //                             foreach ($holidays_temp as $holiday_temp) {
    //                                 $holidays[] = strtolower($holiday_temp);
    //                             }
    //                             $start = Carbon::now()->startOfMonth();
    //                             $end = Carbon::now();
    //                             while ($start->lte($end)) {
    //                                 $dayName = strtolower($start->englishDayOfWeek);

    //                                 if (in_array($dayName, $holidays)) {
    //                                     $holidayCounts++;
    //                                 }
    //                                 $start->addDay();
    //                             }
    //                             $absenceDays = Carbon::now()->day - $holidayCounts - $actual_work_days;
    //                             $basic =  $row->monthly_cost;
    //                             $dayPay =  $basic /  Carbon::now()->daysInMonth;

    //                             return $total_before_absent_days - ceil($dayPay * $absenceDays);
    //                         } else {
    //                             return  '';
    //                         }
    //                     } else {
    //                         return '';
    //                     }
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('over_time_h', function ($row) {
    //                 return $row->over_time_h ?? '';
    //             })
    //             ->addColumn('over_time', function ($row) {
    //                 return $row->over_time ?? '';
    //             })
    //             ->addColumn('other_deduction', function ($row) use ($month, $year) {
    //                 $other_deductions = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->essentials_deductions;
    //                 if ($other_deductions) {
    //                     $deductions = json_decode($other_deductions);

    //                     $html = '<ul>';
    //                     foreach ($deductions->deduction_names as $key => $deduction) {
    //                         $html .= '<li>' . $deduction . ' : ' . $deductions->deduction_amounts[$key] . '</li>';
    //                     }
    //                     $html .= '</ul>';
    //                     return   $html;
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('other_addition', function ($row) use ($month, $year) {
    //                 $other_addition = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->essentials_allowances;
    //                 if ($other_addition) {
    //                     $additions = json_decode($other_addition);

    //                     $html = '<ul>';
    //                     foreach ($additions->allowance_names as $key => $allowance) {
    //                         $html .= '<li>' . $allowance . ' : ' . $additions->allowance_amounts[$key] . '</li>';
    //                     }
    //                     $html .= '</ul>';
    //                     return   $html;
    //                 } else {
    //                     return '';
    //                 }
    //             })

    //             ->addColumn('invoice_value', function ($row) use ($month, $year) {
    //                 // $total_before_absent_days = number_format($row->calculateTotalSalary(), 0, '.', '');
    //                 // if ($row->wd && $row->essentials_pay_period) {
    //                 //     if ($row->wd == 'month' && $row->essentials_pay_period == 'month' && $row->monthly_cost) {
    //                 //         $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
    //                 //         if ($userShift) {
    //                 //             $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
    //                 //                 ->whereYear('created_at', '=', $year)->get();
    //                 //             $actual_work_days = 0;
    //                 //             foreach ($attendances as $attendance) {
    //                 //                 if ($attendance->status_id == 1) {
    //                 //                     $actual_work_days++;
    //                 //                 }
    //                 //             }
    //                 //             $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
    //                 //             $holidays = [];
    //                 //             $holidayCounts = 0;
    //                 //             foreach ($holidays_temp as $holiday_temp) {
    //                 //                 $holidays[] = strtolower($holiday_temp);
    //                 //             }
    //                 //             $start = Carbon::now()->startOfMonth();
    //                 //             $end = Carbon::now();
    //                 //             while ($start->lte($end)) {
    //                 //                 $dayName = strtolower($start->englishDayOfWeek);

    //                 //                 if (in_array($dayName, $holidays)) {
    //                 //                     $holidayCounts++;
    //                 //                 }
    //                 //                 $start->addDay();
    //                 //             }
    //                 //             $absenceDays = Carbon::now()->day - $holidayCounts - $actual_work_days;
    //                 //             $basic =  $row->monthly_cost;
    //                 //             $dayPay =  $basic /  Carbon::now()->daysInMonth;

    //                 //             return $total_before_absent_days - ceil($dayPay * $absenceDays);
    //                 //         } else {
    //                 //             return  '';
    //                 //         }
    //                 //     } else {
    //                 //         return '';
    //                 //     }
    //                 // } else {
    //                 //     return '';
    //                 // }
    //                 $total_before_tax = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->total_before_tax;
    //                 if ($total_before_tax) {
    //                     return number_format($total_before_tax, 0, '.', '');
    //                 } else {
    //                     $total_before_absent_days = number_format($row->calculateTotalSalary(), 0, '.', '');
    //                     if ($row->wd && $row->essentials_pay_period) {
    //                         if ($row->wd == 'month' && $row->essentials_pay_period == 'month' && $row->monthly_cost) {
    //                             $userShift = $row->essentialsUserShifts()->orderBy('id', 'desc')->first();
    //                             if ($userShift) {
    //                                 $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
    //                                     ->whereYear('created_at', '=', $year)->get();
    //                                 $actual_work_days = 0;
    //                                 foreach ($attendances as $attendance) {
    //                                     if ($attendance->status_id == 1) {
    //                                         $actual_work_days++;
    //                                     }
    //                                 }
    //                                 $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
    //                                 $holidays = [];
    //                                 $holidayCounts = 0;
    //                                 foreach ($holidays_temp as $holiday_temp) {
    //                                     $holidays[] = strtolower($holiday_temp);
    //                                 }
    //                                 $start = Carbon::now()->startOfMonth();
    //                                 $end = Carbon::now();
    //                                 while ($start->lte($end)) {
    //                                     $dayName = strtolower($start->englishDayOfWeek);

    //                                     if (in_array($dayName, $holidays)) {
    //                                         $holidayCounts++;
    //                                     }
    //                                     $start->addDay();
    //                                 }
    //                                 $absenceDays = Carbon::now()->day - $holidayCounts - $actual_work_days;
    //                                 $basic =  $row->monthly_cost;
    //                                 $dayPay =  $basic /  Carbon::now()->daysInMonth;

    //                                 return $total_before_absent_days - ceil($dayPay * $absenceDays);
    //                             } else {
    //                                 return  '';
    //                             }
    //                         } else {
    //                             return '';
    //                         }
    //                     } else {
    //                         return '';
    //                     }
    //                 }
    //             })
    //             ->addColumn('vat', function ($row) use ($month, $year) {
    //                 $tax = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->tax_amount;
    //                 if ($tax) {
    //                     return number_format($tax, 0, '.', '');
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('total', function ($row)  use ($month, $year) {
    //                 $total = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->final_total;
    //                 if ($total) {
    //                     return number_format($total, 0, '.', '');
    //                 } else {
    //                     return '';
    //                 }
    //             })



    //             ->addColumn('sponser', function ($row)  use ($month, $year, $businesses) {
    //                 $business_id = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->business_id;
    //                 if ($business_id) {
    //                     return  $businesses[$business_id] ?? '';
    //                 } else {
    //                     return '';
    //                 }
    //             })

    //             // ->addColumn('housing', function ($row) {
    //             //     return $row->housing ?? '';
    //             // })
    //             // ->addColumn('transport', function ($row) {
    //             //     return $row->transport ?? '';
    //             // })
    //             ->addColumn('other_allowances', function ($row) {
    //                 return $row->other_allowances ?? '';
    //             })
    //             ->addColumn('total_salary', function ($row)  use ($month, $year) {
    //                 return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->total_salary ?? '';
    //             })



    //             ->addColumn('deductions', function ($row) {
    //                 $userAllowancesAndDeductions = $row->userAllowancesAndDeductions;
    //                 if ($userAllowancesAndDeductions) {
    //                     $deduction_arr = [];
    //                     foreach ($userAllowancesAndDeductions as $userAllowancesAndDeduction) {
    //                         $deduction = json_decode(json_encode($userAllowancesAndDeduction));
    //                         if ($deduction->essentials_allowance_and_deduction->type == 'deduction') {
    //                             $deduction_arr[] = $deduction;
    //                         }
    //                     }
    //                     $deductions = collect($deduction_arr);
    //                     if (!empty($deductions)) {
    //                         $html = '<ul>';
    //                         foreach ($deductions as $deduction) {
    //                             $html .= '<li>' . $deduction->essentials_allowance_and_deduction->description . ' : ' . number_format($deduction->amount, 0, '.', '') . '</li>';
    //                         }
    //                         $html .= '</ul>';
    //                         return $html;
    //                     } else {
    //                         return '';
    //                     }
    //                 } else {
    //                     return '';
    //                 }
    //             })
    //             ->addColumn('additions', function ($row) {
    //                 $userAllowancesAndDeductions = $row->userAllowancesAndDeductions;
    //                 if ($userAllowancesAndDeductions) {
    //                     $addition_arr = [];
    //                     foreach ($userAllowancesAndDeductions as $userAllowancesAndDeduction) {
    //                         $addition = json_decode(json_encode($userAllowancesAndDeduction));
    //                         if ($addition->essentials_allowance_and_deduction->type == 'allowance') {
    //                             $addition_arr[] = $addition;
    //                         }
    //                     }
    //                     $additions = collect($addition_arr);
    //                     if (!empty($additions)) {
    //                         $html = '<ul>';
    //                         foreach ($additions as $addition) {
    //                             $html .= '<li>' . $addition->essentials_allowance_and_deduction->description . ' : ' . number_format($addition->amount, 0, '.', '') . '</li>';
    //                         }
    //                         $html .= '</ul>';
    //                         return $html;
    //                     } else {
    //                         return '';
    //                     }
    //                 } else {
    //                     return '';
    //                 }
    //             })



    //             ->addColumn('final_salary', function ($row) use ($month, $year) {
    //                 return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->final_salary ?? '';
    //             })->addColumn('action', function ($row) use ($month, $year) {
    //                 $trans = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first();
    //                 if ($trans) {
    //                     if ($trans->payment_status == 'paid') {
    //                         return 'paid';
    //                     }
    //                     return  ' <a href="' . route('agentTimeSheet.timeSheet', [$row->id]) . '" class="btn btn-xs btn-primary">' . __("agent.edit_time_sheet") . '</a>';
    //                 } else {
    //                     return  ' <a href="' . route('agentTimeSheet.timeSheet', [$row->id]) . '" class="btn btn-xs btn-success">' . __("agent.add_time_sheet") . '</a>';
    //                 }
    //             })->rawColumns([
    //                 'name',
    //                 'eqama_number',
    //                 'location',
    //                 'nationality',
    //                 'monthly_cost',
    //                 'wd',
    //                 'actual_work_days',
    //                 'daily_work_hours',
    //                 'absence_day',
    //                 'absence_amount',
    //                 'over_time_h',
    //                 'over_time',
    //                 'other_deduction',
    //                 'other_addition',
    //                 'cost2',
    //                 'invoice_value',
    //                 'vat',
    //                 'total',
    //                 'sponser',
    //                 'basic',
    //                 'housing',
    //                 'transport',
    //                 'other_allowances',
    //                 'total_salary',
    //                 'deductions',
    //                 'additions',
    //                 'final_salary',
    //                 'action'
    //             ])->make(true);
    //     }

    //     return view('custom_views.agents.agent_time_sheet.index');
    // }

    public function timeSheet(Request $request)
    {
        // $user_ids = $request->user_ids ?? [];


        $business_id = 1;

        // $employee_ids = request()->input('employee_ids');
        $employee_ids = $request->employee_ids ?? [];
        // $employee_ids = json_decode($employee_ids, true);
        $month_year_arr = explode('/', request()->input('month_year'));
        $location_id = request()->get('primary_work_location');
        $month = $month_year_arr[0];
        $year = $month_year_arr[1];

        $transaction_date = $year . '-' . $month . '-01';

        //check if payrolls exists for the month year
        $payrolls = Transaction::where('business_id', $business_id)
            ->where('type', 'payroll')
            ->whereIn('expense_for', $employee_ids)
            ->whereDate('transaction_date', $transaction_date)
            ->get();

        $add_payroll_for = array_diff($employee_ids, $payrolls->pluck('expense_for')->toArray());


        if (!empty($add_payroll_for)) {
            $location = BusinessLocation::where('business_id', $business_id)
                ->find($location_id);
            $users = User::whereIn('id', $employee_ids)->get();
            //initialize required data
            $start_date = $transaction_date;
            $end_date = \Carbon::parse($start_date)->lastOfMonth();
            $month_name = $end_date->format('F');

            $employees = User::where('business_id', $business_id)
                ->find($add_payroll_for);

            $payrolls = [];
            foreach ($employees as $employee) {

                //get employee info
                $payrolls[$employee->id]['name'] = $employee->user_full_name;
                $payrolls[$employee->id]['nationality'] = $employee->country->nationality;
                $payrolls[$employee->id]['id_proof_number'] = $employee->id_proof_number;
                $payrolls[$employee->id]['essentials_salary'] = $employee->essentials_salary;
                $payrolls[$employee->id]['total_salary'] = $employee->total_salary;
                $payrolls[$employee->id]['essentials_pay_period'] = $employee->essentials_pay_period;
                $payrolls[$employee->id]['total_leaves'] = $this->essentialsUtil->getTotalLeavesForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date->format('Y-m-d'));
                $payrolls[$employee->id]['total_days_worked'] = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date);

                //get total work duration of employee(attendance)
                $payrolls[$employee->id]['total_work_duration'] = $this->essentialsUtil->getTotalWorkDuration('hour', $employee->id, $business_id, $start_date, $end_date->format('Y-m-d'));


                //get earnings & deductions of employee
                // $allowances_and_deductions = $this->essentialsUtil->getEmployeeAllowancesAndDeductions($business_id, $employee->id, $start_date, $end_date);
                $allowances_and_deductions = EssentialsUserAllowancesAndDeduction::with('essentialsAllowanceAndDeduction')->where('user_id', $employee->id)->get();
                foreach ($allowances_and_deductions as $ad) {
                    if ($ad->essentialsAllowanceAndDeduction->type == 'allowance') {
                        $payrolls[$employee->id]['allowances']['allowance_names'][] = $ad->essentialsAllowanceAndDeduction->description;
                        $payrolls[$employee->id]['allowances']['allowance_amounts'][] =  $ad->amount;
                    } else {
                        $payrolls[$employee->id]['deductions']['deduction_names'][] = $ad->essentialsAllowanceAndDeduction->description;
                        $payrolls[$employee->id]['deductions']['deduction_amounts'][] =  $ad->amount;
                    }
                }
            }

            $action = 'create';
            // return view('custom_views.agents.agent_time_sheet.time_sheet')
            //     ->with(compact('users',  'transaction_date', 'payrolls', 'additions', 'deductions', 'cost2', 'absence_deductions', 'allowances_and_deductions', 'essentialsAllowance', 'essentialsDeduction', 'month_name', 'year', 'action', 'location'));
            // //->with(compact('month_name', 'transaction_date', 'year', 'payrolls', 'action', 'location'));

            return view('custom_views.agents.agent_time_sheet.time_sheet2')
                ->with(compact('month_name', 'transaction_date', 'year', 'payrolls', 'action', 'location'));
        } else {
            return redirect()->route('agentTimeSheet.index')
                ->with(
                    'status',
                    [
                        'success' => true,
                        'msg' => __('essentials::lang.payroll_already_added_for_given_user'),
                    ]
                );
        }
    }


    // public function timeSheet($id)
    // {
    //     $user = User::with('country')->where('id', $id)->select(
    //         DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),' - ',COALESCE(id_proof_number,'')) as full_name"),
    //         'users.*'
    //     )
    //         ->get()[0];

    //     $currentDateTime = Carbon::now('Asia/Riyadh');
    //     $month = $currentDateTime->month;
    //     $year = $currentDateTime->year;
    //     $start_of_month = $currentDateTime->copy()->startOfMonth();
    //     $end_of_month = $currentDateTime->copy()->endOfMonth();
    //     $leavesTypes = RequestsType::where('type', 'leavesAndDepartures')->where('for', 'worker')->pluck('id')->toArray();

    //     $leaves = UserRequest::where('related_to', $id)
    //         ->whereIn('request_type_id', $leavesTypes)
    //         ->whereDate('start_date', '>=', $start_of_month)
    //         ->whereDate('end_date', '<=', $end_of_month)->where('status', 'approved')->get();

    //     $leave_days = 0;
    //     $days_in_a_month = Carbon::parse($start_of_month)->daysInMonth;
    //     foreach ($leaves as $key => $leave) {
    //         $start_date = Carbon::parse($leave->start_date);
    //         $end_date = Carbon::parse($leave->end_date);

    //         $diff = $start_date->diffInDays($end_date);
    //         $diff += 1;
    //         $leave_days += $diff;
    //     }


    //     $work_days = 0;
    //     if ($user->essentials_pay_period) {
    //         if ($user->essentials_pay_period == 'month') {
    //             $work_days = Carbon::now()->daysInMonth;
    //         }
    //     }
    //     $actual_work_days = 0;
    //     $absence_days = 0;
    //     $late_days = 0;
    //     $out_of_site_days = 0;
    //     $absence_deductions = 0;
    //     if ($user->essentials_pay_period && $user->essentials_pay_period == 'month' && $user->essentials_salary) {
    //         $userShift = $user->essentialsUserShifts()->orderBy('id', 'desc')->first();
    //         if ($userShift) {
    //             $holidays_temp = json_decode(json_encode($userShift->shift->holidays));
    //             $holidays = [];
    //             $holidayCounts = 0;
    //             foreach ($holidays_temp as $holiday_temp) {
    //                 $holidays[] = strtolower($holiday_temp);
    //             }
    //             $start = Carbon::now()->startOfMonth();
    //             $end = Carbon::now()->endOfMonth();
    //             while ($start->lte($end)) {
    //                 $dayName = strtolower($start->englishDayOfWeek);

    //                 if (in_array($dayName, $holidays)) {
    //                     $holidayCounts++;
    //                 }
    //                 $start->addDay();
    //             }
    //             if ($user->essentials_pay_period) {
    //                 if ($user->essentials_pay_period == 'month') {
    //                     $actual_work_days = Carbon::now()->daysInMonth - $holidayCounts;
    //                 }
    //             }
    //             $attendances = EssentialsAttendance::where('user_id', $user->id)->whereMonth('created_at', '=', $month)
    //                 ->whereYear('created_at', '=', $year)->get();
    //             $attended_days = 0;
    //             foreach ($attendances as $attendance) {
    //                 if ($attendance->status_id == 1) {
    //                     $attended_days++;
    //                 } else if ($attendance->status_id == 2) {
    //                     $late_days++;
    //                 } else if ($attendance->status_id == 3) {
    //                     $out_of_site_days++;
    //                 }
    //             }
    //             $holidayCounts = 0;
    //             $start = Carbon::now()->startOfMonth();
    //             $end = Carbon::now();
    //             while ($start->lte($end)) {
    //                 $dayName = strtolower($start->englishDayOfWeek);

    //                 if (in_array($dayName, $holidays)) {
    //                     $holidayCounts++;
    //                 }
    //                 $start->addDay();
    //             }
    //             $absence_days = Carbon::now()->day - $holidayCounts - $attended_days;
    //             $basic =  $user->essentials_salary;
    //             $dayPay =  $basic /  Carbon::now()->daysInMonth;

    //             $absence_deductions = ceil($dayPay * $absence_days);
    //         }
    //     }

    //     $cost2 =  $user->calculateTotalSalary() - $absence_deductions;








    //     $attendance = (object)[
    //         'work_days' => $work_days,
    //         'actual_work_days' => $actual_work_days,
    //         'late_days' => $late_days,
    //         'out_of_site_days' => $out_of_site_days,
    //         'absence_days' => $absence_days,
    //         'leave_days' => $leave_days,
    //     ];

    //     $query = EssentialsAllowanceAndDeduction::join('essentials_user_allowance_and_deductions as euad', 'euad.allowance_deduction_id', '=', 'essentials_allowances_and_deductions.id')
    //         ->where('euad.user_id', $id);

    //     $essentialsAllowance = EssentialsAllowanceAndDeduction::where('type', 'allowance')->pluck('description', 'id');
    //     $essentialsDeduction = EssentialsAllowanceAndDeduction::where('type', 'deduction')->pluck('description', 'id');
    //     $essentialsUserAllowancesAndDeduction = EssentialsUserAllowancesAndDeduction::with('essentialsAllowanceAndDeduction')->where('user_id', $id);

    //     //Filter if applicable one
    //     if (!empty($start_date) && !empty($end_date)) {
    //         $essentialsUserAllowancesAndDeduction->where(function ($q) use ($start_date, $end_date) {
    //             $q->whereNull('applicable_date')
    //                 ->orWhereBetween('applicable_date', [$start_date, $end_date]);
    //         });
    //     }
    //     $allowances_and_deductions = $essentialsUserAllowancesAndDeduction->get();
    //     $allowances = [];
    //     $deductions = [];
    //     foreach ($allowances_and_deductions as $ad) {
    //         if ($ad->essentialsAllowanceAndDeduction->type == 'allowance') {
    //             $allowances[] = $ad;
    //         } else {
    //             $deductions[] = $ad;
    //         }
    //     }

    //     $allowances_and_deductions = (object)[
    //         'allowances' => $allowances,
    //         'deductions' => $deductions,
    //     ];


    //     // return $allowances_and_deductions;


    //     $business_id = User::where('id', $id)->first()->business_id;
    //     // $employee_ids = request()->input('employee_ids');
    //     // $month_year_arr = explode('/', request()->input('month_year'));
    //     $employee_ids = [$id];
    //     $location_id = request()->get('primary_work_location');
    //     $currentDateTime = Carbon::now('Asia/Riyadh');

    //     $month = $currentDateTime->month;
    //     $year = $currentDateTime->year;

    //     $transaction_date = $year . '-' . $month . '-01';

    //     //check if payrolls exists for the month year
    //     // $payrolls = Transaction::where('business_id', $business_id)
    //     //     ->where('type', 'payroll')
    //     //     ->whereIn('expense_for', $employee_ids)
    //     //     ->whereDate('transaction_date', $transaction_date)
    //     //     ->get();

    //     // $add_payroll_for = $employee_ids;
    //     if (!empty($employee_ids)) {
    //         $location = BusinessLocation::where('business_id', $business_id)
    //             ->find($location_id);

    //         //initialize required data
    //         $start_date = $transaction_date;
    //         $end_date = Carbon::parse($start_date)->lastOfMonth();
    //         $month_name = $end_date->format('F');

    //         $employees = User::where('business_id', $business_id)
    //             ->find($employee_ids);

    //         // $payrolls = [];
    //         // foreach ($employees as $employee) {

    //         //     //get employee info
    //         //     $payrolls[$employee->id]['name'] = $employee->user_full_name;
    //         //     $payrolls[$employee->id]['essentials_salary'] = $employee->essentials_salary;
    //         //     $payrolls[$employee->id]['essentials_pay_period'] = $employee->essentials_pay_period;
    //         //     $payrolls[$employee->id]['total_leaves'] = $this->essentialsUtil->getTotalLeavesForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date->format('Y-m-d'));
    //         //     $payrolls[$employee->id]['total_days_worked'] = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date);

    //         //     //get total work duration of employee(attendance)
    //         //     $payrolls[$employee->id]['total_work_duration'] = $this->essentialsUtil->getTotalWorkDuration('hour', $employee->id, $business_id, $start_date, $end_date->format('Y-m-d'));

    //         //     //get total earned commission for employee
    //         //     $business_details = $this->businessUtil->getDetails($business_id);
    //         //     $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

    //         //     $commsn_calculation_type = empty($pos_settings['cmmsn_calculation_type']) || $pos_settings['cmmsn_calculation_type'] == 'invoice_value' ? 'invoice_value' : $pos_settings['cmmsn_calculation_type'];

    //         //     $total_commission = 0;
    //         //     if ($commsn_calculation_type == 'payment_received') {
    //         //         $payment_details = $this->transactionUtil->getTotalPaymentWithCommission($business_id, $start_date, $end_date, null, $employee->id);
    //         //         //Get Commision
    //         //         $total_commission = $employee->cmmsn_percent * $payment_details['total_payment_with_commission'] / 100;
    //         //     } else {
    //         //         $sell_details = $this->transactionUtil->getTotalSellCommission($business_id, $start_date, $end_date, null, $employee->id);
    //         //         $total_commission = $employee->cmmsn_percent * $sell_details['total_sales_with_commission'] / 100;
    //         //     }

    //         //     if ($total_commission > 0) {
    //         //         $payrolls[$employee->id]['allowances']['allowance_names'][] = __('essentials::lang.sale_commission');
    //         //         $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $total_commission;
    //         //         $payrolls[$employee->id]['allowances']['allowance_types'][] = 'fixed';
    //         //         $payrolls[$employee->id]['allowances']['allowance_percents'][] = 0;
    //         //     }
    //         //     $settings = $this->essentialsUtil->getEssentialsSettings();
    //         //     //get total sales added by the employee
    //         //     $sale_totals = $this->transactionUtil->getUserTotalSales($business_id, $employee->id, $start_date, $end_date);

    //         //     $total_sales = !empty($settings['calculate_sales_target_commission_without_tax']) && $settings['calculate_sales_target_commission_without_tax'] == 1 ? $sale_totals['total_sales_without_tax'] : $sale_totals['total_sales'];

    //         //     //get sales target if exists
    //         //     $sales_target = EssentialsUserSalesTarget::where('user_id', $employee->id)
    //         //         ->where('target_start', '<=', $total_sales)
    //         //         ->where('target_end', '>=', $total_sales)
    //         //         ->first();

    //         //     $total_sales_target_commission_percent = !empty($sales_target) ? $sales_target->commission_percent : 0;

    //         //     $total_sales_target_commission = $this->transactionUtil->calc_percentage($total_sales, $total_sales_target_commission_percent);

    //         //     if ($total_sales_target_commission > 0) {
    //         //         $payrolls[$employee->id]['allowances']['allowance_names'][] = __('essentials::lang.sales_target_commission');
    //         //         $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $total_sales_target_commission;
    //         //         $payrolls[$employee->id]['allowances']['allowance_types'][] = 'fixed';
    //         //         $payrolls[$employee->id]['allowances']['allowance_percents'][] = 0;
    //         //     }


    //         // }

    //         $action = 'create';

    //         $output = [
    //             'success' => true,
    //             'msg' => __('lang_v1.added_success'),
    //         ];

    //         $deductions = $user->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->essentials_deductions ?? null;

    //         $additions = $user->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->essentials_allowances ?? null;


    //         return view('custom_views.agents.agent_time_sheet.time_sheet')
    //             ->with(compact('additions', 'deductions', 'cost2', 'absence_deductions', 'user', 'attendance', 'allowances_and_deductions', 'essentialsAllowance', 'essentialsDeduction', 'month_name', 'transaction_date', 'year', 'action', 'location'));
    //     }
    //     //  else {
    //     //     return redirect()->route('agentTimeSheet.index')
    //     //         ->with(
    //     //             'status',
    //     //             [
    //     //                 'success' => true,
    //     //                 'msg' => __('essentials::lang.payroll_already_added_for_given_user'),
    //     //             ]
    //     //         );
    //     // }
    // }


    public function storeTimeSheet(Request $request)
    {

        try {
            $user = json_decode($request->user);
            $attendance = json_decode($request->attendance);
            $business_id =  $user->business_id;
            $transaction_date = $request->input('transaction_date');

            $currentDateTime = Carbon::now('Asia/Riyadh');
            $month = $currentDateTime->month;
            $year = $currentDateTime->year;
            $month_name = $currentDateTime->format('F');

            $trans = User::where('id', $user->id)->first()->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first();








            $payroll = [];
            $payroll['name'] =  $user->full_name;
            $payroll['expense_for'] =  $user->id;
            $payroll['essentials_salary'] = $user->essentials_salary;
            $payroll['essentials_pay_period'] = $user->essentials_pay_period;
            $payroll['total_leaves'] =  $attendance->leave_days;
            $payroll['total_days_worked'] =  $attendance->actual_work_days;
            $payroll['transaction_date'] = $transaction_date;
            $payroll['business_id'] = $business_id;
            $payroll['created_by'] = auth()->user()->id;
            $payroll['type'] = 'payroll';
            $payroll['payment_status'] = 'due';
            $payroll['status'] = 'final';
            $payroll['total_before_tax'] = $this->transactionUtil->num_uf($request->input('total_salary'));
            $payroll['final_total'] = $this->transactionUtil->num_uf($request->input('total_salary'));
            $payroll['essentials_amount_per_unit_duration'] = $this->moduleUtil->num_uf($user->essentials_salary);

            // $allowances_and_deductions = json_decode($request->allowances_and_deductions);

            // foreach ($allowances_and_deductions->allowances as $ad) {

            //     $payroll['allowances']['allowance_names'][] = $ad->essentials_allowance_and_deduction->description;
            //     $payroll['allowances']['allowance_amounts'][] = $ad->essentials_allowance_and_deduction->amount_type == 'fixed' ? $ad->amount : 0;
            //     $payroll['allowances']['allowance_types'][] = $ad->essentials_allowance_and_deduction->amount_type;
            //     $payroll['allowances']['allowance_percents'][] = $ad->essentials_allowance_and_deduction->amount_type == 'percent' ? $ad->amount : 0;
            // }
            if (!empty($request->allowances)) {
                foreach ($request->allowances as $key => $ad) {
                    $allowance = EssentialsAllowanceAndDeduction::where('id', $ad)->first();
                    $payroll['allowances']['allowance_names'][] = $allowance->description;
                    $payroll['allowances']['allowance_amounts'][] = $request->allowances_amount[$key];
                    $payroll['allowances']['allowance_types'][] = $allowance->amount_type;
                    $payroll['allowances']['allowance_percents'][] = $allowance->amount_type == 'percent' ? $request->allowances_amount[$key] : 0;
                }
            }


            // foreach ($allowances_and_deductions->deductions as $de) {

            //     $payroll['deductions']['deduction_names'][] = $de->essentials_allowance_and_deduction->description;
            //     $payroll['deductions']['deduction_amounts'][] = $de->essentials_allowance_and_deduction->amount_type == 'fixed' ? $de->amount : 0;
            //     $payroll['deductions']['deduction_types'][] = $de->essentials_allowance_and_deduction->amount_type;
            //     $payroll['deductions']['deduction_percents'][] = $de->essentials_allowance_and_deduction->amount_type == 'percent' ? $de->amount : 0;
            // }
            if (!empty($request->deductions)) {
                foreach ($request->deductions as $key => $de) {
                    $deduction = EssentialsAllowanceAndDeduction::where('id', $de)->first();
                    $payroll['deductions']['deduction_names'][] = $deduction->description;
                    $payroll['deductions']['deduction_amounts'][] = $request->deductions_amount[$key];
                    $payroll['deductions']['deduction_types'][] = $deduction->amount_type;
                    $payroll['deductions']['deduction_percents'][] = $deduction->amount_type == 'percent' ?  $request->deductions_amount[$key] : 0;
                }
            }

            $allowances_and_deductions = $this->getAllowanceAndDeductionJson($payroll);
            $payroll['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'] ?? null;
            $payroll['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'] ?? null;


            //Update reference count
            $ref_count = $this->moduleUtil->setAndGetReferenceCount('payroll');

            //Generate reference number
            if (empty($payroll['ref_no'])) {
                $settings = request()->session()->get('business.essentials_settings');
                $settings = !empty($settings) ? json_decode($settings, true) : [];
                $prefix = !empty($settings['payroll_ref_no_prefix']) ? $settings['payroll_ref_no_prefix'] : '';
                $payroll['ref_no'] = $this->moduleUtil->generateReferenceNumber('payroll', $ref_count, null, $prefix);
            }
            unset($payroll['allowance_names'], $payroll['allowance_types'], $payroll['allowance_percent'], $payroll['allowance_amounts'], $payroll['deduction_names'], $payroll['deduction_types'], $payroll['deduction_percent'], $payroll['deduction_amounts'], $payroll['total']);

            $transaction = $trans ? Transaction::where('id', $trans->id)
                ->update([
                    'essentials_allowances' => $allowances_and_deductions['essentials_allowances'] ?? null,
                    'essentials_deductions' => $allowances_and_deductions['essentials_deductions'] ?? null,
                    'total_before_tax' => $this->transactionUtil->num_uf($request->input('total_salary')),
                    'final_total' => $this->transactionUtil->num_uf($request->input('total_salary')),

                ]) : Transaction::create($payroll);

            $transaction_ids[] = $transaction->id ?? $trans->id;



            $payroll_group['business_id'] = $business_id;
            $payroll_group['name'] = strip_tags(__('essentials::lang.payroll_for_month', ['date' => $month_name . ' ' . $year]));
            //status should be either final or draft, final for now
            //  $payroll_group['status'] = $request->input('payroll_group_status');
            $payroll_group['status'] = "final";
            $payroll_group['gross_total'] = $this->transactionUtil->num_uf($request->input('total_salary'));
            $payroll_group['created_by'] = auth()->user()->id;

            $payrollGroupId = 0;
            if ($trans) {
                $payrollGroupId = PayrollGroupTransaction::where('transaction_id', $trans->id)->orderBy('payroll_group_id', 'desc')->first()->payroll_group_id;
            }
            $payroll_group = $trans ? PayrollGroup::where('id', $payrollGroupId)->update($payroll_group) : PayrollGroup::create($payroll_group);
            if ($trans) {
                $payroll_group =  PayrollGroup::where('id', $payrollGroupId)->first();
            }
            $payroll_group->payrollGroupTransactions()->sync($transaction_ids);
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
        return redirect()->route('agentTimeSheet.index')
            ->with(
                'status',
                $output
            );
    }



    // private function getAllowanceAndDeductionJson($payroll)
    // {
    //     $output = [];
    //     if ($payroll['allowances'] ?? false && $payroll['allowances']['allowance_names'] ?? false) {
    //         $allowance_names = $payroll['allowances']['allowance_names'];
    //         $allowance_types = $payroll['allowances']['allowance_types'];
    //         //$allowance_percents = $payroll['allowances']['allowance_percent'];
    //         $allowance_names_array = [];
    //         $allowance_percent_array = [];
    //         $allowance_amounts = [];

    //         foreach ($payroll['allowances']['allowance_amounts'] as $key => $value) {
    //             if (!empty($allowance_names[$key])) {
    //                 $allowance_amounts[] = $this->moduleUtil->num_uf($value);
    //                 $allowance_names_array[] = $allowance_names[$key];
    //                 $allowance_percent_array[] = !empty($allowance_percents[$key]) ? $this->moduleUtil->num_uf($allowance_percents[$key]) : 0;
    //             }
    //         }
    //         $output['essentials_allowances'] = json_encode([
    //             'allowance_names' => $allowance_names_array,
    //             'allowance_amounts' => $allowance_amounts,
    //             'allowance_types' => $allowance_types,
    //             'allowance_percents' => $allowance_percent_array,
    //         ]);
    //     }
    //     if ($payroll['deductions'] ?? false && $payroll['deductions']['deduction_names'] ?? false) {
    //         $deduction_names = $payroll['deductions']['deduction_names'];
    //         $deduction_types = $payroll['deductions']['deduction_types'];
    //         // $deduction_percents = $payroll['deductions']['deduction_percent'];
    //         $deduction_names_array = [];
    //         $deduction_percents_array = [];
    //         $deduction_amounts = [];
    //         foreach ($payroll['deductions']['deduction_amounts'] as $key => $value) {
    //             if (!empty($deduction_names[$key])) {
    //                 $deduction_names_array[] = $deduction_names[$key];
    //                 $deduction_amounts[] = $this->moduleUtil->num_uf($value);
    //                 $deduction_percents_array[] = !empty($deduction_percents[$key]) ? $this->moduleUtil->num_uf($deduction_percents[$key]) : 0;
    //             }
    //         }
    //         $output['essentials_deductions'] = json_encode([
    //             'deduction_names' => $deduction_names_array,
    //             'deduction_amounts' => $deduction_amounts,
    //             'deduction_types' => $deduction_types,
    //             'deduction_percents' => $deduction_percents_array,
    //         ]);
    //     }
    //     return $output;
    // }

    private function getDocumentnumber($user, $documentType)
    {
        foreach ($user->OfficialDocument as $off) {
            if ($off->type == $documentType) {
                return $off->number;
            }
        }

        return ' ';
    }
}
