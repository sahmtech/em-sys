<?php

namespace Modules\FollowUp\Http\Controllers;

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
            'Modules\followup\Notifications\DocumentShareNotification') {
            $notifiction_data = DocumentShare::documentShareNotificationData($notification->data);
            $notification_data = [
                'msg' => $notifiction_data['msg'],
                'icon_class' => $notifiction_data['icon'],
                'link' => $notifiction_data['link'],
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif ($notification->type ==
            'Modules\followup\Notifications\NewMessageNotification') {
            $data = $notification->data;
            $msg = __('followup::lang.new_message_notification', ['sender' => $data['from']]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fas fa-envelope bg-green',
                'link' => action([\Modules\followup\Http\Controllers\followupMessageController::class, 'index']),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif ($notification->type ==
            'Modules\followup\Notifications\NewLeaveNotification') {
            $data = $notification->data;

            $employee = User::find($data['applied_by']);

            if (! empty($employee)) {
                $msg = __('followup::lang.new_leave_notification', ['employee' => $employee->user_full_name, 'ref_no' => $data['ref_no']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\followupLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\followup\Notifications\LeaveStatusNotification') {
            $data = $notification->data;

            $admin = User::find($data['changed_by']);

            if (! empty($admin)) {
                $msg = __('followup::lang.status_change_notification', ['status' => $data['status'], 'ref_no' => $data['ref_no'], 'admin' => $admin->user_full_name]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\followupLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\followup\Notifications\PayrollNotification') {
            $data = $notification->data;

            $month = \Carbon::createFromFormat('m', $data['month'])->format('F');

            $msg = '';

            $created_by = User::find($data['created_by']);

            if (! empty($created_by)) {
                if ($data['action'] == 'created') {
                    $msg = __('followup::lang.payroll_added_notification', ['month_year' => $month.'/'.$data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                } elseif ($data['action'] == 'updated') {
                    $msg = __('followup::lang.payroll_updated_notification', ['month_year' => $month.'/'.$data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                }

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-money-bill-alt bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\PayrollController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\followup\Notifications\NewTaskNotification') {
            $data = $notification->data;

            $assigned_by = User::find($data['assigned_by']);

            if (! empty($assigned_by)) {
                $msg = __('followup::lang.new_task_notification', ['assigned_by' => $assigned_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'ion ion-clipboard bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\ToDoController::class, 'show'], $data['id']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\followup\Notifications\NewTaskCommentNotification') {
            $data = $notification->data;

            $comment = followupTodoComment::with(['task', 'added_by'])->find($data['comment_id']);
            if (! empty($comment) && $comment->task) {
                $msg = __('followup::lang.new_task_comment_notification', ['added_by' => $comment->added_by->user_full_name, 'task_id' => $comment->task->task_id]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-envelope bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\ToDoController::class, 'show'], $comment->task->id),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\followup\Notifications\NewTaskDocumentNotification') {
            $data = $notification->data;

            $uploaded_by = User::find($data['uploaded_by']);

            if (! empty($uploaded_by)) {
                $msg = __('followup::lang.new_task_document_notification', ['uploaded_by' => $uploaded_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-file bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\ToDoController::class, 'show'], $data['id']),
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
                'value' => 'followup.crud_leave_type',
                'label' => __('followup::lang.crud_leave_type'),
                'default' => false,
            ],
            [
                'value' => 'followup.crud_all_leave',
                'label' => __('followup::lang.crud_all_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'followup.crud_own_leave',
                'label' => __('followup::lang.crud_own_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'followup.approve_leave',
                'label' => __('followup::lang.approve_leave'),
                'default' => false,
            ],
            [
                'value' => 'followup.crud_all_attendance',
                'label' => __('followup::lang.crud_all_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'followup.view_own_attendance',
                'label' => __('followup::lang.view_own_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'followup.allow_users_for_attendance_from_web',
                'label' => __('followup::lang.allow_users_for_attendance_from_web'),
                'default' => false,
            ],
            [
                'value' => 'followup.allow_users_for_attendance_from_api',
                'label' => __('followup::lang.allow_users_for_attendance_from_api'),
                'default' => false,
            ],
            [
                'value' => 'followup.view_allowance_and_deduction',
                'label' => __('followup::lang.view_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'followup.add_allowance_and_deduction',
                'label' => __('followup::lang.add_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'followup.crud_department',
                'label' => __('followup::lang.crud_department'),
                'default' => false,
            ],
            [
                'value' => 'followup.crud_designation',
                'label' => __('followup::lang.crud_designation'),
                'default' => false,
            ],

            [
                'value' => 'followup.view_all_payroll',
                'label' => __('followup::lang.view_all_payroll'),
                'default' => false,
            ],
            [
                'value' => 'followup.create_payroll',
                'label' => __('followup::lang.add_payroll'),
                'default' => false,
            ],
            [
                'value' => 'followup.update_payroll',
                'label' => __('followup::lang.edit_payroll'),
                'default' => false,
            ],
            [
                'value' => 'followup.delete_payroll',
                'label' => __('followup::lang.delete_payroll'),
                'default' => false,
            ],
            [
                'value' => 'followup.assign_todos',
                'label' => __('followup::lang.assign_todos'),
                'default' => false,
            ],
            [
                'value' => 'followup.add_todos',
                'label' => __('followup::lang.add_todos'),
                'default' => false,
            ],
            [
                'value' => 'followup.edit_todos',
                'label' => __('followup::lang.edit_todos'),
                'default' => false,
            ],
            [
                'value' => 'followup.delete_todos',
                'label' => __('followup::lang.delete_todos'),
                'default' => false,
            ],
            [
                'value' => 'followup.create_message',
                'label' => __('followup::lang.create_message'),
                'default' => false,
            ],
            [
                'value' => 'followup.view_message',
                'label' => __('followup::lang.view_message'),
                'default' => false,
            ],
            [
                'value' => 'followup.access_sales_target',
                'label' => __('followup::lang.access_sales_target'),
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
                'name' => 'followup_module',
                'label' => __('followup::lang.followup_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds followup menus
     *
     * @return null
     */
    public function modifyAdminMenu_hm()
    {
        $module_util = new ModuleUtil();

        $business_id = session()->get('user.business_id');
        $is_followup_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'followup_module');
        //dd($is_followup_enabled);
        if ($is_followup_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(action([\Modules\FollowUp\Http\Controllers\DashboardController::class, 'index']), 'إدارة  المتابعة', ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'notification-templates'])->order(85);
            });
        }
    }

 
}
