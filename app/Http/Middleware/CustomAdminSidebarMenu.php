<?php

namespace App\Http\Middleware;

use Closure;
use Menu;
use Illuminate\Support\Str;

class CustomAdminSidebarMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ajax()) {
            return $next($request);
        }
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);
        });
        $currentPath = $request->path();
        // Define logic to set the menuName based on the route
        if (Str::startsWith($currentPath, ['users', 'manage_user', 'roles', 'get-all-users'])) {
            $this->userManagementMenu();
        } elseif (Str::startsWith($currentPath, ['work_cards'])) {
            $this->workCardsMenu();
        } elseif (Str::startsWith($currentPath, ['medicalInsurance'])) {
            $this->medicalInsuranceMenu();
        } elseif (Str::startsWith($currentPath, ['employee_affairs'])) {
            $this->employeeAffairsMenu();
        } elseif (Str::startsWith($currentPath, ['essentials', 'hrm'])) {
            $this->essentialsMenu();
        } elseif (Str::startsWith($currentPath, ['asset', 'taxonomies'])) {
            $this->assetManagementMenu();
        } elseif (Str::startsWith($currentPath, ['sale'])) {
            $this->CUS_salesMenu();
        } elseif (Str::startsWith($currentPath, 'housingmovements')) {
            $this->houseMovementsMenu();
        } elseif (Str::startsWith($currentPath, ['international', 'ir'])) {
            $this->getIRMenu();
        } elseif (Str::startsWith($currentPath, ['all-accounting',])) {
            $this->allAccountingMenu();
        } elseif (Str::startsWith($currentPath, ['accounting', 'sells'])) {
            $this->accountingMenu();
        } elseif (Str::startsWith($currentPath, 'followup')) {
            $this->followUpMenu();
        } elseif (Str::startsWith($currentPath, 'purchase')) {
            $this->purchasesMenu();
        } elseif (Str::startsWith($currentPath, ['movment', 'dashboard-movment'])) {
            $this->movmentMenu();
        } elseif (Str::startsWith($currentPath, ['legalaffairs',])) {
            $this->legalAffairsMenu();
        }
        // elseif (Str::startsWith($currentPath, [
        //     'superadmin',
        //     'subscription',
        //     'alladminRequests',
        //     'manage-modules',
        //     'backup',
        // ])) {
        //     $this->superAdminMenu();
        // } 
        elseif (
            Str::startsWith($currentPath, [
                'product',
                'taxonom',
                'import-opening-stock',
                'update-product-price',
                'labels',
                'variation-templates',
                'import-products',
                'selling-price-group',
                'units',
                'brands',
                'warranties',
            ])

        ) {
            $this->productsMenu();
        } elseif (Str::startsWith($currentPath, 'connector')) {
            $this->connectorMenu();
        } elseif (Str::startsWith($currentPath, 'agent')) {
            $this->agnetMenu();
        } elseif (Str::startsWith($currentPath, 'asset')) {
            $this->assetManagementMenu();
        }
        //  elseif (Str::startsWith($currentPath, 'crm')) {
        //     $this->crmMenu();
        // } 
        elseif (Str::startsWith($currentPath, 'generalmanagement')) {
            $this->generalmanagementMenu();
        } elseif (Str::startsWith($currentPath, 'ceomanagment')) {
            $this->ceoMenu();
        } elseif (Str::startsWith($currentPath, 'toDo')) {
            $this->toDoMenu();
        } elseif (Str::startsWith($currentPath, ['helpdesk', 'tickets'])) {
            $this->helpdeskMenu();
        } elseif (Str::startsWith($currentPath, ['employee_requests'])) {
            $this->myMenu();
        } elseif ($is_admin) {
            $this->settingsMenu();
        } else {
        }





        //Add menus from modules
        // 
        // 
        // $moduleUtil->getModuleData('modifyAdminMenu_hm');
        // $moduleUtil->getModuleData('modifyAdminMenu_IR');
        // $moduleUtil->getModuleData('modifyAdminMenu_CUS_sales');
        return $next($request);
    }
    public function toDoMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            if ($is_admin  || auth()->user()->can('essentials.essentials_todo_dashboard')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index']),
                    __('essentials::lang.todo'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'toDo' &&
                        request()->segment(2) == 'todo_dashboard'],
                );
            }


            if ($is_admin  || auth()->user()->can('essentials.view_document')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\DocumentController::class, 'index']),
                    __('essentials::lang.document'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'toDo' &&
                        request()->segment(2) == 'document'],
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.view_memos')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\DocumentController::class, 'index']) . '?type=memos',
                    __('essentials::lang.memos'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'toDo' &&
                        request()->segment(2) == 'document'],
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.view_reminder')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\ReminderController::class, 'index']),
                    __('essentials::lang.reminders'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'toDo' &&
                        request()->segment(2) == 'reminder'],
                );
            }



            if ($is_admin  || auth()->user()->can('essentials.view_message') ||  auth()->user()->can('essentials.create_message')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsMessageController::class, 'index']),
                    __('essentials::lang.messages'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'toDo' &&
                        request()->segment(2) == 'messages'],
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.view_knowledge_base')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\KnowledgeBaseController::class, 'index']),
                    __('essentials::lang.knowledge_base'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'toDo' &&
                        request()->segment(2) == 'knowledge-base'],
                );
            }

            // if ($is_admin  || auth()->user()->can('essentials.edit_essentials_settings')  ) {
            //     $menu->url(
            //         action([\Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'edit']),
            //         __('business.settings'),
            //         ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'toDo' &&   request()->segment(1) == 'toDo'&&
            //         request()->segment(2) == 'settings'],
            //     );
            // }


        });
    }

    // public function crmMenu()
    // {
    //     Menu::create('admin-sidebar-menu', function ($menu) {
    //         $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
    //         $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
    //         $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
    //         $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
    //         $menu->url(action([\Modules\Crm\Http\Controllers\CrmDashboardController::class, 'index']), __('crm::lang.crm'), ['icon' => 'fas fa fa-broadcast-tower', 'style' => config('app.env') == 'demo' ? 'background-color: #8CAFD4;' : '', 'active' => request()->segment(1) == 'crm' || request()->get('type') == 'life_stage' || request()->get('type') == 'source']);
    //         //$menu->header("");
    //         //$menu->header("");
    //         $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);
    //         $menu->url(
    //             action([\Modules\Crm\Http\Controllers\OrderRequestController::class, 'listOrderRequests']),
    //             __('crm::lang.order_request'),
    //             ['icon' => 'fa fas fa-sync', 'active' => request()->segment(2) == 'order-request']
    //         );
    //     });
    // }

    public function helpdeskMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']
            );
        });
    }
    public function myMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']
            );
        });
    }

    public function assetManagementMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            $menu->url(
                action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'dashboard']),
                __('assetmanagement::lang.asset_management'),
                [
                    'icon' => 'fas fa fa-boxes',
                    'active' => request()->segment(1) === 'asset'
                ]
            );
        });
    }

    public function generalmanagementMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            $menu->url(
                action([\Modules\GeneralManagement\Http\Controllers\DashboardController::class, 'index']),
                '<i class="fas fa-users-cog"></i> ' . __('generalmanagement::lang.GeneralManagement'),
                [
                    'active' => request()->segment(1) == 'generalmanagement',
                    // 'style' => config('app.env') == 'demo' ? 'background-color: #605ca8 !important;' : '',
                ],
            );

            if ($is_admin  || auth()->user()->can('generalmanagement.view_president_requests') || auth()->user()->can('generalmanagement.view_GM_escalate_requests')) {


                $menu->url(
                    ($is_admin  || auth()->user()->can('generalmanagement.view_president_requests')) ? action([\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'index']) : action([\Modules\GeneralManagement\Http\Controllers\RequestController::class, 'escalateRequests']),
                    __('generalmanagement::lang.requests'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => (request()->segment(2) == 'president_requests' || request()->segment(2) == 'escalate_requests')]
                );
            }
        });
    }
    public function ceoMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {

            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            $menu->url(
                action([\Modules\CEOManagment\Http\Controllers\DashboardController::class, 'index']),
                '<i class="fas fa-users-cog"></i> ' . __('ceomanagment::lang.CEO_Managment'),
                [
                    'active' => request()->segment(1) == 'ceomanagment' && request()->segment(2) == 'dashboard',

                ],
            );

            if ($is_admin  || auth()->user()->can('ceomanagment.curd_organizational_structure')) {
                $menu->url(

                    action([\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'index']),
                    __('essentials::lang.organizational_structure'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ceomanagment' && request()->segment(2) == 'departments'],
                );
            }
            if ($is_admin  || auth()->user()->can('ceomanagment.view_requests_types')) {
                $menu->url(
                    action([\Modules\CEOManagment\Http\Controllers\RequestTypeController::class, 'index']),
                    __('ceomanagment::lang.requests_types'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => (request()->segment(2) == 'requests_types')]
                );
            }

            if ($is_admin  || auth()->user()->can('ceomanagment.view_procedures_for_employee') || auth()->user()->can('ceomanagment.view_procedures_for_workers')) {

                $menu->url(
                    ($is_admin  || auth()->user()->can('ceomanagment.view_procedures_for_employee')) ? action([\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'employeesProcedures']) : action([\Modules\CEOManagment\Http\Controllers\WkProcedureController::class, 'workersProcedures']),
                    __('ceomanagment::lang.procedures'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ceomanagment' && (request()->segment(2) == 'employeesProcedures' || request()->segment(2) == 'workersProcedures')],
                );
            }
            if ($is_admin  || auth()->user()->can('ceomanagment.view_CEO_requests') || auth()->user()->can('ceomanagment.view_CEO_escalate_requests')) {

                $menu->url(
                    action([\Modules\CEOManagment\Http\Controllers\RequestController::class, 'index']),
                    __('ceomanagment::lang.requests'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => (request()->segment(2) == 'requests' || request()->segment(2) == 'escalate_requests')]
                );
            }
        });
    }
    public function agnetMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

            //$menu->header("");
            //$menu->header("");
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'agent' &&  request()->segment(2) == 'home'],
            );
            $menu->url(
                route('agent_projects'),
                __('agent.projects'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'agent' &&  request()->segment(2) == 'projects'],
            );
            $menu->url(
                route('agent_contracts'),
                __('agent.contracts'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'agent' &&  request()->segment(2) == 'contracts'],
            );
            $menu->url(
                route('agent_workers'),
                __('agent.workers'),
                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'agent' &&  request()->segment(2) == 'workers'],
            );
            $menu->url(
                route('agentRequests'),
                __('agent.requests'),
                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'agent' &&  request()->segment(2) == 'requests'],
            );
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('agent.pills'),
                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'workers'],
            );
            $menu->url(
                route('agentTimeSheet.index'),
                __('agent.time_sheet'),
                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'agent' &&  request()->segment(2) == 'time_sheet'],
            );
        });
    }
    public function connectorMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(
                action([\Modules\Connector\Http\Controllers\ClientController::class, 'index']),
                __('connector::lang.clients'),
                ['icon' => 'fa fas fa-network-wired', 'active' => request()->segment(1) == 'connector' && request()->segment(2) == 'api']
            );
            //$menu->header("");
            //$menu->header("");
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);
            $menu->dropdown(
                __('connector::lang.connector'),
                function ($sub) {
                    if (auth()->user()->can('superadmin')) {
                        $sub->url(
                            action([\Modules\Connector\Http\Controllers\ClientController::class, 'index']),
                            __('connector::lang.clients'),
                            ['icon' => 'fa fas fa-network-wired', 'active' => request()->segment(1) == 'connector' && request()->segment(2) == 'api']
                        );
                    }
                    $sub->url(
                        url('\docs'),
                        __('connector::lang.documentation'),
                        ['icon' => 'fa fas fa-book', 'active' => request()->segment(1) == 'docs']
                    );
                },
                ['icon' => 'fas fa-plug', 'style' => 'background-color: #2dce89 !important;']
            );
        });
    }
    public function userManagementMenu()
    {

        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            $menu->url(
                action([\App\Http\Controllers\ManageUserController::class, 'index']),
                __('user.users'),
                ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users' || request()->segment(1) == 'manage_user']
            );
            if (auth()->user()->can('essentials.crud_all_roles')) {
                $menu->url(
                    route('roles'),
                    __('user.roles'),
                    ['icon' => 'fa fas fa-key', 'active' => request()->segment(1) == 'roles']
                );
            }



            //$menu->header("");
            //$menu->header("");
            //User management dropdown
            // if ($is_admin || auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')) {
            //     $menu->dropdown(
            //         __('user.user_management'),
            //         function ($sub) {

            //             $sub->url(
            //                 action([\App\Http\Controllers\ManageUserController::class, 'index']),
            //                 __('user.users'),
            //                 ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users' || request()->segment(1) == 'manage_user']
            //             );

            //             // if (auth()->user()->can('roles.view')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\RoleController::class, 'index']),
            //             //         __('user.roles'),
            //             //         ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(1) == 'roles']
            //             //     );
            //             // }
            //             // if (auth()->user()->can('user.create')) {
            //             //     $sub->url(
            //             //         action([\App\Http\Controllers\SalesCommissionAgentController::class, 'index']),
            //             //         __('lang_v1.sales_commission_agents'),
            //             //         ['icon' => 'fa fas fa-handshake', 'active' => request()->segment(1) == 'sales-commission-agents']
            //             //     );
            //             // }
            //         },
            //         ['icon' => 'fas fa-user-tie ']
            //     );
            // }
        });
    }
    public function movmentMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                [
                    'icon' => 'fa fas fa-home  ',
                    'active' => request()->segment(1) == 'home'
                ]
            );
            if ($is_admin || auth()->user()->can('essentials.movement_management_dashbord')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'index']),
                    __('housingmovements::lang.movement_management'),
                    ['icon' => 'fa fa-car', 'active' => request()->segment(2) == 'dashboard-movment']
                );
            }
            if ($is_admin || auth()->user()->can('essentials.car_drivers')) {

                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\DriverCarController::class, 'index']),
                    __('housingmovements::lang.car_drivers'),
                    ['icon' => 'fa fa-bullseye', 'active' => request()->segment(2) == 'car-drivers']
                );
            }
            if ($is_admin || auth()->user()->can('essentials.cars')) {

                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\CarController::class, 'index']),
                    __('housingmovements::lang.cars'),
                    ['icon' => 'fa fa-bullseye', 'active' => request()->segment(2) == 'cars']
                );
            }

            if ($is_admin || auth()->user()->can('essentials.carsChangeOil')) {

                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\CarsChangeOilController::class, 'index']),
                    __('housingmovements::lang.carsChangeOil'),
                    ['icon' => 'fa fa-bullseye', 'active' => request()->segment(2) == 'cars-change-oil']
                );
            }

            if ($is_admin || auth()->user()->can('essentials.carMaintenances')) {

                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\CarsMaintenanceController::class, 'index']),
                    __('housingmovements::lang.carMaintenances'),
                    ['icon' => 'fa fa-bullseye', 'active' => request()->segment(2) == 'cars-maintenances']
                );
            }




            if ($is_admin || auth()->user()->can('essentials.carTypes')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\CarTypeController::class, 'index']),
                    __('housingmovements::lang.carTypes'),
                    ['icon' => 'fa fa-bullseye', 'active' =>  request()->segment(2) == 'cars-type']
                );
            }

            if ($is_admin || auth()->user()->can('essentials.carModels')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\CarModelController::class, 'index']),
                    __('housingmovements::lang.carModels'),
                    ['icon' => 'fa fa-bullseye', 'active' =>  request()->segment(2) == 'cars-model']
                );
            }

            // if ($is_admin  || auth()->user()->can('housingmovements.report')) {

            $menu->dropdown(
                __('housingmovements::lang.report'),
                function ($report)  use ($is_admin) {


                    if ($is_admin  || auth()->user()->can('essentials.carsChangeOilReport')) {

                        $report->url(
                            action([\Modules\Essentials\Http\Controllers\CarsReportsController::class, 'CarsChangeOil']),
                            __('housingmovements::lang.carsChangeOilReport'),
                            ['icon' => 'fa fa-bullseye', 'active' =>  request()->segment(2) == 'cars-change-oil-report']
                        );
                    }
                    if ($is_admin  || auth()->user()->can('essentials.carMaintenancesReport')) {

                        $report->url(
                            action([\Modules\Essentials\Http\Controllers\CarsReportsController::class, 'carMaintenances']),
                            __('housingmovements::lang.carMaintenancesReport'),
                            ['icon' => 'fa fa-bullseye', 'active' =>  request()->segment(2) == 'cars-maintenances-report']
                        );
                    }
                },
                ['icon' => 'fa fa-bullseye',],
            );
            // }
        });
    }

    public function legalAffairsMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home', 'active' => request()->segment(1) == 'home']);
            if ($is_admin || auth()->user()->can('legalaffairs.legalAffairs_dashboard')) {
                $menu->url(route('legalAffairs.dashboard'),  __('legalaffairs::lang.legalaffairs'), ['icon' => 'fas fa-balance-scale', 'active' => request()->segment(1) == 'legalaffairs' && request()->segment(2) == 'dashboard']);
            }
            if ($is_admin || auth()->user()->can('legalaffairs.contracts_management')) {
                $menu->dropdown(
                    __('legalaffairs::lang.contracts_management'),
                    function ($sub) use ($is_admin,) {


                        if ($is_admin  || auth()->user()->can('legalaffairs.crud_employee_contracts')) {
                            $sub->url(
                                route('legalAffairs.employeeContracts'),
                                __('legalaffairs::lang.employee_contracts'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'legalaffairs' && request()->segment(2) == 'employees_contracts'],
                            );
                        }
                        if ($is_admin || auth()->user()->can('legalaffairs.view_sales_contracts')) {
                            $sub->url(
                                route('legalAffairs.salesContracts'),

                                __('legalaffairs::lang.sales_contracts'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'legalaffairs' && request()->segment(2) == 'sales_contracts'],
                            );
                        }
                    },
                    ['icon' => 'fas fa-balance-scale']

                );

                // $menu->url(route('legalAffairs.contracts_management'),  __('legalaffairs::lang.contracts_management'), ['icon' => 'fas fa-balance-scale', 'active' => request()->segment(1) == 'legalaffairs' && request()->segment(2) == 'contracts_management']);
            }
        });
    }

    public function medicalInsuranceMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home', 'active' => request()->segment(1) == 'home']);

            if ($is_admin  || auth()->user()->can('essentials.medicalInsurance_dashboard')) {
                $menu->url(
                    route('insurance-dashbord'),
                    __('essentials::lang.medicalInsurance_dashboard'),
                    ['icon' => 'fa fas fa-book-medical', 'active' => request()->segment(1) == 'medicalInsurance' && request()->segment(2) == 'insurance-dashbord'],
                );
            }

            $menu->url(
                route('employee_insurance'),
                __('essentials::lang.health_insurance'),
                ['icon' => 'fa fas fa-briefcase-medical', 'active' => request()->segment(1) == 'medicalInsurance' && request()->segment(2) == 'employee_insurance'],
            );


            if ($is_admin  || auth()->user()->can('essentials.crud_insurance_contracts')) {
                $menu->url(
                    route('insurance_contracts'),
                    __('essentials::lang.insurance_contracts'),
                    ['icon' => 'fa fas fa-book-medical', 'active' => request()->segment(1) == 'medicalInsurance' && request()->segment(2) == 'insurance_contracts'],
                );
            }
            if ($is_admin  || auth()->user()->can('essentials.view_insurance_requests')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\InsuranceRequestController::class, 'index']),
                    __('essentials::lang.requests'),
                    ['icon' => 'fa fas fa-briefcase-medical', 'active' => request()->segment(1) == 'medicalInsurance' &&  request()->segment(2) == 'insurance_requests']
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.insurance_index_workers')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsWorkerController::class, 'index']),
                    __('essentials::lang.index_workers'),
                    ['icon' => 'fa fas fa-briefcase-medical', 'active' => request()->segment(1) == 'medicalInsurance' &&  request()->segment(2) == 'workers']
                );
            }


            if ($is_admin  || auth()->user()->can('essentials.crud_insurance_companies')) {
                $menu->url(
                    route('insurance_companies'),
                    __('essentials::lang.insurance_companies'),
                    ['icon' => 'fa fas fa-hospital', 'active' => request()->segment(1) == 'medicalInsurance' && request()->segment(2) == 'insurance_companies'],
                );
            }
            if ($is_admin  || auth()->user()->can('essentials.view_insurance_contracts')) {
                $menu->url(
                    route('get_companies_insurance_contracts'),
                    __('essentials::lang.companies_insurance_contracts'),
                    ['icon' => 'fa fas fa-hospital', 'active' => request()->segment(1) == 'medicalInsurance'
                        && request()->segment(2) == 'insurance_contracts'],
                );
            }


            if ($is_admin  || auth()->user()->can('essentials.crud_insurance_classes')) {
                $menu->dropdown(
                    __('essentials::lang.insurance_settings'),
                    function ($report)  use ($is_admin) {

                        if ($is_admin  || auth()->user()->can('essentials.crud_insurance_classes')) {

                            $report->url(
                                route('insurance_categories'),
                                __('essentials::lang.insurance_categories'),
                                ['icon' => 'fa fa-bullseye', 'active' =>  request()->segment(1) == 'medicalInsurance' && request()->segment(2) == 'insurance_categories']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-cog',],
                );
            }
            if ($is_admin  || auth()->user()->can('essentials.view_import_employees_insurance')) {
                $menu->url(
                    route('import_employees_insurance'),
                    __('essentials::lang.import_employees_insurance'),
                    ['icon' => 'fa fas fa-plus', 'active' => request()->segment(1) == 'medicalInsurance' && request()->segment(2) == 'import_employees_insurance'],
                );
            }
        });
    }

    public function workCardsMenu()
    {

        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);
            if ($is_admin  || auth()->user()->can('essentials.essentials_work_cards_dashboard')) {
                $menu->url(
                    route('essentials_word_cards_dashboard'),
                    __('essentials::lang.work_cards'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'essentials' && request()->segment(2) == 'work_cards_dashboard'],
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.workcards_indexWorkerProjects')) {

                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsWorkCardsWorkerController::class, 'index']),
                    __('essentials::lang.workcards_indexWorkerProjects'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'essentials' && request()->segment(2) == 'workers'],
                );
            }


            if ($is_admin  || auth()->user()->can('essentials.crud_workcards_request')) {

                $menu->url(
                    route('work_cards_all_requests'),
                    __('essentials::lang.workcards_allrequest'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'essentials' && request()->segment(2) == 'work_cards_all_requests'],
                );
            }




            if ($is_admin  || auth()->user()->can('essentials.work_cards_operation')) {

                $menu->url(
                    route('work_cards_operation'),
                    __('essentials::lang.work_cards_operation'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'essentials' && request()->segment(2) == 'work_cards_operation'],
                );
            }

            if ($is_admin || auth()->user()->can('essentials.renewal_residence')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'index']),
                    __('essentials::lang.renewal_residence'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'cards'],
                );
            }
            if ($is_admin || auth()->user()->can('essentials.residencyreports')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'residencyreports']),
                    __('essentials::lang.residencyreports'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'get_residency_report'],
                );
            }

            if ($is_admin || auth()->user()->can('essentials.facilities_management')) {
                $menu->url(
                    action([\App\Http\Controllers\BusinessController::class, 'getBusiness']),
                    __('essentials::lang.facilities_management'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'getBusiness'],
                );
            }
            if ($is_admin  || auth()->user()->can('essentials.work_cards_view_department_employees')) {
                $menu->url(

                    route('work_cards_department_employees'),
                    __('essentials::lang.department_employees'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'work_cards_department_employees'],
                );
            }


            // if ($is_admin || auth()->user()->can('essentials.movement_management')) {
            //     $menu->dropdown(
            //         __('housingmovements::lang.movement_management'),
            //         function ($movement_management_SubMenu) use ($is_admin) {
            //             if ($is_admin || auth()->user()->can('essentials.carTypes')) {
            //                 $movement_management_SubMenu->url(
            //                     action([\Modules\Essentials\Http\Controllers\CarTypeController::class, 'index']),
            //                     __('housingmovements::lang.carTypes'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' =>  request()->segment(2) == 'cars-type']
            //                 );
            //             }
            //             if ($is_admin || auth()->user()->can('essentials.carModels')) {
            //                 $movement_management_SubMenu->url(
            //                     action([\Modules\Essentials\Http\Controllers\CarModelController::class, 'index']),
            //                     __('housingmovements::lang.carModels'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' =>  request()->segment(2) == 'cars-model']
            //                 );
            //             }
            //             if ($is_admin || auth()->user()->can('essentials.cars')) {
            //                 $movement_management_SubMenu->url(
            //                     action([\Modules\Essentials\Http\Controllers\CarController::class, 'index']),
            //                     __('housingmovements::lang.cars'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'cars']
            //                 );
            //             }
            //             if ($is_admin || auth()->user()->can('essentials.car_drivers')) {
            //                 $movement_management_SubMenu->url(
            //                     action([\Modules\Essentials\Http\Controllers\DriverCarController::class, 'index']),
            //                     __('housingmovements::lang.car_drivers'),
            //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'car-drivers']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-plus-circle',],
            //         // ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'movement'],

            //     );
            // }
        });
    }

    public function employeeAffairsMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            if ($is_admin  || auth()->user()->can('essentials.view_employee_affairs_dashboard')) {


                $menu->url(
                    route('employee_affairs_dashboard'),
                    __('essentials::lang.employee_affairs_dashboard'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'employee_affairs_dashboard'],
                );

                if ($is_admin  || auth()->user()->can('essentials.curd_employees')) {
                    $menu->url(
                        route('employees'),
                        __('essentials::lang.employees'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'employees'],
                    );
                }
                //workers
                if ($is_admin  || auth()->user()->can('essentials.view_essentials_affairs_workers')) {
                    $menu->url(
                        action([\Modules\Essentials\Http\Controllers\EssentialsWorkersAffairsController::class, 'index']),
                        __('essentials::lang.workers'),
                        [
                            'icon' => 'fa fas fa-plus-circle',
                            'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'workers'
                        ],
                    );
                }


                if ($is_admin  || auth()->user()->can('essentials.view_employees_affairs_requests')) {
                    $menu->url(
                        action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'employee_affairs_all_requests']),
                        __('essentials::lang.employees_requests'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' &&  (request()->segment(2) == 'allEmployeeAffairsRequests')]
                    );
                }
                if ($is_admin  || auth()->user()->can('essentials.crud_employee_appointments')) {
                    $menu->url(
                        route('appointments'),
                        __('essentials::lang.appointment'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'appointments'],
                    );
                }

                if ($is_admin  || auth()->user()->can('essentials.crud_employee_work_adminitions')) {
                    $menu->url(
                        route('admissionToWork'),
                        __('essentials::lang.admissions_to_work'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'admissions_to_work'],
                    );
                }
                if ($is_admin  || auth()->user()->can('essentials.crud_employee_contracts')) {
                    $menu->url(
                        route('employeeContracts'),
                        __('essentials::lang.employee_contracts'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'employee_contracts'],
                    );
                }

                if ($is_admin  || auth()->user()->can('essentials.crud_employee_qualifications')) {
                    $menu->url(
                        route('qualifications'),
                        __('essentials::lang.qualifications'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'qualifications'],
                    );
                }
                if ($is_admin  || auth()->user()->can('essentials.crud_official_documents')) {
                    $menu->url(
                        route('official_documents'),
                        __('essentials::lang.official_documents'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'official_documents'],
                    );
                }
                if ($is_admin  || auth()->user()->can('essentials.crud_employee_families')) {
                    $menu->url(
                        route('employee_families'),
                        __('essentials::lang.employee_families'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'employee_families'],
                    );
                }
                if ($is_admin  || auth()->user()->can('essentials.crud_employee_features')) {
                    $menu->url(
                        route('featureIndex'),
                        __('essentials::lang.features'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'features'],
                    );
                }

                if ($is_admin  || auth()->user()->can('essentials.crud_import_employee')) {
                    $menu->url(
                        route('import-employees'),
                        __('essentials::lang.import_employees'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'import'],
                    );
                }
                if ($is_admin  || auth()->user()->can('essentials.view_import_employees_familiy')) {
                    $menu->url(
                        route('import-employees-familiy'),
                        __('essentials::lang.import_employees_families'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'import_employees_families'],
                    );
                }
                if ($is_admin  || auth()->user()->can('essentials.employee_affairs_view_department_employees')) {
                    $menu->url(

                        route('employee_affairs_department_employees'),
                        __('essentials::lang.department_employees'),
                        ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'employee_affairs' && request()->segment(2) == 'employee_affairs_department_employees'],
                    );
                }
            }
        });
    }

    public function essentialsMenu()
    {

        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            if ($is_admin  || auth()->user()->can('essentials.essentials_dashboard')) {
                $menu->url(
                    route('essentials_landing'),
                    __('essentials::lang.hrm'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'essentials' || request()->segment(1) == 'hrm' || request()->segment(1) == 'roles'],
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.crud_all_attendance')) {
                $menu->url(

                    action([\Modules\Essentials\Http\Controllers\AttendanceController::class, 'index']),
                    __('essentials::lang.attendance'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'attendance'],
                );
            }



            if ($is_admin  || auth()->user()->can('essentials.view_HR_requests')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'requests']),
                    __('essentials::lang.requests'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' &&  (request()->segment(2) == 'allEssentialsRequests' || request()->segment(2) == 'escalate_requests')]
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.view_all_essentials_workers')) {
                //workers:
                $menu->url(
                    route('get-essentials-workers'),
                    __('essentials::lang.workers'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' &&  (request()->segment(2) == 'essentailsworkers' || request()->segment(2) == 'escalate_requests')]
                );
            }



            //employee reports 
            if ($is_admin  || auth()->user()->can('essentials.employees_reports_view')) {

                $menu->dropdown(
                    __('essentials::lang.reports'),
                    function ($sub) use ($enabled_modules) {

                        $sub->url(
                            action([\Modules\Essentials\Http\Controllers\EssentialsReportController::class, 'index']),
                            __('essentials::lang.employees_information_report'),
                            ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'employess-info-report']
                        );
                    },
                    ['icon' => 'fa fas fa-plus-circle']

                );
            }

            if ($is_admin  || auth()->user()->can('essentials.crud_essentials_recuirements_requests')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\RecuirementsRequestsController::class, 'index']),
                    __('essentials::lang.recuirements_requests'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'get-recuirements-requests']
                );
            }



            // if ($is_admin || (auth()->user()->can('essentials.essentials_dashboard') && auth()->user()->can('essentials.view_work_cards'))) {

            //     // $menu->dropdown(
            //     //     __('essentials::lang.work_cards'),
            //     //     function ($sub)  use ($is_admin) {

            //     //         if ($is_admin || auth()->user()->can('essentials.renewal_residence')) {
            //     //             $sub->url(
            //     //                 action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'index']),
            //     //                 __('essentials::lang.renewal_residence'),
            //     //                 ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'cards'],
            //     //             );
            //     //         }

            //     //         if ($is_admin || auth()->user()->can('essentials.residencyreports')) {
            //     //             $sub->url(
            //     //                 action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'residencyreports']),
            //     //                 __('essentials::lang.residencyreports'),
            //     //                 ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'get_residency_report'],
            //     //             );
            //     //         }
            //     //         if ($is_admin || auth()->user()->can('essentials.facilities_management')) {
            //     //             $sub->url(
            //     //                 action([\App\Http\Controllers\BusinessController::class, 'getBusiness']),
            //     //                 __('essentials::lang.facilities_management'),
            //     //                 ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'getBusiness'],
            //     //             );
            //     //         }
            //     //     },
            //     //     ['icon' => 'fa fas fa-plus-circle']

            //     // );
            // } 

            if ($is_admin  || auth()->user()->can('essentials.curd_contracts_end_reasons')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsContractsFinishReasonsController::class, 'index']),
                    __('essentials::lang.contracts_end_reasons'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'contracts-finish-reasons'],
                );
            }
            if ($is_admin  || auth()->user()->can('essentials.view_contract_cancel_requests')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsCancelContractsController::class, 'index']),
                    __('essentials::lang.cancel_contract'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'cancel_contract_requests'],
                );
            }
            if ($is_admin  || auth()->user()->can('essentials.curd_wishes')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsWishesController::class, 'index']),
                    __('essentials::lang.wishes'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'wishes'],
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.crud_all_leave')) {

                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController::class, 'index']),
                    __('essentials::lang.leave_type'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'leave'],
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.view_all_payroll')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index']),
                    __('essentials::lang.payroll'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'payroll'],
                );
            }

            // if ($is_admin ) {
            //     $menu->url(
            //         action([\App\Http\Controllers\TaxonomyController::class, 'index']),
            //         __('essentials::lang.loan'),
            //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'taxonomies'],
            //     );
            // }


            if ($is_admin  || auth()->user()->can('essentials.crud_system_settings')) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'edit']),
                    __('essentials::lang.system_settings'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'settings'],
                );
            }

            if ($is_admin  || auth()->user()->can('essentials.view_employee_settings')) {
                $menu->url(
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
                );
            }

            // if ($is_admin  || auth()->user()->can('essentials.crud_import_employee')) {
            //     $menu->url(
            //         action([\Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class, 'index']),
            //         __('essentials::lang.import_employees'),
            //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'import-employees'],
            //     );
            // }


            if ($is_admin  || auth()->user()->can('essentials.hr_view_department_employees')) {
                $menu->url(

                    route('hr_department_employees'),
                    __('essentials::lang.department_employees'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'essentials' && request()->segment(2) == 'hr_department_employees'],
                );
            }

            // if ($is_admin || auth()->user()->can('essentials.essentials')) {
            //     $menu->url(
            //         action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index']),
            //         __('essentials::lang.essentials'),
            //         ['icon' => 'fa fas fa-check-circle', 'active' => request()->segment(1) == 'essentials' && request()->segment(2) == 'todo', 'style' => config('app.env') == 'demo' ? 'background-color: #001f3f !important;' : '']
            //     );
            // }

        });
    }


    public function followUpMenu()
    {


        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                [
                    'icon' => 'fa fas fa-home  ',
                    'active' => request()->segment(1) == 'home'
                ]
            );

            if ($is_admin  || auth()->user()->can('followup.followup_dashboard')) {
                $menu->url(
                    action([\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'index']),
                    __('followup::lang.followUp'),
                    ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'followup']
                );
            }


            if ($is_admin  || auth()->user()->can('followup.crud_contact_locations')) {
                $menu->url(
                    action([\App\Http\Controllers\ContactLocationController::class, 'index']),
                    __('followup::lang.contact_locations'),
                    ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'contactLocations'],

                );
            }
            if ($is_admin  || auth()->user()->can('followup.crud_projects')) {
                $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'index']), __('followup::lang.projects'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'projects2']);
            }

            if ($is_admin  || auth()->user()->can('followup.crud_workers')) {
                $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index']), __('followup::lang.workers'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'workers']);
            }

            if ($is_admin  || auth()->user()->can('followup.crud_operation_orders')) {

                $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderController::class, 'index']), __('followup::lang.operation_orders'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'operation_orders']);
            }

            if ($is_admin  || auth()->user()->can('followup.view_followup_requests')) {

                $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'requests']), __('followup::lang.requests'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'allRequests']);
            }

            if ($is_admin  || auth()->user()->can('followup.crud_recruitmentRequests')) {

                $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpRecruitmentRequestController::class, 'index']), __('followup::lang.recruitmentRequests'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'recruitmentRequests']);
            }

            if ($is_admin  || auth()->user()->can('followup.crud_documents')) {

                $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowupDocumentController::class, 'index']), __('followup::lang.documents'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'documents']);
            }

            if ($is_admin  || auth()->user()->can('followup.crud_document_delivery')) {

                $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowupDeliveryDocumentController::class, 'index']), __('followup::lang.document_delivery'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'documents-delivery']);
            }

            if ($is_admin  || auth()->user()->can('followup.crud_projectWorkersReports') || auth()->user()->can('followup.crud_projectsReports')) {

                $menu->dropdown(
                    __('followup::lang.reports.title'),
                    function ($sub) use ($enabled_modules, $is_admin) {
                        if ($is_admin || auth()->user()->can('followup.crud_projectsReports')) {
                            $sub->url(
                                action([\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projects']),
                                __('followup::lang.reports.projects'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(3) == 'projects']
                            );
                        }

                        if ($is_admin || auth()->user()->can('followup.crud_projectWorkersReports')) {
                            $sub->url(
                                action([\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projectWorkers']),
                                __('followup::lang.reports.projectWorkers'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(3) == 'project-workers']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-meteor']
                );
            }

            if ($is_admin  || auth()->user()->can('followup.crud_contrascts_wishes')) {

                $menu->url(
                    action([\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'index']),
                    __('followup::lang.contrascts_wishes'),
                    ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'contracts_wishes']
                );
            }
            if ($is_admin  || auth()->user()->can('followup.crud_shifts')) {

                $menu->url(
                    action([\Modules\FollowUp\Http\Controllers\ShiftController::class, 'index']),
                    __('followup::lang.shifts'),
                    [
                        'icon' => 'fa fas fa-meteor',
                        'active' => request()->segment(2) == 'shifts'
                    ],

                );
            }

            if ($is_admin  || auth()->user()->can('followup.projects_access_permissions')) {

                $menu->url(
                    route('projects_access_permissions'),
                    __('followup::lang.projects_access_permissions'),
                    [
                        'icon' => 'fa fas fa-key',
                        'active' => request()->segment(2) == 'projects_access_permissions'
                    ],

                );
            }
            if ($is_admin  || auth()->user()->can('followup.followup_view_department_employees')) {
                $menu->url(

                    route('followup_department_employees'),
                    __('followup::lang.department_employees'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'followup' && request()->segment(2) == 'followup_department_employees'],
                );
            }
        });
    }

    public function CUS_salesMenu()
    {

        Menu::create('admin-sidebar-menu', function ($menu) {


            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            $menu->url(
                route('sales_landing'),
                __('sales::lang.sales'),
                ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'sale'],
            );


            if ($is_admin || auth()->user()->can('sales.view_lead_contacts') 
            || auth()->user()->can('sales.view_qualified_contacts') 
            || auth()->user()->can('sales.view_unqualified_contacts') 
            || auth()->user()->can('sales.view_converted_contacts') 
            || auth()->user()->can('sales.view_draft_contacts')) {


                $menu->url(
                    ($is_admin  || auth()->user()->can('sales.view_draft_contacts')) ? action([
                        \Modules\Sales\Http\Controllers\ClientsController::class,
                        'draft_contacts'
                    ]) : (auth()->user()->can('sales.view_lead_contacts') ? action([
                        \Modules\Sales\Http\Controllers\ClientsController::class,
                        'lead_contacts'
                    ]) : (auth()->user()->can('sales.view_qualified_contacts') ? action([
                        \Modules\Sales\Http\Controllers\ClientsController::class,
                        'qualified_contacts'
                    ]) : (auth()->user()->can('sales.view_unqualified_contacts') ? action([
                        \Modules\Sales\Http\Controllers\ClientsController::class,
                        'unqualified_contacts'
                    ]) : action([\Modules\Sales\Http\Controllers\ClientsController::class, 'converted_contacts'])))),

                    __('sales::lang.contacts'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && (request()->segment(2) == 'lead_contacts'
                        || request()->segment(2) == 'qualified_contacts'
                        || request()->segment(2) == 'unqualified_contacts'
                        || request()->segment(2) == 'converted_contacts'
                        || request()->segment(2) == 'draft_contacts'
                    )],
                );
            }

            if ($is_admin || auth()->user()->can('sales.view_sales_projects')) {
                $menu->url(
                    route('sale.saleProjects'),
                    __('sales::lang.sales_projects'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'saleProjects'],
                );
            }
            if ($is_admin || auth()->user()->can('sales.view_under_study_offer_price') || auth()->user()->can('sales.view_accepted_offer_price') || auth()->user()->can('sales.view_unaccepted_offer_price')) {
                $menu->url(
                    action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'index']),
                    __('sales::lang.offer_price'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'under_study_offer_prices' || request()->segment(2) == 'accepted_offer_prices' || request()->segment(2) == 'unaccepted_offer_prices'],

                );
            }
            if ($is_admin || auth()->user()->can('sales.view_sales_contracts')) {
                $menu->url(
                    action([\Modules\Sales\Http\Controllers\ContractsController::class, 'index']),
                    __('sales::lang.contracts'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'cotracts'],
                );
            }

            if ($is_admin || auth()->user()->can('sales.view_contract_appendics')) {
                $menu->url(
                    action([\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'index']),
                    __('sales::lang.contract_appendics'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'contract_appendices'],
                );
            }
            if ($is_admin || auth()->user()->can('sales.view_sale_operation_orders')) {

                $menu->url(
                    action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'index']),
                    __('sales::lang.sale_operation_orders'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'orderOperations'],
                );
            }
            if ($is_admin || auth()->user()->can('sales.view_sales_requests')) {
                $menu->url(
                    action([\Modules\Sales\Http\Controllers\RequestController::class, 'index']),
                    __('sales::lang.requests'),
                    [
                        'icon' => 'fa fas fa-plus-circle',
                        'active' => request()->segment(1) == 'sale' && (request()->segment(2) == 'sales.requests' || request()->segment(2) == 'escalate_requests')
                    ],
                );
            }

            if ($is_admin || auth()->user()->can('sales.view_sales_salary_requests')) {
                $menu->url(
                    action([\Modules\Sales\Http\Controllers\SalesSalaryRequestsController::class, 'index']),
                    __('sales::lang.salary_requests'),
                    [
                        'icon' => 'fa fas fa-plus-circle',
                        'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'salary-requests-index'
                    ],
                );
            }




            if ($is_admin || auth()->user()->can('sales.view_sale_sources') || auth()->user()->can('sales.view_contract_items') || auth()->user()->can('sales.view_sales_costs')) {
                $menu->dropdown(
                    __('sales::lang.sales_settings'),
                    function ($sub) use ($is_admin) {
                        if ($is_admin || auth()->user()->can('sales.view_sale_sources')) {
                            $sub->url(
                                action([\Modules\Sales\Http\Controllers\SaleSourcesController::class, 'index']),
                                __('sales::lang.sale_sources'),
                                ['icon' => 'fas fa-chart-line', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'sales_sources']
                            );
                        }
                        if ($is_admin || auth()->user()->can('sales.view_contract_items')) {
                            $sub->url(
                                action([\Modules\Sales\Http\Controllers\ContractItemController::class, 'index']),
                                __('sales::lang.contract_itmes'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'contract_itmes'],
                            );
                        }


                        // if ($is_admin || auth()->user()->can('sales.crud_sales_templates')) {

                        //     $sub->url(
                        //         action([\Modules\Sales\Http\Controllers\SalesTemplateController::class, 'first_choice_offer_price_template']),
                        //         __('sales::lang.sales_templates'),
                        //         ['icon' => 'fas fa-chart-line', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'first_choice_offer_price_template']
                        //     );
                        // }
                        if ($is_admin || auth()->user()->can('sales.view_sales_costs')) {
                            $sub->url(
                                action([\Modules\Sales\Http\Controllers\SalesCostController::class, 'index']),
                                __('sales::lang.sales_costs'),
                                ['icon' => 'fas fa-chart-line', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'sales_costs']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-plus-circle'],

                );
            }
            if ($is_admin  || auth()->user()->can('sales.sales_view_department_employees')) {
                $menu->url(

                    route('sales_department_employees'),
                    __('sales::lang.department_employees'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'sales_department_employees'],
                );
            }
        });
    }

    public function houseMovementsMenu()
    {


        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']
            );
            if ($is_admin || auth()->user()->can('housingmovements.housing_move_dashbord')) {
                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index']),
                    __('housingmovements::lang.housing_move'),

                    [
                        'icon' => 'fa fas fa-users',
                        'active' => request()->segment(1) == 'housingmovements',

                    ]
                );
            }
            if ($is_admin  || auth()->user()->can('housingmovements.crud_htr_requests')) {
                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\RequestController::class, 'index']),
                    __('housingmovements::lang.requests'),
                    [
                        'icon' => 'fa fas fa-plus-circle',
                        'active' => request()->segment(1) == 'housingmovements' &&
                            (request()->segment(2) == 'hm.requests' || request()->segment(2) == 'escalate_requests')
                    ],
                );
            }

            if ($is_admin  || auth()->user()->can('housingmovements.crud_htr_trevelers')) {
                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\TravelersController::class, 'index']),
                    __('housingmovements::lang.travelers'),
                    [
                        'icon' => 'fa fas fa-plus-circle',
                        'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'travelers' || request()->segment(2) == 'housed-workers'
                    ],

                );
            }

            if ($is_admin  || auth()->user()->can('housingmovements.workers')) {
                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'index']),
                    __('housingmovements::lang.workers'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' =>  request()->segment(2) == 'workers' && request()->segment(3) == 'index'],

                );
            }

            if ($is_admin  || auth()->user()->can('housingmovements.all_workers')) {
                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\ProjectWorkersController::class, 'available_shopping']),
                    __('housingmovements::lang.all_workers'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(3) == 'available-shopping'],

                );
            }




            if ($is_admin  || auth()->user()->can('housingmovements.crud_buildings')) {
                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'index']),
                    __('housingmovements::lang.buildings'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'buildings']
                );
            }

            if ($is_admin  || auth()->user()->can('housingmovements.crud_rooms')) {

                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\RoomController::class, 'index']),
                    __('housingmovements::lang.rooms'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'rooms']
                );
            }

            if ($is_admin  || auth()->user()->can('housingmovements.view_import_rooms')) {

                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\ImportRoomsController::class, 'index']),

                    __('housingmovements::lang.import_rooms'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' &&
                        request()->segment(2) == 'import_rooms']
                );
            }

            if (($is_admin  || auth()->user()->can('housingmovements.crud_facilities'))) {

                $menu->url(
                    action([\Modules\HousingMovements\Http\Controllers\FacitityController::class, 'index']),
                    __('housingmovements::lang.facilities'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'facilities']
                );
            }
            if ($is_admin  || auth()->user()->can('housingmovements.housingmovements_view_department_employees')) {
                $menu->url(

                    route('housingmovements_department_employees'),
                    __('housingmovements::lang.department_employees'),
                    ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'housingmovements_department_employees'],
                );
            }
        });
    }
    public function allAccountingMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                [
                    'icon' => 'fa fas fa-home  ',
                    'active' => request()->segment(1) == 'home'
                ]
            );
        });
    }
    public function accountingMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                [
                    'icon' => 'fa fas fa-home  ',
                    'active' => request()->segment(1) == 'home'
                ]
            );
            // //$menu->header("");

            // //$menu->header("");
            if (($is_admin  || auth()->user()->can('accounting.accounting_dashboard'))) {

                $menu->url(
                    action('\Modules\Accounting\Http\Controllers\AccountingController@dashboard'),
                    __('accounting::lang.accounting'),
                    [
                        'icon' => 'fas fa-money-check fa',
                        'style' => config('app.env') == 'demo' ? 'background-color: #D483D9;' : '',
                        'active' => request()->segment(1) == 'accounting'
                    ]
                );
            }
            // if ($is_admin) {
            $menu->url(
                action([\App\Http\Controllers\SellController::class, 'index']),
                __('lang_v1.pills'),
                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'sells']
            );
            // }
            if (($is_admin  || auth()->user()->can('accounting.chart_of_accounts'))) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\CoaController::class, 'index']),
                    __('accounting::lang.chart_of_accounts'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'chart-of-accounts']
                );
            }
            if (($is_admin  || auth()->user()->can('accounting.cost_center'))) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\CostCenterController::class, 'index']),
                    __('accounting::lang.cost_center'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'cost_centers']
                );
            }
            if (($is_admin  || auth()->user()->can('accounting.opening_balances'))) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\OpeningBalanceController::class, 'index']),
                    __('accounting::lang.opening_balances'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'opening_balances']
                );
            }
            if (($is_admin  || auth()->user()->can('accounting.receipt_vouchers'))) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\ReceiptVouchersController::class, 'index']),
                    __('accounting::lang.receipt_vouchers'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'receipt_vouchers']
                );
            }
            if (($is_admin  || auth()->user()->can('accounting.payment_vouchers'))) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\PaymentVouchersController::class, 'index']),
                    __('accounting::lang.payment_vouchers'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'payment_vouchers']
                );
            }
            if (($is_admin  || auth()->user()->can('accounting.journal_entry'))) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'index']),
                    __('accounting::lang.journal_entry'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'journal-entry']
                );
            }

            if ($is_admin  || auth()->user()->can('accounting.view_accounting_requests')) {
                $menu->url(

                    action([\Modules\Accounting\Http\Controllers\RequestController::class, 'index']),
                    __('accounting::lang.requests'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'accounting-requests']
                );
            }




            if ($is_admin  || auth()->user()->can('accounting.automatedMigration')) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\AutomatedMigrationController::class, 'index']),
                    __('accounting::lang.automatedMigration'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'automated-migration']
                );
            }
            if ($is_admin  || auth()->user()->can('accounting.transfer')) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\TransferController::class, 'index']),
                    __('accounting::lang.transfer'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'transfer']
                );
            }
            if ($is_admin  || auth()->user()->can('accounting.transactions')) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\TransactionController::class, 'index']),
                    __('accounting::lang.transactions'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'transactions']
                );
            }
            if ($is_admin  || auth()->user()->can('accounting.manage_budget')) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\BudgetController::class, 'index']),
                    __('accounting::lang.budget'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'budget']
                );
            }
            if ($is_admin  || auth()->user()->can('accounting.reports')) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\ReportController::class, 'index']),
                    __('accounting::lang.reports'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'reports']
                );
            }
            if ($is_admin  || auth()->user()->can('accounting.settings')) {

                $menu->url(
                    action([\Modules\Accounting\Http\Controllers\SettingsController::class, 'index']),
                    __('messages.settings'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'settings']
                );
            }
        });
    }

    public function getIRMenu()
    {

        Menu::create('admin-sidebar-menu', function ($menu) {

            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            $menu->url(
                action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index']),
                __('internationalrelations::lang.International'),
                [
                    'icon' => 'fa fas fa-dharmachakra',
                    'active' => request()->segment(2) == 'dashboard',
                    'style' => config('app.env') == 'demo' ? 'background-color: #605ca8 !important;' : '',
                ],
            );




            //$menu->header("");
            //$menu->header("");


            if ($is_admin || auth()->user()->can('internationalrelations.view_orders_operations')) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'index']),
                    __('internationalrelations::lang.order_request'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'order_request'],
                );
            }


            if ($is_admin || auth()->user()->can('internationalrelations.view_employment_companies')) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'index']),
                    __('internationalrelations::lang.EmploymentCompanies'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'EmploymentCompanies'],
                );
            }


            // if ($is_admin || auth()->user()->can('essentials.view_facilities_management') ) {
            //     $menu->url(
            //         action([\App\Http\Controllers\BusinessController::class, 'getBusiness']),
            //         __('essentials::lang.facilities_management'),
            //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'getBusiness'],
            //     );
            // }
            if ($is_admin || auth()->user()->can('internationalrelations.view_all_delegation_requests')) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\DelegationController::class, 'index']),
                    __('internationalrelations::lang.Delegation'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'delegations'],
                );
            }



            if (
                $is_admin || auth()->user()->can('internationalrelations.view_proposed_workers') || auth()->user()->can('internationalrelations.view_accepted_workers')
                || auth()->user()->can('internationalrelations.view_under_trialPeriod_workers')
                || auth()->user()->can('internationalrelations.view_unaccepted_workers')
            ) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\WorkerController::class, 'proposed_laborIndex']),
                    __('internationalrelations::lang.proposed_labor'),
                    [
                        'icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'proposed_laborIndex'
                            || request()->segment(2) == 'accepted_workers'
                            || request()->segment(2) == 'workers_under_trialPeriod'
                            || request()->segment(2) == 'unaccepted_workers'
                    ],
                );
            }
            if ($is_admin || auth()->user()->can('internationalrelations.view_visa_cards')) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\VisaCardController::class, 'index']),
                    __('internationalrelations::lang.visa_cards'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'visa_cards'],
                );
            }
            if ($is_admin || auth()->user()->can('internationalrelations.view_Airlines')) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'index']),
                    __('internationalrelations::lang.Airlines'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'Airlines'],
                );
            }
            if ($is_admin || auth()->user()->can('internationalrelations.view_ir_requests')) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\IrRequestController::class, 'index']),
                    __('followup::lang.requests'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' &&
                        (request()->segment(2) == 'allIrRequests' || request()->segment(2) == 'escalate_requests')]
                );
            }
            if ($is_admin || auth()->user()->can('internationalrelations.crud_all_reports')) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index']),
                    __('followup::lang.reports.title'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'internationalRleations']
                );
            }
            if ($is_admin || auth()->user()->can('internationalrelations.view_all_salary_requests')) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\IRsalaryRequestController::class, 'index']),
                    __('followup::lang.salary_requests'),
                    [
                        'icon' => 'fa fas fa-plus-circle',
                        'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'IrsalaryRequests'
                    ]
                );
            }
        });
    }

    public function settingsMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                [
                    'icon' => 'fa fas fa-home  ',
                    'active' => request()->segment(1) == 'home'
                ]
            );
            $menu->url(
                action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings']),
                __('business.settings'),
                [
                    'icon' => 'fa fas fa-cog',
                    // 'active' => request()->segment(1) == 'home'
                ]
            );
            //$menu->header("");
            //$menu->header("");

            $menu->dropdown(
                __('business.settings'),
                function ($sub) use ($enabled_modules) {
                    if (auth()->user()->can('business_settings.access')) {
                        $sub->url(
                            action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings']),
                            __('business.business_settings'),
                            ['icon' => 'fa fas fa-cogs', 'active' => request()->segment(1) == 'business', 'id' => 'tour_step2']
                        );
                        $sub->url(
                            action([\App\Http\Controllers\BusinessLocationController::class, 'index']),
                            __('business.business_locations'),
                            ['icon' => 'fa fas fa-map-marker', 'active' => request()->segment(1) == 'business-location']
                        );
                    }
                    if (auth()->user()->can('invoice_settings.access')) {
                        $sub->url(
                            action([\App\Http\Controllers\InvoiceSchemeController::class, 'index']),
                            __('invoice.invoice_settings'),
                            ['icon' => 'fa fas fa-file', 'active' => in_array(request()->segment(1), ['invoice-schemes', 'invoice-layouts'])]
                        );
                    }
                    if (auth()->user()->can('barcode_settings.access')) {
                        $sub->url(
                            action([\App\Http\Controllers\BarcodeController::class, 'index']),
                            __('barcode.barcode_settings'),
                            ['icon' => 'fa fas fa-barcode', 'active' => request()->segment(1) == 'barcodes']
                        );
                    }
                    if (auth()->user()->can('access_printers')) {
                        $sub->url(
                            action([\App\Http\Controllers\PrinterController::class, 'index']),
                            __('printer.receipt_printers'),
                            ['icon' => 'fa fas fa-share-alt', 'active' => request()->segment(1) == 'printers']
                        );
                    }

                    if (auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create')) {
                        $sub->url(
                            action([\App\Http\Controllers\TaxRateController::class, 'index']),
                            __('tax_rate.tax_rates'),
                            ['icon' => 'fa fas fa-bolt', 'active' => request()->segment(1) == 'tax-rates']
                        );
                    }

                    if (in_array('tables', $enabled_modules) && auth()->user()->can('access_tables')) {
                        $sub->url(
                            action([\App\Http\Controllers\Restaurant\TableController::class, 'index']),
                            __('restaurant.tables'),
                            ['icon' => 'fa fas fa-table', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'tables']
                        );
                    }

                    if (in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create'))) {
                        $sub->url(
                            action([\App\Http\Controllers\Restaurant\ModifierSetsController::class, 'index']),
                            __('restaurant.modifiers'),
                            ['icon' => 'fa fas fa-pizza-slice', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'modifiers']
                        );
                    }

                    if (in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service')) {
                        $sub->url(
                            action([\App\Http\Controllers\TypesOfServiceController::class, 'index']),
                            __('lang_v1.types_of_service'),
                            ['icon' => 'fa fas fa-user-circle', 'active' => request()->segment(1) == 'types-of-service']
                        );
                    }
                },
                ['icon' => 'fa fas fa-cog', 'id' => 'tour_step3']
            );

            //Reports dropdown
            if (
                auth()->user()->can('purchase_n_sell_report.view') || auth()->user()->can('contacts_report.view')
                || auth()->user()->can('stock_report.view') || auth()->user()->can('tax_report.view')
                || auth()->user()->can('trending_product_report.view') || auth()->user()->can('sales_representative.view') || auth()->user()->can('register_report.view')
                || auth()->user()->can('expense_report.view')
            ) {
                $menu->dropdown(
                    __('report.reports'),
                    function ($sub) use ($enabled_modules, $is_admin) {
                        if (auth()->user()->can('profit_loss_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getProfitLoss']),
                                __('report.profit_loss'),
                                ['icon' => 'fa fas fa-file-invoice-dollar', 'active' => request()->segment(2) == 'profit-loss']
                            );
                        }
                        if (config('constants.show_report_606') == true) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'purchaseReport']),
                                'Report 606 (' . __('lang_v1.purchase') . ')',
                                ['icon' => 'fa fas fa-arrow-circle-down', 'active' => request()->segment(2) == 'purchase-report']
                            );
                        }
                        if (config('constants.show_report_607') == true) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'saleReport']),
                                'Report 607 (' . __('business.sale') . ')',
                                ['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'sale-report']
                            );
                        }
                        if ((in_array('purchases', $enabled_modules) || in_array('add_sale', $enabled_modules) || in_array('pos_sale', $enabled_modules)) && auth()->user()->can('purchase_n_sell_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getPurchaseSell']),
                                __('report.purchase_sell_report'),
                                ['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(2) == 'purchase-sell']
                            );
                        }

                        if (auth()->user()->can('tax_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getTaxReport']),
                                __('report.tax_report'),
                                ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'tax-report']
                            );
                        }
                        if (auth()->user()->can('contacts_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getCustomerSuppliers']),
                                __('report.contacts'),
                                ['icon' => 'fa fas fa-address-book', 'active' => request()->segment(2) == 'customer-supplier']
                            );
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getCustomerGroup']),
                                __('lang_v1.customer_groups_report'),
                                ['icon' => 'fa fas fa-users', 'active' => request()->segment(2) == 'customer-group']
                            );
                        }
                        if (auth()->user()->can('stock_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
                                __('report.stock_report'),
                                ['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'stock-report']
                            );
                            if (session('business.enable_product_expiry') == 1) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getStockExpiryReport']),
                                    __('report.stock_expiry_report'),
                                    ['icon' => 'fa fas fa-calendar-times', 'active' => request()->segment(2) == 'stock-expiry']
                                );
                            }
                            if (session('business.enable_lot_number') == 1) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getLotReport']),
                                    __('lang_v1.lot_report'),
                                    ['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'lot-report']
                                );
                            }

                            if (in_array('stock_adjustment', $enabled_modules)) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getStockAdjustmentReport']),
                                    __('report.stock_adjustment_report'),
                                    ['icon' => 'fa fas fa-sliders-h', 'active' => request()->segment(2) == 'stock-adjustment-report']
                                );
                            }
                        }

                        if (auth()->user()->can('trending_product_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getTrendingProducts']),
                                __('report.trending_products'),
                                ['icon' => 'fa fas fa-chart-line', 'active' => request()->segment(2) == 'trending-products']
                            );
                        }

                        if (auth()->user()->can('purchase_n_sell_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'itemsReport']),
                                __('lang_v1.items_report'),
                                ['icon' => 'fa fas fa-tasks', 'active' => request()->segment(2) == 'items-report']
                            );

                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getproductPurchaseReport']),
                                __('lang_v1.product_purchase_report'),
                                ['icon' => 'fa fas fa-arrow-circle-down', 'active' => request()->segment(2) == 'product-purchase-report']
                            );

                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getproductSellReport']),
                                __('lang_v1.product_sell_report'),
                                ['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'product-sell-report']
                            );

                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'purchasePaymentReport']),
                                __('lang_v1.purchase_payment_report'),
                                ['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'purchase-payment-report']
                            );

                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'sellPaymentReport']),
                                __('lang_v1.sell_payment_report'),
                                ['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'sell-payment-report']
                            );
                        }
                        if (in_array('expenses', $enabled_modules) && auth()->user()->can('expense_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getExpenseReport']),
                                __('report.expense_report'),
                                ['icon' => 'fa fas fa-search-minus', 'active' => request()->segment(2) == 'expense-report']
                            );
                        }
                        if (auth()->user()->can('register_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getRegisterReport']),
                                __('report.register_report'),
                                ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(2) == 'register-report']
                            );
                        }
                        if (auth()->user()->can('sales_representative.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getSalesRepresentativeReport']),
                                __('report.sales_representative'),
                                ['icon' => 'fa fas fa-user', 'active' => request()->segment(2) == 'sales-representative-report']
                            );
                        }
                        if (auth()->user()->can('purchase_n_sell_report.view') && in_array('tables', $enabled_modules)) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getTableReport']),
                                __('restaurant.table_report'),
                                ['icon' => 'fa fas fa-table', 'active' => request()->segment(2) == 'table-report']
                            );
                        }

                        if (auth()->user()->can('tax_report.view') && !empty(config('constants.enable_gst_report_india'))) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'gstSalesReport']),
                                __('lang_v1.gst_sales_report'),
                                ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'gst-sales-report']
                            );

                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'gstPurchaseReport']),
                                __('lang_v1.gst_purchase_report'),
                                ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'gst-purchase-report']
                            );
                        }

                        if (auth()->user()->can('sales_representative.view') && in_array('service_staff', $enabled_modules)) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getServiceStaffReport']),
                                __('restaurant.service_staff_report'),
                                ['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'service-staff-report']
                            );
                        }

                        if ($is_admin) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'activityLog']),
                                __('lang_v1.activity_log'),
                                ['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'activity-log']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-chart-bar', 'id' => 'tour_step8']
                );
            };

            //Expense dropdown
            if (
                in_array('expenses', $enabled_modules) &&
                (auth()->user()->can('all_expense.access')
                    || auth()->user()->can('view_own_expense'))
            ) {
                $menu->dropdown(
                    __('expense.expenses'),
                    function ($sub) {
                        $sub->url(
                            action([\App\Http\Controllers\ExpenseController::class, 'index']),
                            __('lang_v1.list_expenses'),
                            ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == null]
                        );

                        if (auth()->user()->can('expense.add')) {
                            $sub->url(
                                action([\App\Http\Controllers\ExpenseController::class, 'create']),
                                __('expense.add_expense'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == 'create']
                            );
                        }

                        if (auth()->user()->can('expense.add') || auth()->user()->can('expense.edit')) {
                            $sub->url(
                                action([\App\Http\Controllers\ExpenseCategoryController::class, 'index']),
                                __('expense.expense_categories'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'expense-categories']
                            );
                        }
                    },
                    ['icon' => 'fas fa-shopping-basket ']
                );
            };




            //Booking menu
            if (in_array('booking', $enabled_modules) && (auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings'))) {
                $menu->url(action([\App\Http\Controllers\Restaurant\BookingController::class, 'index']), __('restaurant.bookings'), ['icon' => 'fas fa fa-calendar-check', 'active' => request()->segment(1) == 'bookings']);
            }

            //Kitchen menu
            if (in_array('kitchen', $enabled_modules)) {
                $menu->url(action([\App\Http\Controllers\Restaurant\KitchenController::class, 'index']), __('restaurant.kitchen'), ['icon' => 'fa fas fa-fire', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'kitchen']);
            }

            //Service Staff menu
            if (in_array('service_staff', $enabled_modules)) {
                $menu->url(action([\App\Http\Controllers\Restaurant\OrderController::class, 'index']), __('restaurant.orders'), ['icon' => 'fa fas fa-list-alt', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'orders']);
            }

            //Notification template menu
            if (auth()->user()->can('send_notifications')) {
                $menu->url(action([\App\Http\Controllers\NotificationTemplateController::class, 'index']), __('lang_v1.notification_templates'), ['icon' => 'fa fas fa-envelope', 'active' => request()->segment(1) == 'notification-templates']);
            }
        });
    }

    public function purchasesMenu()
    {

        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            $menu->url(route('purchases.index'),   __('purchase.purchases'), ['icon' => 'fas fa-cart-plus']);
            //$menu->header("");
            //$menu->header("");

            //Purchase dropdown
            if ((auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') || auth()->user()->can('purchase.update'))) {
                $menu->dropdown(
                    __('purchase.purchases'),
                    function ($sub) use ($common_settings) {
                        if (!empty($common_settings['enable_purchase_requisition']) && (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own'))) {
                            $sub->url(
                                action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']),
                                __('lang_v1.purchase_requisition'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchase-requisition']
                            );
                        }

                        if (!empty($common_settings['enable_purchase_order']) && (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own'))) {
                            $sub->url(
                                action([\App\Http\Controllers\PurchaseOrderController::class, 'index']),
                                __('lang_v1.purchase_order'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchase-order']
                            );
                        }
                        if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
                            $sub->url(
                                action([\App\Http\Controllers\PurchaseController::class, 'index']),
                                __('purchase.list_purchase'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == null]
                            );
                        }
                        if (auth()->user()->can('purchase.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\PurchaseController::class, 'create']),
                                __('purchase.add_purchase'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == 'create']
                            );
                        }
                        if (auth()->user()->can('purchase.update')) {
                            $sub->url(
                                action([\App\Http\Controllers\PurchaseReturnController::class, 'index']),
                                __('lang_v1.list_purchase_return'),
                                ['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'purchase-return']
                            );
                        }
                    },
                    ['icon' => 'fas fa-cart-plus ', 'id' => 'tour_step6']
                );
            }
        });
    }

    public function productsMenu()
    {

        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            $menu->url(action([\App\Http\Controllers\ProductController::class, 'index']), __('sale.products'), ['icon' => 'fas fa-chart-pie']);
            //$menu->header("");
            //$menu->header("");
            //Products dropdown
            if (
                auth()->user()->can('product.view') || auth()->user()->can('product.create') ||
                auth()->user()->can('brand.view') || auth()->user()->can('unit.view') ||
                auth()->user()->can('category.view') || auth()->user()->can('brand.create') ||
                auth()->user()->can('unit.create') || auth()->user()->can('category.create')
            ) {
                $menu->dropdown(
                    __('sale.products'),
                    function ($sub) {
                        if (auth()->user()->can('product.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ProductController::class, 'index']),
                                __('lang_v1.list_products'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'products' && request()->segment(2) == '']
                            );
                        }


                        if (auth()->user()->can('product.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\ProductController::class, 'create']),
                                __('product.add_product'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'products' && request()->segment(2) == 'create']
                            );
                        }
                        if (auth()->user()->can('product.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\SellingPriceGroupController::class, 'updateProductPrice']),
                                __('lang_v1.update_product_price'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'update-product-price']
                            );
                        }
                        if (auth()->user()->can('product.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\LabelsController::class, 'show']),
                                __('barcode.print_labels'),
                                ['icon' => 'fa fas fa-barcode', 'active' => request()->segment(1) == 'labels' && request()->segment(2) == 'show']
                            );
                        }
                        if (auth()->user()->can('product.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\VariationTemplateController::class, 'index']),
                                __('product.variations'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'variation-templates']
                            );
                            $sub->url(
                                action([\App\Http\Controllers\ImportProductsController::class, 'index']),
                                __('product.import_products'),
                                ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'import-products']
                            );
                        }
                        if (auth()->user()->can('product.opening_stock')) {
                            $sub->url(
                                action([\App\Http\Controllers\ImportOpeningStockController::class, 'index']),
                                __('lang_v1.import_opening_stock'),
                                ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'import-opening-stock']
                            );
                        }
                        if (auth()->user()->can('product.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\SellingPriceGroupController::class, 'index']),
                                __('lang_v1.selling_price_group'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'selling-price-group']
                            );
                        }
                        if (auth()->user()->can('unit.view') || auth()->user()->can('unit.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\UnitController::class, 'index']),
                                __('unit.units'),
                                ['icon' => 'fa fas fa-balance-scale', 'active' => request()->segment(1) == 'units']
                            );
                        }
                        if (auth()->user()->can('category.view') || auth()->user()->can('category.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=product',
                                __('category.categories'),
                                ['icon' => 'fa fas fa-tags', 'active' => request()->segment(1) == 'taxonomies' && request()->get('type') == 'product']
                            );
                        }
                        if (auth()->user()->can('brand.view') || auth()->user()->can('brand.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\BrandController::class, 'index']),
                                __('brand.brands'),
                                ['icon' => 'fa fas fa-gem', 'active' => request()->segment(1) == 'brands']
                            );
                        }

                        $sub->url(
                            action([\App\Http\Controllers\WarrantyController::class, 'index']),
                            __('lang_v1.warranties'),
                            ['icon' => 'fa fas fa-shield-alt', 'active' => request()->segment(1) == 'warranties']
                        );
                    },
                    ['icon' => 'fas fa-chart-pie ', 'id' => 'tour_step5']
                );
            }
        });
    }

    // public function superAdminMenu()
    // {

    //     Menu::create('admin-sidebar-menu', function ($menu) {
    //         $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
    //         $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
    //         $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
    //         $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
    //         $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home  ', 'active' => request()->segment(1) == 'home']);

    //         $menu->url(action([\Modules\Superadmin\Http\Controllers\SuperadminController::class, 'index']), __('superadmin::lang.superadmin'), ['icon' => 'fa fas fa-users-cog', 'active' => request()->segment(1) == 'superadmin']);

    //         //$menu->header("");
    //         //$menu->header("");

    //         if (($is_admin) || (auth()->user()->can('superadmin.access_package_subscriptions') && auth()->user()->can('business_settings.access'))) {
    //             $menu->url(
    //                 action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'index']),
    //                 __('superadmin::lang.subscription'),
    //                 ['icon' => 'fa fas fa-sync', 'active' => request()->segment(1) == 'subscription']
    //             );
    //         }

    //         //Modules menu
    //         if (($is_admin) || (auth()->user()->can('manage_modules'))) {
    //             $menu->url(action([\App\Http\Controllers\Install\ModulesController::class, 'index']), __('lang_v1.modules'), ['icon' => 'fa fas fa-plug', 'active' => request()->segment(1) == 'manage-modules']);
    //         }
    //         //Backup menu
    //         if (($is_admin) || (auth()->user()->can('backup'))) {
    //             $menu->url(action([\App\Http\Controllers\BackUpController::class, 'index']), __('lang_v1.backup'), ['icon' => 'fa fas fa-hdd', 'active' => request()->segment(1) == 'backup']);
    //         }
    //     });
    // }

    // public function oldHandle($request, Closure $next)
    // {
    //     if (auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')) {
    //         $menu->dropdown(
    //             __('user.user_management'),
    //             function ($sub) use($is_admin){
    //                 if (auth()->user()->can('user.view')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ManageUserController::class, 'index']),
    //                         __('user.users'),
    //                         ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users' || request()->segment(1) == 'manage_user']
    //                     );
    //                 }
    //                 // if (auth()->user()->can('roles.view')) {
    //                 //     $sub->url(
    //                 //         action([\App\Http\Controllers\RoleController::class, 'index']),
    //                 //         __('user.roles'),
    //                 //         ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(1) == 'roles']
    //                 //     );
    //                 // }
    //                 // if (auth()->user()->can('user.create')) {
    //                 //     $sub->url(
    //                 //         action([\App\Http\Controllers\SalesCommissionAgentController::class, 'index']),
    //                 //         __('lang_v1.sales_commission_agents'),
    //                 //         ['icon' => 'fa fas fa-handshake', 'active' => request()->segment(1) == 'sales-commission-agents']
    //                 //     );
    //                 // }
    //             },
    //             ['icon' => 'fas fa-user-tie ']
    //         );
    //     }


    //     //Contacts dropdown
    //     if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own')) {
    //         $menu->dropdown(
    //             __('contact.contacts'),
    //             function ($sub) {
    //                 if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'supplier']),
    //                         __('report.supplier'),
    //                         ['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'supplier']
    //                     );
    //                 }
    //                 if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']),
    //                         __('report.customer'),
    //                         ['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'customer']
    //                     );
    //                     $sub->url(
    //                         action([\App\Http\Controllers\CustomerGroupController::class, 'index']),
    //                         __('lang_v1.customer_groups'),
    //                         ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'customer-group']
    //                     );
    //                 }
    //                 if (auth()->user()->can('supplier.create') || auth()->user()->can('customer.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ContactController::class, 'getImportContacts']),
    //                         __('lang_v1.import_contacts'),
    //                         ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'import']
    //                     );
    //                 }

    //                 if (!empty(env('GOOGLE_MAP_API_KEY'))) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ContactController::class, 'contactMap']),
    //                         __('lang_v1.map'),
    //                         ['icon' => 'fa fas fa-map-marker-alt', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'map']
    //                     );
    //                 }
    //             },
    //             ['icon' => 'fas fa-id-card ', 'id' => 'tour_step4']
    //         );
    //     }

    //     //Products dropdown
    //     if (
    //         auth()->user()->can('product.view') || auth()->user()->can('product.create') ||
    //         auth()->user()->can('brand.view') || auth()->user()->can('unit.view') ||
    //         auth()->user()->can('category.view') || auth()->user()->can('brand.create') ||
    //         auth()->user()->can('unit.create') || auth()->user()->can('category.create')
    //     ) {
    //         $menu->dropdown(
    //             __('sale.products'),
    //             function ($sub) use($is_admin){
    //                 if (auth()->user()->can('product.view')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ProductController::class, 'index']),
    //                         __('lang_v1.list_products'),
    //                         ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'products' && request()->segment(2) == '']
    //                     );
    //                 }


    //                 if (auth()->user()->can('product.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ProductController::class, 'create']),
    //                         __('product.add_product'),
    //                         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'products' && request()->segment(2) == 'create']
    //                     );
    //                 }
    //                 if (auth()->user()->can('product.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellingPriceGroupController::class, 'updateProductPrice']),
    //                         __('lang_v1.update_product_price'),
    //                         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'update-product-price']
    //                     );
    //                 }
    //                 if (auth()->user()->can('product.view')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\LabelsController::class, 'show']),
    //                         __('barcode.print_labels'),
    //                         ['icon' => 'fa fas fa-barcode', 'active' => request()->segment(1) == 'labels' && request()->segment(2) == 'show']
    //                     );
    //                 }
    //                 if (auth()->user()->can('product.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\VariationTemplateController::class, 'index']),
    //                         __('product.variations'),
    //                         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'variation-templates']
    //                     );
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ImportProductsController::class, 'index']),
    //                         __('product.import_products'),
    //                         ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'import-products']
    //                     );
    //                 }
    //                 if (auth()->user()->can('product.opening_stock')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ImportOpeningStockController::class, 'index']),
    //                         __('lang_v1.import_opening_stock'),
    //                         ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'import-opening-stock']
    //                     );
    //                 }
    //                 if (auth()->user()->can('product.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellingPriceGroupController::class, 'index']),
    //                         __('lang_v1.selling_price_group'),
    //                         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'selling-price-group']
    //                     );
    //                 }
    //                 if (auth()->user()->can('unit.view') || auth()->user()->can('unit.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\UnitController::class, 'index']),
    //                         __('unit.units'),
    //                         ['icon' => 'fa fas fa-balance-scale', 'active' => request()->segment(1) == 'units']
    //                     );
    //                 }
    //                 if (auth()->user()->can('category.view') || auth()->user()->can('category.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=product',
    //                         __('category.categories'),
    //                         ['icon' => 'fa fas fa-tags', 'active' => request()->segment(1) == 'taxonomies' && request()->get('type') == 'product']
    //                     );
    //                 }
    //                 if (auth()->user()->can('brand.view') || auth()->user()->can('brand.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\BrandController::class, 'index']),
    //                         __('brand.brands'),
    //                         ['icon' => 'fa fas fa-gem', 'active' => request()->segment(1) == 'brands']
    //                     );
    //                 }

    //                 $sub->url(
    //                     action([\App\Http\Controllers\WarrantyController::class, 'index']),
    //                     __('lang_v1.warranties'),
    //                     ['icon' => 'fa fas fa-shield-alt', 'active' => request()->segment(1) == 'warranties']
    //                 );
    //             },
    //             ['icon' => 'fas fa-chart-pie ', 'id' => 'tour_step5']
    //         );
    //     }

    //     // //Purchase dropdown
    //     // if (in_array('purchases', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') || auth()->user()->can('purchase.update'))) {
    //     //     $menu->dropdown(
    //     //         __('purchase.purchases'),
    //     //         function ($sub) use ($common_settings) {
    //     //             if (!empty($common_settings['enable_purchase_requisition']) && (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own'))) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']),
    //     //                     __('lang_v1.purchase_requisition'),
    //     //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchase-requisition']
    //     //                 );
    //     //             }

    //     //             if (!empty($common_settings['enable_purchase_order']) && (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own'))) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\PurchaseOrderController::class, 'index']),
    //     //                     __('lang_v1.purchase_order'),
    //     //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchase-order']
    //     //                 );
    //     //             }
    //     //             if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\PurchaseController::class, 'index']),
    //     //                     __('purchase.list_purchase'),
    //     //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == null]
    //     //                 );
    //     //             }
    //     //             if (auth()->user()->can('purchase.create')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\PurchaseController::class, 'create']),
    //     //                     __('purchase.add_purchase'),
    //     //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == 'create']
    //     //                 );
    //     //             }
    //     //             if (auth()->user()->can('purchase.update')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\PurchaseReturnController::class, 'index']),
    //     //                     __('lang_v1.list_purchase_return'),
    //     //                     ['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'purchase-return']
    //     //                 );
    //     //             }
    //     //         },
    //     //         ['icon' => 'fas fa-cart-plus ', 'id' => 'tour_step6']
    //     //     );
    //     // }
    //     //Sell dropdown
    //     if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping', 'access_sell_return', 'direct_sell.view', 'direct_sell.update', 'access_own_sell_return'])) {
    //         $menu->dropdown(
    //             __('sale.sale'),
    //             function ($sub) use ($enabled_modules, $is_admin, $pos_settings) {
    //                 if (!empty($pos_settings['enable_sales_order']) && ($is_admin || auth()->user()->hasAnyPermission(['so.view_own', 'so.view_all', 'so.create']))) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SalesOrderController::class, 'index']),
    //                         __('lang_v1.sales_order'),
    //                         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sales-order']
    //                     );
    //                 }

    //                 if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'direct_sell.view', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellController::class, 'index']),
    //                         __('lang_v1.all_sales'),
    //                         ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == null]
    //                     );
    //                 }
    //                 if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellController::class, 'create']),
    //                         __('sale.add_sale'),
    //                         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == 'create' && empty(request()->get('status'))]
    //                     );
    //                 }
    //                 if (auth()->user()->can('sell.create')) {
    //                     if (in_array('pos_sale', $enabled_modules)) {
    //                         if (auth()->user()->can('sell.view')) {
    //                             $sub->url(
    //                                 action([\App\Http\Controllers\SellPosController::class, 'index']),
    //                                 __('sale.list_pos'),
    //                                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'pos' && request()->segment(2) == null]
    //                             );
    //                         }

    //                         $sub->url(
    //                             action([\App\Http\Controllers\SellPosController::class, 'create']),
    //                             __('sale.pos_sale'),
    //                             ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'pos' && request()->segment(2) == 'create']
    //                         );
    //                     }
    //                 }

    //                 if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'draft']),
    //                         __('lang_v1.add_draft'),
    //                         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->get('status') == 'draft']
    //                     );
    //                 }
    //                 if (in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['draft.view_all', 'draft.view_own']))) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellController::class, 'getDrafts']),
    //                         __('lang_v1.list_drafts'),
    //                         ['icon' => 'fa fas fa-pen-square', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == 'drafts']
    //                     );
    //                 }
    //                 if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'quotation']),
    //                         __('lang_v1.add_quotation'),
    //                         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->get('status') == 'quotation']
    //                     );
    //                 }
    //                 if (in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['quotation.view_all', 'quotation.view_own']))) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellController::class, 'getQuotations']),
    //                         __('lang_v1.list_quotations'),
    //                         ['icon' => 'fa fas fa-pen-square', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == 'quotations']
    //                     );
    //                 }

    //                 if (auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellReturnController::class, 'index']),
    //                         __('lang_v1.list_sell_return'),
    //                         ['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'sell-return' && request()->segment(2) == null]
    //                     );
    //                 }

    //                 if ($is_admin || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellController::class, 'shipments']),
    //                         __('lang_v1.shipments'),
    //                         ['icon' => 'fa fas fa-truck', 'active' => request()->segment(1) == 'shipments']
    //                     );
    //                 }

    //                 if (auth()->user()->can('discount.access')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\DiscountController::class, 'index']),
    //                         __('lang_v1.discounts'),
    //                         ['icon' => 'fa fas fa-percent', 'active' => request()->segment(1) == 'discount']
    //                     );
    //                 }
    //                 if (in_array('subscription', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\SellPosController::class, 'listSubscriptions']),
    //                         __('lang_v1.subscriptions'),
    //                         ['icon' => 'fa fas fa-recycle', 'active' => request()->segment(1) == 'subscriptions']
    //                     );
    //                 }

    //                 if (auth()->user()->can('sell.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\ImportSalesController::class, 'index']),
    //                         __('lang_v1.import_sales'),
    //                         ['icon' => 'fa fas fa-file-import', 'active' => request()->segment(1) == 'import-sales']
    //                     );
    //                 }
    //             },
    //             ['icon' => 'fas fa-universal-access ', 'id' => 'tour_step7']
    //         );
    //     }
    //     //Stock transfer dropdown
    //     if (in_array('stock_transfers', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
    //         $menu->dropdown(
    //             __('lang_v1.stock_transfers'),
    //             function ($sub) {
    //                 if (auth()->user()->can('purchase.view')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\StockTransferController::class, 'index']),
    //                         __('lang_v1.list_stock_transfers'),
    //                         ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'stock-transfers' && request()->segment(2) == null]
    //                     );
    //                 }
    //                 if (auth()->user()->can('purchase.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\StockTransferController::class, 'create']),
    //                         __('lang_v1.add_stock_transfer'),
    //                         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'stock-transfers' && request()->segment(2) == 'create']
    //                     );
    //                 }
    //             },
    //             ['icon' => 'fa fas fa-truck']
    //         );
    //     }

    //     //stock adjustment dropdown
    //     if (in_array('stock_adjustment', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
    //         $menu->dropdown(
    //             __('stock_adjustment.stock_adjustment'),
    //             function ($sub) {
    //                 if (auth()->user()->can('purchase.view')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\StockAdjustmentController::class, 'index']),
    //                         __('stock_adjustment.list'),
    //                         ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'stock-adjustments' && request()->segment(2) == null]
    //                     );
    //                 }
    //                 if (auth()->user()->can('purchase.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\StockAdjustmentController::class, 'create']),
    //                         __('stock_adjustment.add'),
    //                         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'stock-adjustments' && request()->segment(2) == 'create']
    //                     );
    //                 }
    //             },
    //             ['icon' => 'fa fas fa-database']
    //         );
    //     }


    //     //customer_sales

    //     // // Expense dropdown
    //     // if (in_array('expenses', $enabled_modules) && (auth()->user()->can('all_expense.access') || auth()->user()->can('view_own_expense'))) {
    //     //     $menu->dropdown(
    //     //         __('expense.expenses'),
    //     //         function ($sub) {
    //     //             $sub->url(
    //     //                 action([\App\Http\Controllers\ExpenseController::class, 'index']),
    //     //                 __('lang_v1.list_expenses'),
    //     //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == null]
    //     //             );

    //     //             if (auth()->user()->can('expense.add')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ExpenseController::class, 'create']),
    //     //                     __('expense.add_expense'),
    //     //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == 'create']
    //     //                 );
    //     //             }

    //     //             if (auth()->user()->can('expense.add') || auth()->user()->can('expense.edit')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ExpenseCategoryController::class, 'index']),
    //     //                     __('expense.expense_categories'),
    //     //                     ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'expense-categories']
    //     //                 );
    //     //             }
    //     //         },
    //     //         ['icon' => 'fas fa-shopping-basket ']
    //     //     );
    //     // }
    //     //Accounts dropdown
    //     if (auth()->user()->can('account.access') && in_array('account', $enabled_modules)) {
    //         $menu->dropdown(
    //             __('lang_v1.payment_accounts'),
    //             function ($sub) {
    //                 $sub->url(
    //                     action([\App\Http\Controllers\AccountController::class, 'index']),
    //                     __('account.list_accounts'),
    //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'account']
    //                 );
    //                 $sub->url(
    //                     action([\App\Http\Controllers\AccountReportsController::class, 'balanceSheet']),
    //                     __('account.balance_sheet'),
    //                     ['icon' => 'fa fas fa-book', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'balance-sheet']
    //                 );
    //                 $sub->url(
    //                     action([\App\Http\Controllers\AccountReportsController::class, 'trialBalance']),
    //                     __('account.trial_balance'),
    //                     ['icon' => 'fa fas fa-balance-scale', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'trial-balance']
    //                 );
    //                 $sub->url(
    //                     action([\App\Http\Controllers\AccountController::class, 'cashFlow']),
    //                     __('lang_v1.cash_flow'),
    //                     ['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'cash-flow']
    //                 );
    //                 $sub->url(
    //                     action([\App\Http\Controllers\AccountReportsController::class, 'paymentAccountReport']),
    //                     __('account.payment_account_report'),
    //                     ['icon' => 'fa fas fa-file-alt', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'payment-account-report']
    //                 );
    //             },
    //             ['icon' => 'fa fas fa-money-check-alt']
    //         );
    //     }

    //     // //Reports dropdown
    //     // if (
    //     //     auth()->user()->can('purchase_n_sell_report.view') || auth()->user()->can('contacts_report.view')
    //     //     || auth()->user()->can('stock_report.view') || auth()->user()->can('tax_report.view')
    //     //     || auth()->user()->can('trending_product_report.view') || auth()->user()->can('sales_representative.view') || auth()->user()->can('register_report.view')
    //     //     || auth()->user()->can('expense_report.view')
    //     // ) {
    //     //     $menu->dropdown(
    //     //         __('report.reports'),
    //     //         function ($sub) use ($enabled_modules, $is_admin) {
    //     //             if (auth()->user()->can('profit_loss_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getProfitLoss']),
    //     //                     __('report.profit_loss'),
    //     //                     ['icon' => 'fa fas fa-file-invoice-dollar', 'active' => request()->segment(2) == 'profit-loss']
    //     //                 );
    //     //             }
    //     //             if (config('constants.show_report_606') == true) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'purchaseReport']),
    //     //                     'Report 606 (' . __('lang_v1.purchase') . ')',
    //     //                     ['icon' => 'fa fas fa-arrow-circle-down', 'active' => request()->segment(2) == 'purchase-report']
    //     //                 );
    //     //             }
    //     //             if (config('constants.show_report_607') == true) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'saleReport']),
    //     //                     'Report 607 (' . __('business.sale') . ')',
    //     //                     ['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'sale-report']
    //     //                 );
    //     //             }
    //     //             if ((in_array('purchases', $enabled_modules) || in_array('add_sale', $enabled_modules) || in_array('pos_sale', $enabled_modules)) && auth()->user()->can('purchase_n_sell_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getPurchaseSell']),
    //     //                     __('report.purchase_sell_report'),
    //     //                     ['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(2) == 'purchase-sell']
    //     //                 );
    //     //             }

    //     //             if (auth()->user()->can('tax_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getTaxReport']),
    //     //                     __('report.tax_report'),
    //     //                     ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'tax-report']
    //     //                 );
    //     //             }
    //     //             if (auth()->user()->can('contacts_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getCustomerSuppliers']),
    //     //                     __('report.contacts'),
    //     //                     ['icon' => 'fa fas fa-address-book', 'active' => request()->segment(2) == 'customer-supplier']
    //     //                 );
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getCustomerGroup']),
    //     //                     __('lang_v1.customer_groups_report'),
    //     //                     ['icon' => 'fa fas fa-users', 'active' => request()->segment(2) == 'customer-group']
    //     //                 );
    //     //             }
    //     //             if (auth()->user()->can('stock_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
    //     //                     __('report.stock_report'),
    //     //                     ['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'stock-report']
    //     //                 );
    //     //                 if (session('business.enable_product_expiry') == 1) {
    //     //                     $sub->url(
    //     //                         action([\App\Http\Controllers\ReportController::class, 'getStockExpiryReport']),
    //     //                         __('report.stock_expiry_report'),
    //     //                         ['icon' => 'fa fas fa-calendar-times', 'active' => request()->segment(2) == 'stock-expiry']
    //     //                     );
    //     //                 }
    //     //                 if (session('business.enable_lot_number') == 1) {
    //     //                     $sub->url(
    //     //                         action([\App\Http\Controllers\ReportController::class, 'getLotReport']),
    //     //                         __('lang_v1.lot_report'),
    //     //                         ['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'lot-report']
    //     //                     );
    //     //                 }

    //     //                 if (in_array('stock_adjustment', $enabled_modules)) {
    //     //                     $sub->url(
    //     //                         action([\App\Http\Controllers\ReportController::class, 'getStockAdjustmentReport']),
    //     //                         __('report.stock_adjustment_report'),
    //     //                         ['icon' => 'fa fas fa-sliders-h', 'active' => request()->segment(2) == 'stock-adjustment-report']
    //     //                     );
    //     //                 }
    //     //             }

    //     //             if (auth()->user()->can('trending_product_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getTrendingProducts']),
    //     //                     __('report.trending_products'),
    //     //                     ['icon' => 'fa fas fa-chart-line', 'active' => request()->segment(2) == 'trending-products']
    //     //                 );
    //     //             }

    //     //             if (auth()->user()->can('purchase_n_sell_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'itemsReport']),
    //     //                     __('lang_v1.items_report'),
    //     //                     ['icon' => 'fa fas fa-tasks', 'active' => request()->segment(2) == 'items-report']
    //     //                 );

    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getproductPurchaseReport']),
    //     //                     __('lang_v1.product_purchase_report'),
    //     //                     ['icon' => 'fa fas fa-arrow-circle-down', 'active' => request()->segment(2) == 'product-purchase-report']
    //     //                 );

    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getproductSellReport']),
    //     //                     __('lang_v1.product_sell_report'),
    //     //                     ['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'product-sell-report']
    //     //                 );

    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'purchasePaymentReport']),
    //     //                     __('lang_v1.purchase_payment_report'),
    //     //                     ['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'purchase-payment-report']
    //     //                 );

    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'sellPaymentReport']),
    //     //                     __('lang_v1.sell_payment_report'),
    //     //                     ['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'sell-payment-report']
    //     //                 );
    //     //             }
    //     //             if (in_array('expenses', $enabled_modules) && auth()->user()->can('expense_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getExpenseReport']),
    //     //                     __('report.expense_report'),
    //     //                     ['icon' => 'fa fas fa-search-minus', 'active' => request()->segment(2) == 'expense-report']
    //     //                 );
    //     //             }
    //     //             if (auth()->user()->can('register_report.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getRegisterReport']),
    //     //                     __('report.register_report'),
    //     //                     ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(2) == 'register-report']
    //     //                 );
    //     //             }
    //     //             if (auth()->user()->can('sales_representative.view')) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getSalesRepresentativeReport']),
    //     //                     __('report.sales_representative'),
    //     //                     ['icon' => 'fa fas fa-user', 'active' => request()->segment(2) == 'sales-representative-report']
    //     //                 );
    //     //             }
    //     //             if (auth()->user()->can('purchase_n_sell_report.view') && in_array('tables', $enabled_modules)) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getTableReport']),
    //     //                     __('restaurant.table_report'),
    //     //                     ['icon' => 'fa fas fa-table', 'active' => request()->segment(2) == 'table-report']
    //     //                 );
    //     //             }

    //     //             if (auth()->user()->can('tax_report.view') && !empty(config('constants.enable_gst_report_india'))) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'gstSalesReport']),
    //     //                     __('lang_v1.gst_sales_report'),
    //     //                     ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'gst-sales-report']
    //     //                 );

    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'gstPurchaseReport']),
    //     //                     __('lang_v1.gst_purchase_report'),
    //     //                     ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'gst-purchase-report']
    //     //                 );
    //     //             }

    //     //             if (auth()->user()->can('sales_representative.view') && in_array('service_staff', $enabled_modules)) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'getServiceStaffReport']),
    //     //                     __('restaurant.service_staff_report'),
    //     //                     ['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'service-staff-report']
    //     //                 );
    //     //             }

    //     //             if ($is_admin) {
    //     //                 $sub->url(
    //     //                     action([\App\Http\Controllers\ReportController::class, 'activityLog']),
    //     //                     __('lang_v1.activity_log'),
    //     //                     ['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'activity-log']
    //     //                 );
    //     //             }
    //     //         },
    //     //         ['icon' => 'fa fas fa-chart-bar', 'id' => 'tour_step8']
    //     //     )->order(55);
    //     // }

    //     // //Backup menu
    //     // if (auth()->user()->can('backup')) {
    //     //     $menu->url(action([\App\Http\Controllers\BackUpController::class, 'index']), __('lang_v1.backup'), ['icon' => 'fa fas fa-hdd', 'active' => request()->segment(1) == 'backup'])->order(60);
    //     // }

    //     // //Modules menu
    //     // if (auth()->user()->can('manage_modules')) {
    //     //     $menu->url(action([\App\Http\Controllers\Install\ModulesController::class, 'index']), __('lang_v1.modules'), ['icon' => 'fa fas fa-plug', 'active' => request()->segment(1) == 'manage-modules'])->order(60);
    //     // }

    //     // //Booking menu
    //     // if (in_array('booking', $enabled_modules) && (auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings'))) {
    //     //     $menu->url(action([\App\Http\Controllers\Restaurant\BookingController::class, 'index']), __('restaurant.bookings'), ['icon' => 'fas fa fa-calendar-check', 'active' => request()->segment(1) == 'bookings'])->order(65);
    //     // }

    //     // //Kitchen menu
    //     // if (in_array('kitchen', $enabled_modules)) {
    //     //     $menu->url(action([\App\Http\Controllers\Restaurant\KitchenController::class, 'index']), __('restaurant.kitchen'), ['icon' => 'fa fas fa-fire', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'kitchen'])->order(70);
    //     // }

    //     // //Service Staff menu
    //     // if (in_array('service_staff', $enabled_modules)) {
    //     //     $menu->url(action([\App\Http\Controllers\Restaurant\OrderController::class, 'index']), __('restaurant.orders'), ['icon' => 'fa fas fa-list-alt', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'orders'])->order(75);
    //     // }

    //     // //Notification template menu
    //     // if (auth()->user()->can('send_notifications')) {
    //     //     $menu->url(action([\App\Http\Controllers\NotificationTemplateController::class, 'index']), __('lang_v1.notification_templates'), ['icon' => 'fa fas fa-envelope', 'active' => request()->segment(1) == 'notification-templates'])->order(80);
    //     // }

    //     //Settings Dropdown
    //     if (
    //         auth()->user()->can('business_settings.access') ||
    //         auth()->user()->can('barcode_settings.access') ||
    //         auth()->user()->can('invoice_settings.access') ||
    //         auth()->user()->can('tax_rate.view') ||
    //         auth()->user()->can('tax_rate.create') ||
    //         auth()->user()->can('access_package_subscriptions')
    //     ) {
    //         $menu->dropdown(
    //             __('business.settings'),
    //             function ($sub) use ($enabled_modules) {
    //                 if (auth()->user()->can('business_settings.access')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings']),
    //                         __('business.business_settings'),
    //                         ['icon' => 'fa fas fa-cogs', 'active' => request()->segment(1) == 'business', 'id' => 'tour_step2']
    //                     );
    //                     $sub->url(
    //                         action([\App\Http\Controllers\BusinessLocationController::class, 'index']),
    //                         __('business.business_locations'),
    //                         ['icon' => 'fa fas fa-map-marker', 'active' => request()->segment(1) == 'business-location']
    //                     );
    //                 }
    //                 if (auth()->user()->can('invoice_settings.access')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\InvoiceSchemeController::class, 'index']),
    //                         __('invoice.invoice_settings'),
    //                         ['icon' => 'fa fas fa-file', 'active' => in_array(request()->segment(1), ['invoice-schemes', 'invoice-layouts'])]
    //                     );
    //                 }
    //                 if (auth()->user()->can('barcode_settings.access')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\BarcodeController::class, 'index']),
    //                         __('barcode.barcode_settings'),
    //                         ['icon' => 'fa fas fa-barcode', 'active' => request()->segment(1) == 'barcodes']
    //                     );
    //                 }
    //                 if (auth()->user()->can('access_printers')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\PrinterController::class, 'index']),
    //                         __('printer.receipt_printers'),
    //                         ['icon' => 'fa fas fa-share-alt', 'active' => request()->segment(1) == 'printers']
    //                     );
    //                 }

    //                 if (auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\TaxRateController::class, 'index']),
    //                         __('tax_rate.tax_rates'),
    //                         ['icon' => 'fa fas fa-bolt', 'active' => request()->segment(1) == 'tax-rates']
    //                     );
    //                 }

    //                 if (in_array('tables', $enabled_modules) && auth()->user()->can('access_tables')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\Restaurant\TableController::class, 'index']),
    //                         __('restaurant.tables'),
    //                         ['icon' => 'fa fas fa-table', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'tables']
    //                     );
    //                 }

    //                 if (in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create'))) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\Restaurant\ModifierSetsController::class, 'index']),
    //                         __('restaurant.modifiers'),
    //                         ['icon' => 'fa fas fa-pizza-slice', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'modifiers']
    //                     );
    //                 }

    //                 if (in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service')) {
    //                     $sub->url(
    //                         action([\App\Http\Controllers\TypesOfServiceController::class, 'index']),
    //                         __('lang_v1.types_of_service'),
    //                         ['icon' => 'fa fas fa-user-circle', 'active' => request()->segment(1) == 'types-of-service']
    //                     );
    //                 }
    //             },
    //             ['icon' => 'fa fas fa-cog', 'id' => 'tour_step3']
    //         );
    //     }
    // }
}
