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
                ],
            ],
        ];
    }
}
