<?php

namespace Modules\GeneralManagement\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DataController extends Controller
{
    public function user_permissions()
    {
        return [
            [
                'group_name' => __('generalmanagement::lang.GeneralManagement'),
                'group_permissions' =>
                [

                    [
                        'value' => 'generalmanagement.generalmanagement_dashboard',
                        'label' => __('generalmanagement::lang.generalmanagement_dashboard'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.view_president_requests',
                        'label' => __('generalmanagement::lang.view_president_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.view_GM_escalate_requests',
                        'label' => __('generalmanagement::lang.view_GM_escalate_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.change_request_status',
                        'label' => __('generalmanagement::lang.change_request_status'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.view_request',
                        'label' => __('generalmanagement::lang.view_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.return_request',
                        'label' => __('generalmanagement::lang.return_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.send_notifications',
                        'label' => __('generalmanagement::lang.send_notifications'),
                        'default' => false,
                    ],

                ],
            ],
        ];
    }
}
