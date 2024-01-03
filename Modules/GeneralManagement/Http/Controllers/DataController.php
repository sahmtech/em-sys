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
                'value' => 'generalmanagement.president_requests',
                'label' => __('generalmanagement::lang.crud_president_requests'),
                'default' => false,
            ],
          
            
        ];
    }
    
}
