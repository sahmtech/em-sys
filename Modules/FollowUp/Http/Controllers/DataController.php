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
        if (
            $notification->type ==
            'Modules\followup\Notifications\DocumentShareNotification'
        ) {
            $notifiction_data = DocumentShare::documentShareNotificationData($notification->data);
            $notification_data = [
                'msg' => $notifiction_data['msg'],
                'icon_class' => $notifiction_data['icon'],
                'link' => $notifiction_data['link'],
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif (
            $notification->type ==
            'Modules\followup\Notifications\NewMessageNotification'
        ) {
            $data = $notification->data;
            $msg = __('followup::lang.new_message_notification', ['sender' => $data['from']]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fas fa-envelope bg-green',
                'link' => action([\Modules\followup\Http\Controllers\followupMessageController::class, 'index']),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif (
            $notification->type ==
            'Modules\followup\Notifications\NewLeaveNotification'
        ) {
            $data = $notification->data;

            $employee = User::find($data['applied_by']);

            if (!empty($employee)) {
                $msg = __('followup::lang.new_leave_notification', ['employee' => $employee->user_full_name, 'ref_no' => $data['ref_no']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\followupLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\followup\Notifications\LeaveStatusNotification'
        ) {
            $data = $notification->data;

            $admin = User::find($data['changed_by']);

            if (!empty($admin)) {
                $msg = __('followup::lang.status_change_notification', ['status' => $data['status'], 'ref_no' => $data['ref_no'], 'admin' => $admin->user_full_name]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\followupLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\followup\Notifications\PayrollNotification'
        ) {
            $data = $notification->data;

            $month = \Carbon::createFromFormat('m', $data['month'])->format('F');

            $msg = '';

            $created_by = User::find($data['created_by']);

            if (!empty($created_by)) {
                if ($data['action'] == 'created') {
                    $msg = __('followup::lang.payroll_added_notification', ['month_year' => $month . '/' . $data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                } elseif ($data['action'] == 'updated') {
                    $msg = __('followup::lang.payroll_updated_notification', ['month_year' => $month . '/' . $data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                }

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-money-bill-alt bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\PayrollController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\followup\Notifications\NewTaskNotification'
        ) {
            $data = $notification->data;

            $assigned_by = User::find($data['assigned_by']);

            if (!empty($assigned_by)) {
                $msg = __('followup::lang.new_task_notification', ['assigned_by' => $assigned_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'ion ion-clipboard bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\ToDoController::class, 'show'], $data['id']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\followup\Notifications\NewTaskCommentNotification'
        ) {
            $data = $notification->data;

            $comment = followupTodoComment::with(['task', 'added_by'])->find($data['comment_id']);
            if (!empty($comment) && $comment->task) {
                $msg = __('followup::lang.new_task_comment_notification', ['added_by' => $comment->added_by->user_full_name, 'task_id' => $comment->task->task_id]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-envelope bg-green',
                    'link' => action([\Modules\followup\Http\Controllers\ToDoController::class, 'show'], $comment->task->id),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\followup\Notifications\NewTaskDocumentNotification'
        ) {
            $data = $notification->data;

            $uploaded_by = User::find($data['uploaded_by']);

            if (!empty($uploaded_by)) {
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
                'value' => 'followup.crud_workers',
                'label' => __('followup::lang.crud_workers'),
                'default' => false,
            ],
            [
                'value' => 'followup.crud_projects',
                'label' => __('followup::lang.crud_projects'),
                'default' => false,

            ],
            [
                'value' => 'followup.crud_operation_orders',
                'label' => __('followup::lang.crud_operation_orders'),
                'default' => false,

            ],
            [
                'value' => 'followup.create_order',
                'label' => __('followup::lang.create_order'),
                'default' => false,

            ],
            [
                'value' => 'followup.return_request',
                'label' => __('followup::lang.return_request'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewExitRequests',
                'label' => __('followup::lang.viewExitRequests'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudExitRequests',
                'label' => __('followup::lang.crudExitRequests'),
                'default' => false,

            ],
            [
                'value' => 'followup.view_requests',
                'label' => __('followup::lang.view_requests'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewReturnRequest',
                'label' => __('followup::lang.viewReturnRequest'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudReturnRequest',
                'label' => __('followup::lang.crudReturnRequest'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewEscapeRequest',
                'label' => __('followup::lang.viewEscapeRequest'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudEscapeRequest',
                'label' => __('followup::lang.crudEscapeRequest'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewAdvanceSalary',
                'label' => __('followup::lang.viewAdvanceSalary'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudAdvanceSalary',
                'label' => __('followup::lang.crudAdvanceSalary'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewLeavesAndDepartures',
                'label' => __('followup::lang.viewLeavesAndDepartures'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudLeavesAndDepartures',
                'label' => __('followup::lang.crudLeavesAndDepartures'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewAtmCard',
                'label' => __('followup::lang.viewAtmCard'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudAtmCard',
                'label' => __('followup::lang.crudAtmCard'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewResidenceRenewal',
                'label' => __('followup::lang.viewResidenceRenewal'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudResidenceRenewal',
                'label' => __('followup::lang.crudResidenceRenewal'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewResidenceCard',
                'label' => __('followup::lang.viewResidenceCard'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudResidenceCard',
                'label' => __('followup::lang.crudResidenceCard'),
                'default' => false,

            ],
            [
                'value' => 'followup.viewWorkerTransfer',
                'label' => __('followup::lang.viewWorkerTransfer'),
                'default' => false,

            ],
            [
                'value' => 'followup.crudWorkerTransfer',
                'label' => __('followup::lang.crudWorkerTransfer'),
                'default' => false,

            ],

            [
                'value' => 'followup.curd_contracts_wishes',
                'label' => __('followup::lang.curd_contracts_wishes'),
                'default' => false,

            ],



            [


                'value' => 'followup.viewWorkInjuriesRequest',
                'label' => __('followup::lang.viewWorkInjuriesRequest'),
                'default' => false,

            ],
            [


                'value' => 'followup.viewResidenceEditRequest',
                'label' => __('followup::lang.viewResidenceEditRequest'),
                'default' => false,

            ],
            [


                'value' => 'followup.viewBaladyCardRequest',
                'label' => __('followup::lang.viewBaladyCardRequest'),
                'default' => false,

            ],
            [


                'value' => 'followup.viewRecruitmentRequest',
                'label' => __('followup::lang.viewRecruitmentRequest'),
                'default' => false,

            ],
            [

                'value' => 'followup.viewInsuranceUpgradeRequest',
                'label' => __('followup::lang.viewInsuranceUpgradeRequest'),
                'default' => false,

            ],
            [


                'value' => 'followup.viewMofaRequest',
                'label' => __('followup::lang.viewMofaRequest'),
                'default' => false,

            ],
            [


                'value' => 'followup.viewChamberRequest',
                'label' => __('followup::lang.viewChamberRequest'),
                'default' => false,

            ],
            [

                

                'value' => 'followup.contact_locations',
                'label' => __('followup::lang.contact_locations'),
                'default' => false,

            ],
            [


                'value' => 'followup.projects',
                'label' => __('followup::lang.projects'),
                'default' => false,

            ],
            [


                'value' => 'followup.workers',
                'label' => __('followup::lang.workers'),
                'default' => false,

            ],
            [


                'value' => 'followup.operation_orders',
                'label' => __('followup::lang.operation_orders'),
                'default' => false,

            ],
             [


                'value' => 'followup.requests',
                'label' => __('followup::lang.requests'),
                'default' => false,

            ],
            [


                'value' => 'followup.recruitmentRequests',
                'label' => __('followup::lang.recruitmentRequests'),
                'default' => false,

            ],

            [


                'value' => 'followup.document_delivery',
                'label' => __('followup::lang.document_delivery'),
                'default' => false,

            ],

            [


                'value' => 'followup.documents',
                'label' => __('followup::lang.documents'),
                'default' => false,

            ],
            [


                'value' => 'followup.reports.projects',
                'label' => __('followup::lang.reports.projectsReports'),
                'default' => false,

            ],
            [


                'value' => 'followup.reports.projectWorkers',
                'label' => __('followup::lang.reports.projectWorkersReports'),
                'default' => false,

            ],

            [


                'value' => 'followup.contrascts_wishes',
                'label' => __('followup::lang.contrascts_wishes'),
                'default' => false,

            ],

            [


                'value' => 'followup.shifts',
                'label' => __('followup::lang.shifts'),
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