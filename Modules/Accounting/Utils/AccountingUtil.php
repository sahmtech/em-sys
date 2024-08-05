<?php

namespace Modules\Accounting\Utils;

use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use App\Business;
use App\Company;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
// use CarbonCarbon;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountType;
use Modules\Accounting\Entities\AccountingMappingSettingAutoMigration;
use Modules\Accounting\Entities\AccountingUserAccessCompany;

class AccountingUtil extends Util
{

    public function allowedCompanies()
    {
        $user_id = auth()->user()->id;
        $companies_ids = AccountingUserAccessCompany::where('user_id', $user_id)->pluck('company_id')->unique()->toArray();
        return  array_unique($companies_ids);
    }

    public function balanceFormula(
        $accounting_accounts_alias = 'accounting_accounts',
        $accounting_account_transaction_alias = 'AAT'
    ) {

        // ($accounting_accounts_alias.account_primary_type='asset' AND $accounting_account_transaction_alias.type='debit')
        // OR ($accounting_accounts_alias.account_primary_type='expense' AND $accounting_account_transaction_alias.type='debit')
        // OR ($accounting_accounts_alias.account_primary_type='income' AND $accounting_account_transaction_alias.type='credit')
        // OR ($accounting_accounts_alias.account_primary_type='equity' AND $accounting_account_transaction_alias.type='credit')
        // OR ($accounting_accounts_alias.account_primary_type='liability' AND $accounting_account_transaction_alias.type='credit'), 

        return "SUM( IF(
            ($accounting_accounts_alias.account_primary_type='asset' AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type='commitments' AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type='cost_goods_sold' AND $accounting_account_transaction_alias.type='credit')
            OR ($accounting_accounts_alias.account_primary_type='expenses' AND $accounting_account_transaction_alias.type='credit')
            OR ($accounting_accounts_alias.account_primary_type='income' AND $accounting_account_transaction_alias.type='debit')
            OR ($accounting_accounts_alias.account_primary_type='property_rights' AND $accounting_account_transaction_alias.type='debit'), 
            amount, -1*amount)) as balance";
    }

    public function getAccountingSettings($business_id, $company_id = null)
    {
        // $accounting_settings = Business::where('id', $business_id)
        //     ->value('accounting_settings');

        $accounting_settings = Company::where('id', $company_id)
            ->value('accounting_settings');
        $accounting_settings = !empty($accounting_settings) ? json_decode($accounting_settings, true) : [];

        return $accounting_settings;
    }

    public function getAgeingReport($business_id, $type, $company_id = null, $group_by, $location_id = null)
    {
        $today = Carbon::now()->format('Y-m-d');
        $query = Transaction::where('transactions.business_id', $business_id)->where('transactions.company_id', $company_id);

        if ($type == 'sell') {
            $query->where('transactions.type', 'sell')
                ->where('transactions.status', 'final');
        } elseif ($type == 'purchase') {
            $query->where('transactions.type', 'purchase')
                ->where('transactions.status', 'received');
        }

        if (!empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $dues = $query->whereNotNull('transactions.pay_term_number')
            ->whereIn('transactions.payment_status', ['partial', 'due'])
            ->join('contacts as c', 'c.id', '=', 'transactions.contact_id')
            ->select(
                DB::raw(
                    'DATEDIFF(
                            "' . $today . '", 
                            IF(
                                transactions.pay_term_type="days",
                                DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY),
                                DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH)
                            )
                        ) as diff'
                ),
                DB::raw('SUM(transactions.final_total - 
                        (SELECT COALESCE(SUM(IF(tp.is_return = 1, -1*tp.amount, tp.amount)), 0) 
                        FROM transaction_payments as tp WHERE tp.transaction_id = transactions.id) )  
                        as total_due'),

                'c.name as contact_name',
                'transactions.contact_id'
            )
            ->groupBy('transactions.id')
            ->get();

        $report_details = [];
        foreach ($dues as $due) {
            if (!isset($report_details[$due->contact_id])) {
                $report_details[$due->contact_id] = [
                    'name' => $due->contact_name,
                    '<1' => 0,
                    '1_30' => 0,
                    '31_60' => 0,
                    '61_90'  => 0,
                    '>90' => 0,
                    'total_due' => 0
                ];
            }

            if ($due->diff < 1) {
                $report_details[$due->contact_id]['<1'] += $due->total_due;
            } elseif ($due->diff >= 1 && $due->diff <= 30) {
                $report_details[$due->contact_id]['1_30'] += $due->total_due;
            } elseif ($due->diff >= 31 && $due->diff <= 60) {
                $report_details[$due->contact_id]['31_60'] += $due->total_due;
            } elseif ($due->diff >= 61 && $due->diff <= 90) {
                $report_details[$due->contact_id]['61_90'] += $due->total_due;
            } elseif ($due->diff > 90) {
                $report_details[$due->contact_id]['>90'] += $due->total_due;
            }

            $report_details[$due->contact_id]['total_due'] += $due->total_due;
        }

        return $report_details;
    }


    public static function next_GLC($parent_account_id, $business_id, $company_id = null)
    {



        // parent_account_id
        $last_parent_account = AccountingAccount::where([['parent_account_id', '=', $parent_account_id], ['company_id', '=', $company_id], ['business_id', '=', $business_id]])->latest()->first();


        if ($last_parent_account) {


            $last_code = $last_parent_account ? substr($last_parent_account->gl_code, -strlen($last_parent_account->gl_code)) : "00";

            $lastDotPosition = strrpos($last_code, '.');


            $numberAfterLastDot = substr($last_code, $lastDotPosition + 1);

            $removedNumberString = substr($last_code, 0, $lastDotPosition);
            $next_code = $removedNumberString . '.' . $numberAfterLastDot + 1;
            return $next_code;
        }

        $parent_account = AccountingAccount::find($parent_account_id);
        $last_code = substr($parent_account->gl_code, -strlen($parent_account->gl_code));


        $next_code = $last_code . '.1';


        return $next_code;
    }


    public static function  Default_Accounts($business_id, $user_id, $company_id = null)
    {
        $current_assets_id = AccountingAccountType::where('name', 'current_assets')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $non_current_assets_id = AccountingAccountType::where('name', 'non_current_assets')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $other_assets_id = AccountingAccountType::where('name', 'other_assets')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $Currentliabilities_id = AccountingAccountType::where('name', 'Current liabilities')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $non_current_liabilities_id = AccountingAccountType::where('name', 'Non-current liabilities')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $capital_id = AccountingAccountType::where('name', 'capital')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $retained_earnings_id = AccountingAccountType::where('name', 'retained earnings')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $reserves_id = AccountingAccountType::where('name', 'Reserves')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $retained_profits_and_losses_id = AccountingAccountType::where('name', 'Retained profits and losses')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $partners_are_underway_id = AccountingAccountType::where('name', 'Partners are underway')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $cost_of_goods_sold_id = AccountingAccountType::where('name', 'Cost of goods sold')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $expenses_id = AccountingAccountType::where('name', 'expenses')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $expenses_outside_the_activity_id = AccountingAccountType::where('name', 'Expenses outside the activity')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $income_id = AccountingAccountType::where('name', 'income')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;
        $income_outside_the_activity_id = AccountingAccountType::where('name', 'Income outside the activity')->where('business_id', $business_id)->where('company_id', $company_id)->first()->id;

        return   array(
            0 =>
            array(
                'name' => 'Cash and cash equivalents',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.1.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            1 =>
            array(
                'name' => 'Financial investments and accounts receivable',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.1.2',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            2 =>
            array(
                'name' => 'Inventory',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.1.3',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            3 =>
            array(
                'name' => 'accounts receivable',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.1.4',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            4 =>
            array(
                'name' => 'Prepaid expenses',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.1.5',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            5 =>
            array(
                'name' => 'Non-current assets held for sale',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.1.6',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            6 =>
            array(
                'name' => 'Long-term tangible assets',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $non_current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.2.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            7 =>
            array(
                'name' => 'Intangible assets',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $non_current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.2.2',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            8 =>
            array(
                'name' => 'Real estate investments',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $non_current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.2.3',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            9 =>
            array(
                'name' => 'Long-term financial investments',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $non_current_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.2.4',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),

            10 =>
            array(
                'name' => 'Other assets',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'asset',
                'account_sub_type_id' => $other_assets_id,
                'detail_type_id' => null,
                'gl_code' => '1.3.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            11 =>
            array(
                'name' => 'Short-term current liabilities',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'commitments',
                'account_sub_type_id' => $Currentliabilities_id,
                'detail_type_id' => null,
                'gl_code' => '2.1.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            12 =>
            array(
                'name' => 'Amounts and revenues received in advance',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'commitments',
                'account_sub_type_id' => $Currentliabilities_id,
                'detail_type_id' => null,
                'gl_code' => '2.1.2',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            13 =>
            array(
                'name' => 'Other obligations that fall due during the operating cycle',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'commitments',
                'account_sub_type_id' => $Currentliabilities_id,
                'detail_type_id' => null,
                'gl_code' => '2.1.3',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            14 =>
            array(
                'name' => 'Other obligations and accruals',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'commitments',
                'account_sub_type_id' => $Currentliabilities_id,
                'detail_type_id' => null,
                'gl_code' => '2.1.4',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            15 =>
            array(
                'name' => 'Bonds, contracts and long-term papers',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'commitments',
                'account_sub_type_id' => $non_current_liabilities_id,
                'detail_type_id' => null,
                'gl_code' => '2.2.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            16 =>
            array(
                'name' => 'Employee benefits and deferred taxes',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'commitments',
                'account_sub_type_id' => $non_current_liabilities_id,
                'detail_type_id' => null,
                'gl_code' => '2.2.2',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            17 =>
            array(
                'name' => 'capital',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'property_rights',
                'account_sub_type_id' => $capital_id,
                'detail_type_id' => null,
                'gl_code' => '3.1.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            18 =>
            array(
                'name' => 'retained earnings',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'property_rights',
                'account_sub_type_id' => $retained_earnings_id,
                'detail_type_id' => null,
                'gl_code' => '3.2.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            19 =>
            array(
                'name' => 'Reserves',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'property_rights',
                'account_sub_type_id' => $reserves_id,
                'detail_type_id' => null,
                'gl_code' => '3.3.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            20 =>
            array(
                'name' => 'Retained profits and losses',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'property_rights',
                'account_sub_type_id' => $retained_profits_and_losses_id,
                'detail_type_id' => null,
                'gl_code' => '3.4.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            21 =>
            array(
                'name' => 'Partners are underway',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'property_rights',
                'account_sub_type_id' => $partners_are_underway_id,
                'detail_type_id' => null,
                'gl_code' => '3.5.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            21 =>
            array(
                'name' => 'Cost of goods sold',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'cost_goods_sold',
                'account_sub_type_id' => $cost_of_goods_sold_id,
                'detail_type_id' => null,
                'gl_code' => '4.1.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            22 =>
            array(
                'name' => 'General and administrative expenses',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'expenses',
                'account_sub_type_id' => $expenses_id,
                'detail_type_id' => null,
                'gl_code' => '5.1.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            23 =>
            array(
                'name' => 'Marketing and sales expenses',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'expenses',
                'account_sub_type_id' => $expenses_id,
                'detail_type_id' => null,
                'gl_code' => '5.1.2',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            24 =>
            array(
                'name' => 'operating expensesd',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'expenses',
                'account_sub_type_id' => $expenses_id,
                'detail_type_id' => null,
                'gl_code' => '5.1.3',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            25 =>
            array(
                'name' => 'Asset depreciation expenses',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'expenses',
                'account_sub_type_id' => $expenses_id,
                'detail_type_id' => null,
                'gl_code' => '5.1.4',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            26 =>
            array(
                'name' => 'Expenses outside the activity',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'expenses',
                'account_sub_type_id' =>  $expenses_outside_the_activity_id,
                'detail_type_id' => null,
                'gl_code' => '5.2.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            27 =>
            array(
                'name' => 'Revenues',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'income',
                'account_sub_type_id' => $income_id,
                'detail_type_id' => null,
                'gl_code' => '6.1.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),
            28 =>
            array(
                'name' => 'Income outside the activity',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'account_primary_type' => 'income',
                'account_sub_type_id' =>  $income_outside_the_activity_id,
                'detail_type_id' => null,
                'gl_code' => '6.2.1',
                'status' => 'active',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ),

        );
    }


    public static function default_accounting_account_types($business_id, $company_id)
    {
        return  $account_sub_types = [
            // asset
            [
                // 'id'=>1,
                'name' => 'current_assets',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '1.1',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null
            ],
            [
                // 'id'=>2,
                'name' => 'non_current_assets',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '1.2',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null
            ],
            [
                // 'id'=>3,
                'name' => 'other_assets',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '1.3',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'asset',
                'parent_id' => null
            ],
            // commitments
            [
                // 'id'=>4,
                'name' => 'Current liabilities',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '2.1',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'commitments',
                'parent_id' => null
            ],
            [
                // 'id'=>5,
                'name' => 'Non-current liabilities',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '2.2',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'commitments',
                'parent_id' => null
            ],
            //property rights
            [
                // 'id'=>6,
                'name' => 'capital',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '3.1',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'property_rights',
                'parent_id' => null
            ],
            [
                // 'id'=>7,
                'name' => 'retained earnings',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '3.2',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'property_rights',
                'parent_id' => null
            ],
            [
                // 'id'=>8,
                'name' => 'Reserves',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '3.3',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'property_rights',
                'parent_id' => null
            ],
            [
                // 'id'=>9,
                'name' => 'Retained profits and losses',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '3.4',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'property_rights',
                'parent_id' => null
            ],
            [
                // 'id'=>10,
                'name' => 'Partners are underway',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '3.5',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'property_rights',
                'parent_id' => null
            ],
            // cost of goods sold
            [
                // 'id'=>11,
                'name' => 'Cost of goods sold',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '4.1',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'cost_goods_sold',
                'parent_id' => null
            ],
            // expenses
            [
                // 'id'=>12,
                'name' => 'expenses',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '5.1',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'expenses',
                'parent_id' => null
            ],
            [
                // 'id'=>13,
                'name' => 'Expenses outside the activity',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '5.2',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'expenses',
                'parent_id' => null
            ],
            // income
            [
                // 'id'=>14,
                'name' => 'income',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '6.1',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'income',
                'parent_id' => null
            ],
            [
                // 'id'=>15,
                'name' => 'Income outside the activity',
                'business_id' => $business_id,
                'company_id' => $company_id,
                'gl_code' => '6.2',
                'show_balance' => 1,
                'account_type' => 'sub_type',
                'account_primary_type' => 'income',
                'parent_id' => null
            ],

        ];
    }


    public static function account_type()
    {
        return [
            'normal' => __('accounting::lang.normal'),
            'customer_receivables_main_account' => __('accounting::lang.customer_receivables_main_account'),
            'suppliers_receivables_main_account' => __('accounting::lang.suppliers_receivables_main_account'),
            'main_account_employee_receivables' => __('accounting::lang.main_account_employee_receivables'),
            'main_account_requests_approvals' => __('accounting::lang.main_account_requests_approvals'),
            'main_account_other_receivables' => __('accounting::lang.main_account_other_receivables'),
        ];
    }

    public static function account_category()
    {
        return [
            'balance_sheet' => __('accounting::lang.balance_sheet'),
            'income_list' => __('accounting::lang.income_list'),
            'Boxes' => __('accounting::lang.Boxes'),
            'Banks' => __('accounting::lang.Banks'),
            'Cheques' => __('accounting::lang.Cheques'),
            'general' => __('accounting::lang.general'),
            'expenses' => __('accounting::lang.expenses'),
            'Revenues' => __('accounting::lang.Revenues'),
            'Fixed assets' => __('accounting::lang.Fixed assets'),
            'Receivables' => __('accounting::lang.Receivables'),
            'Liabilities' => __('accounting::lang.Liabilities'),
            'taxes' => __('accounting::lang.taxes'),
            'Past due checks' => __('accounting::lang.Past due checks'),
            'Warehouses' => __('accounting::lang.Warehouses'),
            'Revenues received in advance' => __('accounting::lang.Revenues received in advance'),
            'Prepaid expenses' => __('accounting::lang.Prepaid expenses'),

        ];
    }

    public function deflute_auto_migration($request)
    {
        $user_id = request()->session()->get('user.id');
        $business_id = request()->session()->get('user.business_id');
        $names = [
            'sales_bill',
            'sell_return_bill',
            'opening_stock',
            'purchase_bill',
            'purchase_return_bill',
            'expens_bill',
            'sell_transfer',
            'purchase_transfer',
            'payroll',

        ];
        $types = [
            'sell',
            'sell_return',
            'opening_stock',
            'purchase',
            'purchase_return',
            'expense',
            'sell_transfer',
            'purchase_transfer',
            'payroll',

        ];
        $payment_status = [
            'paid',
            'due',
            'partial',
        ];

        $methods = [
            'cash',
            'card',
            'bank_transfer',
            'cheque',
        ];

        foreach ($types as $key => $value) {
            foreach ($payment_status as $paymentStatus) {
                foreach ($methods as $method) {
                    AccountingMappingSettingAutoMigration::create([
                        'name' => $names[$key],
                        'type' => $value,
                        'company_id' => Session::get('selectedCompanyId'),
                        'status' => 'final',
                        'payment_status' => $paymentStatus,
                        'method' => $method,
                        'created_by' => $user_id,
                        'business_id' => $business_id,
                        'active' => false,
                    ]);
                }
            }
        }
    }
}
