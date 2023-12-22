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
                'value' => 'internationalrelations.crud_airlines',
                'label' => __('internationalrelations::lang.crud_airlines'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.crud_employment_companies',
                'label' => __('internationalrelations::lang.crud_employment_companies'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_company_requests',
                'label' => __('internationalrelations::lang.view_company_requests'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.store_emoloyment_company',
                'label' => __('internationalrelations::lang.store_emoloyment_company'),
                'default' => false,
            ],
          
            [
                'value' => 'internationalrelations.crud_orders_operations',
                'label' => __('internationalrelations::lang.crud_orders_operations'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.delegate_order',
                'label' => __('internationalrelations::lang.delegate_order'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_delegation_info',
                'label' => __('internationalrelations::lang.view_delegation_info'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_delegation',
                'label' => __('internationalrelations::lang.view_delegation'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.crud_visa_cards',
                'label' => __('internationalrelations::lang.crud_visa_cards'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.store_visa_card',
                'label' => __('internationalrelations::lang.store_visa_card'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_visa_workers',
                'label' => __('internationalrelations::lang.view_visa_workers'),
                'default' => false,
            ],

            [
                'value' => 'internationalrelations.view_proposed_labors',
                'label' => __('internationalrelations::lang.view_proposed_labors'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_worker_info',
                'label' => __('internationalrelations::lang.view_worker_info'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_accepted_workers',
                'label' => __('internationalrelations::lang.view_accepted_workers'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.view_unaccepted_workers',
                'label' => __('internationalrelations::lang.view_unaccepted_workers'),
                'default' => false,
            ],

            [
                'value' => 'internationalrelations.fingerprinting',
                'label' => __('internationalrelations::lang.fingerprinting'),
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
                'value' => 'internationalrelations.send_offer_price',
                'label' => __('internationalrelations::lang.send_offer_price'),
                'default' => false,
            ],
            
            [
                'value' => 'internationalrelations.accepted_by_worker',
                'label' => __('internationalrelations::lang.accepted_by_worker'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.store_visa_worker',
                'label' => __('internationalrelations::lang.store_visa_worker'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.store_proposed_labor',
                'label' => __('internationalrelations::lang.store_proposed_labor'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.change_worker_status',
                'label' => __('internationalrelations::lang.change_worker_status'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.crud_all_reports',
                'label' => __('internationalrelations::lang.crud_all_reports'),
                'default' => false,
            ],
            [
                'value' => 'internationalrelations.crud_all_salary_requests',
                'label' => __('internationalrelations::lang.crud_all_salary_requests'),
                'default' => false,
            ],
              [
                'value' => 'internationalrelations.crud_all_ir_requests',
                'label' => __('internationalrelations::lang.crud_all_ir_requests'),
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

            Menu::create('custom_admin-sidebar-menu', function ($menu) {
                $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home'])->order(5);

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
