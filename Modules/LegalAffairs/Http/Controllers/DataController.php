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

                ],
            ],
        ];
    }
}
