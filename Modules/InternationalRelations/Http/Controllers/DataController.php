<?php

namespace Modules\InternationalRelations\Http\Controllers;

use App\BusinessLocation;
use App\Category;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Menu;


class DataController extends Controller
{
 
    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return
         [
            [
                'value' => 'internationalrelations.view_dashboard',
                'label' => __('internationalrelations::lang.view_dashboard'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_Airlines',
                'label' => __('internationalrelations::lang.view_Airlines'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_EmploymentCompanies',
                'label' => __('internationalrelations::lang.view_EmploymentCompanies'),
                'default' => false,
            ],
            
            [
                'value' => 'internationalrelations.crud_airlines',
                'label' => __('internationalrelations::lang.crud_airlines'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.order_request_view',
                'label' => __('internationalrelations::lang.order_request_view'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.crud_order_request',
                'label' => __('internationalrelations::lang.crud_order_request'),
                'default' => false,
            ],
        ];
    }

    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'internationalRelations_module',
                'label' => __('internationalRelations::lang.internationalRelations_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds internationalRelations menus
     *
     * @return null
     */
    public function modifyAdminMenu_IR()
    {
        $module_util = new ModuleUtil();

        $business_id = session()->get('user.business_id');
        $is_internationalRelations_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'internationalRelations_module');
      
        if ($is_internationalRelations_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {

                $menu->dropdown(
                    __('internationalrelations::lang.International'),
                    function ($subMenu) {

                        if (auth()->user()->can('internationalrelations.view_dashboard')) 
                        {
                            $subMenu->url(
                                action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index']),
                                __('internationalrelations::lang.dashboard'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'dashboard'],
                            )->order(1);
                        }

                        
                        if (auth()->user()->can('internationalrelations.view_Airlines')) 
                        {
                            $subMenu->url(
                                action([\Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'index']),
                                __('internationalrelations::lang.Airlines'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'Airlines'],
                            )->order(2);
                        }
                        if (auth()->user()->can('internationalrelations.view_EmploymentCompanies')) 
                        {
                            $subMenu->url(
                                action([\Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'index']),
                                __('internationalrelations::lang.EmploymentCompanies'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'EmploymentCompanies'],
                            )->order(3);
                        
                        }
                        if (auth()->user()->can('internationalrelations.order_request_view')) 
                        {
                            $subMenu->url(
                                action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController ::class, 'index']),
                                __('internationalrelations::lang.order_request'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'OrderRequest'],
                            )->order(4);
                        
                        }
                      },
                    [
                        'icon' => 'fa fas fa-dharmachakra',
                        'active' => request()->segment(1) == 'internationalRleations',
                        'style' => config('app.env') == 'demo' ? 'background-color: #605ca8 !important;' : '',
                    ]
                )->order(20);
                // $menu->url(action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index']),
                //  'العلاقات الدولية', ['icon' => 'fa fas fa-dharmachakra', 'active' => request()->segment(1) == 'notification-templates'])->order(86);
            });
        }
    }

 
}
