<?php

namespace Modules\HousingMovements\Http\Controllers;

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
     * Parses notification message from database.
     *
     * @return array
     */
 

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'housingmovement.crud_buildings',
                'label' => __('housingmovements::lang.crud_buildings'),
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
                'name' => 'housingmovement_module',
                'label' => __('housingmovements::lang.housingmovement_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds housingmoveement menus
     *
     * @return null
     */
    public function modifyAdminMenu_hm()
    {
        $module_util = new ModuleUtil();

        $business_id = session()->get('user.business_id');
        $is_housingmoveement_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'housingmovement_module');
     
        if ($is_housingmoveement_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
            //     $menu->url(action([\Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index']),
            //     __('housingmovements::lang.housing_move'), ['icon' => 'fa 	fas fa-dolly', 'active' => request()->segment(1) == 'notification-templates'])->order(85);
            // });
            $menu->dropdown(
                __('housingmovements::lang.housing_move'),
                function ($subMenu) {

                    $subMenu->url(
                        action([\Modules\HousingMovements\Http\Controllers\RequestController::class, 'index']),
                        __('housingmovements::lang.requests'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'requests'],
                    )->order(1);

                    // $subMenu->url(
                    //     action([\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'index']),
                    //      __('housingmovements::lang.building_management'),
                    //      ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'buildings'],
                    //        )->order(2);
                    $subMenu->dropdown(
                        __('housingmovements::lang.building_management'),
                        function ($buildingSubMenu) {
                            $buildingSubMenu->url(
                                action([\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'index']),
                                __('housingmovements::lang.buildings'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'buildings']
                            )->order(1);
            
                            $buildingSubMenu->url(
                                action([\Modules\HousingMovements\Http\Controllers\RoomController::class, 'index']),
                                __('housingmovements::lang.rooms'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'rooms']
                            )->order(2);

                            $buildingSubMenu->url(
                                action([\Modules\HousingMovements\Http\Controllers\FacitityController::class, 'index']),
                                __('housingmovements::lang.facilities'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'facilities']
                            )->order(3);
                        }
                    )->order(2);
            

                    $subMenu->url(
                        action([\Modules\HousingMovements\Http\Controllers\MovementController::class, 'index']),
                        __('housingmovements::lang.movement_management'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'movement'],
                     
                    )->order(3);


                   
                
                  },
                [
                    'icon' => 'fa fas fa-users',
                    'active' => request()->segment(1) == 'housingmovements',
                    'style' => config('app.env') == 'demo' ? 'background-color: #605ca8 !important;' : '',
                ]
                )->order(10);
         
            });
        }
    }
}

