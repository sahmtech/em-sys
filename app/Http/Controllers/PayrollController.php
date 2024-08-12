<?php

namespace App\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use App\BusinessLocation;
use App\Company;
use Yajra\DataTables\Facades\DataTables;
use App\PayrollGroup;
use App\PayrollGroupUser;
use App\TimesheetUser;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsPayrollGroup;
use Modules\Sales\Entities\SalesProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class PayrollController extends Controller
{
    protected $moduleUtil;
    protected $commonUtil;
    protected $transactionUtil;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, Util $commonUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
    }
    public function show_payrolls_checkpoint($id, $from)
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

        $payrollGroup = PayrollGroup::findOrFail($id);
        $payrollGroupUsers = PayrollGroupUser::where('payroll_group_id', $id)->join('users as u', 'u.id', '=', 'payroll_group_users.user_id')
            ->whereIn('u.company_id',  $companies_ids)
            ->select([
                'payroll_group_users.*',
                'u.first_name',
                'u.mid_name',
                'u.last_name',
                'u.bank_details',
                'u.assigned_to',
                'u.id',
                'u.user_type',
                'u.id_proof_number',
                'u.company_id',
                'u.user_type',

            ])
            ->get();


        $user_type = $payrollGroupUsers->first()?->user_type;
        $payrollGroupUsers->each(function ($item) {
            $bankDetails = json_decode($item->bank_details, true);
            $item->bank_name = $bankDetails['bank_name'] ?? '';
            $item->branch = $bankDetails['branch'] ?? '';
            $item->iban_number = $bankDetails['iban_number'] ?? '';
            $item->account_holder_name = $bankDetails['account_holder_name'] ?? '';
            $item->account_number = $bankDetails['account_number'] ?? '';
            $item->tax_number = $bankDetails['tax_number'] ?? '';
        });
        $projects = SalesProject::pluck('name', 'id');
        $companies = Company::pluck('name', 'id');
        // return $payrollGroupUsers;

        $payrolls = $payrollGroupUsers->map(function ($user) use ($projects, $companies) {
            $salary = floatval($user->salary);
            $housing_allowance = floatval($user->housing_allowance);
            $transportation_allowance = floatval($user->transportation_allowance);
            $other_allowance = floatval($user->other_allowance);
            $violations = floatval($user->violations);
            $absence_deduction = floatval($user->absence_deduction);
            $late_deduction = floatval($user->late_deduction);
            $other_deductions = floatval($user->other_deductions);
            $loan = floatval($user->loan);
            $over_time_hours_addition = floatval($user->over_time_hours_addition);
            $additional_addition = floatval($user->additional_addition);
            $other_additions = floatval($user->other_additions);

            $total_salary = $salary + $housing_allowance + $transportation_allowance + $other_allowance;
            $total_deductions = $violations + $absence_deduction + $late_deduction + $other_deductions + $loan;
            $total_additions = $over_time_hours_addition + $additional_addition + $other_additions;
            $final_salary = $total_salary - $total_deductions + $total_additions;

            return [
                'id' => $user->user_id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'nationality' => $user->country ? $user->country->nationality : '',
                'identity_card_number' => $user->id_proof_number,
                'company' => $user->company_id ? ($companies[$user->company_id] ?? '') : '',
                'project_name' => $user->assigned_to ? $projects[$user->assigned_to] ?? '' : '',
                'region' => $user->region ?? '',
                'work_days' => $user->work_days,
                'salary' => $salary,
                'housing_allowance' => $housing_allowance,
                'transportation_allowance' => $transportation_allowance,
                'other_allowance' => $other_allowance,
                'total' => $total_salary,
                'violations' => $violations,
                'absence' => $user->absence,
                'absence_deduction' => $absence_deduction,
                'late' => $user->late,
                'late_deduction' => $late_deduction,
                'other_deductions' => $other_deductions,
                'loan' => $loan,
                'total_deduction' => $total_deductions,
                'over_time_hours' => $user->over_time_hours,
                'over_time_hours_addition' => $over_time_hours_addition,
                'additional_addition' => $additional_addition,
                'other_additions' => $other_additions,
                'total_additions' => $total_additions,
                'final_salary' => $final_salary,
                'payment_method' => $user->payment_method,
                'notes' => $user->notes,
                'timesheet_user_id' => $user->timesheet_user_id,
            ];
        });


        return view('essentials::payrolls_show', compact('payrolls', 'user_type',));
    }

    public function store_to_transaction($id)
    {

        $request = PayrollGroup::where('id', $id)->with('payrolls')->first();

        $user = User::find(auth()->user()->id);
        $business_id = $user->business_id ?? 1;
        $company_id = $user->company_id ?? 1;
        try {
            DB::beginTransaction();
            $payroll_group['business_id'] = $business_id;
            $payroll_group['company_id'] = $company_id;
            $payroll_group['name'] = $request->payroll_group_name;
            $payroll_group['status'] = $request->payroll_group_status;
            $payroll_group['gross_total'] = $request->total_payrolls;
            $payroll_group['created_by'] = auth()->user()->id;
            $payroll_group = EssentialsPayrollGroup::create($payroll_group);
            $transaction_date = Carbon::createFromFormat('m/Y', $request->transaction_date)->format('Y-m-d H:i:s');


            $payrollGroupUsers = PayrollGroupUser::where('payroll_group_id', $id)
                ->join('users as u', 'u.id', '=', 'payroll_group_users.user_id')
                ->select([
                    'payroll_group_users.*',
                    'u.user_type',
                ])->get();

            $user_type = $payrollGroupUsers->first()?->user_type;


            $total_before_tax = 0;
            $essentials_amount_per_unit_duration = 0;
            $final_total = 0;

            foreach ($payrollGroupUsers as  $payrollGroupUser) {
                $essentials_amount_per_unit_duration += $payrollGroupUser->salary;

                $final_total += $payrollGroupUser->final_salary;
            }
            $total_before_tax = $final_total;


            $payroll['transaction_date'] = $transaction_date;
            $payroll['business_id'] = $business_id;
            $payroll['created_by'] = auth()->user()->id;
            $payroll['type'] = 'payroll';
            $payroll['payment_status'] = 'due';
            $payroll['status'] = $request->payroll_group_status;
            $payroll['total_before_tax'] = $total_before_tax;
            $payroll['essentials_amount_per_unit_duration'] = $essentials_amount_per_unit_duration;
            $payroll['final_total'] = $final_total;
            $ref_count = $this->moduleUtil->setAndGetReferenceCount('payroll');
            //Generate reference number
            if (empty($payroll['ref_no'])) {
                $settings = request()->session()->get('business.essentials_settings');
                $settings = !empty($settings) ? json_decode($settings, true) : [];
                $prefix = !empty($settings['payroll_ref_no_prefix']) ? $settings['payroll_ref_no_prefix'] : '';
                $payroll['ref_no'] = $this->moduleUtil->generateReferenceNumber('payroll', $ref_count, null, $prefix);
            }
            $payroll['payroll_group_id'] = $id;
            $payroll['company_id'] =   $company_id;
            $payroll['location_id'] =   $company_id == 2 ? BusinessLocation::where('company_id', 2)?->first()?->id : BusinessLocation::where('company_id', 1)?->first()?->id;
            $transaction = Transaction::create($payroll);
            $transaction_ids[] = $transaction->id;
            $payroll_group->payrollGroupTransactions()->sync($transaction_ids);
            $util = new Util();
            $auto_migration = $util->createTransactionJournal_entry($transaction->id, $user_type);




            // //ref_no,
            // $transaction_ids = [];
            // $employees_details = $request->payrolls;
            // foreach ($employees_details as $employee_details) {
            //     error_log($employee_details['final_salary'] ?? 1263761253761);
            //     $payroll['expense_for'] = $employee_details['id'];
            //     $payroll['transaction_date'] = $transaction_date;
            //     $payroll['business_id'] = $business_id;
            //     $payroll['created_by'] = auth()->user()->id;
            //     $payroll['type'] = 'payroll';
            //     $payroll['payment_status'] = 'due';
            //     $payroll['status'] = $request->payroll_group_status;
            //     $payroll['total_before_tax'] = $employee_details['final_salary'] ?? 0;
            //     $payroll['essentials_amount_per_unit_duration'] = $employee_details['salary'];

            //     $allowances_and_deductions = $this->getAllowanceAndDeductionJson($employee_details);
            //     $payroll['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'];
            //     $payroll['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'];
            //     $payroll['final_total'] = $employee_details['final_salary'] ?? 0;
            //     //Update reference count
            //     $ref_count = $this->moduleUtil->setAndGetReferenceCount('payroll');

            //     //Generate reference number
            //     if (empty($payroll['ref_no'])) {
            //         $settings = request()->session()->get('business.essentials_settings');
            //         $settings = !empty($settings) ? json_decode($settings, true) : [];
            //         $prefix = !empty($settings['payroll_ref_no_prefix']) ? $settings['payroll_ref_no_prefix'] : '';
            //         $payroll['ref_no'] = $this->moduleUtil->generateReferenceNumber('payroll', $ref_count, null, $prefix);
            //     }
            //     unset(
            //         $payroll['allowance_names'],
            //         $payroll['allowance_types'],
            //         $payroll['allowance_percent'],
            //         $payroll['allowance_amounts'],
            //         $payroll['deduction_names'],
            //         $payroll['deduction_types'],
            //         $payroll['deduction_percent'],
            //         $payroll['deduction_amounts'],
            //         $payroll['total']
            //     );
            //     $payroll['payroll_group_id'] = $id;
            //     $transaction = Transaction::create($payroll);
            //     error_log($transaction->id);
            //     $transaction_ids[] = $transaction->id;

            //     // if ($notify_employee && $payroll_group->status == 'final') {
            //     //     $transaction->action = 'created';
            //     //     $transaction->transaction_for->notify(new PayrollNotification($transaction));
            //     // }
            // }

            // $payroll_group->payrollGroupTransactions()->sync($transaction_ids);

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
        return redirect()->back()->with('status', $output);


        // $user = User::find(auth()->user()->id);
        // $business_id = $user->business_id ?? 1;
        // $company_id = $user->company_id ?? 1;
        // try {
        //     DB::beginTransaction();
        //     $payroll_group['business_id'] = $business_id;
        //     $payroll_group['company_id'] = $company_id;
        //     $payroll_group['name'] = $request->input('payroll_group_name');
        //     $payroll_group['status'] = $request->input('payroll_group_status');
        //     $payroll_group['gross_total'] = $request->input('total_payrolls');
        //     $payroll_group['created_by'] = auth()->user()->id;
        //     $payroll_group = EssentialsPayrollGroup::create($payroll_group);
        //     $transaction_date = Carbon::createFromFormat('m/Y', $request->transaction_date)->format('Y-m-d H:i:s');

        //     //ref_no,
        //     $transaction_ids = [];
        //     $employees_details = $request->payrolls;
        //     foreach ($employees_details as $employee_details) {
        //         error_log($employee_details['final_salary'] ?? 1263761253761);
        //         $payroll['expense_for'] = $employee_details['id'];
        //         $payroll['transaction_date'] = $transaction_date;
        //         $payroll['business_id'] = $business_id;
        //         $payroll['created_by'] = auth()->user()->id;
        //         $payroll['type'] = 'payroll';
        //         $payroll['payment_status'] = 'due';
        //         $payroll['status'] = $request->input('payroll_group_status');
        //         $payroll['total_before_tax'] = $employee_details['final_salary'] ?? 0;
        //         $payroll['essentials_amount_per_unit_duration'] = $employee_details['salary'];

        //         $allowances_and_deductions = $this->getAllowanceAndDeductionJson($employee_details);
        //         $payroll['essentials_allowances'] = $allowances_and_deductions['essentials_allowances'];
        //         $payroll['essentials_deductions'] = $allowances_and_deductions['essentials_deductions'];
        //         $payroll['final_total'] = $employee_details['final_salary'] ?? 0;
        //         //Update reference count
        //         $ref_count = $this->moduleUtil->setAndGetReferenceCount('payroll');

        //         //Generate reference number
        //         if (empty($payroll['ref_no'])) {
        //             $settings = request()->session()->get('business.essentials_settings');
        //             $settings = !empty($settings) ? json_decode($settings, true) : [];
        //             $prefix = !empty($settings['payroll_ref_no_prefix']) ? $settings['payroll_ref_no_prefix'] : '';
        //             $payroll['ref_no'] = $this->moduleUtil->generateReferenceNumber('payroll', $ref_count, null, $prefix);
        //         }
        //         unset(
        //             $payroll['allowance_names'],
        //             $payroll['allowance_types'],
        //             $payroll['allowance_percent'],
        //             $payroll['allowance_amounts'],
        //             $payroll['deduction_names'],
        //             $payroll['deduction_types'],
        //             $payroll['deduction_percent'],
        //             $payroll['deduction_amounts'],
        //             $payroll['total']
        //         );

        //         $transaction = Transaction::create($payroll);
        //         $transaction_ids[] = $transaction->id;

        //         // if ($notify_employee && $payroll_group->status == 'final') {
        //         //     $transaction->action = 'created';
        //         //     $transaction->transaction_for->notify(new PayrollNotification($transaction));
        //         // }
        //     }

        //     $payroll_group->payrollGroupTransactions()->sync($transaction_ids);

        //     DB::commit();

        //     $output = [
        //         'success' => true,
        //         'msg' => __('lang_v1.added_success'),
        //     ];
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        //     error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        //     $output = [
        //         'success' => false,
        //         'msg' => __('messages.something_went_wrong'),
        //     ];
        // }
        // return redirect()->route('payrolls.index')->with('status', $output);
    }
    public function clear_payrolls_checkpoint($id, $from)
    {
        try {

            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
            if (!$is_admin) {
                $userIds = [];
                $userIds = $this->moduleUtil->applyAccessRole();
            }

            if ($from == 'hr') {
                $user =  auth()->user()->id;
                $date = Carbon::now()->timezone('Asia/Riyadh');
                $payroll_group = PayrollGroup::where('id', $id)->first();
                PayrollGroupUser::where('payroll_group_id', $payroll_group->id)->whereIn('user_id', $userIds)->update(
                    ['hr_management_cleared' => true,]
                );


                $hr_management_cleared_by = [];
                if ($payroll_group?->hr_management_cleared_by) {
                    $hr_management_cleared_by = json_decode($payroll_group->hr_management_cleared_by);
                }
                $hr_management_cleared_by[] = [
                    'user' =>  $user,
                    'date' => $date
                ];

                $hr_management_cleared = PayrollGroupUser::where('payroll_group_id', $payroll_group->id)->where('hr_management_cleared', false)->count() == 0;

                PayrollGroup::where('id', $id)->update([
                    'hr_management_cleared' =>  $hr_management_cleared,
                    'hr_management_cleared_by' => json_encode($hr_management_cleared_by),
                ]);
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.added_success'),
                ];
            } else if ($from == 'accountant') {
                $user =  auth()->user()->id;
                $date = Carbon::now()->timezone('Asia/Riyadh');
                $payroll_group = PayrollGroup::where('id', $id)->first();
                PayrollGroupUser::where('payroll_group_id', $payroll_group->id)->whereIn('user_id', $userIds)->update(
                    ['accountant_cleared' => true,]
                );

                $accountant_cleared_by = [];
                if ($payroll_group?->accountant_cleared_by) {
                    $accountant_cleared_by = json_decode($payroll_group->accountant_cleared_by);
                }
                $accountant_cleared_by[] = [
                    'user' =>  $user,
                    'date' => $date
                ];
                $accountant_cleared = PayrollGroupUser::where('payroll_group_id', $payroll_group->id)->where('accountant_cleared', false)->count() == 0;
                PayrollGroup::where('id', $id)->update([
                    'accountant_cleared' => $accountant_cleared,
                    'accountant_cleared_by' => json_encode($accountant_cleared_by),
                ]);
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.added_success'),
                ];
            } else   if ($from == 'financial') {
                $user =  auth()->user()->id;
                $date = Carbon::now()->timezone('Asia/Riyadh');
                $payroll_group = PayrollGroup::where('id', $id)->first();
                PayrollGroupUser::where('payroll_group_id', $payroll_group->id)->whereIn('user_id', $userIds)->update(
                    ['financial_management_cleared' => true,]
                );

                $financial_management_cleared_by = [];
                if ($payroll_group?->financial_management_cleared_by) {
                    $financial_management_cleared_by = json_decode($payroll_group->financial_management_cleared_by);
                }
                $financial_management_cleared_by[] = [
                    'user' =>  $user,
                    'date' => $date
                ];
                $financial_management_cleared = PayrollGroupUser::where('payroll_group_id', $payroll_group->id)->where('financial_management_cleared', false)->count() == 0;
                PayrollGroup::where('id', $id)->update([
                    'financial_management_cleared' => $financial_management_cleared,
                    'financial_management_cleared_by' => json_encode($financial_management_cleared_by),
                ]);
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.added_success'),
                ];
            } else  if ($from == 'ceo' || $from == 'generalmanagement') {

                $user =  auth()->user()->id;
                $date = Carbon::now()->timezone('Asia/Riyadh');
                $payroll_group = PayrollGroup::where('id', $id)->first();
                PayrollGroupUser::where('payroll_group_id', $payroll_group->id)->whereIn('user_id', $userIds)->update(
                    ['ceo_cleared' => true,]
                );
                $ceo_cleared_by = [];
                if ($payroll_group?->ceo_cleared_by) {
                    $ceo_cleared_by = json_decode($payroll_group->ceo_cleared_by);
                }
                $ceo_cleared_by[] = [
                    'user' =>  $user,
                    'date' => $date
                ];

                $ceo_cleared = PayrollGroupUser::where('payroll_group_id', $payroll_group->id)->where('ceo_cleared', false)->count() == 0;
                if ($ceo_cleared) {
                    $this->store_to_transaction($id);
                }
                PayrollGroup::where('id', $id)->update([
                    'ceo_cleared' => $ceo_cleared,
                    'ceo_cleared_by' => json_encode($ceo_cleared_by),
                ]);
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.added_success'),
                ];
            }
        } catch (\Exception $e) {

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
            return redirect()->back()->with('status', $output);
        }

        return redirect()->back()->with('status', $output);
    }
    public function payrolls_checkpoint($from = null)
    {
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


        $payrollGroups = PayrollGroup::whereIn('company_id', $companies_ids);
        $can_clear = auth()->user()->can('essentials.confirm_payroll_checkpoint')
            || auth()->user()->can('accounting.confirm_payroll_checkpoint')
            || auth()->user()->can('accounting.confirm_payroll_checkpoint_financial')
            || auth()->user()->can('ceomanagment.confirm_payroll_checkpoint')
            || auth()->user()->can('generalmanagement.confirm_payroll_checkpoint');
        $can_view = auth()->user()->can('essentials.show_payroll_checkpoint')
            || auth()->user()->can('accounting.show_payroll_checkpoint')
            || auth()->user()->can('accounting.show_payroll_checkpoint_financial')
            || auth()->user()->can('ceomanagment.show_payroll_checkpoint')
            || auth()->user()->can('generalmanagement.show_payroll_checkpoint')
            || $from == "none";

        $companies = Company::pluck('name', 'id');


        if ($from == 'accountant' || $from == 'financial') {
            $company_id = Session::get('selectedCompanyId');
            $payrollGroups = $payrollGroups->where('company_id', $company_id);
        }
        if (request()->ajax()) {
            return DataTables::of($payrollGroups)
                ->addColumn('name', function ($row) {
                    return $row->payroll_group_name;
                })
                ->addColumn('company', function ($row) use ($companies) {
                    return $companies[$row->company_id];
                })
                ->addColumn('projects', function ($row) {
                    $html = ' <ul role="menu">';
                    $projects = PayrollGroupUser::where('payroll_group_id', $row->id)->pluck('project_name')->unique()->toArray();
                    foreach ($projects  as   $project) {
                        $html .= '<li>' . $project . '</li>';
                    }

                    $html .= ' </ul>';
                    return  $html;
                })
                ->editColumn('hr_management_cleared', function ($row) {
                    if ($row->hr_management_cleared) {
                        return __('lang_v1.is_approved');
                    }
                    return __('lang_v1.is_not_approved');
                })
                ->editColumn('hr_management_cleared_by', function ($row) {
                    if ($row->hr_management_cleared_by) {
                        $hr_management_cleared_by = json_decode($row->hr_management_cleared_by);
                        $html = '<ul role="menu">';
                        foreach ($hr_management_cleared_by as $user_info) {
                            $user = User::where('id', $user_info->user)->first();
                            $name = ($user->first_name ?? '') . ' ' . ($user->mid_name ?? '') . ' ' . ($user->last_name ?? '') . '<br>';
                            $name .= \Carbon\Carbon::parse($user_info->date)->format('Y-m-d H:i:s');
                            $html .= '<li> ' . $name . '</li>';
                        }
                        $html .= '</ul>';
                        return    $html;
                    }
                    return null;
                })

                ////////////////////////////////////////////////////////////////////
                ->editColumn('accountant_cleared', function ($row) {
                    if ($row->accountant_cleared) {
                        return __('lang_v1.is_approved');
                    }
                    return __('lang_v1.is_not_approved');
                })
                ->editColumn('accountant_cleared_by', function ($row) {

                    if ($row->accountant_cleared_by) {
                        $accountant_cleared_by = json_decode($row->accountant_cleared_by);
                        $html = '<ul role="menu">';
                        foreach ($accountant_cleared_by as $user_info) {
                            $user = User::where('id', $user_info->user)->first();
                            $name = ($user->first_name ?? '') . ' ' . ($user->mid_name ?? '') . ' ' . ($user->last_name ?? '') . '<br>';
                            $name .=  \Carbon\Carbon::parse($user_info->date)->format('Y-m-d H:i:s');
                            $html .= '<li> ' . $name . '</li>';
                        }
                        $html .= '</ul>';
                        return    $html;
                    }
                    return null;
                })

                ////////////////////////////////////////////////////////////////////
                ->editColumn('financial_management_cleared', function ($row) {
                    if ($row->financial_management_cleared) {
                        return __('lang_v1.is_approved');
                    }
                    return __('lang_v1.is_not_approved');
                })
                ->editColumn('financial_management_cleared_by', function ($row) {
                    if ($row->financial_management_cleared_by) {
                        $financial_management_cleared_by = json_decode($row->financial_management_cleared_by);
                        $html = '<ul role="menu">';
                        foreach ($financial_management_cleared_by as $user_info) {
                            $user = User::where('id', $user_info->user)->first();
                            $name = ($user->first_name ?? '') . ' ' . ($user->mid_name ?? '') . ' ' . ($user->last_name ?? '') . '<br>';
                            $name .= \Carbon\Carbon::parse($user_info->date)->format('Y-m-d H:i:s');
                            $html .= '<li> ' . $name . '</li>';
                        }
                        $html .= '</ul>';
                        return    $html;
                    }
                    return null;
                })

                ////////////////////////////////////////////////////////////////////
                ->editColumn('ceo_cleared', function ($row) {
                    if ($row->ceo_cleared) {
                        return __('lang_v1.is_approved');
                    }
                    return __('lang_v1.is_not_approved');
                })
                ->editColumn('ceo_cleared_by', function ($row) {
                    if ($row->ceo_cleared_by) {
                        $ceo_cleared_by = json_decode($row->ceo_cleared_by);
                        $html = '<ul role="menu">';
                        foreach ($ceo_cleared_by as $user_info) {
                            $user = User::where('id', $user_info->user)->first();
                            $name = ($user->first_name ?? '') . ' ' . ($user->mid_name ?? '') . ' ' . ($user->last_name ?? '') . '<br>';
                            $name .= \Carbon\Carbon::parse($user_info->date)->format('Y-m-d H:i:s');
                            $html .= '<li> ' . $name . '</li>';
                        }
                        $html .= '</ul>';
                        return    $html;
                    }
                    return null;
                })

                ////////////////////////////////////////////////////////////////////

                ->addColumn('action', function ($row) use ($can_view, $can_clear, $is_admin, $from) {

                    $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">';
                    if ($is_admin || $can_view) {
                        $html .= '<li><a href="' . route('payrolls_checkpoint.show', ['id' => $row->id, 'from' => $from]) . '"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';
                    }


                    if (($is_admin || $can_clear) &&
                        (
                            ($from == 'hr' && !$row->hr_management_cleared)
                            || ($from == 'accountant' && $row->hr_management_cleared && !$row->accountant_cleared)
                            || ($from == 'financial' && $row->hr_management_cleared && $row->accountant_cleared && !$row->financial_management_cleared)
                            || (($from == 'ceo' || $from == 'generalmanagement') && $row->hr_management_cleared && $row->accountant_cleared && $row->financial_management_cleared && !$row->ceo_cleared_by)
                        )
                    ) {
                        $html .= '<li><a href="' . route('payrolls_checkpoint.clear', ['id' => $row->id, 'from' => $from]) . '"><i class="fa fa-check" aria-hidden="true"></i> ' . __('lang_v1.approve') . '</a></li>';
                    }

                    $html .= '</ul></div>';
                    return $html;
                })
                ->addColumn('status', function ($row) use ($from) {



                    $html = '';
                    if ($row->hr_management_cleared && $row->accountant_cleared && $row->financial_management_cleared && $row->ceo_cleared_by) {
                        $html .= '<div><a class="btn btn-xs  btn-success"   >' . __('lang_v1.paid') . '</a></div>';
                    }
                    if (($from == 'accountant')) {
                        $transaction  = Transaction::where('payroll_group_id', $row->id)?->first();
                        $status =  $transaction?->payment_status;
                        if ($status && $status == 'partial') {
                            $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info btn-xs add_payment_modal" data-id="' . $row->id . '" data-amount="' .  $transaction->final_total . '" data-toggle="modal" data-target="#createPaymentModal">' .
                                __('lang_v1.partial') .
                                '</button>
                            </div>';
                            return $html;
                        }
                        if ($status && $status != 'paid') {
                            $html = '<div class="btn-group">
                            <button type="button" class="btn btn-warning btn-xs add_payment_modal" data-id="' . $row->id . '" data-amount="' .  $transaction->final_total . '" data-toggle="modal" data-target="#createPaymentModal">' .
                                __('lang_v1.yet_to_be_paind') .
                                '</button>
                            </div>';
                            return $html;
                        }
                    }


                    return $html;
                })

                ->rawColumns([
                    'name',
                    'company',
                    'projects',
                    'hr_management_cleared',
                    'hr_management_cleared_by',
                    'accountant_cleared',
                    'accountant_cleared_by',
                    'financial_management_cleared',
                    'financial_management_cleared_by',
                    'ceo_cleared',
                    'ceo_cleared_by',
                    'status',
                    'action'
                ])
                ->make(true);
        }
        if ($from == 'hr') {
            return view('essentials::payrolls_index');
        }
        if ($from == 'accountant') {
            $payment_types = [
                'cash' => __('lang_v1.cash'),
                'card' => __('lang_v1.card'),
                'cheque' => __('lang_v1.cheque'),
                'bank_transfer' => __('lang_v1.bank_transfer'),
                'other' => __('lang_v1.other')
            ];
            return view('accounting::custom_views.payrolls_index')->with(compact('payment_types'));
        }
        if ($from == 'financial') {
            return view('accounting::custom_views.payrolls_index_financial');
        }
        if ($from == 'ceo') {
            return view('ceomanagment::payrolls_index');
        }
        if ($from == 'generalmanagement') {
            return view('generalmanagement::payrolls_index');
        }
        return 'error';
    }

    public function payrolls_list_index()
    {
        $departments = EssentialsDepartment::all()->pluck('name', 'id');

        $company_id = Session::get('selectedCompanyId');
        $payrollGroupUsers = PayrollGroupUser::with('user')->where('ceo_cleared', 1)->whereHas('user', function ($query) use ($company_id) {
            $query->where('company_id', $company_id);
        });
        if (request()->ajax()) {
            return DataTables::of($payrollGroupUsers)
                ->addColumn('name', function ($row) {
                    return $row?->name ?? '';
                })
                ->addColumn('eqama', function ($row) {
                    return $row?->identity_card_number ?? '';
                })
                ->addColumn('department', function ($row) use ($departments) {
                    $item = $departments[$row->user?->essentials_department_id] ?? '';
                    return $item;
                })
                ->addColumn('company', function ($row) {
                    return $row?->company ?? '';
                })
                ->addColumn('project', function ($row) {
                    return $row?->project_name ?? '';
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->created_at)->format('m/Y');
                })
                ->addColumn('the_total', function ($row) {
                    return $row?->final_salary ?? 0;
                })
                ->addColumn('status', function ($row) {



                    $html = '';
                    if ($row->status == 'paid') {
                        $html .= '<div>  <button type="button" class="btn btn-success btn-xs add_payment_modal" data-id="' . $row->id . '" data-amount="' .  $row->final_salary . '" data-toggle="modal" data-target="#createPaymentModal">' .
                            __('lang_v1.paid') .
                            '</button></div>';
                    } else {
                        $html .= '<div>  <button type="button" class="btn btn-warning btn-xs add_payment_modal" data-id="' . $row->id . '" data-amount="' .  $row->final_salary . '" data-toggle="modal" data-target="#createPaymentModal">' .
                            __('lang_v1.yet_to_be_paind') .
                            '</button></div>';
                    }


                    return $html;
                })




                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">';

                    $html .= '<li><a href="#" data-href="' . route('show_payroll_details', ['id' => $row->id]) . '" data-container=".view_modal" class="btn-modal"><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';



                    $html .= '</ul></div>';

                    return $html;




                    $html = '';
                    if (true) {
                        $html .= '<div><a class="btn btn-xs btn-info btn-modal" href="' . route('show_payroll_details', ['id' => $row->id]) . '" data-container=".view_modal" ><i class="fa fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></div>';
                    }


                    return $html;
                })

                ->rawColumns([
                    'name',
                    'eqama',
                    'department',
                    'company',
                    'project',
                    'date',
                    'the_total',
                    'status',
                    'action',
                ])
                ->make(true);
        }
    }

    public function create_single_payment(Request $request, $id)
    {
        
        try {

            $payroll_group_user = PayrollGroupUser::where('id', $id)
               ->first();
         

            
            $payroll_grouo = PayrollGroup::where('id', $payroll_group_user->payroll_group_id)->first();
            $transaction = Transaction::where('payroll_group_id',   $payroll_grouo->id)?->first();
            if ($transaction->payment_status && $transaction->payment_status != "paid") {

                $inputs['method'] = $request->payment_method;
                $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
                $inputs['transaction_id'] = $transaction->id;
                $inputs['amount'] = $this->transactionUtil->num_uf($payroll_group_user->final_salary);
                $inputs['created_by'] = auth()->user()->id;
                $inputs['note'] = $request->note;
               
                $payment_line =  TransactionPayment::create($inputs);
                PayrollGroupUser::where('id', $id)->update(['status' => "paid"]);
            }

            $payroll_group_users = PayrollGroupUser::where('payroll_group_id',   $payroll_grouo->id)->get();
            $paid = true;
            $partial = false;
            foreach ($payroll_group_users as $payroll_user) {
                if ($payroll_user->status != "paid") {
                    $paid = false;
                }
                if ($payroll_user->status == "paid") {
                    $partial = true;
                    
                }
            }
            Transaction::where('payroll_group_id',   $payroll_grouo->id)->update([
                'payment_status' => $paid ? 'paid' : ($partial ? 'partial' : 'due'),
            ]);

            // $transaction = Transaction::where('payroll_group_id',$payroll_grouo->id)?->first();
            $user = User::find($payroll_group_user->user_id);
            $user_type = $user?->user_type;
            $user_id = $user?->id;

            $util = new Util();
            $auto_migration = $util->createTransactionJournal_entry_single_payment($transaction->id, $user_type, $user_id);

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
        return redirect()->back()->with('status', $output);
    }

    public function create_payment(Request $request, $id)
    {
        try {
            $transaction = Transaction::where('payroll_group_id', $id)?->first();
            if ($transaction->payment_status && $transaction->payment_status != "paid") {

                $inputs['amount'] = $transaction->final_total;
                $inputs['method'] = $request->payment_method;
                $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
                $inputs['transaction_id'] = $transaction->id;
                $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
                $inputs['created_by'] = auth()->user()->id;
                $inputs['note'] = $request->note;
                $payment_line =  TransactionPayment::create($inputs);


                $transaction->update(['payment_status' => 'paid']);
            }
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
        return redirect()->back()->with('status', $output);
    }

    public function show_payroll_details($id)
    {


        $payrollGroupUser = PayrollGroupUser::with('payrollGroup', 'user')->where('id', $id)->first();

        $business_id = request()->session()->get('user.business_id');

        $departments = EssentialsDepartment::all()->pluck('name', 'id');
        $transaction_date =  Carbon::parse($payrollGroupUser->payrollGroup->payroll_date);
        $department = $payrollGroupUser?->user?->essentials_department_id ? ($departments[$payrollGroupUser->user?->essentials_department_id] ?? '') : '';
        $month_name = $transaction_date->format('F');
        $year = $transaction_date->format('Y');
        $bank_details = json_decode($payrollGroupUser->user->bank_details, true);
        $start_of_month = Carbon::parse($transaction_date);
        $end_of_month = Carbon::parse($transaction_date)->endOfMonth();
        $total_leaves = $payrollGroupUser->absence;
        $days_in_a_month = 30;
        $total_work_duration = 30;
        $total_days_present = 30 - $payrollGroupUser->absence;
        $payroll = $payrollGroupUser;
        $final_total_in_words = $this->commonUtil->numToIndianFormat(floatval($payroll->final_total));
        $allowances = [];
        $deductions = [];
        $payment_types = [];
        $designation = null;
        $location = null;

        return view('essentials::payroll.show_payroll_details')
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


        // $final_total_in_words = $this->commonUtil->numToIndianFormat($payroll->final_total);

        //         $query = Transaction::where('business_id', $business_id)
        //         ->with(['transaction_for', 'payment_lines']);

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

        //     return view('essentials::payroll.show_payroll_details')
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
    }
}