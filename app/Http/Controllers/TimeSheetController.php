<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsAttendance;
use Yajra\DataTables\Facades\DataTables;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;
use Modules\Essentials\Entities\EssentialsUserSalesTarget;
use Modules\Essentials\Entities\PayrollGroup;
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



    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $contact_id =  $user->crm_contact_id;
        $projectsIds = SalesProject::where('contact_id', $contact_id)->pluck('id')->unique()->toArray();
        $workers = User::with([ 'essentialsUserShifts.shift', 'transactions', 'userAllowancesAndDeductions.essentialsAllowanceAndDeduction'])->where('user_type', 'worker')
            ->whereIn('assigned_to', $projectsIds)
            ->select(
                'users.*',
              

                'users.id as user_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),
                'users.id_proof_number as eqama_number',
                // 'users.location_id as location',
                'users.essentials_salary as monthly_cost',
                'users.essentials_pay_period as wd',
                // 'as actual_work_days',
                // 'as daily_work_hours',
                // 'as absence_day',
                // 'as absence_amount',
                // 'as over_time_h',
                // 'as over_time',
                // 'transactions.essentials_allowances as other_deduction',
                // 'transactions.essentials_deductions as other_addition',
                'users.essentials_salary as cost2',
                // 'transactions.total_before_tax as invoice_value',
                // 'transactions.tax_amount as vat',
                // 'transactions.final_total as total',
                // 'transactions.business_id as sponser',
                // 'transactions.essentials_amount_per_unit_duration as basic',
                // 'as housing',
                // 'as transport',
                // 'as other_allowances',
                // 'transactions.total_before_tax as total_salary',
                // 'transactions.essentials_deductions as deductions',
                // 'transactions.essentials_allowances as additions',
                // 'transactions.final_total as final_salary',
            );

        $businessLocations = BusinessLocation::pluck('name','business_id', );
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
                ->addColumn('location', function ($row) use ($businessLocations) {
                    if ($row->business_id) {
                        return $businessLocations[$row->business_id];
                    } else {
                        return '';
                    }
                })
                ->addColumn('nationality', function ($row) {
                    return $row->user->country?->nationality ?? '';
                })
                ->addColumn('monthly_cost', function ($row) {
                    return $row->monthly_cost ?? '';
                })
                ->addColumn('wd', function ($row) {
                    if ($row->wd) {
                        if ($row->wd == 'month') {
                            return 30;
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('actual_work_days', function ($row) {
                    // $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
                    //     ->whereYear('created_at', '=', $year)->get();
                    // $actual_work_days = 0;
                    // foreach ($attendances as $attendance) {
                    //     if ($attendance->status_id == 1) {
                    //         $actual_work_days++;
                    //     }
                    // }
                    // return $actual_work_days;
                    if ($row->wd) {
                        if ($row->wd == 'month') {
                            return 30;
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('daily_work_hours', function ($row) {
                    return $row->daily_work_hours;
                })
                ->addColumn('absence_day', function ($row) use ($month, $year) {

                    if ($row->wd) {
                        if ($row->wd == 'month') {
                            $attendances = EssentialsAttendance::where('user_id', $row->user_id)->whereMonth('created_at', '=', $month)
                                ->whereYear('created_at', '=', $year)->get();
                            $actual_work_days = 0;
                            foreach ($attendances as $attendance) {
                                if ($attendance->status_id == 1) {
                                    $actual_work_days++;
                                }
                            }
                            return 30 - $actual_work_days;
                        }
                    } else {
                        return '';
                    }
                })
                ->addColumn('absence_amount', function ($row) {
                    return $row->absence_amount ?? '';
                })
                ->addColumn('over_time_h', function ($row) {
                    return $row->over_time_h ?? '';
                })
                ->addColumn('over_time', function ($row) {
                    return $row->over_time ?? '';
                })
                ->addColumn('other_deduction', function ($row) use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->other_deduction ?? '';
                })
                ->addColumn('other_addition', function ($row) use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->other_addition ?? '';
                })
                ->addColumn('cost2', function ($row) {
                    return $row->cost2 ?? '';
                })
                ->addColumn('invoice_value', function ($row) use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->invoice_value ?? '';
                })
                ->addColumn('vat', function ($row) use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->vat ?? '';
                })
                ->addColumn('total', function ($row)  use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->total ?? '';
                    return $row->total ?? '';
                })
                ->addColumn('sponser', function ($row)  use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->sponser ?? '';
                })
                ->addColumn('basic', function ($row)  use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->basic ?? '';
                })
                ->addColumn('housing', function ($row) {
                    return $row->housing ?? '';
                })
                ->addColumn('transport', function ($row) {
                    return $row->transport ?? '';
                })
                ->addColumn('other_allowances', function ($row) {
                    return $row->other_allowances ?? '';
                })
                ->addColumn('total_salary', function ($row)  use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->total_salary ?? '';
                })
                ->addColumn('deductions', function ($row) use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->deductions ?? '';
                })
                ->addColumn('additions', function ($row)  use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->additions ?? '';
                })
                ->addColumn('final_salary', function ($row) use ($month, $year) {
                    return $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->orderBy('id', 'desc')->first()?->final_salary ?? '';
                })->rawColumns([
                    'name',
                    'eqama_number',
                    'location',
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
                    'final_salary'
                ])->make(true);
        }

        return view('custom_views.agents.agent_time_sheet.index');
    }



    public function timeSheet($id)
    {
        $user = User::with('country')->where('id', $id)->select(
            DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),' - ',COALESCE(id_proof_number,'')) as full_name"),
            'users.*'
        )
            ->get()[0];

        $currentDateTime = Carbon::now('Asia/Riyadh');
        $month = $currentDateTime->month;
        $year = $currentDateTime->year;
        $start_of_month = $currentDateTime->copy()->startOfMonth();
        $end_of_month = $currentDateTime->copy()->endOfMonth();

        $attendances = EssentialsAttendance::where('user_id', $user->id)->whereMonth('created_at', '=', $month)
            ->whereYear('created_at', '=', $year)->get();
        $work_days = 0;
        $actual_work_days = 0;
        $late_days = 0;
        $out_of_site_days = 0;
        $absence_days = 0;
        foreach ($attendances as $attendance) {
            if ($attendance->status_id == 1) {
                $actual_work_days++;
            } else if ($attendance->status_id == 2) {
                $late_days++;
            } else if ($attendance->status_id == 3) {
                $out_of_site_days++;
            }
        }

        $leaves = FollowupWorkerRequest::where('worker_id', $id)
            ->where('type', 'leavesAndDepartures')
            ->whereDate('start_date', '>=', $start_of_month)
            ->whereDate('end_date', '<=', $end_of_month)->where('status', 'approved')->get();

        $leave_days = 0;
        $days_in_a_month = Carbon::parse($start_of_month)->daysInMonth;
        foreach ($leaves as $key => $leave) {
            $start_date = Carbon::parse($leave->start_date);
            $end_date = Carbon::parse($leave->end_date);

            $diff = $start_date->diffInDays($end_date);
            $diff += 1;
            $leave_days += $diff;
        }
        $attendance = (object)[
            'work_days' => $work_days,
            'actual_work_days' => $actual_work_days,
            'late_days' => $late_days,
            'out_of_site_days' => $out_of_site_days,
            'absence_days' => $absence_days,
            'leave_days' => $leave_days,
        ];

        $query = EssentialsAllowanceAndDeduction::join('essentials_user_allowance_and_deductions as euad', 'euad.allowance_deduction_id', '=', 'essentials_allowances_and_deductions.id')
            ->where('euad.user_id', $id);

        $essentialsAllowance = EssentialsAllowanceAndDeduction::where('type', 'allowance')->pluck('description', 'id');
        $essentialsDeduction = EssentialsAllowanceAndDeduction::where('type', 'deduction')->pluck('description', 'id');
        $essentialsUserAllowancesAndDeduction = EssentialsUserAllowancesAndDeduction::with('essentialsAllowanceAndDeduction')->where('user_id', $id);

        //Filter if applicable one
        if (!empty($start_date) && !empty($end_date)) {
            $essentialsUserAllowancesAndDeduction->where(function ($q) use ($start_date, $end_date) {
                $q->whereNull('applicable_date')
                    ->orWhereBetween('applicable_date', [$start_date, $end_date]);
            });
        }
        $allowances_and_deductions = $essentialsUserAllowancesAndDeduction->get();
        $allowances = [];
        $deductions = [];
        foreach ($allowances_and_deductions as $ad) {
            if ($ad->essentialsAllowanceAndDeduction->type == 'allowance') {
                $allowances[] = $ad;
            } else {
                $deductions[] = $ad;
            }
        }

        $allowances_and_deductions = (object)[
            'allowances' => $allowances,
            'deductions' => $deductions,
        ];


        // return $allowances_and_deductions;


        $business_id = User::where('id', $id)->first()->business_id;
        // $employee_ids = request()->input('employee_ids');
        // $month_year_arr = explode('/', request()->input('month_year'));
        $employee_ids = [$id];
        $location_id = request()->get('primary_work_location');
        $currentDateTime = Carbon::now('Asia/Riyadh');

        $month = $currentDateTime->month;
        $year = $currentDateTime->year;

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

            //initialize required data
            $start_date = $transaction_date;
            $end_date = Carbon::parse($start_date)->lastOfMonth();
            $month_name = $end_date->format('F');

            $employees = User::where('business_id', $business_id)
                ->find($add_payroll_for);

            $payrolls = [];
            foreach ($employees as $employee) {

                //get employee info
                $payrolls[$employee->id]['name'] = $employee->user_full_name;
                $payrolls[$employee->id]['essentials_salary'] = $employee->essentials_salary;
                $payrolls[$employee->id]['essentials_pay_period'] = $employee->essentials_pay_period;
                $payrolls[$employee->id]['total_leaves'] = $this->essentialsUtil->getTotalLeavesForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date->format('Y-m-d'));
                $payrolls[$employee->id]['total_days_worked'] = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date);

                //get total work duration of employee(attendance)
                $payrolls[$employee->id]['total_work_duration'] = $this->essentialsUtil->getTotalWorkDuration('hour', $employee->id, $business_id, $start_date, $end_date->format('Y-m-d'));

                //get total earned commission for employee
                $business_details = $this->businessUtil->getDetails($business_id);
                $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

                $commsn_calculation_type = empty($pos_settings['cmmsn_calculation_type']) || $pos_settings['cmmsn_calculation_type'] == 'invoice_value' ? 'invoice_value' : $pos_settings['cmmsn_calculation_type'];

                $total_commission = 0;
                if ($commsn_calculation_type == 'payment_received') {
                    $payment_details = $this->transactionUtil->getTotalPaymentWithCommission($business_id, $start_date, $end_date, null, $employee->id);
                    //Get Commision
                    $total_commission = $employee->cmmsn_percent * $payment_details['total_payment_with_commission'] / 100;
                } else {
                    $sell_details = $this->transactionUtil->getTotalSellCommission($business_id, $start_date, $end_date, null, $employee->id);
                    $total_commission = $employee->cmmsn_percent * $sell_details['total_sales_with_commission'] / 100;
                }

                if ($total_commission > 0) {
                    $payrolls[$employee->id]['allowances']['allowance_names'][] = __('essentials::lang.sale_commission');
                    $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $total_commission;
                    $payrolls[$employee->id]['allowances']['allowance_types'][] = 'fixed';
                    $payrolls[$employee->id]['allowances']['allowance_percents'][] = 0;
                }
                $settings = $this->essentialsUtil->getEssentialsSettings();
                //get total sales added by the employee
                $sale_totals = $this->transactionUtil->getUserTotalSales($business_id, $employee->id, $start_date, $end_date);

                $total_sales = !empty($settings['calculate_sales_target_commission_without_tax']) && $settings['calculate_sales_target_commission_without_tax'] == 1 ? $sale_totals['total_sales_without_tax'] : $sale_totals['total_sales'];

                //get sales target if exists
                $sales_target = EssentialsUserSalesTarget::where('user_id', $employee->id)
                    ->where('target_start', '<=', $total_sales)
                    ->where('target_end', '>=', $total_sales)
                    ->first();

                $total_sales_target_commission_percent = !empty($sales_target) ? $sales_target->commission_percent : 0;

                $total_sales_target_commission = $this->transactionUtil->calc_percentage($total_sales, $total_sales_target_commission_percent);

                if ($total_sales_target_commission > 0) {
                    $payrolls[$employee->id]['allowances']['allowance_names'][] = __('essentials::lang.sales_target_commission');
                    $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $total_sales_target_commission;
                    $payrolls[$employee->id]['allowances']['allowance_types'][] = 'fixed';
                    $payrolls[$employee->id]['allowances']['allowance_percents'][] = 0;
                }

                //get earnings & deductions of employee
                // $allowances_and_deductions = $this->essentialsUtil->getEmployeeAllowancesAndDeductions($business_id, $employee->id, $start_date, $end_date);
                // foreach ($allowances_and_deductions as $ad) {
                //     if ($ad->type == 'allowance') {
                //         $payrolls[$employee->id]['allowances']['allowance_names'][] = $ad->description;
                //         $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $ad->amount_type == 'fixed' ? $ad->amount : 0;
                //         $payrolls[$employee->id]['allowances']['allowance_types'][] = $ad->amount_type;
                //         $payrolls[$employee->id]['allowances']['allowance_percents'][] = $ad->amount_type == 'percent' ? $ad->amount : 0;
                //     } else {
                //         $payrolls[$employee->id]['deductions']['deduction_names'][] = $ad->description;
                //         $payrolls[$employee->id]['deductions']['deduction_amounts'][] = $ad->amount_type == 'fixed' ? $ad->amount : 0;
                //         $payrolls[$employee->id]['deductions']['deduction_types'][] = $ad->amount_type;
                //         $payrolls[$employee->id]['deductions']['deduction_percents'][] = $ad->amount_type == 'percent' ? $ad->amount : 0;
                //     }
                // }
            }

            $action = 'create';

            return view('custom_views.agents.agent_time_sheet.time_sheet')
                ->with(compact('user', 'attendance', 'allowances_and_deductions', 'essentialsAllowance', 'essentialsDeduction', 'month_name', 'transaction_date', 'year', 'payrolls', 'action', 'location'));
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

            $payroll_group['business_id'] = $business_id;
            $payroll_group['name'] = strip_tags(__('essentials::lang.payroll_for_month', ['date' => $month_name . ' ' . $year]));
            //status should be either final or draft, final for now
            //  $payroll_group['status'] = $request->input('payroll_group_status');
            $payroll_group['status'] = "final";
            $payroll_group['gross_total'] = $this->transactionUtil->num_uf($request->input('total_salary'));
            $payroll_group['created_by'] = auth()->user()->id;

            $payroll_group = PayrollGroup::create($payroll_group);



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

            $allowances_and_deductions = json_decode($request->allowances_and_deductions);

            foreach ($allowances_and_deductions->allowances as $ad) {

                $payroll['allowances']['allowance_names'][] = $ad->essentials_allowance_and_deduction->description;
                $payroll['allowances']['allowance_amounts'][] = $ad->essentials_allowance_and_deduction->amount_type == 'fixed' ? $ad->amount : 0;
                $payroll['allowances']['allowance_types'][] = $ad->essentials_allowance_and_deduction->amount_type;
                $payroll['allowances']['allowance_percents'][] = $ad->essentials_allowance_and_deduction->amount_type == 'percent' ? $ad->amount : 0;
            }
            if ($request->allowances) {
                foreach ($request->allowances as $key => $ad) {
                    $allowance = EssentialsAllowanceAndDeduction::where('id', $ad)->first();
                    $payroll['allowances']['allowance_names'][] = $allowance->description;
                    $payroll['allowances']['allowance_amounts'][] = $request->allowances_amount[$key];
                    $payroll['allowances']['allowance_types'][] = $allowance->amount_type;
                    $payroll['allowances']['allowance_percents'][] = $allowance->amount_type == 'percent' ? $request->allowances_amount[$key] : 0;
                }
            }


            foreach ($allowances_and_deductions->deductions as $de) {

                $payroll['deductions']['deduction_names'][] = $de->essentials_allowance_and_deduction->description;
                $payroll['deductions']['deduction_amounts'][] = $de->essentials_allowance_and_deduction->amount_type == 'fixed' ? $de->amount : 0;
                $payroll['deductions']['deduction_types'][] = $de->essentials_allowance_and_deduction->amount_type;
                $payroll['deductions']['deduction_percents'][] = $de->essentials_allowance_and_deduction->amount_type == 'percent' ? $de->amount : 0;
            }
            if ($request->deductions) {
                foreach ($request->deductions as $key => $de) {
                    $deduction = EssentialsAllowanceAndDeduction::where('id', $ad)->first();
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

            $transaction = Transaction::create($payroll);
            $transaction_ids[] = $transaction->id;
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


        return redirect()->route('agentTimeSheet.timeSheet', ['id' =>  $user->id])->with('status', $output);
    }

    private function getAllowanceAndDeductionJson($payroll)
    {
        if ($payroll['allowances'] ?? false && $payroll['allowances']['allowance_names'] ?? false) {
            $allowance_names = $payroll['allowances']['allowance_names'];
            $allowance_types = $payroll['allowances']['allowance_types'];
            //$allowance_percents = $payroll['allowances']['allowance_percent'];
            $allowance_names_array = [];
            $allowance_percent_array = [];
            $allowance_amounts = [];

            foreach ($payroll['allowances']['allowance_amounts'] as $key => $value) {
                if (!empty($allowance_names[$key])) {
                    $allowance_amounts[] = $this->moduleUtil->num_uf($value);
                    $allowance_names_array[] = $allowance_names[$key];
                    $allowance_percent_array[] = !empty($allowance_percents[$key]) ? $this->moduleUtil->num_uf($allowance_percents[$key]) : 0;
                }
            }
            $output['essentials_allowances'] = json_encode([
                'allowance_names' => $allowance_names_array,
                'allowance_amounts' => $allowance_amounts,
                'allowance_types' => $allowance_types,
                'allowance_percents' => $allowance_percent_array,
            ]);
        }
        if ($payroll['deductions'] ?? false && $payroll['deductions']['deduction_names'] ?? false) {
            $deduction_names = $payroll['deductions']['deduction_names'];
            $deduction_types = $payroll['deductions']['deduction_types'];
            // $deduction_percents = $payroll['deductions']['deduction_percent'];
            $deduction_names_array = [];
            $deduction_percents_array = [];
            $deduction_amounts = [];
            foreach ($payroll['deductions']['deduction_amounts'] as $key => $value) {
                if (!empty($deduction_names[$key])) {
                    $deduction_names_array[] = $deduction_names[$key];
                    $deduction_amounts[] = $this->moduleUtil->num_uf($value);
                    $deduction_percents_array[] = !empty($deduction_percents[$key]) ? $this->moduleUtil->num_uf($deduction_percents[$key]) : 0;
                }
            }
            $output['essentials_deductions'] = json_encode([
                'deduction_names' => $deduction_names_array,
                'deduction_amounts' => $deduction_amounts,
                'deduction_types' => $deduction_types,
                'deduction_percents' => $deduction_percents_array,
            ]);
        }
        return $output;
    }

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
