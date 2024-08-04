<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use App\PayrollGroup;
use App\PayrollGroupUser;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsPayrollGroup;

class PayrollController extends Controller
{
    protected $moduleUtil;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function show_payrolls_checkpoint($id, $from)
    {

        if ($from == 'hr') {
            return view('essentials::payrolls_index');
        }
        if ($from == 'accountant') {
            return view('accounting::custom_views.payrolls_index');
        }
        if ($from == 'financial') {
            return view('accounting::custom_views.payrolls_index_financial');
        }
        if ($from == 'ceo') {
            return view('ceomanagment::payrolls_index');
        }
        return 'error';
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
                $payroll['status'] = $request->payroll_group_status;
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
                $payroll['payroll_group_id'] = $id;
                $transaction = Transaction::create($payroll);
                error_log($transaction->id);
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
            } else  if ($from == 'ceo') {

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
        }

        return redirect()->back()->with('status', $output);
    }
    public function payrolls_checkpoint($from = null)
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $payrollGroups = PayrollGroup::query();
        $can_clear = auth()->user()->can('essentials.confirm_payroll_checkpoint')
            || auth()->user()->can('accounting.confirm_payroll_checkpoint')
            || auth()->user()->can('accounting.confirm_payroll_checkpoint_financial')
            || auth()->user()->can('ceomanagment.confirm_payroll_checkpoint');
        $can_view = auth()->user()->can('essentials.show_payroll_checkpoint')
            || auth()->user()->can('accounting.show_payroll_checkpoint')
            || auth()->user()->can('accounting.show_payroll_checkpoint_financial')
            || auth()->user()->can('ceomanagment.show_payroll_checkpoint');
        if (request()->ajax()) {
            return DataTables::of($payrollGroups)
                ->addColumn('name', function ($row) {
                    return $row->payroll_group_name;
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
                            || ($from == 'ceo' && $row->hr_management_cleared && $row->accountant_cleared && $row->financial_management_cleared && !$row->ceo_cleared_by)
                        )
                    ) {
                        $html .= '<li><a href="' . route('payrolls_checkpoint.clear', ['id' => $row->id, 'from' => $from]) . '"><i class="fa fa-check" aria-hidden="true"></i> ' . __('lang_v1.approve') . '</a></li>';
                    }

                    $html .= '</ul></div>';
                    return $html;
                })

                ->rawColumns([
                    'name',
                    'hr_management_cleared',
                    'hr_management_cleared_by',
                    'accountant_cleared',
                    'accountant_cleared_by',
                    'financial_management_cleared',
                    'financial_management_cleared_by',
                    'ceo_cleared',
                    'ceo_cleared_by',
                    'action'
                ])
                ->make(true);
        }
        if ($from == 'hr') {
            return view('essentials::payrolls_index');
        }
        if ($from == 'accountant') {
            return view('accounting::custom_views.payrolls_index');
        }
        if ($from == 'financial') {
            return view('accounting::custom_views.payrolls_index_financial');
        }
        if ($from == 'ceo') {
            return view('ceomanagment::payrolls_index');
        }
        return 'error';
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
}
