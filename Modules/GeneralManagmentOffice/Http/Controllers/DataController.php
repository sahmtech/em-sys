<?php

namespace Modules\GeneralManagmentOffice\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DataController extends Controller
{
    public function user_permissions()
    {
        return [
            [
                'group_name' => __('generalmanagmentoffice::lang.GeneralManagmentOffice'),
                'group_permissions' =>
                [

                    [
                        'value' => 'generalmanagmentoffice.generalmanagmentoffice_dashboard',
                        'label' => __('generalmanagmentoffice::lang.generalmanagmentoffice_dashboard'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagmentoffice.view_president_requests',
                        'label' => __('generalmanagmentoffice::lang.view_president_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagmentoffice.view_escalate_requests',
                        'label' => __('generalmanagmentoffice::lang.view_escalate_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagmentoffice.change_request_status',
                        'label' => __('generalmanagmentoffice::lang.change_request_status'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagmentoffice.view_request',
                        'label' => __('generalmanagmentoffice::lang.view_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagmentoffice.return_request',
                        'label' => __('generalmanagmentoffice::lang.return_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'office.generalmanagement.send_notifications',
                        'label' => __('generalmanagement::lang.send_notifications'),
                        'default' => false,
                    ],
                    [
                        'value' => 'office.generalmanagement.edit_notification_settings',
                        'label' => __('generalmanagement::lang.edit_notification_settings'),
                        'default' => false,
                    ],
                    [
                        'value' => 'office.generalmanagement.view_notifications',
                        'label' => __('generalmanagement::lang.view_notifications'),
                        'default' => false,
                    ],
                ],
            ],
        ];
    }
}
