<?php

namespace Modules\CEOManagment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CEOManagmentController extends Controller
{
  
        public function user_permissions()
        {
            return [
                [
                    'group_name' => __('ceomanagment::lang.CEO_Managment'),
                    'group_permissions' =>
                    [
                        
                        [
                            'value' => 'ceomanagment.CEOmanagement_dashboard',
                            'label' => __('ceomanagment::lang.CEOmanagement_dashboard'),
                            'default' => false,
                        ],
                        // [
                        //     'value' => 'generalmanagement.view_president_requests',
                        //     'label' => __('generalmanagement::lang.view_president_requests'),
                        //     'default' => false,
                        // ],
                        // [
                        //     'value' => 'generalmanagement.change_request_status',
                        //     'label' => __('generalmanagement::lang.change_request_status'),
                        //     'default' => false,
                        // ],
                        // [
                        //     'value' => 'generalmanagement.view_request',
                        //     'label' => __('generalmanagement::lang.view_request'),
                        //     'default' => false,
                        // ],
                        // [
                        //     'value' => 'generalmanagement.return_request',
                        //     'label' => __('generalmanagement::lang.return_request'),
                        //     'default' => false,
                        // ],
                       
                    ],
                ],
            ];
        }
    
}
