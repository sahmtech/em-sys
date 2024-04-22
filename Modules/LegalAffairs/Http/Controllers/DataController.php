<?php

namespace Modules\LegalAffairs\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DataController extends Controller
{
    public function user_permissions()
    {
        return [
            [
                'group_name' => __('legalaffairs::lang.legalaffairs'),
                'group_permissions' =>
                [

                    [
                        'value' => 'legalaffairs.legalAffairs_dashboard',
                        'label' => __('legalaffairs::lang.legalAffairs_dashboard'),
                        'default' => false,
                    ],
                    [
                        'value' => 'legalaffairs.contracts_management',
                        'label' => __('legalaffairs::lang.contracts_management'),
                        'default' => false,
                    ],
                    [
                        'value' => 'legalaffairs.crud_employee_contracts',
                        'label' => __('legalaffairs::lang.employee_contracts'),
                        'default' => false,
                    ],
                    [
                        'value' => 'legalaffairs.view_sales_contracts',
                        'label' => __('legalaffairs::lang.sales_contracts'),
                        'default' => false,
                    ],
                    [
                        'value' => 'legalaffairs.view_legalaffairs_requests',
                        'label' => __('legalaffairs::lang.view_legalaffairs_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'legalaffairs.change_request_status',
                        'label' => __('legalaffairs::lang.change_request_status'),
                        'default' => false,
                    ],   [
                        'value' => 'legalaffairs.return_request',
                        'label' => __('legalaffairs::lang.return_request'),
                        'default' => false,
                    ],   [
                        'value' => 'legalaffairs.show_request',
                        'label' => __('legalaffairs::lang.show_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'legalaffairs.add_request',
                        'label' => __('legalaffairs::lang.add_request'),
                        'default' => false,
                    ],
                ],
            ],
        ];
    }
}
