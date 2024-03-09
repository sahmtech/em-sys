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
    // public function parse_notification($notification)
    // {
    //     $notification_data = [];
    //     if (
    //         $notification->type ==
    //         'Modules\followup\Notifications\DocumentShareNotification'
    //     ) {
    //         $notifiction_data = DocumentShare::documentShareNotificationData($notification->data);
    //         $notification_data = [
    //             'msg' => $notifiction_data['msg'],
    //             'icon_class' => $notifiction_data['icon'],
    //             'link' => $notifiction_data['link'],
    //             'read_at' => $notification->read_at,
    //             'created_at' => $notification->created_at->diffForHumans(),
    //         ];
    //     } elseif (
    //         $notification->type ==
    //         'Modules\followup\Notifications\NewMessageNotification'
    //     ) {
    //         $data = $notification->data;
    //         $msg = __('followup::lang.new_message_notification', ['sender' => $data['from']]);

    //         $notification_data = [
    //             'msg' => $msg,
    //             'icon_class' => 'fas fa-envelope bg-green',
    //             'link' => action([\Modules\followup\Http\Controllers\followupMessageController::class, 'index']),
    //             'read_at' => $notification->read_at,
    //             'created_at' => $notification->created_at->diffForHumans(),
    //         ];
    //     } elseif (
    //         $notification->type ==
    //         'Modules\followup\Notifications\NewLeaveNotification'
    //     ) {
    //         $data = $notification->data;

    //         $employee = User::find($data['applied_by']);

    //         if (!empty($employee)) {
    //             $msg = __('followup::lang.new_leave_notification', ['employee' => $employee->user_full_name, 'ref_no' => $data['ref_no']]);

    //             $notification_data = [
    //                 'msg' => $msg,
    //                 'icon_class' => 'fas fa-user-times bg-green',
    //                 'link' => action([\Modules\followup\Http\Controllers\followupLeaveController::class, 'index']),
    //                 'read_at' => $notification->read_at,
    //                 'created_at' => $notification->created_at->diffForHumans(),
    //             ];
    //         }
    //     } elseif (
    //         $notification->type ==
    //         'Modules\followup\Notifications\LeaveStatusNotification'
    //     ) {
    //         $data = $notification->data;

    //         $admin = User::find($data['changed_by']);

    //         if (!empty($admin)) {
    //             $msg = __('followup::lang.status_change_notification', ['status' => $data['status'], 'ref_no' => $data['ref_no'], 'admin' => $admin->user_full_name]);

    //             $notification_data = [
    //                 'msg' => $msg,
    //                 'icon_class' => 'fas fa-user-times bg-green',
    //                 'link' => action([\Modules\followup\Http\Controllers\followupLeaveController::class, 'index']),
    //                 'read_at' => $notification->read_at,
    //                 'created_at' => $notification->created_at->diffForHumans(),
    //             ];
    //         }
    //     } elseif (
    //         $notification->type ==
    //         'Modules\followup\Notifications\PayrollNotification'
    //     ) {
    //         $data = $notification->data;

    //         $month = \Carbon::createFromFormat('m', $data['month'])->format('F');

    //         $msg = '';

    //         $created_by = User::find($data['created_by']);

    //         if (!empty($created_by)) {
    //             if ($data['action'] == 'created') {
    //                 $msg = __('followup::lang.payroll_added_notification', ['month_year' => $month . '/' . $data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
    //             } elseif ($data['action'] == 'updated') {
    //                 $msg = __('followup::lang.payroll_updated_notification', ['month_year' => $month . '/' . $data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
    //             }

    //             $notification_data = [
    //                 'msg' => $msg,
    //                 'icon_class' => 'fas fa-money-bill-alt bg-green',
    //                 'link' => action([\Modules\followup\Http\Controllers\PayrollController::class, 'index']),
    //                 'read_at' => $notification->read_at,
    //                 'created_at' => $notification->created_at->diffForHumans(),
    //             ];
    //         }
    //     } elseif (
    //         $notification->type ==
    //         'Modules\followup\Notifications\NewTaskNotification'
    //     ) {
    //         $data = $notification->data;

    //         $assigned_by = User::find($data['assigned_by']);

    //         if (!empty($assigned_by)) {
    //             $msg = __('followup::lang.new_task_notification', ['assigned_by' => $assigned_by->user_full_name, 'task_id' => $data['task_id']]);

    //             $notification_data = [
    //                 'msg' => $msg,
    //                 'icon_class' => 'ion ion-clipboard bg-green',
    //                 'link' => action([\Modules\followup\Http\Controllers\ToDoController::class, 'show'], $data['id']),
    //                 'read_at' => $notification->read_at,
    //                 'created_at' => $notification->created_at->diffForHumans(),
    //             ];
    //         }
    //     } elseif (
    //         $notification->type ==
    //         'Modules\followup\Notifications\NewTaskCommentNotification'
    //     ) {
    //         $data = $notification->data;

    //         $comment = followupTodoComment::with(['task', 'added_by'])->find($data['comment_id']);
    //         if (!empty($comment) && $comment->task) {
    //             $msg = __('followup::lang.new_task_comment_notification', ['added_by' => $comment->added_by->user_full_name, 'task_id' => $comment->task->task_id]);

    //             $notification_data = [
    //                 'msg' => $msg,
    //                 'icon_class' => 'fas fa-envelope bg-green',
    //                 'link' => action([\Modules\followup\Http\Controllers\ToDoController::class, 'show'], $comment->task->id),
    //                 'read_at' => $notification->read_at,
    //                 'created_at' => $notification->created_at->diffForHumans(),
    //             ];
    //         }
    //     } elseif (
    //         $notification->type ==
    //         'Modules\followup\Notifications\NewTaskDocumentNotification'
    //     ) {
    //         $data = $notification->data;

    //         $uploaded_by = User::find($data['uploaded_by']);

    //         if (!empty($uploaded_by)) {
    //             $msg = __('followup::lang.new_task_document_notification', ['uploaded_by' => $uploaded_by->user_full_name, 'task_id' => $data['task_id']]);

    //             $notification_data = [
    //                 'msg' => $msg,
    //                 'icon_class' => 'fas fa-file bg-green',
    //                 'link' => action([\Modules\followup\Http\Controllers\ToDoController::class, 'show'], $data['id']),
    //                 'read_at' => $notification->read_at,
    //                 'created_at' => $notification->created_at->diffForHumans(),
    //             ];
    //         }
    //     }

    //     return $notification_data;
    // }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [

                'group_name' => __('followup::lang.followUp'),
                'group_permissions' => [
                    [
                        'value' => 'followup.followup_dashboard',
                        'label' => __('followup::lang.followup_dashboard'),
                        'default' => false,
                    ],
                    [
                        'value' => 'followup.create_worker',
                        'label' => __('followup::lang.create_worker'),
                        'default' => false,
                    ],
                    [
                        'value' => 'followup.crud_contact_locations',
                        'label' => __('followup::lang.crud_contact_locations'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.editContactLocations',
                        'label' => __('followup::lang.editContactLocations'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.deleteContactLocations',
                        'label' => __('followup::lang.deleteContactLocations'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_projects',
                        'label' => __('followup::lang.crud_projects'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.projectView',
                        'label' => __('followup::lang.projectView'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.cancle_worker_project',
                        'label' => __('followup::lang.cancle_worker_project'),
                        'default' => false,

                    ],
                    
                    [
                        'value' => 'followup.view_worker_details',
                        'label' => __('followup::lang.view_workerProject_details'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_workers',
                        'label' => __('followup::lang.crud_workers'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_operation_orders',
                        'label' => __('followup::lang.crud_operation_orders'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_requests',
                        'label' => __('followup::lang.crud_requests'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_advanceSalary',
                        'label' => __('followup::lang.return_the_request_advanceSalary'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_atmCard',
                        'label' => __('followup::lang.return_the_request_atmCard'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_baladyCardRequest',
                        'label' => __('followup::lang.return_the_request_baladyCardRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_cancleContractRequest',
                        'label' => __('followup::lang.return_the_request_cancleContractRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_chamberRequest',
                        'label' => __('followup::lang.return_the_request_chamberRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_escapeRequest',
                        'label' => __('followup::lang.return_the_request_escapeRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_exitRequest',
                        'label' => __('followup::lang.return_the_request_exitRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_insuranceUpgradeRequest',
                        'label' => __('followup::lang.return_the_request_insuranceUpgradeRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_leavesAndDepartures',
                        'label' => __('followup::lang.return_the_request_leavesAndDepartures'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_mofaRequest',
                        'label' => __('followup::lang.return_the_request_mofaRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_residenceCard',
                        'label' => __('followup::lang.return_the_request_residenceCard'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_residenceEditRequest',
                        'label' => __('followup::lang.return_the_request_residenceEditRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_residenceRenewal',
                        'label' => __('followup::lang.return_the_request_residenceRenewal'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_returnRequest',
                        'label' => __('followup::lang.return_the_request_returnRequest'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_workerTransfer',
                        'label' => __('followup::lang.return_the_request_workerTransfer'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.return_the_request_workInjuriesRequest',
                        'label' => __('followup::lang.return_the_request_workInjuriesRequest'),
                        'default' => false,

                    ],


                   
                  
                    
                    
                    [
                        'value' => 'followup.view_request_escalate_requests',
                        'label' => __('followup::lang.view_request_escalate_requests'),
                        'default' => false,

                    ],


                    [
                        'value' => 'followup.crud_recruitmentRequests',
                        'label' => __('followup::lang.crud_recruitmentRequests'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_documents',
                        'label' => __('followup::lang.crud_documents'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.edit_document',
                        'label' => __('followup::lang.edit_document'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.documents.delete',
                        'label' => __('followup::lang.documents_delete'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_document_delivery',
                        'label' => __('followup::lang.crud_document_delivery'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.edit_document_delivery',
                        'label' => __('followup::lang.edit_document_delivery'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.delete_document_deliver',
                        'label' => __('followup::lang.delete_document_deliver'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.view_document_deliver',
                        'label' => __('followup::lang.view_document_deliver'),
                        'default' => false,

                    ],

                    [
                        'value' => 'followup.crud_projectsReports',
                        'label' => __('followup::lang.crud_projectsReports'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_projectWorkersReports',
                        'label' => __('followup::lang.crud_projectWorkersReports'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_contrascts_wishes',
                        'label' => __('followup::lang.crud_contrascts_wishes'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.change_wish',
                        'label' => __('followup::lang.change_wish'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.crud_shifts',
                        'label' => __('followup::lang.crud_shifts'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.edit_shifts',
                        'label' => __('followup::lang.edit_shifts'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.delete_shifts',
                        'label' => __('followup::lang.delete_shifts'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.projects_access_permissions',
                        'label' => __('followup::lang.projects_access_permissions'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.add_user_project_access_permissions',
                        'label' => __('followup::lang.add_user_project_access_permissions'),
                        'default' => false,

                    ],
                    [
                        'value' => 'followup.view_followup_requests',
                        'label' => __('followup::lang.view_followup_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'followup.add_request',
                        'label' => __('followup::lang.add_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'followup.change_request_status',
                        'label' => __('followup::lang.change_request_status'),
                        'default' => false,
                    ],
                    [
                        'value' => 'followup.show_request',
                        'label' => __('followup::lang.show_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'followup.return_request',
                        'label' => __('followup::lang.return_request'),
                        'default' => false,
                    ],
                       [
                        'value' => 'followup.followup_view_department_employees',
                        'label' => __('followup::lang.followup_view_department_employees'),
                        'default' => false,
                    ],
        
                ]

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