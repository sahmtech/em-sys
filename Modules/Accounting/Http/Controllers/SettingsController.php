<?php

namespace Modules\Accounting\Http\Controllers;

use App\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Accounting\Entities\AccountingAccountType;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingBudget;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\Accounting\Entities\AccountingMappingSetting;
use Modules\Accounting\Utils\AccountingUtil;
use App\Utils\ModuleUtil;
use App\Business;
use App\Company;
use App\System;
use App\TaxRate;
use App\Unit;
use App\User;
use DateTimeZone;
use Illuminate\Support\Facades\Session;
use Modules\Accounting\Entities\AccountingAccTransMappingSettingAutoMigration;
use Modules\Accounting\Entities\AccountingMappingSettingAutoMigration;
use Yajra\DataTables\Facades\DataTables;

class SettingsController extends Controller
{
    protected $accountingUtil;
    protected $moduleUtil;
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(AccountingUtil $accountingUtil, ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->accountingUtil = $accountingUtil;
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
        $can_settings = auth()->user()->can('accounting.settings');
        if (!($is_admin || $can_settings)) {
            return redirect()->route('home')->with('status', [
                'success' => false,
                'msg' => __('message.unauthorized'),
            ]);
        }

        $account_sub_types = AccountingAccountType::where('account_type', 'sub_type')
            ->where(function ($q) use ($business_id) {
                $q->whereNull('business_id')
                    ->orWhere('business_id', $business_id);
            })
            ->where(function ($q) use ($company_id) {
                $q->whereNull('company_id')
                    ->orWhere('company_id', $company_id);
            })
            ->get();

        $account_types = AccountingAccountType::accounting_primary_type();

        $accounting_settings = $this->accountingUtil->getAccountingSettings($business_id, $company_id);
        $bankAccounts = BankAccount::where('business_id',$business_id)->where('company_id',$company_id)->get();

        return view('accounting::settings.index')->with(compact('account_sub_types','bankAccounts', 'account_types', 'accounting_settings'));
    }

    public function resetData()
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');



        //check for admin
        if (!$this->accountingUtil->is_admin(auth()->user())) {
            //temp  abort(403, 'Unauthorized action.');
        }

        //reset logic
        AccountingBudget::join('accounting_accounts', 'accounting_budgets.accounting_account_id', '=', 'accounting_accounts.id')
            ->where('accounting_accounts.business_id', $business_id)
            ->where('accounting_accounts.company_id', $company_id)
            ->delete();

        AccountingAccountType::where('business_id', $business_id)->where('company_id', $company_id)
            ->delete();

        AccountingMappingSettingAutoMigration::where('company_id', $company_id)
        ->delete();
        
        AccountingAccTransMappingSettingAutoMigration::where('company_id', $company_id)
        ->delete();


        AccountingAccTransMapping::where('business_id', $business_id)->where('company_id', $company_id)->delete();

        AccountingAccountsTransaction::join('accounting_accounts', 'accounting_accounts_transactions.accounting_account_id', '=', 'accounting_accounts.id')
            ->where('business_id', $business_id)->where('company_id', $company_id)->delete();


        AccountingAccount::where('business_id', $business_id)->where('company_id', $company_id)->delete();

        return back();
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('accounting::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function saveSettings(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');



        try {
            $input = $request->only(['journal_entry_prefix', 'transfer_prefix', 'barcode_type', 'repair_tc_condition', 'job_sheet_prefix', 'problem_reported_by_customer', 'product_condition', 'product_configuration', 'job_sheet_custom_field_1', 'job_sheet_custom_field_2', 'job_sheet_custom_field_3', 'job_sheet_custom_field_4', 'job_sheet_custom_field_5', 'default_repair_checklist']);

            Business::where('id', $business_id)
                ->update(['accounting_settings' => json_encode($input)]);
            Company::where('id', $company_id)
                ->update(['accounting_settings' => json_encode($input)]);
            $output = [
                'success' => true,
                'msg' => __("lang_v1.updated_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('accounting::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('accounting::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    protected function autoMapping()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $can_map_transactions = auth()->user()->can('accounting.map_transactions');
        if (\request()->ajax()) {
            $settings = [
                (object)[
                    'id' => 1,
                    'sub_type' => 'sell',
                    'type' => 'credit',
                    'map_type' => 'payment_account',
                    'accounting_account_id' => 49,
                    'method' => 'cash'
                ],
                (object)[
                    'id' => 4,
                    'sub_type' => 'sell',
                    'type' => 'credit',
                    'map_type' => 'payment_account',
                    'accounting_account_id' => 49,
                    'method' => 'card'
                ],
                (object)[
                    'id' => 7,
                    'sub_type' => 'sell',
                    'type' => 'credit',
                    'map_type' => 'payment_account',
                    'accounting_account_id' => 49,
                    'method' => 'cheque'
                ],
                (object)[
                    'id' => 10,
                    'sub_type' => 'sell',
                    'type' => 'credit',
                    'map_type' => 'payment_account',
                    'accounting_account_id' => 49,
                    'method' => 'bank_transfer'
                ],
                (object)[
                    'id' => 13,
                    'sub_type' => 'sell',
                    'type' => 'credit',
                    'map_type' => 'payment_account',
                    'accounting_account_id' => 49,
                    'method' => 'other'
                ]
            ];
            $settings = collect($settings);
            return DataTables::of($settings)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin, $can_map_transactions) {
                        $html = '';
                        if ($is_admin || $can_map_transactions) {
                            $html .= '<a href="#" 
                                    data-href="' . action("\Modules\Accounting\Http\Controllers\SettingsController@map") . '?id=' . $row->id . '" class="btn-modal btn btn-warning btn-xs mt-10" data-container=".view_modal"><i class="fas fa-link"></i> ' . __('accounting::lang.edit_settings') . '</a>';
                        }
                        return $html;
                    }
                )
                ->rawColumns([
                    'action'
                ])
                ->make(true);
        }
        return '';
    }

    protected function map(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $company_id = Session::get('selectedCompanyId');




        if (request()->ajax()) {
            $elem = AccountingMappingSetting::query()->where('id', $request->id)->first();

            $existing_payment_deposit = AccountingMappingSetting::query()->where('sub_type', $elem->sub_type)
                ->whereIn('map_type', ['payment_account', 'deposit_to'])->where('method', $elem->method)
                ->get();
            return view('accounting::settings.map')
                ->with(compact('existing_payment_deposit', 'elem'));
        }
    }
    protected function saveMap(Request $request)
    {
        $elem = AccountingMappingSetting::query()->where('id', $request->id)->first();
        foreach ($request->account_id as $key => $acc) {
            AccountingMappingSetting::query()->updateOrCreate(
                [
                    'accounting_account_id' => $acc,
                    'sub_type' => $elem->sub_type,
                    'method' => $elem->method
                ],
                [
                    'type' =>  $request->type[$key],
                    'map_type' => $request->type[$key] == 'credit' ? 'payment_account' : 'deposit_to'
                ]
            );
        }
        $output = [
            'success' => 1,
            'msg' => __('lang_v1.added_success')
        ];

        return $output;
    }


    public function getBusinessSettings_accounting()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        if (!($is_admin || auth()->user()->can('business_settings.access'))) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezone_list = [];
        foreach ($timezones as $timezone) {
            $timezone_list[$timezone] = $timezone;
        }
        $company_id = Session::get('selectedCompanyId');

        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();

        // $currencies = $this->businessUtil->allCurrencies();
        $tax_details = TaxRate::forBusinessDropdown($business_id);
        $tax_rates = $tax_details['tax_rates'];

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = __('business.months.' . $i);
        }

        $accounting_methods = [
            'fifo' => __('business.fifo'),
            'lifo' => __('business.lifo'),
        ];
        $commission_agent_dropdown = [
            '' => __('lang_v1.disable'),
            'logged_in_user' => __('lang_v1.logged_in_user'),
            'user' => __('lang_v1.select_from_users_list'),
            'cmsn_agnt' => __('lang_v1.select_from_commisssion_agents_list'),
        ];

        $units_dropdown = Unit::forDropdown($business_id, true);

        $date_formats = Business::date_formats();

        $shortcuts = json_decode($business->keyboard_shortcuts, true);

        // $pos_settings = empty($business->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business->pos_settings, true);

        // $email_settings = empty($business->email_settings) ? $this->businessUtil->defaultEmailSettings() : $business->email_settings;

        // $sms_settings = empty($business->sms_settings) ? $this->businessUtil->defaultSmsSettings() : $business->sms_settings;

        $modules = $this->moduleUtil->availableModules();

        // $theme_colors = $this->theme_colors;

        // $mail_drivers = $this->mailDrivers;

        $allow_superadmin_email_settings = System::getProperty('allow_email_settings_to_businesses');

        $custom_labels = !empty($business->custom_labels) ? json_decode($business->custom_labels, true) : [];

        $common_settings = !empty($business->common_settings) ? $business->common_settings : [];

        $weighing_scale_setting = !empty($business->weighing_scale_setting) ? $business->weighing_scale_setting : [];

        $payment_types = $this->moduleUtil->payment_types(null, false, $business_id);

        $bankAccounts = BankAccount::where('business_id',$business_id)->where('company_id',$company_id)->get();

        return view('accounting::settings.businessSetting', compact('business', 'bankAccounts', 'tax_rates', 'timezone_list', 'months', 'accounting_methods', 'commission_agent_dropdown', 'units_dropdown', 'date_formats', 'shortcuts',  'modules', 'allow_superadmin_email_settings', 'custom_labels', 'common_settings', 'weighing_scale_setting', 'payment_types'));
       
    }
}