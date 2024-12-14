<?php

namespace Modules\InternationalRelations\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
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
                'group_name' => __('internationalrelations::lang.International'),
                'group_permissions' => [
                    [
                        'value' => 'internationalrelations.internationalrelations_dashboard',
                        'label' => __('internationalrelations::lang.internationalrelations_dashboard'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_operations_order_for_contract',
                        'label' => __('internationalrelations::lang.view_operations_order_for_contract'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.view_operations_order_for_unSupported_workers',
                        'label' => __('internationalrelations::lang.view_operations_order_for_unSupported_workers'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.add_ir_operation_orders',
                        'label' => __('sales::lang.add_sale_operation_orders'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.add_operation_order_visa',
                        'label' => __('internationalrelations::lang.add_operation_order_visa'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.delegate_operation_order',
                        'label' => __('internationalrelations::lang.delegate_operation_order'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.view_order_delegations',
                        'label' => __('internationalrelations::lang.view_order_delegations'),
                        'default' => false,
                    ],

                    //

                    [
                        'value' => 'internationalrelations.view_employment_companies',
                        'label' => __('internationalrelations::lang.view_employment_companies'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.add_employment_company',
                        'label' => __('internationalrelations::lang.add_employment_company'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.edit_employment_company',
                        'label' => __('internationalrelations::lang.edit_employment_company'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.show_employment_company_profile',
                        'label' => __('internationalrelations::lang.show_employment_company_profile'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.change_arrival_date',
                        'label' => __('internationalrelations::lang.change_arrival_date'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.international_reports',
                        'label' => __('internationalrelations::lang.international_reports'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.view_visa_reports',
                        'label' => __('internationalrelations::lang.view_visa_reports'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_employment_company_delegation_requests',
                        'label' => __('internationalrelations::lang.view_employment_company_delegation_requests'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_all_delegation_requests',
                        'label' => __('internationalrelations::lang.view_all_delegation_requests'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.add_proposed_worker',
                        'label' => __('internationalrelations::lang.add_proposed_worker'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.import_proposed_workers',
                        'label' => __('internationalrelations::lang.import_proposed_workers'),
                        'default' => false,
                    ],

                    //TODO::import_new_arrival_workers
                    [
                        'value' => 'internationalrelations.import_new_arrival_workers',
                        'label' => __('internationalrelations::lang.import_new_arrival_workers'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_proposed_workers',
                        'label' => __('internationalrelations::lang.view_proposed_workers'),
                        'default' => false,
                    ],
                    //internationalrelations.Unsupported_workers
                    [
                        'value' => 'internationalrelations.Unsupported_workers',
                        'label' => __('sales::lang.Unsupported_workers'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.view_accepted_workers',
                        'label' => __('internationalrelations::lang.view_accepted_workers'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.view_under_trialPeriod_workers',
                        'label' => __('internationalrelations::lang.view_under_trialPeriod_workers'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.view_unaccepted_workers',
                        'label' => __('internationalrelations::lang.view_unaccepted_workers'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.change_worker_interview_status',
                        'label' => __('internationalrelations::lang.change_worker_interview_status'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_worker_profile',
                        'label' => __('internationalrelations::lang.view_worker_profile'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.send_price_offer_to_worker',
                        'label' => __('internationalrelations::lang.send_price_offer_to_worker'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.accepte_offer_from_worker',
                        'label' => __('internationalrelations::lang.accepte_offer_from_worker'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_visa_cards',
                        'label' => __('internationalrelations::lang.view_visa_cards'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.view_visa_card_workers',
                        'label' => __('internationalrelations::lang.view_visa_card_workers'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.add_worker_to_visa',
                        'label' => __('internationalrelations::lang.add_worker_to_visa'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.medical_examination',
                        'label' => __('internationalrelations::lang.medical_examination'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.passport_stamped',
                        'label' => __('internationalrelations::lang.passport_stamped'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.fingerprinting',
                        'label' => __('internationalrelations::lang.fingerprinting'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_Airlines',
                        'label' => __('internationalrelations::lang.view_Airlines'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.add_Airline_company',
                        'label' => __('internationalrelations::lang.add_Airline_company'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.edit_Airline_company',
                        'label' => __('internationalrelations::lang.edit_Airline_company'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.delete_Airline_company',
                        'label' => __('internationalrelations::lang.delete_Airline_company'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_ir_requests',
                        'label' => __('internationalrelations::lang.view_ir_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.add_request',
                        'label' => __('internationalrelations::lang.add_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.change_request_status',
                        'label' => __('internationalrelations::lang.change_request_status'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.return_ir_request',
                        'label' => __('internationalrelations::lang.return_ir_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.show_ir_request',
                        'label' => __('internationalrelations::lang.show_ir_request'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.crud_all_reports',
                        'label' => __('internationalrelations::lang.crud_all_reports'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.view_all_salary_requests',
                        'label' => __('internationalrelations::lang.view_all_salary_requests'),
                        'default' => false,
                    ],

                    [
                        'value' => 'internationalrelations.view_travel_categorie_requests',
                        'label' => __('internationalrelations::lang.view_travel_categorie_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'internationalrelations.book_visa',
                        'label' => __('internationalrelations::lang.book_visa'),
                        'default' => false,
                    ],

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

            Menu::create('custom_admin-sidebar-menu', function ($menu) {
                $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home'])->order(5);

                $menu->dropdown(
                    __('internationalrelations::lang.International'),
                    function ($subMenu) {

                        if (auth()->user()->can('internationalrelations.view_dashboard')) {
                            $subMenu->url(
                                action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index']),
                                __('internationalrelations::lang.dashboard'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'dashboard'],
                            )->order(1);
                        }

                        if (auth()->user()->can('internationalrelations.view_Airlines')) {
                            $subMenu->url(
                                action([\Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'index']),
                                __('internationalrelations::lang.Airlines'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'Airlines'],
                            )->order(2);
                        }
                        if (auth()->user()->can('internationalrelations.view_EmploymentCompanies')) {
                            $subMenu->url(
                                action([\Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'index']),
                                __('internationalrelations::lang.EmploymentCompanies'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'EmploymentCompanies'],
                            )->order(3);
                        }
                        if (auth()->user()->can('internationalrelations.order_request_view')) {
                            $subMenu->url(
                                action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'index']),
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
            });
        }
    }
}
