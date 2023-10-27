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
    public function parse_notification($notification)
    {
        $notification_data = [];
        if ($notification->type ==
            'Modules\housingmoveement\Notifications\DocumentShareNotification') {
            $notifiction_data = DocumentShare::documentShareNotificationData($notification->data);
            $notification_data = [
                'msg' => $notifiction_data['msg'],
                'icon_class' => $notifiction_data['icon'],
                'link' => $notifiction_data['link'],
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif ($notification->type ==
            'Modules\housingmoveement\Notifications\NewMessageNotification') {
            $data = $notification->data;
            $msg = __('housingmoveement::lang.new_message_notification', ['sender' => $data['from']]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fas fa-envelope bg-green',
                'link' => action([\Modules\housingmoveement\Http\Controllers\housingmoveementMessageController::class, 'index']),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif ($notification->type ==
            'Modules\housingmoveement\Notifications\NewLeaveNotification') {
            $data = $notification->data;

            $employee = User::find($data['applied_by']);

            if (! empty($employee)) {
                $msg = __('housingmoveement::lang.new_leave_notification', ['employee' => $employee->user_full_name, 'ref_no' => $data['ref_no']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\housingmoveement\Http\Controllers\housingmoveementLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\housingmoveement\Notifications\LeaveStatusNotification') {
            $data = $notification->data;

            $admin = User::find($data['changed_by']);

            if (! empty($admin)) {
                $msg = __('housingmoveement::lang.status_change_notification', ['status' => $data['status'], 'ref_no' => $data['ref_no'], 'admin' => $admin->user_full_name]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\housingmoveement\Http\Controllers\housingmoveementLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\housingmoveement\Notifications\PayrollNotification') {
            $data = $notification->data;

            $month = \Carbon::createFromFormat('m', $data['month'])->format('F');

            $msg = '';

            $created_by = User::find($data['created_by']);

            if (! empty($created_by)) {
                if ($data['action'] == 'created') {
                    $msg = __('housingmoveement::lang.payroll_added_notification', ['month_year' => $month.'/'.$data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                } elseif ($data['action'] == 'updated') {
                    $msg = __('housingmoveement::lang.payroll_updated_notification', ['month_year' => $month.'/'.$data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                }

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-money-bill-alt bg-green',
                    'link' => action([\Modules\housingmoveement\Http\Controllers\PayrollController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\housingmoveement\Notifications\NewTaskNotification') {
            $data = $notification->data;

            $assigned_by = User::find($data['assigned_by']);

            if (! empty($assigned_by)) {
                $msg = __('housingmoveement::lang.new_task_notification', ['assigned_by' => $assigned_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'ion ion-clipboard bg-green',
                    'link' => action([\Modules\housingmoveement\Http\Controllers\ToDoController::class, 'show'], $data['id']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\housingmoveement\Notifications\NewTaskCommentNotification') {
            $data = $notification->data;

            $comment = housingmoveementTodoComment::with(['task', 'added_by'])->find($data['comment_id']);
            if (! empty($comment) && $comment->task) {
                $msg = __('housingmoveement::lang.new_task_comment_notification', ['added_by' => $comment->added_by->user_full_name, 'task_id' => $comment->task->task_id]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-envelope bg-green',
                    'link' => action([\Modules\housingmoveement\Http\Controllers\ToDoController::class, 'show'], $comment->task->id),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\housingmoveement\Notifications\NewTaskDocumentNotification') {
            $data = $notification->data;

            $uploaded_by = User::find($data['uploaded_by']);

            if (! empty($uploaded_by)) {
                $msg = __('housingmoveement::lang.new_task_document_notification', ['uploaded_by' => $uploaded_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-file bg-green',
                    'link' => action([\Modules\housingmoveement\Http\Controllers\ToDoController::class, 'show'], $data['id']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        }

        return $notification_data;
    }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'housingmoveement.crud_leave_type',
                'label' => __('housingmoveement::lang.crud_leave_type'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.crud_all_leave',
                'label' => __('housingmoveement::lang.crud_all_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'housingmoveement.crud_own_leave',
                'label' => __('housingmoveement::lang.crud_own_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'housingmoveement.approve_leave',
                'label' => __('housingmoveement::lang.approve_leave'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.crud_all_attendance',
                'label' => __('housingmoveement::lang.crud_all_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'housingmoveement.view_own_attendance',
                'label' => __('housingmoveement::lang.view_own_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'housingmoveement.allow_users_for_attendance_from_web',
                'label' => __('housingmoveement::lang.allow_users_for_attendance_from_web'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.allow_users_for_attendance_from_api',
                'label' => __('housingmoveement::lang.allow_users_for_attendance_from_api'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.view_allowance_and_deduction',
                'label' => __('housingmoveement::lang.view_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.add_allowance_and_deduction',
                'label' => __('housingmoveement::lang.add_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.crud_department',
                'label' => __('housingmoveement::lang.crud_department'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.crud_designation',
                'label' => __('housingmoveement::lang.crud_designation'),
                'default' => false,
            ],

            [
                'value' => 'housingmoveement.view_all_payroll',
                'label' => __('housingmoveement::lang.view_all_payroll'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.create_payroll',
                'label' => __('housingmoveement::lang.add_payroll'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.update_payroll',
                'label' => __('housingmoveement::lang.edit_payroll'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.delete_payroll',
                'label' => __('housingmoveement::lang.delete_payroll'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.assign_todos',
                'label' => __('housingmoveement::lang.assign_todos'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.add_todos',
                'label' => __('housingmoveement::lang.add_todos'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.edit_todos',
                'label' => __('housingmoveement::lang.edit_todos'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.delete_todos',
                'label' => __('housingmoveement::lang.delete_todos'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.create_message',
                'label' => __('housingmoveement::lang.create_message'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.view_message',
                'label' => __('housingmoveement::lang.view_message'),
                'default' => false,
            ],
            [
                'value' => 'housingmoveement.access_sales_target',
                'label' => __('housingmoveement::lang.access_sales_target'),
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
                'name' => 'housingmoveement_module',
                'label' => __('housingmoveement::lang.housingmoveement_module'),
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
        $is_housingmoveement_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'housingmoveement_module');
        //dd($is_housingmoveement_enabled);
        if ($is_housingmoveement_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(action([\Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index']),
                __('housingmovements::lang.housing_move'), ['icon' => 'fa 	fas fa-dolly', 'active' => request()->segment(1) == 'notification-templates'])->order(85);
            });
        }
    }

 
}
