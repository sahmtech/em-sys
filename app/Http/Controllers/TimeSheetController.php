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
        $workers = User::with(['essentialsUserShifts.shift', 'transactions', 'userAllowancesAndDeductions.essentialsAllowanceAndDeduction'])->where('user_type', 'worker')
            ->whereIn('assigned_to', $projectsIds)
            ->select(
                'users.*',
                'users.id as user_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as name"),
                'users.id_proof_number as eqama_number',
                'users.essentials_pay_period',
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
                // 'users.essentials_salary as cost2',
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

        $businesses = Business::pluck('name', 'id',);
        $currentDateTime = Carbon::now('Asia/Riyadh');
        $month = $currentDateTime->month;
        $year = $currentDateTime->year;
        $start_of_month = $currentDateTime->copy()->startOfMonth();
        $end_of_month = $currentDateTime->copy()->endOfMonth();
        // $temp=$workers->first()->userAllowancesAndDeductions;
        // foreach($temp as $t){
        //     return json_decode(json_encode($t))->essentials_allowance_and_deduction;
        // }
        if (request()->ajax()) {

            return Datatables::of($workers)
                ->addColumn('name', function ($row) {
                    return $row->name ?? '';
                })
                ->addColumn('eqama_number', function ($row) {
                    return $row->eqama_number ?? '';
                })
                ->addColumn('location', function ($row) use ($businesses) {
                    if ($row->business_id) {
                        return $businesses[$row->business_id] ?? '';
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
                        return '';
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
                    $trans = $row->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first();
                    if ($trans) {
                        if ($trans->payment_status == 'paid') {
                            return 'paid';
                        }
                        return  ' <a href="' . route('agentTimeSheet.timeSheet', [$row->id]) . '" class="btn btn-xs btn-primary">' . __("agent.edit_time_sheet") . '</a>';
                    } else {
                        return  ' <a href="' . route('agentTimeSheet.timeSheet', [$row->id]) . '" class="btn btn-xs btn-success">' . __("agent.add_time_sheet") . '</a>';
                    }
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
                    'final_salary',
                    'action'
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
        $leavesTypes= RequestsType::where('type','leavesAndDepartures')->where('for','worker')->pluck('id')->toArray();
       
        $leaves = UserRequest::where('related_to', $id)
            ->whereIn('request_type_id',$leavesTypes)
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


        $work_days = 0;
        if ($user->essentials_pay_period) {
            if ($user->essentials_pay_period == 'month') {
                $work_days = Carbon::now()->daysInMonth;
            }
        }
        $actual_work_days = 0;
        $absence_days = 0;
        $late_days = 0;
        $out_of_site_days = 0;
        $absence_deductions = 0;
        if ($user->essentials_pay_period && $user->essentials_pay_period == 'month' && $user->essentials_salary) {
            $userShift = $user->essentialsUserShifts()->orderBy('id', 'desc')->first();
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
                if ($user->essentials_pay_period) {
                    if ($user->essentials_pay_period == 'month') {
                        $actual_work_days = Carbon::now()->daysInMonth - $holidayCounts;
                    }
                }
                $attendances = EssentialsAttendance::where('user_id', $user->id)->whereMonth('created_at', '=', $month)
                    ->whereYear('created_at', '=', $year)->get();
                $attended_days = 0;
                foreach ($attendances as $attendance) {
                    if ($attendance->status_id == 1) {
                        $attended_days++;
                    } else if ($attendance->status_id == 2) {
                        $late_days++;
                    } else if ($attendance->status_id == 3) {
                        $out_of_site_days++;
                    }
                }
                $holidayCounts = 0;
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now();
                while ($start->lte($end)) {
                    $dayName = strtolower($start->englishDayOfWeek);

                    if (in_array($dayName, $holidays)) {
                        $holidayCounts++;
                    }
                    $start->addDay();
                }
                $absence_days = Carbon::now()->day - $holidayCounts - $attended_days;
                $basic =  $user->essentials_salary;
                $dayPay =  $basic /  Carbon::now()->daysInMonth;

                $absence_deductions = ceil($dayPay * $absence_days);
            }
        }

        $cost2 =  $user->calculateTotalSalary() - $absence_deductions;








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
        // $payrolls = Transaction::where('business_id', $business_id)
        //     ->where('type', 'payroll')
        //     ->whereIn('expense_for', $employee_ids)
        //     ->whereDate('transaction_date', $transaction_date)
        //     ->get();

        // $add_payroll_for = $employee_ids;
        if (!empty($employee_ids)) {
            $location = BusinessLocation::where('business_id', $business_id)
                ->find($location_id);

            //initialize required data
            $start_date = $transaction_date;
            $end_date = Carbon::parse($start_date)->lastOfMonth();
            $month_name = $end_date->format('F');

            $employees = User::where('business_id', $business_id)
                ->find($employee_ids);

            // $payrolls = [];
            // foreach ($employees as $employee) {

            //     //get employee info
            //     $payrolls[$employee->id]['name'] = $employee->user_full_name;
            //     $payrolls[$employee->id]['essentials_salary'] = $employee->essentials_salary;
            //     $payrolls[$employee->id]['essentials_pay_period'] = $employee->essentials_pay_period;
            //     $payrolls[$employee->id]['total_leaves'] = $this->essentialsUtil->getTotalLeavesForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date->format('Y-m-d'));
            //     $payrolls[$employee->id]['total_days_worked'] = $this->essentialsUtil->getTotalDaysWorkedForGivenDateOfAnEmployee($business_id, $employee->id, $start_date, $end_date);

            //     //get total work duration of employee(attendance)
            //     $payrolls[$employee->id]['total_work_duration'] = $this->essentialsUtil->getTotalWorkDuration('hour', $employee->id, $business_id, $start_date, $end_date->format('Y-m-d'));

            //     //get total earned commission for employee
            //     $business_details = $this->businessUtil->getDetails($business_id);
            //     $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            //     $commsn_calculation_type = empty($pos_settings['cmmsn_calculation_type']) || $pos_settings['cmmsn_calculation_type'] == 'invoice_value' ? 'invoice_value' : $pos_settings['cmmsn_calculation_type'];

            //     $total_commission = 0;
            //     if ($commsn_calculation_type == 'payment_received') {
            //         $payment_details = $this->transactionUtil->getTotalPaymentWithCommission($business_id, $start_date, $end_date, null, $employee->id);
            //         //Get Commision
            //         $total_commission = $employee->cmmsn_percent * $payment_details['total_payment_with_commission'] / 100;
            //     } else {
            //         $sell_details = $this->transactionUtil->getTotalSellCommission($business_id, $start_date, $end_date, null, $employee->id);
            //         $total_commission = $employee->cmmsn_percent * $sell_details['total_sales_with_commission'] / 100;
            //     }

            //     if ($total_commission > 0) {
            //         $payrolls[$employee->id]['allowances']['allowance_names'][] = __('essentials::lang.sale_commission');
            //         $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $total_commission;
            //         $payrolls[$employee->id]['allowances']['allowance_types'][] = 'fixed';
            //         $payrolls[$employee->id]['allowances']['allowance_percents'][] = 0;
            //     }
            //     $settings = $this->essentialsUtil->getEssentialsSettings();
            //     //get total sales added by the employee
            //     $sale_totals = $this->transactionUtil->getUserTotalSales($business_id, $employee->id, $start_date, $end_date);

            //     $total_sales = !empty($settings['calculate_sales_target_commission_without_tax']) && $settings['calculate_sales_target_commission_without_tax'] == 1 ? $sale_totals['total_sales_without_tax'] : $sale_totals['total_sales'];

            //     //get sales target if exists
            //     $sales_target = EssentialsUserSalesTarget::where('user_id', $employee->id)
            //         ->where('target_start', '<=', $total_sales)
            //         ->where('target_end', '>=', $total_sales)
            //         ->first();

            //     $total_sales_target_commission_percent = !empty($sales_target) ? $sales_target->commission_percent : 0;

            //     $total_sales_target_commission = $this->transactionUtil->calc_percentage($total_sales, $total_sales_target_commission_percent);

            //     if ($total_sales_target_commission > 0) {
            //         $payrolls[$employee->id]['allowances']['allowance_names'][] = __('essentials::lang.sales_target_commission');
            //         $payrolls[$employee->id]['allowances']['allowance_amounts'][] = $total_sales_target_commission;
            //         $payrolls[$employee->id]['allowances']['allowance_types'][] = 'fixed';
            //         $payrolls[$employee->id]['allowances']['allowance_percents'][] = 0;
            //     }


            // }

            $action = 'create';

            $output = [
                'success' => true,
                'msg' => __('lang_v1.added_success'),
            ];

            $deductions = $user->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->essentials_deductions ?? null;

            $additions = $user->transactions()->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->where('type', 'payroll')->orderBy('id', 'desc')->first()?->essentials_allowances ?? null;


            return view('custom_views.agents.agent_time_sheet.time_sheet')
                ->with(compact('additions', 'deductions', 'cost2', 'absence_deductions', 'user', 'attendance', 'allowances_and_deductions', 'essentialsAllowance', 'essentialsDeduction', 'month_name', 'transaction_date', 'year', 'action', 'location'));
        }
        //  else {
        //     return redirect()->route('agentTimeSheet.index')
        //         ->with(
        //             'status',
        //             [
        //                 'success' => true,
        //                 'msg' => __('essentials::lang.payroll_already_added_for_given_user'),
        //             ]
        //         );
        // }
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



    private function getAllowanceAndDeductionJson($payroll)
    {
        $output = [];
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
