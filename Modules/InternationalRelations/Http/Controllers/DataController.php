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
        return [
            [
                'value' => 'internationalRelations.crud_leave_type',
                'label' => __('internationalRelations::lang.crud_leave_type'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.crud_all_leave',
                'label' => __('internationalRelations::lang.crud_all_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'internationalRelations.crud_own_leave',
                'label' => __('internationalRelations::lang.crud_own_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'internationalRelations.approve_leave',
                'label' => __('internationalRelations::lang.approve_leave'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.crud_all_attendance',
                'label' => __('internationalRelations::lang.crud_all_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'internationalRelations.view_own_attendance',
                'label' => __('internationalRelations::lang.view_own_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'internationalRelations.allow_users_for_attendance_from_web',
                'label' => __('internationalRelations::lang.allow_users_for_attendance_from_web'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.allow_users_for_attendance_from_api',
                'label' => __('internationalRelations::lang.allow_users_for_attendance_from_api'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.view_allowance_and_deduction',
                'label' => __('internationalRelations::lang.view_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.add_allowance_and_deduction',
                'label' => __('internationalRelations::lang.add_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.crud_department',
                'label' => __('internationalRelations::lang.crud_department'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.crud_designation',
                'label' => __('internationalRelations::lang.crud_designation'),
                'default' => false,
            ],

            [
                'value' => 'internationalRelations.view_all_payroll',
                'label' => __('internationalRelations::lang.view_all_payroll'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.create_payroll',
                'label' => __('internationalRelations::lang.add_payroll'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.update_payroll',
                'label' => __('internationalRelations::lang.edit_payroll'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.delete_payroll',
                'label' => __('internationalRelations::lang.delete_payroll'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.assign_todos',
                'label' => __('internationalRelations::lang.assign_todos'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.add_todos',
                'label' => __('internationalRelations::lang.add_todos'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.edit_todos',
                'label' => __('internationalRelations::lang.edit_todos'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.delete_todos',
                'label' => __('internationalRelations::lang.delete_todos'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.create_message',
                'label' => __('internationalRelations::lang.create_message'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.view_message',
                'label' => __('internationalRelations::lang.view_message'),
                'default' => false,
            ],
            [
                'value' => 'internationalRelations.access_sales_target',
                'label' => __('internationalRelations::lang.access_sales_target'),
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
        //dd($is_internationalRelations_enabled);
        if ($is_internationalRelations_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index']), 'العلاقات الدولية', ['icon' => 'fa fas fa-dharmachakra', 'active' => request()->segment(1) == 'notification-templates'])->order(86);
            });
        }
    }

 
}
