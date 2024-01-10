<?php

namespace Modules\Essentials\Http\Controllers;

use App\BusinessLocation;
use App\Category;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Menu;
use Modules\Essentials\Entities\EssentialsContractType;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\DocumentShare;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsHoliday;
use Modules\Essentials\Entities\EssentialsLeave;
use Modules\Essentials\Entities\EssentialsTodoComment;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;
use Modules\Essentials\Entities\EssentialsEmployeeTravelCategorie;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\Reminder;
use Modules\Essentials\Entities\ToDo;
use Modules\Essentials\Entities\EssentialsEntitlementType;
use Modules\Essentials\Entities\EssentialsTravelTicketCategorie;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\Essentials\Entities\EssentialsBasicSalaryType;
use Modules\Essentials\Entities\EssentialsAdmissionsToWork;
use Modules\Essentials\Entities\EssentialsProfession;
use Modules\Essentials\Entities\EssentialsSpecialization;

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
            'Modules\Essentials\Notifications\DocumentShareNotification'
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
            'Modules\Essentials\Notifications\NewMessageNotification'
        ) {
            $data = $notification->data;
            $msg = __('essentials::lang.new_message_notification', ['sender' => $data['from']]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fas fa-envelope bg-green',
                'link' => action([\Modules\Essentials\Http\Controllers\EssentialsMessageController::class, 'index']),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif (
            $notification->type ==
            'Modules\Essentials\Notifications\NewLeaveNotification'
        ) {
            $data = $notification->data;

            $employee = User::find($data['applied_by']);

            if (!empty($employee)) {
                $msg = __('essentials::lang.new_leave_notification', ['employee' => $employee->user_full_name, 'ref_no' => $data['ref_no']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\Essentials\Notifications\LeaveStatusNotification'
        ) {
            $data = $notification->data;

            $admin = User::find($data['changed_by']);

            if (!empty($admin)) {
                $msg = __('essentials::lang.status_change_notification', ['status' => $data['status'], 'ref_no' => $data['ref_no'], 'admin' => $admin->user_full_name]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\Essentials\Notifications\PayrollNotification'
        ) {
            $data = $notification->data;

            $month = \Carbon::createFromFormat('m', $data['month'])->format('F');

            $msg = '';

            $created_by = User::find($data['created_by']);

            if (!empty($created_by)) {
                if ($data['action'] == 'created') {
                    $msg = __('essentials::lang.payroll_added_notification', ['month_year' => $month . '/' . $data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                } elseif ($data['action'] == 'updated') {
                    $msg = __('essentials::lang.payroll_updated_notification', ['month_year' => $month . '/' . $data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                }

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-money-bill-alt bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\Essentials\Notifications\NewTaskNotification'
        ) {
            $data = $notification->data;

            $assigned_by = User::find($data['assigned_by']);

            if (!empty($assigned_by)) {
                $msg = __('essentials::lang.new_task_notification', ['assigned_by' => $assigned_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'ion ion-clipboard bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'show'], $data['id']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\Essentials\Notifications\NewTaskCommentNotification'
        ) {
            $data = $notification->data;

            $comment = EssentialsTodoComment::with(['task', 'added_by'])->find($data['comment_id']);
            if (!empty($comment) && $comment->task) {
                $msg = __('essentials::lang.new_task_comment_notification', ['added_by' => $comment->added_by->user_full_name, 'task_id' => $comment->task->task_id]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-envelope bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'show'], $comment->task->id),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif (
            $notification->type ==
            'Modules\Essentials\Notifications\NewTaskDocumentNotification'
        ) {
            $data = $notification->data;

            $uploaded_by = User::find($data['uploaded_by']);

            if (!empty($uploaded_by)) {
                $msg = __('essentials::lang.new_task_document_notification', ['uploaded_by' => $uploaded_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-file bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'show'], $data['id']),
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
                'value' => 'essentials.crud_leave_type',
                'label' => __('essentials::lang.crud_leave_type'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_all_leave',
                'label' => __('essentials::lang.crud_all_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'essentials.crud_own_leave',
                'label' => __('essentials::lang.crud_own_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'essentials.approve_leave',
                'label' => __('essentials::lang.approve_leave'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_all_attendance',
                'label' => __('essentials::lang.crud_all_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'essentials.view_own_attendance',
                'label' => __('essentials::lang.view_own_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'essentials.allow_users_for_attendance_from_web',
                'label' => __('essentials::lang.allow_users_for_attendance_from_web'),
                'default' => false,
            ],
            [
                'value' => 'essentials.allow_users_for_attendance_from_api',
                'label' => __('essentials::lang.allow_users_for_attendance_from_api'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_allowance_and_deduction',
                'label' => __('essentials::lang.view_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'essentials.add_allowance_and_deduction',
                'label' => __('essentials::lang.add_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_department',
                'label' => __('essentials::lang.crud_department'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_designation',
                'label' => __('essentials::lang.crud_designation'),
                'default' => false,
            ],

            [
                'value' => 'essentials.view_all_payroll',
                'label' => __('essentials::lang.view_all_payroll'),
                'default' => false,
            ],
            [
                'value' => 'essentials.create_payroll',
                'label' => __('essentials::lang.add_payroll'),
                'default' => false,
            ],
            [
                'value' => 'essentials.update_payroll',
                'label' => __('essentials::lang.edit_payroll'),
                'default' => false,
            ],
            [
                'value' => 'essentials.delete_payroll',
                'label' => __('essentials::lang.delete_payroll'),
                'default' => false,
            ],
            [
                'value' => 'essentials.assign_todos',
                'label' => __('essentials::lang.assign_todos'),
                'default' => false,
            ],
            [
                'value' => 'essentials.add_todos',
                'label' => __('essentials::lang.add_todos'),
                'default' => false,
            ],
            [
                'value' => 'essentials.edit_todos',
                'label' => __('essentials::lang.edit_todos'),
                'default' => false,
            ],
            [
                'value' => 'essentials.delete_todos',
                'label' => __('essentials::lang.delete_todos'),
                'default' => false,
            ],
            [
                'value' => 'essentials.create_message',
                'label' => __('essentials::lang.create_message'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_message',
                'label' => __('essentials::lang.view_message'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_employee_settings',
                'label' => __('essentials::lang.view_employee_settings'),
                'default' => false,
            ],

            [
                'value' => 'essentials.access_sales_target',
                'label' => __('essentials::lang.access_sales_target'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_user_travel_categorie',
                'label' => __('essentials::lang.view_user_travel_categorie'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_holidays',
                'label' => __('essentials::lang.crud_holidays'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_countries',
                'label' => __('essentials::lang.crud_countries'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_cities',
                'label' => __('essentials::lang.crud_cities'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_allowances',
                'label' => __('essentials::lang.crud_allowances'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_bank_accounts',
                'label' => __('essentials::lang.crud_bank_accounts'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_organizational_structure',
                'label' => __('essentials::lang.crud_Organizational_Chart'),
                'default' => false,
            ],

            [
                'value' => 'essentials.crud_contract_types',
                'label' => __('essentials::lang.crud_contract_types'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_all_roles',
                'label' => __('essentials::lang.crud_all_roles'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_employees',
                'label' => __('essentials::lang.view_employees'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_official_documents',
                'label' => __('essentials::lang.crud_official_documents'),
                'default' => false,
            ],

            [
                'value' => 'essentials.crud_import_employee',
                'label' => __('essentials::lang.crud_import_employee'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_insurance_companies',
                'label' => __('essentials::lang.crud_insurance_companies'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_facilities_management',
                'label' => __('essentials::lang.view_facilities_management'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_insurance_contracts',
                'label' => __('essentials::lang.crud_insurance_contracts'),
                'default' => false,
            ],

            [
                'value' => 'essentials.crud_employee_appointments',
                'label' => __('essentials::lang.crud_employee_appointments'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_employee_work_adminitions',
                'label' => __('essentials::lang.crud_employee_work_adminitions'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_employee_contracts',
                'label' => __('essentials::lang.crud_employee_contracts'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_employee_qualifications',
                'label' => __('essentials::lang.crud_employee_qualifications'),
                'default' => false,
            ],

            [
                'value' => 'essentials.crud_employee_features',
                'label' => __('essentials::lang.crud_employee_features'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_employee_families',
                'label' => __('essentials::lang.crud_employee_families'),
                'default' => false,
            ],

            [
                'value' => 'essentials.crud_employees_insurances',
                'label' => __('essentials::lang.crud_employees_insurances'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_regions',
                'label' => __('essentials::lang.crud_regions'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_profile_picture',
                'label' => __('essentials::lang.view_profile_picture'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_all_procedures',
                'label' => __('essentials::lang.crud_all_procedures'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_all_essentials_requests',
                'label' => __('essentials::lang.crud_all_essentials_requests'),
                'default' => false,
            ],

            [
                'value' => 'essentials.employees_reports_view',
                'label' => __('essentials::lang.employees_reports_view'),
                'default' => false,
            ],

            [
                'value' => 'essentials.employees_information_report',
                'label' => __('essentials::lang.employees_information_report'),
                'default' => false,
            ],
            [
                'value' => 'essentials.curd_wishes',
                'label' => __('essentials::lang.curd_wishes'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_attendances_status',
                'label' => __('essentials::lang.crud_attendances_status'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_work_cards',
                'label' => __('essentials::lang.view_work_cards'),
                'default' => false,
            ],

            [
                'value' => 'essentials.work_cards_all_requests',
                'label' => __('essentials::lang.workcards_allrequest'),
                'default' => false,
            ],

            [
                'value' => 'essentials.work_cards_vaction_requests',
                'label' => __('essentials::lang.work_cards_vaction_requests'),
                'default' => false,
            ],

            [
                'value' => 'essentials.work_cards_operation',
                'label' => __('essentials::lang.work_cards_operation'),
                'default' => false,
            ],

            [
                'value' => 'essentials.renewal_residence',
                'label' => __('essentials::lang.renewal_residence'),
                'default' => false,
            ],
            [
                'value' => 'essentials.residencyreports',
                'label' => __('essentials::lang.residencyreports'),
                'default' => false,
            ],
            [
                'value' => 'essentials.facilities_management',
                'label' => __('essentials::lang.facilities_management'),
                'default' => false,
            ],
           
            [
                'value' => 'essentials.carTypes',
                'label' => __('housingmovements::lang.carTypes'),
                'default' => false,
            ],
            [
                'value' => 'essentials.carModels',
                'label' => __('housingmovements::lang.carModels'),
                'default' => false,
            ],
            [
                'value' => 'essentials.cars',
                'label' => __('housingmovements::lang.cars'),
                'default' => false,
            ],
            [
                'value' => 'essentials.car_drivers',
                'label' => __('housingmovements::lang.car_drivers'),
                'default' => false,
            ],

            
            [
                'value' => 'essentials.movement_management',
                'label' => __('housingmovements::lang.movement_management_dashbord'),
                'default' => false,
            ],
            [
                'value' => 'essentials.carsChangeOil',
                'label' => __('housingmovements::lang.carsChangeOil'),
                'default' => false,
            ],
            [
                'value' => 'essentials.carMaintenances',
                'label' => __('housingmovements::lang.carMaintenances'),
                'default' => false,
            ],
            [
                'value' => 'essentials.report',
                'label' => __('housingmovements::lang.report'),
                'default' => false,
            ],
            [
                'value' => 'essentials.carsChangeOilReport',
                'label' => __('housingmovements::lang.carsChangeOilReport'),
                'default' => false,
            ],
            [
                'value' => 'essentials.carMaintenancesReport',
                'label' => __('housingmovements::lang.carMaintenancesReport'),
                'default' => false,
            ],

            [
                'value' => 'essentials.curd_contracts_end_reasons',
                'label' => __('essentials::lang.contracts_end_reasons'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_all_leave',
                'label' => __('essentials::lang.system_settings'),
                'default' => false,
            ],
            [
                'value' => 'essentials.curd_organizational_structure',
                'label' => __('essentials::lang.organizational_structure'),
                'default' => false,
            ],
            [
                'value' => 'essentials.essentials',
                'label' => __('essentials::lang.essentials'),
                'default' => false,
            ],


            [
                'value' => 'essentials.crud_essentials_recuirements_requests',
                'label' => __('essentials::lang.crud_essentials_recuirements_requests'),
                'default' => false,
            ],
            [
                'value' => 'essentials.essentials_dashboard',
                'label' => __('essentials::lang.essentials_dashboard'),
                'default' => false,
            ],
            [
                'value' => 'essentials.essentials_work_cards_dashboard',
                'label' => __('essentials::lang.essentials_work_cards_dashboard'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_employee_affairs',
                'label' => __('essentials::lang.view_employee_affairs'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_medical_insurance',
                'label' => __('essentials::lang.view_medical_insurance'),
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
                'name' => 'essentials_module',
                'label' => __('essentials::lang.essentials_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds Essentials menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $module_util = new ModuleUtil();

        $business_id = session()->get('user.business_id');
        $is_essentials_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'essentials_module');

        // if ($is_essentials_enabled) {
        Menu::create('custom_admin-sidebar-menu', function ($menu) {

            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home'])->order(5);
            $menu->dropdown(
                __('essentials::lang.hrm'),
                function ($subMenu) {
                    if (auth()->user()->can('essentials.view_employee_affairs')) {

                        $subMenu->url(
                            route('employees'),
                            __('essentials::lang.employees_affairs'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && (request()->segment(2) == 'employees' ||
                                request()->segment(2) == 'roles'
                                || request()->segment(2) == 'appointments'
                                || request()->segment(2) == 'admissionToWork'
                                || request()->segment(2) == 'employeeContracts'
                                || request()->segment(2) == 'qualifications'
                                || request()->segment(2) == 'official_documents'
                                || request()->segment(2) == 'featureIndex')],
                        )->order(1);
                    }

                    if (auth()->user()->can('essentials.view_facilities_management')) {
                        $subMenu->url(
                            action([\App\Http\Controllers\BusinessController::class, 'getBusiness']),
                            __('essentials::lang.facilities_management'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'getBusiness'],
                        )->order(2);
                    }

                    if (auth()->user()->can('essentials.crud_all_attendance')) {
                        $subMenu->url(

                            action([\Modules\Essentials\Http\Controllers\AttendanceController::class, 'index']),
                            __('essentials::lang.attendance'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'attendance'],
                        )->order(3);
                    }

                    if (auth()->user()->can('essentials.crud_all_leave')) {
                        $subMenu->url(
                            action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']),
                            __('essentials::lang.leave_requests'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'leave'],
                        )->order(4);
                    }

                    if (auth()->user()->can('essentials.view_all_payroll')) {
                        $subMenu->url(
                            action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index']),
                            __('essentials::lang.payroll'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'payroll'],
                        )->order(5);
                    }

                    if (auth()->user()->can('essentials.view_work_cards')) {
                        $subMenu->url(
                            action([\Modules\Essentials\Http\Controllers\WorkCardsController::class, 'index']),
                            __('essentials::lang.work_cards'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'work_cards'],
                        )->order(5);
                    }

                    // if (auth()->user()->can('essentials.crud_holidays')) 
                    // {
                    //     $subMenu->url(
                    //         action([\Modules\Essentials\Http\Controllers\EssentialsHolidayController::class, 'index']),
                    //         __('essentials::lang.requests'),
                    //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'holiday'],
                    //     )->order(6);
                    // }

                    $subMenu->url(
                        action([\App\Http\Controllers\TaxonomyController::class, 'index']),
                        __('essentials::lang.loan'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'taxonomies'],
                    )->order(7);

                    if (auth()->user()->can('essentials.crud_insurance_contracts')) {
                        $subMenu->url(
                            action([\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'index']),
                            __('essentials::lang.insurance_contracts'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'insurance_contracts'],
                        )->order(8);
                    }
                    if (auth()->user()->can('essentials.crud_insurance_companies')) {
                        $subMenu->url(
                            action([\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'index']),
                            __('essentials::lang.insurance_companies'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'insurance_companies'],
                        )->order(9);
                    }
                    if (auth()->user()->can('essentials.crud_system_settings')) {
                        $subMenu->url(
                            action([\Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'edit']),
                            __('essentials::lang.system_settings'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'settings'],
                        )->order(10);
                    }

                    if (auth()->user()->can('essentials.view_employee_settings')) {
                        $subMenu->url(
                            action([\Modules\Essentials\Http\Controllers\EssentialsCountryController::class, 'index']),
                            __('essentials::lang.employees_settings'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && (request()->segment(2) == 'countries' ||
                                request()->segment(2) == 'cities'
                                || request()->segment(2) == 'bank_accounts'
                                || request()->segment(2) == 'holiday'
                                || request()->segment(2) == 'travel_categories'
                                || request()->segment(2) == 'professions'
                                || request()->segment(2) == 'allowances'
                                || request()->segment(2) == 'contract_types'
                                || request()->segment(2) == 'insurance_categories'


                            )],
                        )->order(11);
                    }

                    // if (auth()->user()->can('essentials.access_sales_target')) 
                    // {
                    //     $subMenu->url(
                    //         action([\Modules\Essentials\Http\Controllers\SalesTargetController::class, 'index']),
                    //         __('essentials::lang.sales_target'),
                    //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'sales-target'],
                    //     )->order(11);
                    // }
                    if (auth()->user()->can('essentials.crud_import_employee')) {
                        $subMenu->url(
                            action([\Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class, 'index']),
                            __('essentials::lang.import_employees'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'import_employee'],
                        )->order(12);
                    }

                    if (auth()->user()->can('essentials.curd_organizational_structure')) {
                        $subMenu->url(

                            action([\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'index']),
                            __('essentials::lang.organizational_structure'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'settings'],
                        )->order(13);
                    }
                },
                [
                    'icon' => 'fa fas fa-users',
                    'active' => request()->segment(1) == 'essentials',
                    'style' => config('app.env') == 'demo' ? 'background-color: #605ca8 !important;' : '',
                ]
            )->order(10);

            $menu->url(
                action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index']),
                __('essentials::lang.essentials'),
                ['icon' => 'fa fas fa-check-circle', 'active' => request()->segment(1) == 'essentials' && request()->segment(2) == 'essentials', 'style' => config('app.env') == 'demo' ? 'background-color: #001f3f !important;' : '']
            )
                ->order(10);
        });
        //}
    }

    /**
     * Function to add essential module taxonomies
     *
     * @return array
     */
    public function addTaxonomies()
    {
        return [
            'hrm_department' => [
                'taxonomy_label' => __('essentials::lang.department'),
                'heading' => __('essentials::lang.departments'),
                'sub_heading' => __('essentials::lang.manage_departments'),
                'enable_taxonomy_code' => true,
                'taxonomy_code_label' => __('essentials::lang.department_id'),
                'taxonomy_code_help_text' => __('essentials::lang.department_code_help'),
                'enable_sub_taxonomy' => false,
                'navbar' => 'essentials::layouts.nav_hrm',
            ],

            'hrm_designation' => [
                'taxonomy_label' => __('essentials::lang.designation'),
                'heading' => __('essentials::lang.designations'),
                'sub_heading' => __('essentials::lang.manage_designations'),
                'enable_taxonomy_code' => false,
                'taxonomy_code_help_text' => __('essentials::lang.designation_code_help'),
                'enable_sub_taxonomy' => false,
                'navbar' => 'essentials::layouts.nav_hrm',
            ],
        ];
    }

    /**
     * Function to generate view parts
     *
     * @param  array  $data
     */
    public function moduleViewPartials($data)
    {
        if ($data['view'] == 'manage_user.create' || $data['view'] == 'manage_user.edit') {
            $business_id = session()->get('business.id');

            $designations = Category::forDropdown($business_id, 'hrm_designation');
            $departments = EssentialsDepartment::where('business_id', $business_id)->pluck('name', 'id')->all();
            $pay_comoponenets = EssentialsAllowanceAndDeduction::forDropdown($business_id);

            $user = !empty($data['user']) ? $data['user'] : null;

            $allowance_deduction_ids = [];
            if (!empty($user)) {
                $allowance_deduction_ids = EssentialsUserAllowancesAndDeduction::where('user_id', $user->id)
                    ->pluck('allowance_deduction_id')
                    ->toArray();
            }

            if (!empty($user)) {
                $contract = EssentialsEmployeesContract::where('employee_id', $user->id)->first();
            } else {
                $contract = null;
            }

            $locations = BusinessLocation::forDropdown($business_id, false, false, true, false);
            $allowance_types = EssentialsAllowanceAndDeduction::pluck('description', 'id')->all();
            $travel_ticket_categorie = EssentialsTravelTicketCategorie::pluck('name', 'id')->all();
            $contract_types = EssentialsContractType::pluck('type', 'id')->all();
            $nationalities = EssentialsCountry::nationalityForDropdown();
            $specializations = EssentialsSpecialization::all()->pluck('name', 'id');
            $professions = EssentialsProfession::all()->pluck('name', 'id');
            return view('essentials::partials.user_form_part', compact('contract', 'nationalities', 'travel_ticket_categorie', 'contract_types', 'allowance_types', 'specializations', 'professions', 'departments', 'designations', 'user', 'pay_comoponenets', 'allowance_deduction_ids', 'locations'))
                ->render();
        } elseif ($data['view'] == 'manage_user.show') {
            $user = !empty($data['user']) ? $data['user'] : null;
            $user_department = EssentialsDepartment::find($user->essentials_department_id);
            $user_designstion = Category::find($user->essentials_designation_id);
            $work_location = BusinessLocation::find($user->location_id);
            $contract = EssentialsEmployeesContract::where('employee_id', $user->id)->select([
                'essentials_employees_contracts.id',
                'essentials_employees_contracts.contract_number',
                'essentials_employees_contracts.contract_start_date',
                'essentials_employees_contracts.contract_end_date',
                'essentials_employees_contracts.contract_duration',
                'essentials_employees_contracts.contract_per_period',
                'essentials_employees_contracts.probation_period',
                'essentials_employees_contracts.status',
                'essentials_employees_contracts.is_renewable',

            ])->first();
            return view('essentials::partials.user_details_part', compact('contract', 'user_department', 'user_designstion', 'user', 'work_location'))
                ->render();
        }
    }

    /**
     * Function to process model after being saved
     *
     * @param  array  $data['event' => 'Event name', 'model_instance' => 'Model instance']
     */
    public function afterModelSaved($data)
    {

        if ($data['event'] == 'user_saved') {


            $user = $data['model_instance'];
            $user->essentials_department_id = request()->input('essentials_department_id');
            $user->essentials_designation_id = request()->input('essentials_designation_id');
            $user->essentials_salary = request()->input('essentials_salary');
            $user->essentials_pay_period = request()->input('essentials_pay_period');
            $user->essentials_pay_cycle = request()->input('essentials_pay_cycle');
            $user->location_id = request()->input('location_id');
            if (request()->input('health_insurance') != null) {
                $user->has_insurance = request()->input('health_insurance');
            }

            $user->save();


            if (
                request()->input('contract_number') != null || request()->input('contract_type') != null
                || request()->input('contract_start_date') != null || request()->input('contract_end_date') != null
            ) {

                $contractDuration =  request()->input('contract_duration');
                $contract_per_period = request()->input('contract_duration_unit');
                $contract = new EssentialsEmployeesContract();
                $contract->employee_id = $user->id;
                $latestRecord = EssentialsEmployeesContract::orderBy('contract_number', 'desc')->first();


                if ($latestRecord) {
                    $latestRefNo = $latestRecord->contract_number;
                    $numericPart = (int)substr($latestRefNo, 3);
                    $numericPart++;
                    $contract->contract_number = 'EC' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);
                } else {

                    $contract->contract_number = 'EC0001';
                }

                //  $contract->contract_number = request()->input('contract_number');
                $contract->contract_start_date = request()->input('contract_start_date');
                $contract->contract_end_date = request()->input('contract_end_date');
                $contract->contract_duration = $contractDuration;
                $contract->contract_per_period = $contract_per_period;
                $contract->probation_period = request()->input('probation_period');
                $contract->is_renewable = request()->input('is_renewable');
                $contract->contract_type_id = request()->input('contract_type');



                if (request()->hasFile('contract_file')) {
                    $file = request()->file('contract_file');
                    $filePath = $file->store('/employee_contracts');
                    $contract->file_path = $filePath;
                }
                $contract->save();
            }





            if (request()->input('qualification_type')) {
                $qualification2 = new EssentialsEmployeesQualification();

                $qualification2->qualification_type = request()->input('qualification_type');
                $qualification2->major = request()->input('major');
                $qualification2->graduation_year =  request()->input('graduation_year');
                $qualification2->graduation_institution =  request()->input('graduation_institution');
                $qualification2->employee_id = $user->id;
                $qualification2->graduation_country = request()->input('graduation_country');
                $qualification2->degree =  request()->input('degree');

                $qualification2->save();
            }




            if (request()->input('document_type')) {
                $document2 = new EssentialsOfficialDocument();
                $document2->type = request()->input('document_type');
                $document2->employee_id =   $user->id;
                $document2->status = 'valid';

                if (request()->hasFile('document_file')) {
                    $file = request()->file('document_file');
                    $filePath = $file->store('/officialDocuments');

                    $document2->file_path = $filePath;
                }

                $document2->save();
            }



            if (request()->input('can_add_category') == 1 && request()->input('travel_ticket_categorie')) {
                $travel_ticket_categorie = new EssentialsEmployeeTravelCategorie();
                $travel_ticket_categorie->employee_id = $user->id;
                $travel_ticket_categorie->categorie_id = request()->input('travel_ticket_categorie');
                $travel_ticket_categorie->save();
            }
            if (request()->input('essentials_department_id')) {
                $essentials_employee_appointmets = new EssentialsEmployeeAppointmet();
                $essentials_employee_appointmets->employee_id = $user->id;
                $essentials_employee_appointmets->department_id = request()->input('essentials_department_id');
                $essentials_employee_appointmets->business_location_id = request()->input('location_id');
                // $essentials_employee_appointmets->superior = "superior";
                $essentials_employee_appointmets->profession_id = (int)$data['request']['profession'];
                $essentials_employee_appointmets->specialization_id = (int)$data['request']['specialization'];
                $essentials_employee_appointmets->save();
            }


            if (request()->selectedData) {
                $jsonData = json_decode(request()->selectedData, true);
                foreach ($jsonData as $item) {


                    try {
                        $userAllowancesAndDeduction = new EssentialsUserAllowancesAndDeduction();
                        $userAllowancesAndDeduction->user_id = $user->id;
                        $userAllowancesAndDeduction->allowance_deduction_id = (int)$item['salaryType'];

                        if ($item['amount'] != Null) {
                            $userAllowancesAndDeduction->amount = $item['amount'];
                        } else {
                            $allowanceDeduction = Db::table('essentials_allowances_and_deductions')
                                ->where('id', $item['salaryType'])
                                ->first();

                            if ($allowanceDeduction) {
                                $userAllowancesAndDeduction->amount = $allowanceDeduction->amount;
                            }
                        }
                        $userAllowancesAndDeduction->save();
                    } catch (\Exception $e) {
                        \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                        error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                    }
                }
            }
        }



        //update
        if ($data['event'] == 'user_updated') {


            $user = $data['model_instance'];

            $user->essentials_department_id = request()->input('essentials_department_id');
            $user->essentials_designation_id = request()->input('essentials_designation_id');
            $user->essentials_salary = request()->input('essentials_salary');
            $user->essentials_pay_period = request()->input('essentials_pay_period');
            $user->essentials_pay_cycle = request()->input('essentials_pay_cycle');
            $user->user_type = request()->input('user_type');
            $user->location_id = request()->input('location_id');

            if (request()->input('health_insurance') != null) {
                $user->has_insurance = request()->input('health_insurance');
            }
            if (request()->input('border_no') == 3) {

                $user->border_no = null;
            }

            if (request()->input('contact_number') == 05) {

                $user->contact_number = null;
            }




            $user->save();

            if (!empty(request()->input('expiration_date'))) {
                $id = $data['model_instance']['id'];
                $doc = EssentialsOfficialDocument::where('employee_id', $id)->first();

                if ($doc) {
                    $doc->type = 'residence_permit';
                    $doc->status = 'vaild';
                    $doc->employee_id = $user->id;
                    $doc->number = request()->input('id_proof_number');
                    $doc->expiration_date = request()->input('expiration_date');
                    $doc->update();
                } else {
                    $doc = new EssentialsOfficialDocument();
                    $doc->type = 'residence_permit';
                    $doc->status = 'vaild';
                    $doc->employee_id = $user->id;
                    $doc->number = request()->input('id_proof_number');
                    $doc->expiration_date = request()->input('expiration_date');
                    $doc->save();
                }
            }

            $id = $data['model_instance']['id'];
            if (request()->input('qualification_type') != null) {

                $qualification2 = EssentialsEmployeesQualification::where('employee_id', $id)->first();
                if ($qualification2) {
                    $qualification2->qualification_type = request()->input('qualification_type');
                    $qualification2->major = request()->input('major');
                    $qualification2->graduation_year =  request()->input('graduation_year');
                    $qualification2->graduation_institution =  request()->input('graduation_institution');
                    $qualification2->employee_id = $user->id;
                    $qualification2->graduation_country = request()->input('graduation_country');
                    $qualification2->degree =  request()->input('degree');

                    $qualification2->save();
                } else {
                    $qualification2 = new EssentialsEmployeesQualification();

                    $qualification2->qualification_type = request()->input('qualification_type');
                    $qualification2->major = request()->input('major');
                    $qualification2->graduation_year =  request()->input('graduation_year');
                    $qualification2->graduation_institution =  request()->input('graduation_institution');
                    $qualification2->employee_id = $user->id;
                    $qualification2->graduation_country = request()->input('graduation_country');
                    $qualification2->degree =  request()->input('degree');
                    $qualification2->save();
                }
            }


            if (request()->input('document_type')) {
                $document2 = EssentialsOfficialDocument::where('employee_id', $id)->first();
                if ($document2) {

                    $document2->type = request()->input('document_type');
                    $document2->employee_id =   $user->id;
                    $document2->status = 'valid';

                    if (request()->hasFile('document_file')) {
                        $file = request()->file('document_file');
                        $filePath = $file->store('/officialDocuments');

                        $document2->file_path = $filePath;
                    }

                    $document2->save();
                } else {
                    $document2 = new EssentialsOfficialDocument();
                    $document2->type = request()->input('document_type');
                    $document2->employee_id =   $user->id;
                    $document2->status = 'valid';

                    if (request()->hasFile('document_file')) {
                        $file = request()->file('document_file');
                        $filePath = $file->store('/officialDocuments');

                        $document2->file_path = $filePath;
                    }

                    $document2->save();
                }
            }

            $id = $data['model_instance']['id'];
            if (
                request()->input('contract_number') != null || request()->input('contract_type') != null
                || request()->input('contract_start_date') != null || request()->input('contract_end_date') != null
            ) {
                $contractDuration =  request()->input('contract_duration');
                $contract_per_period = request()->input('contract_duration_unit');
                $contract = EssentialsEmployeesContract::where('employee_id', $id)->first();

                if ($contract) {
                    $contract->contract_number = request()->input('contract_number');
                    $contract->contract_start_date = request()->input('contract_start_date');
                    $contract->contract_end_date = request()->input('contract_end_date');
                    $contract->contract_duration = $contractDuration;
                    $contract->contract_per_period = $contract_per_period;
                    $contract->probation_period = request()->input('probation_period');
                    $contract->is_renewable = request()->input('is_renewable');
                    $contract->contract_type_id = request()->input('contract_type');

                    if (request()->hasFile('contract_file')) {
                        $file = request()->file('contract_file');
                        $filePath = $file->store('/employee_contracts');
                        $contract->file_path = $filePath;
                    }

                    $contract->save();
                } else {
                    $contractDuration =  request()->input('contract_duration');
                    $contract_per_period = request()->input('contract_duration_unit');

                    $contract = new EssentialsEmployeesContract();
                    $contract->employee_id = $user->id;
                    $contract->contract_number = request()->input('contract_number');
                    $contract->contract_start_date = request()->input('contract_start_date');
                    $contract->contract_end_date = request()->input('contract_end_date');
                    $contract->contract_duration = $contractDuration;
                    $contract->contract_per_period = $contract_per_period;
                    $contract->probation_period = request()->input('probation_period');
                    $contract->is_renewable = request()->input('is_renewable');
                    $contract->contract_type_id = request()->input('contract_type');



                    if (request()->hasFile('contract_file')) {
                        $file = request()->file('contract_file');
                        $filePath = $file->store('/employee_contracts');
                        $contract->file_path = $filePath;
                    }
                    $contract->save();
                }
            }
            if (request()->input('can_add_category') == 1 && request()->input('travel_ticket_categorie')) {

                $travel_ticket_categorie = EssentialsEmployeeTravelCategorie::where('employee_id', $id)->first();

                if ($travel_ticket_categorie) {
                    $travel_ticket_categorie->categorie_id = request()->input('travel_ticket_categorie');
                    $travel_ticket_categorie->save();
                }
            }

            if (request()->input('essentials_department_id')) {

                $essentials_employee_appointmets = EssentialsEmployeeAppointmet::where('employee_id', $id)->first();

                if ($essentials_employee_appointmets) {
                    $essentials_employee_appointmets->department_id = request()->input('essentials_department_id');
                    $essentials_employee_appointmets->business_location_id = request()->input('location_id');
                    // $essentials_employee_appointmets->superior = "superior";
                    $essentials_employee_appointmets->profession_id = (int)$data['request']['profession'];
                    $essentials_employee_appointmets->specialization_id = (int)$data['request']['specialization'];
                    $essentials_employee_appointmets->save();
                }
            }

            if (request()->selectedData) {
                $jsonData = json_decode(request()->selectedData, true);

                foreach ($jsonData as $item) {
                    try {
                        $userAllowancesAndDeduction = EssentialsUserAllowancesAndDeduction::where('user_id', $id)
                            ->where('allowance_deduction_id', (int)$item['salaryType'])
                            ->first();

                        if ($userAllowancesAndDeduction) {
                            if ($item['amount'] !== null) {
                                $userAllowancesAndDeduction->amount = $item['amount'];
                            } else {
                                $allowanceDeduction = Db::table('essentials_allowances_and_deductions')
                                    ->where('id', $item['salaryType'])
                                    ->first();

                                if ($allowanceDeduction) {
                                    $userAllowancesAndDeduction->amount = $allowanceDeduction->amount;
                                }
                            }
                            $userAllowancesAndDeduction->save();
                        }
                    } catch (\Exception $e) {
                        \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                        error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                    }
                }
            }
        }
    }



    // In your AllowanceController





    // public function afterModelSaved($data)
    // {
    //     if ($data['event'] = 'user_saved') {
    //         $user = $data['model_instance'];
    //         $user->essentials_department_id = request()->input('essentials_department_id');
    //         $user->essentials_designation_id = request()->input('essentials_designation_id');
    //         $user->essentials_salary = request()->input('essentials_salary');
    //         $user->essentials_pay_period = request()->input('essentials_pay_period');
    //         $user->essentials_pay_cycle = request()->input('essentials_pay_cycle');
    //         $user->location_id = request()->input('location_id');
    //         $user->save();

    //         $non_deleteable_pc_ids = $this->getNonDeletablePayComponents($user->business_id, $user->id);

    //         //delete  existing pay component
    //         EssentialsUserAllowancesAndDeduction::where('user_id', $user->id)
    //                 ->whereNotIn('allowance_deduction_id', $non_deleteable_pc_ids)
    //                 ->delete();

    //         //if pay component exist add to db
    //         if (! empty(request()->input('pay_components'))) {
    //             $pay_components = request()->input('pay_components');
    //             foreach ($pay_components as $key => $pay_component) {
    //                 EssentialsUserAllowancesAndDeduction::insert(['user_id' => $user->id, 'allowance_deduction_id' => $pay_component]);
    //             }
    //         }
    //     }
    // }

    public function profitLossReportData($data)
    {
        $business_id = $data['business_id'];
        $location_id = !empty($data['location_id']) ? $data['location_id'] : null;
        $start_date = !empty($data['start_date']) ? $data['start_date'] : null;
        $end_date = !empty($data['end_date']) ? $data['end_date'] : null;
        $user_id = !empty($data['user_id']) ? $data['user_id'] : null;

        $total_payroll = $this->__getTotalPayroll(
            $business_id,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        $report_data = [
            //left side data
            [
                [
                    'value' => $total_payroll,
                    'label' => __('essentials::lang.total_payroll'),
                    'add_to_net_profit' => true,
                ],
            ],

            //right side data
            [],
        ];

        return $report_data;
    }

    /**
     * Calculates total payroll
     *
     * @param  int  $business_id
     * @param  string  $start_date = null
     * @param  string  $end_date = null
     * @param  int  $location_id = null
     * @return array
     */
    private function __getTotalPayroll(
        $business_id,
        $start_date = null,
        $end_date = null,
        $location_id = null,
        $user_id = null
    ) {
        $transactionUtil = new TransactionUtil();

        $transaction_totals = $transactionUtil->getTransactionTotals(
            $business_id,
            ['payroll'],
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        return $transaction_totals['total_payroll'];
    }

    /**
     * Fetches all calender events for the module
     *
     * @param  array  $data
     * @return array
     */
    public function calendarEvents($data)
    {
        $events = [];
        if (in_array('todo', $data['events'])) {
            $todos = ToDo::where('business_id', $data['business_id'])
                ->with(['users'])
                ->where(function ($query) use ($data) {
                    $query->where('created_by', $data['user_id'])
                        ->orWhereHas('users', function ($q) use ($data) {
                            $q->where('user_id', $data['user_id']);
                        });
                })
                ->whereBetween(DB::raw('date(date)'), [$data['start_date'], $data['end_date']])
                ->get();

            foreach ($todos as $todo) {
                $events[] = [
                    'title' => $todo->task,
                    'start' => $todo->date,
                    'end' => $todo->end_date,
                    'url' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index']),
                    'backgroundColor' => '#33006F',
                    'borderColor' => '#33006F',
                    'event_type' => 'todo',
                    'allDay' => false,
                ];
            }
        }

        if (in_array('holiday', $data['events'])) {
            $holidays_query = EssentialsHoliday::where('business_id', $data['business_id']);

            if (!empty($data['user_id'])) {
                $user = User::where('business_id', $data['business_id'])->find($data['user_id']);
                $permitted_locations = $user->permitted_locations();
                if ($permitted_locations != 'all') {
                    $holidays_query->where(function ($query) use ($permitted_locations) {
                        $query->whereIn('location_id', $permitted_locations)
                            ->orWhereNull('location_id');
                    });
                }
            }

            if (!empty($data['location_id'])) {
                $holidays_query->where('location_id', $data['location_id']);
            }

            $holidays = $holidays_query->whereDate(
                'start_date',
                '>=',
                $data['start_date']
            )
                ->whereDate('start_date', '<=', $data['end_date'])
                ->get();

            foreach ($holidays as $holiday) {
                $events[] = [
                    'title' => $holiday->name,
                    'start' => $holiday->start_date,
                    'end' => $holiday->end_date,
                    'url' => action([\Modules\Essentials\Http\Controllers\EssentialsHolidayController::class, 'index']),
                    'backgroundColor' => '#568203',
                    'borderColor' => '#568203',
                    'allDay' => true,
                    'event_type' => 'holiday',
                ];
            }
        }

        if (in_array('leaves', $data['events'])) {
            $leaves_query = EssentialsLeave::where('essentials_leaves.business_id', $data['business_id'])
                ->join('users as u', 'u.id', '=', 'essentials_leaves.user_id')
                ->join('essentials_leave_types as lt', 'lt.id', '=', 'essentials_leaves.essentials_leave_type_id')
                ->select([
                    'essentials_leaves.id',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                    'lt.leave_type',
                    'start_date',
                    'end_date',
                ]);

            if (!empty($data['user_id'])) {
                $leaves_query->where('essentials_leaves.user_id', $data['user_id']);
            }

            $leaves = $leaves_query->whereDate('essentials_leaves.start_date', '>=', $data['start_date'])
                ->whereDate('essentials_leaves.start_date', '<=', $data['end_date'])
                ->get();
            foreach ($leaves as $leave) {
                $events[] = [
                    'title' => $leave->user,
                    'title_html' => $leave->user . '<br>' . $leave->leave_type,
                    'start' => $leave->start_date,
                    'end' => $leave->end_date,
                    'url' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']),
                    'backgroundColor' => '#BA0021',
                    'borderColor' => '#BA0021',
                    'allDay' => true,
                    'event_type' => 'leaves',
                ];
            }
        }

        if (in_array('reminder', $data['events'])) {
            $reminder_events = Reminder::getReminders($data);
            $events = array_merge($events, $reminder_events);
        }

        return $events;
    }

    /**
     * List of calendar event types
     *
     * @return array
     */
    public function eventTypes()
    {
        return [
            'todo' => [
                'label' => __('essentials::lang.todo'),
                'color' => '#33006F',
            ],
            'holiday' => [
                'label' => __('essentials::lang.holidays'),
                'color' => '#568203',
            ],
            'leaves' => [
                'label' => __('essentials::lang.leaves'),
                'color' => '#BA0021',
            ],
            'reminder' => [
                'label' => __('essentials::lang.reminders'),
                'color' => '#ff851b',
            ],
        ];
    }

    /**
     * Returns addtional js, css, html and files which
     * will be included in the app layout
     *
     * @return array
     */
    public function get_additional_script()
    {
        $additional_js = '';
        $additional_css = '';
        $additional_html =
            '<div class="modal fade" id="task_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
        </div>';
        $additional_views = ['essentials::todo.todo_javascript'];

        return [
            'additional_js' => $additional_js,
            'additional_css' => $additional_css,
            'additional_html' => $additional_html,
            'additional_views' => $additional_views,
        ];
    }

    /**
     * Returns pay components who has applicable date
     * and assigned to given user
     *
     * @return array
     */
    public function getNonDeletablePayComponents($business_id, $user_id)
    {
        $ads = EssentialsAllowanceAndDeduction::join('essentials_user_allowance_and_deductions as euad', 'euad.allowance_deduction_id', '=', 'essentials_allowances_and_deductions.id')
            ->whereNotNull('essentials_allowances_and_deductions.applicable_date')
            ->where('business_id', $business_id)
            ->where('euad.user_id', $user_id)
            ->get();

        $ids = $ads->pluck('id')->toArray();

        return $ids;
    }

    /**
     * Returns todo dropdown
     *
     * @param $business_id
     * @return array
     */
    public function getTodosDropdown($business_id)
    {
        $todos = ToDo::where('business_id', $business_id)
            ->select(DB::raw("CONCAT(task, ' (', task_id , ')') AS task_name"), 'id')
            ->pluck('task_name', 'id')
            ->toArray();

        return $todos;
    }

    /**
     * Returns task for user
     *
     * @param $user_id
     * @return array
     */
    public function getAssignedTaskForUser($user_id)
    {
        $task_ids = DB::table('essentials_todos_users')
            ->where('user_id', $user_id)
            ->pluck('todo_id')
            ->toArray();

        return $task_ids;
    }
}