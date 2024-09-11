<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountType;
use App\Charts\CommonChart;
use App\Company;
use App\User;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Utils\AccountingUtil;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Illuminate\Support\Facades\Session;
use Modules\Accounting\Entities\AccountingUserAccessCompany;

class AccountingController extends Controller
{
    protected $accountingUtil;
    protected $moduleUtil;
    protected $requestUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(AccountingUtil $accountingUtil, ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->accountingUtil = $accountingUtil;
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;
    }


    public function landing()
    {
        $companies = Company::all();
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_accounting_view_companies = auth()->user()->can('accounting.view_companies') ? true : false;
        $can_access_all = auth()->user()->can('accounting.access_to_all') ? true : false;

        if (!($is_admin || $can_accounting_view_companies)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }
        if (!$is_admin && !$can_access_all) {
            $companies = Company::whereIn('id', $this->accountingUtil->allowedCompanies())->get();
        }
        $cardsOfCompanies = [];
        foreach ($companies as $company) {
            $cardsOfCompanies[] = [
                'id' => $company->id,
                'name' => $company->name,
                'link' => route('setSession', ['companyId' => $company->id]),
                // 'link' => action('\Modules\Accounting\Http\Controllers\AccountingController@dashboard'),
            ];
        }
        return view('accounting::accounting_landing_page')->with(compact('cardsOfCompanies'));
    }

    public function setSession(Request $request)
    {
        Session::put('selectedCompanyId', $request->companyId);
        return redirect()->route('accounting.dashboard');
    }

    public function getFilteredRequests($filter = null)
    {
        $can_change_status = auth()->user()->can('accounting.change_status');
        $can_return_request = auth()->user()->can('accounting.return_the_request');
        $can_show_request = auth()->user()->can('accounting.show_request');
        return $this->requestUtil->getFilteredRequests('accounting', $filter, $can_change_status, $can_return_request, $can_show_request, false, null);
    }


    /**
     * @return Factory|View|Application
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function dashboard()
    {
        $company_id = Session::get('selectedCompanyId');

        $business_id = request()->session()->get('user.business_id');

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_accounting_dashboard = auth()->user()->can('accounting.accounting_dashboard');
        $allowed_companies_ids = $this->accountingUtil->allowedCompanies();
        $can_access_company = in_array($company_id, $allowed_companies_ids);
        if (!($is_admin || ($can_accounting_dashboard &&  $can_access_company))) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $start_date = request()->get('start_date', session()->get('financial_year.start'));
        $end_date = request()->get('end_date', session()->get('financial_year.end'));
        $balance_formula = $this->accountingUtil->balanceFormula();

        $coa_overview = AccountingAccount::leftjoin(
            'accounting_accounts_transactions as AAT',
            'AAT.accounting_account_id',
            '=',
            'accounting_accounts.id'
        )
            ->where('business_id', $business_id)
            ->where('company_id', $company_id)
            ->whereDate('AAT.operation_date', '>=', $start_date)
            ->whereDate('AAT.operation_date', '<=', $end_date)
            ->select(
                DB::raw($balance_formula),
                'accounting_accounts.account_primary_type'
            )
            ->groupBy('accounting_accounts.account_primary_type')
            ->get();

        $account_types = AccountingAccountType::accounting_primary_type();

        $labels = [];
        $values = [];

        foreach ($account_types as $k =>  $v) {
            $value = 0;

            foreach ($coa_overview as $overview) {
                if ($overview->account_primary_type == $k && !empty($overview->balance)) {
                    $value = (float)$overview->balance;
                }
            }
            $values[] = abs($value);

            //Suffix CR/DR as per value
            $tmp = $v['label'];
            if ($value < 0) {
                $tmp .= (in_array($v['label'], ['Asset', 'Expenses']) ? ' (CR)' : ' (DR)');
            }
            $labels[] = $tmp;
        }

        $colors = [
            '#E75E82',
            '#37A2EC',
            '#FACD56',
            '#5CA85C',
            '#605CA8',
            '#2f7ed8',
            '#0d233a',
            '#8bbc21',
            '#910000',
            '#1aadce',
            '#492970',
            '#f28f43',
            '#77a1e5',
            '#c42525',
            '#a6c96a'
        ];
        $coa_overview_chart = new CommonChart;
        $coa_overview_chart->labels($labels)
            ->options($this->__chartOptions())
            ->dataset(__('accounting::lang.current_balance'), 'pie', $values)
            ->color($colors);

        $all_charts = [];
        foreach ($account_types as $k =>  $v) {
            $sub_types = AccountingAccountType::where('account_primary_type', $k)
                ->where(function ($q) use ($business_id) {
                    $q->whereNull('business_id')
                        ->orWhere('business_id', $business_id);
                })
                ->get();

            $balances = AccountingAccount::leftjoin(
                'accounting_accounts_transactions as AAT',
                'AAT.accounting_account_id',
                '=',
                'accounting_accounts.id'
            )
                ->where('business_id', $business_id)
                ->whereDate('AAT.operation_date', '>=', $start_date)
                ->whereDate('AAT.operation_date', '<=', $end_date)
                ->select(
                    DB::raw($balance_formula),
                    'accounting_accounts.account_sub_type_id'
                )
                ->groupBy('accounting_accounts.account_sub_type_id')
                ->get();

            $labels = [];
            $values = [];

            foreach ($sub_types as $st) {
                $labels[] = $st->account_type_name;
                $value = 0;

                foreach ($balances as $bal) {
                    if ($bal->account_sub_type_id == $st->id && !empty($bal->balance)) {
                        $value = (float)$bal->balance;
                    }
                }
                $values[] = $value;
            }
            $chart = new CommonChart;
            $chart->labels($labels)
                ->options($this->__chartOptions())
                ->dataset(__('accounting::lang.current_balance'), 'pie', $values)
                ->color($colors);

            $all_charts[$k] = $chart;
        }

        $counts =  $this->requestUtil->getCounts('accounting');
        $today_requests =   $counts->today_requests;
        $pending_requests =   $counts->pending_requests;
        $completed_requests =   $counts->completed_requests;
        $all_requests =   $counts->all_requests;
        return view('accounting::accounting.dashboard')->with(compact(
            'coa_overview_chart',
            'all_charts',
            'coa_overview',
            'account_types',
            'end_date',
            'start_date',
            'today_requests',
            'pending_requests',
            'completed_requests',
            'all_requests'
        ));
    }

    private function __chartOptions()
    {
        return [
            'plotOptions' => [
                'pie' => [
                    'allowPointSelect' => true,
                    'cursor' => 'pointer',
                    'dataLabels' => [
                        'enabled' => false
                    ],
                    'showInLegend' => true,
                ],
            ],
        ];
    }

    public function AccountsDropdown()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $company_id = Session::get('selectedCompanyId');
            $q = request()->input('q', '');
            $accounts = AccountingAccount::forDropdown($business_id, $company_id, true, $q);

            $accounts_array = [];
            foreach ($accounts as $account) {
                $accounts_array[] = [
                    'id' => $account->id,
                    'text' => $account->name . ' - <small class="text-muted">' . $account->account_primary_type . ' - ' .
                        $account->sub_type . '</small>',
                    'html' => $account->name . ' - <small class="text-muted">' . $account->account_primary_type . ' - ' .
                        $account->sub_type . '</small>'
                ];
            }

            return $accounts_array;
        }
    }

    public function primaryAccountsDropdown()
    {
        if (request()->ajax()) {
            $account_types = AccountingAccountType::accounting_primary_type();
            $accounts_array = [];

            $accounts_array[] = [
                'id' => ' ',
                'text' => '<strong>' . __('lang_v1.all') . '</strong>',
                'html' => '<strong>' . __('lang_v1.all') . '</strong>',
            ];

            foreach ($account_types as $key => $account_type) {
                $accounts_array[] = [
                    'id' => $account_type['GLC'],
                    'text' => $account_type['label'] . ' - <small class="text-muted">' . $account_type['GLC'],
                    'html' => $account_type['label'] . ' - <small class="text-muted">' . $account_type['GLC']
                ];
            }
            return $accounts_array;
        }
    }
}
