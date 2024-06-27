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
                    [
                        'value' => 'generalmanagement.edit_notification_settings',
                        'label' => __('generalmanagement::lang.edit_notification_settings'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.view_notifications',
                        'label' => __('generalmanagement::lang.view_notifications'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.curd_organizational_structure',
                        'label' => __('generalmanagement::lang.organizational_structure'),
                        'default' => false,
                    ],

                    [
                        'value' => 'generalmanagement.delegatingManager_name',
                        'label' => __('generalmanagement::lang.delegatingManager_name'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.add_manager',
                        'label' => __('generalmanagement::lang.add_manager'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.add_deputy',
                        'label' => __('generalmanagement::lang.add_deputy'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.show_depatments',
                        'label' => __('generalmanagement::lang.show_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.edit_depatments',
                        'label' => __('generalmanagement::lang.edit_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.delete_depatments',
                        'label' => __('generalmanagement::lang.delete_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.add_departments',
                        'label' => __('generalmanagement::lang.add_departments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.delete_procedure',
                        'label' => __('generalmanagement::lang.delete_procedure'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.edit_procedure',
                        'label' => __('generalmanagement::lang.edit_procedure'),
                        'default' => false,
                    ],



                    [
                        'value' => 'generalmanagement.view_requests_types',
                        'label' => __('generalmanagement::lang.view_requests_types'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.add_requests_type',
                        'label' => __('generalmanagement::lang.add_requests_type'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.edit_requests_type',
                        'label' => __('generalmanagement::lang.edit_requests_type'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.delete_requests_type',
                        'label' => __('generalmanagement::lang.delete_requests_type'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.delete_request_type_tasks',
                        'label' => __('generalmanagement::lang.delete_request_type_tasks'),
                        'default' => false,
                    ],

                    [
                        'value' => 'generalmanagement.view_procedures_for_employee',
                        'label' => __('generalmanagement::lang.view_procedures_for_employee'),
                        'default' => false,
                    ],
                    [
                        'value' => 'generalmanagement.view_procedures_for_workers',
                        'label' => __('generalmanagement::lang.view_procedures_for_workers'),
                        'default' => false,
                    ],
                ],
            ],
        ];
    }
}