<?php

namespace Modules\Accounting\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\TaxRate;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountType;
use Modules\Accounting\Utils\AccountingUtil;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    protected $accountingUtil;
    protected $businessUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(
        AccountingUtil $accountingUtil,
        BusinessUtil $businessUtil,
        ModuleUtil $moduleUtil
    ) {
        $this->accountingUtil = $accountingUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    // public function index()
    // {

    //     $business_id = request()->session()->get('user.business_id');
    //     $company_id = Session::get('selectedCompanyId');

    //     $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
    //     $can_reports = auth()->user()->can('accounting.reports');
    //     if (!($is_admin || $can_reports)) {
    //         return redirect()->route('home')->with('status', [
    //             'success' => false,
    //             'msg' => __('message.unauthorized'),
    //         ]);
    //     }

    //     $first_account = AccountingAccount::where('business_id', $business_id)->where('company_id', $company_id)
    //         ->where('status', 'active')

    //         ->first();

    //     $first_contact = Contact::where('business_id', $business_id)->where('company_id', $company_id)
    //         ->whereIn('type', ['customer', 'converted', 'draft', 'qualified', 'supplier'])->active()->first();

    //     $first_employee = User::where('business_id', $business_id)->where('company_id', $company_id)
    //         ->whereIn('user_type', ['employee', 'manager', 'Department_head'])->where('status', 'active')->first();

    //     $ledger_url = null;
    //     if (!empty($first_account)) {
    //         $ledger_url = route('accounting.ledger', $first_account);
    //         dd($first_employee);
    //         $employees_statement_url = route('accounting.employeesStatement', $first_employee);
    //         $customers_suppliers_statement_url = route('accounting.customersSuppliersStatement', $first_contact);
    //     }
    //     return view('accounting::report.index')
    //         ->with(compact('ledger_url', 'employees_statement_url', 'customers_suppliers_statement_url'));
    // }
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        $is_admin = auth()->user()->hasRole('Admin#1');
        $can_reports = auth()->user()->can('accounting.reports');
        if (!($is_admin || $can_reports)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $first_account = AccountingAccount::where('business_id', $business_id)
            ->where('company_id', $company_id)
            ->where('status', 'active')
            ->first();

        $first_contact = Contact::where('business_id', $business_id)
            ->where('company_id', $company_id)
            ->whereIn('type', ['customer', 'converted', 'draft', 'qualified', 'supplier'])
            ->active()
            ->first();

        $first_employee = User::where('business_id', $business_id)
            ->where('company_id', $company_id)
            ->whereIn('user_type', ['employee', 'manager', 'Department_head'])
            ->where('status', 'active')
            ->first();

        // Check if $first_employee is null
        // if (is_null($first_employee)) {
        //     return back()->with('status', [
        //         'success' => false,
        //         'msg' => __('message.no_employees_found'), // Customize your error message
        //     ]);
        // }

        $ledger_url = null;
        $ledger_url = $first_account ? route('accounting.ledger', $first_account) : null;
        $employees_statement_url = $first_employee ? route('accounting.employeesStatement', $first_employee) : null;
        $customers_suppliers_statement_url = $first_contact ? route('accounting.customersSuppliersStatement', $first_contact) : route('accounting.customersSuppliersStatement', 0);
        // dd($customers_suppliers_statement_url);
        return view('accounting::report.index')
            ->with(compact('ledger_url', 'employees_statement_url', 'customers_suppliers_statement_url'));
    }

    /**
     * Trial Balance
     * @return Response
     */
    public function trialBalance(Request $request)
    {
        try {

            $account_types = AccountingAccountType::accounting_primary_type();
            $accounts_array = [];
            foreach ($account_types as $key => $account_type) {
                $accounts_array[$key] =
                    $account_type['label'];
            }

            $business_id = request()->session()->get('user.business_id');
            $company_id = Session::get('selectedCompanyId');

            $with_zero_balances = $request->input('with_zero_balances', 0);

            $aggregated = $request->input('aggregated', 0);

            $choose_accounts_select = $request->input('choose_accounts_select');

            $level_filter = $request->input('level_filter');

            $max_levels = AccountingAccount::where('accounting_accounts.business_id', $business_id)
                ->where('accounting_accounts.company_id', $company_id)->pluck('gl_code')->toArray();

            $lengths = array_map(function ($length) {
                return str_replace(".", "", $length);
            }, $max_levels);
            if (empty($max_levels)) {
                // Redirect to the 'chart-of-accounts' route with a flash message
                return redirect()->route('chart-of-accounts.index')
                    ->with('message', 'Please create a tree account for the chart of accounts.');

            }
            $levels = strlen(max($lengths));

            $levelsArray = [];
            for ($i = 1; $i <= $levels; $i++) {
                $levelsArray[$i] = $i;
            }

            $levelsArray = [null => __('lang_v1.all')] + $levelsArray;

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start_date = request()->input('start_date');
                $end_date = request()->input('end_date');
            } else {
                $fy = $this->businessUtil->getCurrentFinancialYear($business_id, $company_id);
                $start_date = $fy['start'];
                $end_date = $fy['end'];
            }

            if (!$with_zero_balances) {
                $accounts = AccountingAccount::join(
                    'accounting_accounts_transactions as AAT',
                    'AAT.accounting_account_id',
                    '=',
                    'accounting_accounts.id'
                )
                    ->where(function ($query) use ($start_date, $end_date) {
                        $query->where(function ($query) use ($start_date, $end_date) {
                            $query->where('AAT.sub_type', '!=', 'opening_balance')
                                ->whereDate('AAT.operation_date', '>=', $start_date)
                                ->whereDate('AAT.operation_date', '<=', $end_date);
                        })
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('AAT.sub_type', 'opening_balance')
                                    ->whereYear('AAT.operation_date', '>=', date('Y', strtotime($start_date)))
                                    ->whereYear('AAT.operation_date', '<=', date('Y', strtotime($end_date)));
                            });
                    });
            } else {
                $accounts = AccountingAccount::leftJoin(
                    'accounting_accounts_transactions as AAT',
                    function ($join) use ($start_date, $end_date) {
                        $join->on('AAT.accounting_account_id', '=', 'accounting_accounts.id')
                            ->where(function ($query) use ($start_date, $end_date) {
                                $query->where('AAT.sub_type', '!=', 'opening_balance')
                                    ->whereBetween('AAT.operation_date', [$start_date, $end_date]);
                            })
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('AAT.sub_type', 'opening_balance')
                                    ->whereYear('AAT.operation_date', '>=', date('Y', strtotime($start_date)))
                                    ->whereYear('AAT.operation_date', '<=', date('Y', strtotime($end_date)));
                            });
                    }
                );
            }

            $accounts->when($choose_accounts_select, function ($query, $choose_accounts_select) {
                return $query->where(function ($query) use ($choose_accounts_select) {
                    foreach ($choose_accounts_select as $type) {
                        $query->orWhere('accounting_accounts.account_primary_type', 'like', $type . '%');
                    }
                });
            })
                ->when($level_filter, function ($query, $level_filter) {

                    return $query
                        ->whereRaw('LENGTH(REGEXP_REPLACE(accounting_accounts.gl_code, "[0-9]", "")) = ?', [$level_filter - 1])
                        ->orwhereRaw('LENGTH(REGEXP_REPLACE(accounting_accounts.gl_code, "[0-9]", "")) < ?', [$level_filter - 1]);
                })
                ->where('accounting_accounts.business_id', $business_id)
                ->where('accounting_accounts.company_id', $company_id)
                ->select(
                    DB::raw("IF($aggregated = 1, accounting_accounts.account_primary_type, accounting_accounts.name) as name"),
                    DB::raw("SUM(IF(AAT.type = 'credit' AND AAT.sub_type != 'opening_balance', AAT.amount, 0)) as credit_balance"),
                    DB::raw("SUM(IF(AAT.type = 'debit' AND AAT.sub_type != 'opening_balance', AAT.amount, 0)) as debit_balance"),
                    DB::raw("IFNULL((SELECT AAT.amount FROM accounting_accounts_transactions as AAT
            WHERE AAT.accounting_account_id = accounting_accounts.id
            AND AAT.sub_type = 'opening_balance'
            AND AAT.type = 'credit'
            ORDER BY AAT.operation_date ASC
            LIMIT 1), 0) as credit_opening_balance"),
                    DB::raw("IFNULL((SELECT AAT.amount FROM accounting_accounts_transactions as AAT
            WHERE AAT.accounting_account_id = accounting_accounts.id
            AND AAT.sub_type = 'opening_balance'
            AND AAT.type = 'debit'
            ORDER BY AAT.operation_date ASC LIMIT 1), 0) as debit_opening_balance"),
                    'AAT.sub_type as sub_type',
                    'AAT.type as type',
                    'accounting_accounts.gl_code',
                    'accounting_accounts.id'
                )
                /* ->when($level_filter, function ($query, $level_filter) {
                return $query->havingRaw('code_length <= ?', [$level_filter - 1]);
                }) */
                ->groupBy(
                    'name',
                )
                ->orderBy('accounting_accounts.gl_code');

            if ($aggregated) {
                $aggregatedAccounts = [];
                foreach ($accounts->get() as $account) {

                    $groupKey = $account->name;
                    if (!isset($aggregatedAccounts[$groupKey])) {
                        $aggregatedAccounts[$groupKey] = (object) [
                            'name' => Lang::has('accounting::lang.' . $groupKey) ? __('accounting::lang.' . $groupKey) : $groupKey,
                            'gl_code' => $account->gl_code[0],
                            'credit_balance' => 0,
                            'debit_balance' => 0,
                            'credit_opening_balance' => 0,
                            'debit_opening_balance' => 0,
                        ];
                    }
                    $aggregatedAccounts[$groupKey]->credit_balance += $account->credit_balance;
                    $aggregatedAccounts[$groupKey]->debit_balance += $account->debit_balance;
                    $aggregatedAccounts[$groupKey]->credit_opening_balance += $account->credit_opening_balance;
                    $aggregatedAccounts[$groupKey]->debit_opening_balance += $account->debit_opening_balance;
                }
                $accounts = $aggregatedAccounts;
            }

            if (request()->ajax()) {

                $totalDebitOpeningBalance = 0;
                $totalCreditOpeningBalance = 0;
                $totalClosingDebitBalance = 0;
                $totalClosingCreditBalance = 0;
                $totalDebitBalance = 0;
                $totalCreditBalance = 0;

                foreach ($aggregated ? $accounts : $accounts->get() as $account) {
                    $totalDebitOpeningBalance += $account->debit_opening_balance;
                    $totalCreditOpeningBalance += $account->credit_opening_balance;
                    $totalDebitBalance += $account->debit_balance;
                    $totalCreditBalance += $account->credit_balance;

                    $closing_balance = $this->calculateClosingBalance($account);
                    $totalClosingDebitBalance += $closing_balance['closing_debit_balance'];
                    $totalClosingCreditBalance += $closing_balance['closing_credit_balance'];
                }

                return DataTables::of($accounts)
                    ->editColumn('gl_code', function ($account) {
                        return $account->gl_code;
                    })
                    ->editColumn('name', function ($account) {
                        return $account->name;
                    })
                    ->editColumn('debit_opening_balance', function ($account) {
                        return $account->debit_opening_balance;
                    })
                    ->editColumn('credit_opening_balance', function ($account) {
                        return $account->credit_opening_balance;
                    })
                    ->editColumn('debit_balance', function ($account) {
                        return $account->debit_balance;
                    })
                    ->editColumn('credit_balance', function ($account) {
                        return $account->credit_balance;
                    })
                    ->addColumn('closing_debit_balance', function ($account) {
                        $closing_balance = $this->calculateClosingBalance($account);
                        return $closing_balance['closing_debit_balance'];
                    })
                    ->addColumn('closing_credit_balance', function ($account) {
                        $closing_balance = $this->calculateClosingBalance($account);
                        return $closing_balance['closing_credit_balance'];
                    })
                    ->addColumn('action', function ($account) use ($aggregated) {
                        $html = ' ';
                        if (!$aggregated) {
                            $html =
                                '<div class="btn-group">
                                <button type="button" class="btn btn-info btn-xs" >' . '
                                    <a class=" btn-modal text-white" data-container="#printledger"
                                        data-href="' . action('\Modules\Accounting\Http\Controllers\CoaController@ledgerPrint', [$account->id]) . '"
                                    >
                                        ' . __("accounting::lang.account_statement") . '
                                    </a>
                                </button>
                            </div>';
                        }
                        return $html;
                    })
                    ->with([
                        'totalDebitOpeningBalance' => $totalDebitOpeningBalance,
                        'totalCreditOpeningBalance' => $totalCreditOpeningBalance,
                        'totalDebitBalance' => $totalDebitBalance,
                        'totalCreditBalance' => $totalCreditBalance,
                        'totalClosingDebitBalance' => $totalClosingDebitBalance,
                        'totalClosingCreditBalance' => $totalClosingCreditBalance,
                    ])
                    ->make(true);
            }

            return view('accounting::report.trial_balance')
                ->with(compact('levelsArray', 'accounts_array'));
        } catch (\Exception $e) {
            Log::error('Error in trialBalance method: ' . $e->getMessage());
            return redirect()->route('chart-of-accounts.index')
                ->with('message', 'Please create a tree account for the chart of accounts.');

        }
    }

    private function calculateClosingBalance($account)
    {
        $closing_debit_balance = $account->debit_opening_balance + $account->debit_balance;
        $closing_credit_balance = $account->credit_opening_balance + $account->credit_balance;
        $closing_balance = $closing_credit_balance - $closing_debit_balance;

        return [
            'closing_debit_balance' => $closing_balance < 0 ? abs($closing_balance) : 0,
            'closing_credit_balance' => $closing_balance >= 0 ? $closing_balance : 0,
        ];
    }

    public function employeesStatement($user_id, Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        $user = User::where('business_id', $business_id)->where('company_id', $company_id)
            ->whereIn('user_type', ['employee', 'manager', 'Department_head'])
            ->findorFail($user_id);

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date = request()->end_date;
        } else {
            $fy = $this->businessUtil->getCurrentFinancialYear($business_id, $company_id);
            $start_date = $fy['start'];
            $end_date = $fy['end'];
        }

        if ($request->ajax()) {

            $users = User::where('users.business_id', $business_id)
                ->where('users.company_id', $company_id)
                ->where('users.id', $user_id)
                ->join('payroll_group_users as pgu', 'pgu.user_id', '=', 'users.id')
                ->join('transactions as t', 't.payroll_group_id', '=', 'pgu.payroll_group_id')
                ->join('accounting_accounts_transactions as aat', 't.id', '=', 'aat.transaction_id')
                ->leftJoin('accounting_acc_trans_mappings as atm', 'aat.acc_trans_mapping_id', '=', 'atm.id')
                ->leftJoin('users as u', 'aat.created_by', '=', 'u.id')
                ->leftJoin('accounting_cost_centers as cc', 'aat.cost_center_id', '=', 'cc.id')
                ->select(
                    'aat.operation_date',
                    'aat.sub_type',
                    'aat.type',
                    'atm.ref_no',
                    'atm.id as atm_id',
                    'cc.ar_name as cost_center_name',
                    'atm.note',
                    'aat.amount',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
                    't.invoice_no',
                )
                ->whereDate('aat.operation_date', '>=', $start_date)
                ->whereDate('aat.operation_date', '<=', $end_date)
                ->groupBy(
                    'aat.operation_date',
                    'aat.sub_type',
                    'aat.type',
                    'atm.ref_no',
                    'atm_id',
                    'cc.ar_name',
                    'atm.note',
                    'aat.amount',
                    't.invoice_no',
                    'u.first_name',
                    'u.last_name',
                );

            return DataTables::of($users)
                ->editColumn('operation_date', function ($row) {
                    return $this->accountingUtil->format_date($row->operation_date, true);
                })
                ->editColumn('ref_no', function ($row) {
                    $description = '';
                    if ($row->sub_type == 'journal_entry') {
                        $description = $row->ref_no;
                    }

                    if ($row->sub_type == 'sell') {
                        $description = $row->invoice_no;
                    }
                    if ($row->atm_id) {
                        $description = '<a class=" btn-modal"
                      data-container="#printJournalEntry"
                         data-href="' . action('\Modules\Accounting\Http\Controllers\JournalEntryController@print', [$row->atm_id]) . '">
                            <i class="fa fa-print" aria-hidden="true"></i>' . $description . '
                        </a>';
                    }
                    return $description;
                })
                ->addColumn('transaction', function ($row) {
                    if (Lang::has('accounting::lang.' . $row->sub_type)) {

                        $description = __('accounting::lang.' . $row->sub_type);
                    } else {
                        $description = $row->sub_type;
                    }
                    return $description;
                })
                ->addColumn('debit', function ($row) {
                    if ($row->type == 'debit') {
                        return '<span class="debit" data-orig-value="' . $row->amount . '">' . $this->accountingUtil->num_f($row->amount, true) . '</span>';
                    }
                    return '';
                })
                ->addColumn('credit', function ($row) {
                    if ($row->type == 'credit') {
                        return '<span class="credit"  data-orig-value="' . $row->amount . '">' . $this->accountingUtil->num_f($row->amount, true) . '</span>';
                    }
                    return '';
                })
                ->filterColumn('cost_center_name', function ($query, $keyword) {
                    $query->whereRaw("LOWER(cc.ar_name) LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['ref_no', 'credit', 'debit', 'balance', 'action'])
                ->make(true);
        }

        $employee_dropdown = User::employeesDropdown($business_id, $company_id);

        $current_bal = User::where('users.business_id', $business_id)
            ->where('users.company_id', $company_id)
            ->where('users.id', $user_id)
            ->join('payroll_group_users as pgu', 'pgu.user_id', '=', 'users.id')
            ->join('transactions as t', 't.payroll_group_id', '=', 'pgu.payroll_group_id')
            ->join('accounting_accounts_transactions as AAT', 't.id', '=', 'AAT.transaction_id')
            ->leftjoin(
                'accounting_accounts as accounting_accounts',
                'AAT.accounting_account_id',
                '=',
                'accounting_accounts.id'
            )
            ->select([DB::raw($this->accountingUtil->balanceFormula())]);

        $current_bal = $current_bal?->first()->balance;

        $total_debit_bal = User::where('users.business_id', $business_id)
            ->where('users.company_id', $company_id)
            ->where('users.id', $user_id)
            ->join('payroll_group_users as pgu', 'pgu.user_id', '=', 'users.id')
            ->join('transactions as t', 't.payroll_group_id', '=', 'pgu.payroll_group_id')
            ->join('accounting_accounts_transactions as AAT', 't.id', '=', 'AAT.transaction_id')
            ->leftjoin(
                'accounting_accounts as accounting_accounts',
                'AAT.accounting_account_id',
                '=',
                'accounting_accounts.id'
            )
            ->select(DB::raw("SUM(IF((AAT.type = 'debit'), AAT.amount, 0)) as balance"))
            ->first();
        $total_debit_bal = $total_debit_bal->balance;

        $total_credit_bal = User::where('users.business_id', $business_id)
            ->where('users.company_id', $company_id)
            ->where('users.id', $user_id)
            ->join('payroll_group_users as pgu', 'pgu.user_id', '=', 'users.id')
            ->join('transactions as t', 't.payroll_group_id', '=', 'pgu.payroll_group_id')
            ->join('accounting_accounts_transactions as AAT', 't.id', '=', 'AAT.transaction_id')
            ->leftjoin(
                'accounting_accounts as accounting_accounts',
                'AAT.accounting_account_id',
                '=',
                'accounting_accounts.id'
            )
            ->select(DB::raw("SUM(IF((AAT.type = 'credit'), AAT.amount, 0)) as balance"))
            ->first();

        $total_credit_bal = $total_credit_bal->balance;

        return view('accounting::chart_of_accounts.employees-statement')
            ->with(compact('user', 'employee_dropdown', 'current_bal', 'total_debit_bal', 'total_credit_bal'));
    }

    public function customersSuppliersStatement($contact_id = null, Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        // Check if $contact_id is provided
        if ($contact_id) {
            $contact = Contact::where('business_id', $business_id)->where('company_id', $company_id)
                ->whereIn('type', ['customer', 'converted', 'draft', 'qualified', 'supplier'])
                ->with(['transactions'])
                ->findorFail($contact_id);
        } else {
            // Handle case where $contact_id is null
            $contact = null;
        }

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date = request()->end_date;
        } else {
            $fy = $this->businessUtil->getCurrentFinancialYear($business_id, $company_id);
            $start_date = $fy['start'];
            $end_date = $fy['end'];
        }

        if ($request->ajax()) {
            $contacts = Contact::where('contacts.business_id', $business_id)
                ->where('contacts.company_id', $company_id)
                ->where('contacts.id', $contact_id)
                ->join('accounting_accounts_transactions as aat', function ($join) {
                    $join->on('contacts.id', '=', 'aat.partner_id');
                })
                ->leftJoin('accounting_acc_trans_mappings as atm', 'aat.acc_trans_mapping_id', '=', 'atm.id')
                ->leftJoin('users as u', 'aat.created_by', '=', 'u.id')
                ->leftJoin('accounting_cost_centers as cc', 'aat.cost_center_id', '=', 'cc.id')
                ->select(
                    'aat.operation_date',
                    'aat.sub_type',
                    'aat.type',
                    'atm.ref_no',
                    'atm.id as atm_id',
                    'cc.ar_name as cost_center_name',
                    'atm.note',
                    'aat.amount',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
                )
                ->whereDate('aat.operation_date', '>=', $start_date)
                ->whereDate('aat.operation_date', '<=', $end_date)
                ->groupBy(
                    'contacts.id',
                    'aat.operation_date',
                    'aat.sub_type',
                    'aat.type',
                    'atm_id',
                    'atm.ref_no',
                    'cc.ar_name',
                    'atm.note',
                    'aat.amount',
                    'u.first_name',
                    'u.last_name',
                );
            return DataTables::of($contacts)
                ->editColumn('operation_date', function ($row) {
                    return $this->accountingUtil->format_date($row->operation_date, true);
                })
                ->editColumn('ref_no', function ($row) {
                    $description = '';
                    if ($row->sub_type == 'journal_entry') {
                        $description = $row->ref_no;
                    }

                    if ($row->sub_type == 'sell') {
                        $description = $row->invoice_no;
                    }
                    if ($row->atm_id) {
                        $description = '<a class=" btn-modal"
                      data-container="#printJournalEntry"
                         data-href="' . action('\Modules\Accounting\Http\Controllers\JournalEntryController@print', [$row->atm_id]) . '">
                            <i class="fa fa-print" aria-hidden="true"></i>' . $description . '
                        </a>';
                    }
                    return $description;
                })
                ->addColumn('transaction', function ($row) {
                    if (Lang::has('accounting::lang.' . $row->sub_type)) {

                        $description = __('accounting::lang.' . $row->sub_type);
                    } else {
                        $description = $row->sub_type;
                    }
                    return $description;
                })
                ->addColumn('debit', function ($row) {
                    if ($row->type == 'debit') {
                        return '<span class="debit" data-orig-value="' . $row->amount . '">' . $this->accountingUtil->num_f($row->amount, true) . '</span>';
                    }
                    return '';
                })
                ->addColumn('credit', function ($row) {
                    if ($row->type == 'credit') {
                        return '<span class="credit"  data-orig-value="' . $row->amount . '">' . $this->accountingUtil->num_f($row->amount, true) . '</span>';
                    }
                    return '';
                })
                ->filterColumn('cost_center_name', function ($query, $keyword) {
                    $query->whereRaw("LOWER(cc.ar_name) LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['ref_no', 'credit', 'debit', 'balance', 'action'])
                ->make(true);
        }

        $contact_dropdown = Contact::customersSuppliersDropdown($business_id, $company_id);

        $current_bal = Contact::where('contacts.business_id', $business_id)
            ->where('contacts.company_id', $company_id)
            ->where('contacts.id', $contact_id)
            ->join('transactions as t', 'contacts.id', '=', 't.contact_id')
            ->join('accounting_accounts_transactions as AAT', 't.id', '=', 'AAT.transaction_id')
            ->leftjoin(
                'accounting_accounts as accounting_accounts',
                'AAT.accounting_account_id',
                '=',
                'accounting_accounts.id'
            )
            ->select([DB::raw($this->accountingUtil->balanceFormula())]);

        $current_bal = $current_bal?->first()->balance;

        $total_debit_bal = Contact::join('transactions as t', 'contacts.id', '=', 't.contact_id')
            ->join('accounting_accounts_transactions as AAT', 't.id', '=', 'AAT.transaction_id')
            ->leftjoin(
                'accounting_accounts as accounting_accounts',
                'AAT.accounting_account_id',
                '=',
                'accounting_accounts.id'
            )
            ->where('contacts.business_id', $business_id)
            ->where('contacts.company_id', $company_id)
            ->where('contacts.id', $contact_id)
            ->select(DB::raw("SUM(IF((AAT.type = 'debit'), AAT.amount, 0)) as balance"))
            ->first();
        $total_debit_bal = $total_debit_bal->balance;

        $total_credit_bal = Contact::join('transactions as t', 'contacts.id', '=', 't.contact_id')
            ->join('accounting_accounts_transactions as AAT', 't.id', '=', 'AAT.transaction_id')
            ->leftjoin(
                'accounting_accounts as accounting_accounts',
                'AAT.accounting_account_id',
                '=',
                'accounting_accounts.id'
            )
            ->where('contacts.business_id', $business_id)
            ->where('contacts.company_id', $company_id)
            ->where('contacts.id', $contact_id)
            ->select(DB::raw("SUM(IF((AAT.type = 'credit'), AAT.amount, 0)) as balance"))
            ->first();

        $total_credit_bal = $total_credit_bal->balance;

        return view('accounting::chart_of_accounts.customers-suppliers-statement')
            ->with(compact('contact', 'contact_dropdown', 'current_bal', 'total_debit_bal', 'total_credit_bal'));
    }

    /**
     * Income Statement
     * @return Response
     */
    public function incomeStatement()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date = request()->end_date;
        } else {
            $fy = $this->businessUtil->getCurrentFinancialYear($business_id, $company_id);
            $start_date = $fy['start'];
            $end_date = $fy['end'];
        }
        $accounts = AccountingAccount::join(
            'accounting_accounts_transactions as AAT',
            'AAT.accounting_account_id',
            '=',
            'accounting_accounts.id'
        )
            ->where('business_id', $business_id)->where('company_id', $company_id)
            ->where(function ($qu) {
                $qu->where('accounting_accounts.account_primary_type', '!=', 'asset')
                    ->where('accounting_accounts.account_primary_type', '!=', 'commitments')
                    ->where('accounting_accounts.account_primary_type', '!=', 'property_rights');
            })
            ->where(function ($query) use ($start_date, $end_date) {
                $query->where(function ($query) use ($start_date, $end_date) {
                    $query->where('AAT.sub_type', '!=', 'opening_balance')
                        ->whereDate('AAT.operation_date', '>=', $start_date)
                        ->whereDate('AAT.operation_date', '<=', $end_date);
                })
                    ->orWhere(function ($query) use ($start_date, $end_date) {
                        $query->where('AAT.sub_type', 'opening_balance')
                            ->whereYear('AAT.operation_date', '>=', date('Y', strtotime($start_date)))
                            ->whereYear('AAT.operation_date', '<=', date('Y', strtotime($end_date)));
                    });
            })
            ->select(
                DB::raw("SUM(IF(AAT.type = 'credit' AND AAT.sub_type != 'opening_balance', AAT.amount, 0)) as credit_balance"),
                DB::raw("SUM(IF(AAT.type = 'debit' AND AAT.sub_type != 'opening_balance', AAT.amount, 0)) as debit_balance"),
                DB::raw("IFNULL(
                    (SELECT AAT2.amount
                     FROM accounting_accounts_transactions as AAT2
                     WHERE AAT2.accounting_account_id = accounting_accounts.id
                     AND AAT2.sub_type = 'opening_balance'
                     AND AAT2.type = 'credit'
                     ORDER BY AAT2.operation_date ASC
                     LIMIT 1),
                    0) as credit_opening_balance"),
                DB::raw("IFNULL(
                    (SELECT AAT2.amount
                     FROM accounting_accounts_transactions as AAT2
                     WHERE AAT2.accounting_account_id = accounting_accounts.id
                     AND AAT2.sub_type = 'opening_balance'
                     AND AAT2.type = 'debit'
                     ORDER BY AAT2.operation_date ASC
                     LIMIT 1),
                    0) as debit_opening_balance"),
                'accounting_accounts.name',
                'accounting_accounts.gl_code',
                'accounting_accounts.account_primary_type as acc_type'
            )

            ->groupBy('accounting_accounts.name')
            ->orderBy('accounting_accounts.gl_code')
            ->get();

        $data = $this->getIcomeStatementData($accounts);

        return view('accounting::report.income-statement')
            ->with(compact(
                'accounts',
                'start_date',
                'end_date',
                'data'
            ));
    }

    public function getIcomeStatementData($accounts)
    {
        $total_debit = 0;
        $total_credit = 0;
        $cost_of_revenue = 0;
        $total_expense = 0;
        $revenue_net = 0;
        $total_other_income = 0;
        $total_other_expense = 0;
        $total_balances = [];

        foreach ($accounts as $account) {
            if (
                str_starts_with($account->gl_code, '6.1') ||
                str_starts_with($account->gl_code, '6.2') ||
                str_starts_with($account->gl_code, '4') ||
                str_starts_with($account->gl_code, '5.1') ||
                str_starts_with($account->gl_code, '5.2')
            ) {
                $total_debit += $account->debit_balance;

                $total_credit += $account->credit_balance;

                $debit_balance = $account->debit_opening_balance + $account->debit_balance;

                $credit_balance = $account->credit_opening_balance + $account->credit_balance;

                $balance = $credit_balance - $debit_balance;

                $total_balances[$account->gl_code] = $balance;
            }
        }
        foreach ($total_balances as $key => $total_balance) {
            if (str_starts_with($key, '6.1')) {
                $revenue_net += $total_balance;
            } elseif (str_starts_with($key, '6.2')) {
                $total_other_income += $total_balance;
            } elseif (str_starts_with($key, '5.1')) {
                $total_expense += abs($total_balance);
            } elseif (str_starts_with($key, '5.2')) {
                $total_other_expense += abs($total_balance);
            } elseif (str_starts_with($key, '4')) {
                $cost_of_revenue += abs($total_balance);
            }
        }

        $gross_profit = $revenue_net - $cost_of_revenue;

        $operation_income = $gross_profit - $total_expense;

        $other = $total_other_income + $operation_income;

        $income_before_tax = $other - $total_other_expense;

        $tax = TaxRate::first()->amount;
        $tax_amount = ($tax * $income_before_tax) / 100;

        return (object) [
            'gross_profit' => $gross_profit,
            'operation_income' => $operation_income,
            'income_before_tax' => $income_before_tax,
            'tax_amount' => $tax_amount,
            'revenue_net' => $revenue_net,
            'cost_of_revenue' => $cost_of_revenue,
            'total_expense' => $total_expense,
            'total_other_income' => $total_other_income,
            'total_other_expense' => $total_other_expense,
        ];
    }

    /**
     * Trial Balance
     * @return Response
     */
    public function balanceSheet()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date = request()->end_date;
        } else {
            $fy = $this->businessUtil->getCurrentFinancialYear($business_id, $company_id);
            $start_date = $fy['start'];
            $end_date = $fy['end'];
        }

        $balance_formula = $this->accountingUtil->balanceFormula();

        $assets = AccountingAccount::join(
            'accounting_accounts_transactions as AAT',
            'AAT.accounting_account_id',
            '=',
            'accounting_accounts.id'
        )
            ->join(
                'accounting_account_types as AATP',
                'AATP.id',
                '=',
                'accounting_accounts.account_sub_type_id'
            )
            ->whereDate('AAT.operation_date', '>=', $start_date)
            ->whereDate('AAT.operation_date', '<=', $end_date)
            ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
            ->where('accounting_accounts.business_id', $business_id)
            ->where('accounting_accounts.company_id', $company_id)

            ->whereIn('accounting_accounts.account_primary_type', ['asset'])
            ->groupBy('accounting_accounts.name')
            ->get();

        $liabilities = AccountingAccount::join(
            'accounting_accounts_transactions as AAT',
            'AAT.accounting_account_id',
            '=',
            'accounting_accounts.id'
        )
            ->join(
                'accounting_account_types as AATP',
                'AATP.id',
                '=',
                'accounting_accounts.account_sub_type_id'
            )
            ->whereDate('AAT.operation_date', '>=', $start_date)
            ->whereDate('AAT.operation_date', '<=', $end_date)
            ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
            ->where('accounting_accounts.business_id', $business_id)
            ->where('accounting_accounts.company_id', $company_id)

            ->whereIn('accounting_accounts.account_primary_type', ['liability'])
            ->groupBy('accounting_accounts.name')
            ->get();

        $equities = AccountingAccount::join(
            'accounting_accounts_transactions as AAT',
            'AAT.accounting_account_id',
            '=',
            'accounting_accounts.id'
        )
            ->join(
                'accounting_account_types as AATP',
                'AATP.id',
                '=',
                'accounting_accounts.account_sub_type_id'
            )
            ->whereDate('AAT.operation_date', '>=', $start_date)
            ->whereDate('AAT.operation_date', '<=', $end_date)
            ->select(DB::raw($balance_formula), 'accounting_accounts.name', 'AATP.name as sub_type')
            ->where('accounting_accounts.business_id', $business_id)
            ->where('accounting_accounts.company_id', $company_id)

            ->whereIn('accounting_accounts.account_primary_type', ['equity'])
            ->groupBy('accounting_accounts.name')
            ->get();

        return view('accounting::report.balance_sheet')
            ->with(compact('assets', 'liabilities', 'equities', 'start_date', 'end_date'));
    }

    public function accountReceivableAgeingReport()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        $location_id = request()->input('location_id', null);

        $report_details = $this->accountingUtil->getAgeingReport($business_id, 'sell', $company_id, 'contact', $location_id);

        // $business_locations = BusinessLocation::forDropdown($business_id, true);
        $business_locations = BusinessLocation::forDropdownWithCompany($business_id, $company_id, true);
        return view('accounting::report.account_receivable_ageing_report')
            ->with(compact('report_details', 'business_locations'));
    }

    public function accountPayableAgeingReport()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        $location_id = request()->input('location_id', null);
        $report_details = $this->accountingUtil->getAgeingReport($business_id, 'purchase', $company_id, 'contact', $location_id);
        $business_locations = BusinessLocation::forDropdownWithCompany($business_id, $company_id, true);

        return view('accounting::report.account_payable_ageing_report')
            ->with(compact('report_details', 'business_locations'));
    }
}
