<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Nwidart\Menus\Facades\Menu;

class DataController extends Controller
{
    /**
     * Superadmin package permissions
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'accounting_module',
                'label' => __('accounting::lang.accounting_module'),
                'default' => false
            ]
        ];
    }

    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        $is_accounting_enabled = (bool)$module_util->hasThePermissionInSubscription($business_id, 'accounting_module');

        $commonUtil = new Util();
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

        if (auth()->user()->can('accounting.access_accounting_module') && $is_accounting_enabled) {
            Menu::create(
                'custom_admin-sidebar-menu',
                function ($menu) use ($is_admin) {
                    $menu->url(action('\Modules\Accounting\Http\Controllers\AccountingController@dashboard'), __('accounting::lang.accounting'), ['icon' => 'fas fa-money-check fa', 'style' => config('app.env') == 'demo' ? 'background-color: #D483D9;' : '', 'active' => request()->segment(1) == 'accounting'])->order(50);
                }
            );
        }
    }

    /**
     * Defines user permissions for the module.
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'group_name' => __('accounting::lang.accounting'),
                'group_permissions' => [
                    [
                        'value' => 'accounting.accounting_dashboard',
                        'label' => __('accounting::lang.accounting_dashboard'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.view_companies',
                        'label' => __('accounting::lang.view_companies'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.companies_access_permissions',
                        'label' => __('accounting::lang.companies_access_permissions'),
                        'default' => false
                    ],

                    [
                        'value' => 'accounting.chart_of_accounts',
                        'label' => __('accounting::lang.chart_of_accounts'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.account.edit',
                        'label' => __('accounting::lang.account_edit'),
                        'default' => false
                    ],

                    [
                        'value' => 'accounting.cost_center',
                        'label' => __('accounting::lang.cost_center'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.costCenter.edit',
                        'label' => __('accounting::lang.cost_center_edit'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.costCenter.delete',
                        'label' => __('accounting::lang.cost_center_delete'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.opening_balances',
                        'label' => __('accounting::lang.opening_balances'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.OpeningBalance.delete',
                        'label' => __('accounting::lang.opening_balances_delete'),
                        'default' => false
                    ],

                    [
                        'value' => 'accouning.import_opeining_balances',
                        'label' => __('accounting::lang.importe_openingBalance'),
                        'default' => false
                    ],

                    

                    [
                        'value' => 'accounting.receipt_vouchers',
                        'label' => __('accounting::lang.receipt_vouchers'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.add_receipt_voucher',
                        'label' => __('accounting::lang.add_receipt_voucher'),
                        'default' => false
                    ],

                    [
                        'value' => 'accounting.payment_vouchers',
                        'label' => __('accounting::lang.payment_vouchers'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.add_payment_voucher',
                        'label' => __('accounting::lang.add_payment_voucher'),
                        'default' => false
                    ],


                    [
                        'value' => 'accounting.journal_entry',
                        'label' => __('accounting::lang.journal_entry'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.view_journal',
                        'label' => __('accounting::lang.view_journal'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.add_journal',
                        'label' => __('accounting::lang.add_journal'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.edit_journal',
                        'label' => __('accounting::lang.edit_journal'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.delete_journal',
                        'label' => __('accounting::lang.delete_journal'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.automatedMigration',
                        'label' => __('accounting::lang.automatedMigration'),
                        'default' => false
                    ],
                    [
                        'value' => 'AutomatedMigration.edit',
                        'label' => __('accounting::lang.automatedMigration_edit'),
                        'default' => false
                    ],
                    [
                        'value' => 'AutomatedMigration.active_toggle',
                        'label' => __('accounting::lang.automatedMigration_active_toggle'),
                        'default' => false
                    ],

                    [
                        'value' => 'accounting.transfer',
                        'label' => __('accounting::lang.transfer'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.view_transfer',
                        'label' => __('accounting::lang.view_transfer'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.add_transfer',
                        'label' => __('accounting::lang.add_transfer'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.edit_transfer',
                        'label' => __('accounting::lang.edit_transfer'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.delete_transfer',
                        'label' => __('accounting::lang.delete_transfer'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.transactions',
                        'label' => __('accounting::lang.transactions'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.reports',
                        'label' => __('accounting::lang.reports'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.settings',
                        'label' => __('accounting::lang.settings'),
                        'default' => false
                    ],


                    [
                        'value' => 'accounting.map_transactions',
                        'label' => __('accounting::lang.map_transactions'),
                        'default' => false
                    ],

                    [
                        'value' => 'accounting.manage_budget',
                        'label' => __('accounting::lang.manage_budget'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.view_reports',
                        'label' => __('accounting::lang.view_reports'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.view_accounting_requests',
                        'label' => __('accounting::lang.view_accounting_requests'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.change_status',
                        'label' => __('accounting::lang.change_status'),
                        'default' => false
                    ],
                    [
                        'value' => 'accounting.return_the_request',
                        'label' => __('accounting::lang.return_the_request'),
                        'default' => false,
                    ],

                    [
                        'value' => 'accounting.show_request',
                        'label' => __('accounting::lang.show_request'),
                        'default' => false,
                    ],

                    [
                        'value' => 'accounting.add_request',
                        'label' => __('accounting::lang.add_request'),
                        'default' => false,
                    ],
                ]

            ],
        ];
    }
}