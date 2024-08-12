<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Utils\AccountingUtil;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use App\BusinessLocation;
use App\Contact;
use App\TaxRate;
use App\User;
use Illuminate\Support\Facades\Session;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
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
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');


        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_reports = auth()->user()->can('accounting.reports');
        if (!($is_admin || $can_reports)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }


        $first_account = AccountingAccount::where('business_id', $business_id)->where('company_id', $company_id)
            ->where('status', 'active')

            ->first();

        $first_contact = Contact::where('business_id', $business_id)->where('company_id', $company_id)
            ->whereIn('type', ['customer', 'converted', 'draft', 'qualified', 'supplier'])->active()->first();

        $first_employee = User::where('business_id', $business_id)->where('company_id', $company_id)
            ->whereIn('user_type', ['employee', 'manager', 'Department_head'])->where('status', 'active')->first();

        $ledger_url = null;
        if (!empty($first_account)) {
            $ledger_url = route('accounting.ledger', $first_account);
            $employees_statement_url = route('accounting.employeesStatement', $first_employee);
            $customers_suppliers_statement_url = route('accounting.customersSuppliersStatement', $first_contact);
        }

        return view('accounting::report.index')
            ->with(compact('ledger_url', 'employees_statement_url', 'customers_suppliers_statement_url'));
    }

    /**
     * Trial Balance
     * @return Response
     */
    public function trialBalance()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');



        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date =  request()->end_date;
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
                'accounting_accounts.gl_code'
            )
            ->groupBy('accounting_accounts.name')
            ->orderBy('accounting_accounts.gl_code')
            ->get();

        return view('accounting::report.trial_balance')
            ->with(compact('accounts', 'start_date', 'end_date'));
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
            $end_date =  request()->end_date;
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
                    'cc.ar_name as cost_center_name',
                    'atm.note',
                    'aat.amount',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
                    't.invoice_no',
                )
                ->whereDate('t.transaction_date', '>=', $start_date)
                ->whereDate('t.transaction_date', '<=', $end_date)
                ->groupBy(
                    'aat.operation_date',
                    'aat.sub_type',
                    'aat.type',
                    'atm.ref_no',
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
                        $description = '<b>' . __('accounting::lang.journal_entry') . '</b>';
                        $description .= '<br>' . __('purchase.ref_no') . ': ' . $row->ref_no;
                    }

                    if ($row->sub_type == 'opening_balance') {
                        $description = '<b>' . __('accounting::lang.opening_balance') . '</b>';
                    }

                    if ($row->sub_type == 'sell') {
                        $description = '<b>' . __('sale.sale') . '</b>';
                        $description .= '<br>' . __('sale.invoice_no') . ': ' . $row->invoice_no;
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
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('operation_date', function ($query, $keyword) {
                    // Assuming the keyword is in the format of "YYYY-MM-DD" or partial match
                    $query->whereRaw("DATE_FORMAT(aat.operation_date, '%m/%d/%Y') LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('cost_center_name', function ($query, $keyword) {
                    // Use the correct alias for filtering
                    $query->whereRaw("LOWER(cc.ar_name) LIKE ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['ref_no', 'credit', 'debit', 'balance'])
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

        return view('accounting::chart_of_accounts.employees-statement')
            ->with(compact('user', 'employee_dropdown', 'current_bal'));
    }

    public function customersSuppliersStatement($contact_id, Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');

        $contact = Contact::where('business_id', $business_id)->where('company_id', $company_id)
            ->whereIn('type', ['customer', 'converted', 'draft', 'qualified', 'supplier'])
            ->with(['transactions'])
            ->findorFail($contact_id);


        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start_date = request()->start_date;
            $end_date =  request()->end_date;
        } else {
            $fy = $this->businessUtil->getCurrentFinancialYear($business_id, $company_id);
            $start_date = $fy['start'];
            $end_date = $fy['end'];
        }

        if ($request->ajax()) {

            $contacts = Contact::where('contacts.business_id', $business_id)
                ->where('contacts.company_id', $company_id)
                ->where('contacts.id', $contact_id)
                ->join('transactions as t', 'contacts.id', '=', 't.contact_id')
                ->join('accounting_accounts_transactions as aat', 't.id', '=', 'aat.transaction_id')
                ->leftJoin('accounting_acc_trans_mappings as atm', 'aat.acc_trans_mapping_id', '=', 'atm.id')
                ->leftJoin('users as u', 'aat.created_by', '=', 'u.id')
                ->leftJoin('accounting_cost_centers as cc', 'aat.cost_center_id', '=', 'cc.id')
                ->select(
                    'aat.operation_date',
                    'aat.sub_type',
                    'aat.type',
                    'atm.ref_no',
                    'cc.ar_name as cost_center_name',
                    'atm.note',
                    'aat.amount',
                    DB::raw("CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
                    't.invoice_no',
                )
                ->whereDate('t.transaction_date', '>=', $start_date)
                ->whereDate('t.transaction_date', '<=', $end_date)
                ->groupBy(
                    'contacts.id',
                    'aat.operation_date',
                    'aat.sub_type',
                    'aat.type',
                    'atm.ref_no',
                    'cc.ar_name',
                    'atm.note',
                    'aat.amount',
                    't.invoice_no',
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
                        $description = '<b>' . __('accounting::lang.journal_entry') . '</b>';
                        $description .= '<br>' . __('purchase.ref_no') . ': ' . $row->ref_no;
                    }

                    if ($row->sub_type == 'opening_balance') {
                        $description = '<b>' . __('accounting::lang.opening_balance') . '</b>';
                    }

                    if ($row->sub_type == 'sell') {
                        $description = '<b>' . __('sale.sale') . '</b>';
                        $description .= '<br>' . __('sale.invoice_no') . ': ' . $row->invoice_no;
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
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('operation_date', function ($query, $keyword) {
                    // Assuming the keyword is in the format of "YYYY-MM-DD" or partial match
                    $query->whereRaw("DATE_FORMAT(aat.operation_date, '%m/%d/%Y') LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('cost_center_name', function ($query, $keyword) {
                    // Use the correct alias for filtering
                    $query->whereRaw("LOWER(cc.ar_name) LIKE ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['ref_no', 'credit', 'debit', 'balance'])
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

        return view('accounting::chart_of_accounts.customers-suppliers-statement')
            ->with(compact('contact', 'contact_dropdown', 'current_bal'));
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
            $end_date =  request()->end_date;
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

        return (object)[
            'gross_profit' => $gross_profit,
            'operation_income' => $operation_income,
            'income_before_tax' => $income_before_tax,
            'tax_amount' => $tax_amount,
            'revenue_net' => $revenue_net,
            'cost_of_revenue' => $cost_of_revenue,
            'total_expense' => $total_expense,
            'total_other_income' => $total_other_income,
            'total_other_expense' => $total_other_expense
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
            $end_date =  request()->end_date;
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
