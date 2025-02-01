<?php

namespace Modules\HousingMovements\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
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
                'group_name' => __('housingmovements::lang.housing_move'),
                'group_permissions' => [
                    [
                        'value' => 'housingmovements.create_worker',
                        'label' => __('housingmovements::lang.create_worker'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.housing_move_dashbord',
                        'label' => __('housingmovements::lang.housing_move_dashbord'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.crud_buildings',
                        'label' => __('housingmovements::lang.crud_buildings'),
                        'default' => false,
                    ],
                    [
                        'value' => 'building.edit',
                        'label' => __('housingmovements::lang._buildings_edit'),
                        'default' => false,
                    ],
                    [
                        'value' => 'building.delete',
                        'label' => __('housingmovements::lang._buildings_delete'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.crud_rooms',
                        'label' => __('housingmovements::lang.crud_rooms'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.view_import_rooms',
                        'label' => __('housingmovements::lang.view_import_rooms'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.submit_import_rooms',
                        'label' => __('housingmovements::lang.submit_import_rooms'),
                        'default' => false,
                    ],

                    [
                        'value' => 'room.workers',
                        'label' => __('housingmovements::lang._room_workers'),
                        'default' => false,
                    ],
                    [
                        'value' => 'room.edit',
                        'label' => __('housingmovements::lang._rooms_edit'),
                        'default' => false,
                    ],
                    [
                        'value' => 'room.delete',
                        'label' => __('housingmovements::lang._rooms_delete'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.housed_in_room',
                        'label' => __('housingmovements::lang.housed_in_room'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.crud_facilities',
                        'label' => __('housingmovements::lang.crud_facilities'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.crud_htr_requests',
                        'label' => __('housingmovements::lang.all_request'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.return_the_request',
                        'label' => __('housingmovements::lang.return_the_request'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.view_request',
                        'label' => __('housingmovements::lang.view_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.change_status',
                        'label' => __('housingmovements::lang.change_status'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.add_request',
                        'label' => __('housingmovements::lang.add_request'),
                        'default' => false,
                    ],

                    //////////////////////

                    [
                        'value' => 'housingmovements.workers',
                        'label' => __('housingmovements::lang.view_workers'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.add_worker_project',
                        'label' => __('housingmovements::lang.add_worker_project'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.all_workers',
                        'label' => __('housingmovements::lang.all_workers_view'),
                        'default' => false,
                    ],
                    [
                        'value' => 'worker.book',
                        'label' => __('housingmovements::lang.worker_book_permission'),
                        'default' => false,
                    ],

                    [
                        'value' => 'worker.unbook',
                        'label' => __('housingmovements::lang.worker_unbook_permission'),
                        'default' => false,
                    ],

                    // [
                    //     'value' => 'housingmovements.crud_htr_trevelers',
                    //     'label' => __('housingmovements::lang.crud_htr_trevelers'),
                    //     'default' => false,
                    // ],
                    [
                        'value' => 'housingmovements.change_arrived_status',
                        'label' => __('housingmovements::lang.change_arrived_status'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.worker_housed',
                        'label' => __('housingmovements::lang.worker_housed'),
                        'default' => false,
                    ],

                    [
                        'value' => 'housingmovements.leave_room',
                        'label' => __('housingmovements::lang.leave_room'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.transfer_from_room',
                        'label' => __('housingmovements::lang.transfer_from_room'),
                        'default' => false,
                    ],
                    [
                        'value' => 'housingmovements.housingmovements_view_department_employees',
                        'label' => __('housingmovements::lang.department_employees'),
                        'default' => false,
                    ],
                    //////////////////////
                    
                    // [
                    //     'value' => 'housingmovements.',
                    //     'label' => __('housingmovements::lang.'),
                    //     'default' => false,
                    // ],
                ],
                [
                    'value' => 'housingmovements.crud_timesheet',
                    'label' => __('housingmovements::lang.crud_timesheet'),
                    'default' => false,
                ],
                [
                    'value' => 'housingmovements.create_timesheet',
                    'label' => __('housingmovements::lang.create_timesheet'),
                    'default' => false,
                ],
                [
                    'value' => 'housingmovements.edit_timesheet',
                    'label' => __('housingmovements::lang.edit_timesheet'),
                    'default' => false,
                ],
                [
                    'value' => 'housingmovements.view_timesheet_groups',
                    'label' => __('housingmovements::lang.view_timesheet_groups'),
                    'default' => false,
                ],
                [
                    'value' => 'housingmovements.view_timesheet_users',
                    'label' => __('housingmovements::lang.view_timesheet_users'),
                    'default' => false,
                ],
                [
                    'value' => 'housingmovements.show_timesheet',
                    'label' => __('housingmovements::lang.show_timesheet'),
                    'default' => false,
                ],
                [
                    'value' => 'housingmovements.housingmovements.deal_timesheet',
                    'label' => __('housingmovements::lang.deal_timesheet'),
                    'default' => false,
                ],
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
                //     __('housingmovements::lang.housing_move'), ['icon' => 'fa     fas fa-dolly', 'active' => request()->segment(1) == 'notification-templates'])->order(85);
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
