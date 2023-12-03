<?php

namespace App\Http\Middleware;

use App\Utils\ModuleUtil;
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
        $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home']);
        });
        $currentPath = $request->path();
        // Define logic to set the menuName based on the route
        if (Str::startsWith($currentPath, 'users')) {
            $this->userManagementMenu();
        } elseif (Str::startsWith($currentPath, ['essentials', 'hrm', 'roles'])) {
            $this->essentialsMenu();
        } elseif (Str::startsWith($currentPath, 'sale')) {
            $this->CUS_salesMenu();
        } elseif (Str::startsWith($currentPath, 'housingmovements')) {
            $this->houseMovementsMenu();
        } elseif (Str::startsWith($currentPath, ['international', 'ir'])) {
            $this->getIRMenu();
        } elseif (Str::startsWith($currentPath, 'accounting')) {
            $this->accountingMenu();
        } elseif (Str::startsWith($currentPath, 'followup')) {
            $this->followUpMenu();
        } elseif (Str::startsWith($currentPath, 'purchase')) {
            $this->purchasesMenu();
        } elseif (Str::startsWith($currentPath, 'superadmin')) {
            $this->superAdminMenu();
        } elseif (
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

    public function connectorMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(
                action([\Modules\Connector\Http\Controllers\ClientController::class, 'index']),
                __('connector::lang.clients'),
                ['icon' => 'fa fas fa-network-wired', 'active' => request()->segment(1) == 'connector' && request()->segment(2) == 'api']
            );
            $menu->header("");
            $menu->header("");
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home']);
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
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(
                action([\App\Http\Controllers\ManageUserController::class, 'index']),
                __('user.users'),
                ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users' || request()->segment(1) == 'manage_user']
            );
            $menu->header("");
            $menu->header("");
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home']);
            //User management dropdown
            if (auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')) {
                $menu->dropdown(
                    __('user.user_management'),
                    function ($sub) {
                        if (auth()->user()->can('user.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ManageUserController::class, 'index']),
                                __('user.users'),
                                ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users' || request()->segment(1) == 'manage_user']
                            );
                        }
                        // if (auth()->user()->can('roles.view')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\RoleController::class, 'index']),
                        //         __('user.roles'),
                        //         ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(1) == 'roles']
                        //     );
                        // }
                        // if (auth()->user()->can('user.create')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\SalesCommissionAgentController::class, 'index']),
                        //         __('lang_v1.sales_commission_agents'),
                        //         ['icon' => 'fa fas fa-handshake', 'active' => request()->segment(1) == 'sales-commission-agents']
                        //     );
                        // }
                    },
                    ['icon' => 'fas fa-user-tie ']
                );
            }
        });
    }
    public function essentialsMenu()
    {

        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home'])->order(1);

            $menu->url(
                route('essentials_landing'),
                __('essentials::lang.hrm'),
                ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'essentials' || request()->segment(1) == 'hrm' || request()->segment(1) == 'roles'],
            )->order(0);
            $menu->header("");
            $menu->header("");
            if (auth()->user()->can('essentials.view_employee_affairs') || true) {
                $menu->url(
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
                )->order(2);
            }

            if (auth()->user()->can('essentials.view_facilities_management') || true) {
                $menu->url(
                    action([\App\Http\Controllers\BusinessController::class, 'getBusiness']),
                    __('essentials::lang.facilities_management'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'getBusiness'],
                )->order(3);
            }

            if (auth()->user()->can('essentials.crud_all_attendance') || true) {
                $menu->url(

                    action([\Modules\Essentials\Http\Controllers\AttendanceController::class, 'index']),
                    __('essentials::lang.attendance'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'attendance'],
                )->order(4);
            }
            if (auth()->user()->can('essentials.crud_all_procedures') || true) {
                $menu->url(

                    action([\Modules\Essentials\Http\Controllers\EssentialsWkProcedureController::class, 'index']),
                    __('essentials::lang.procedures'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'procedures'],
                )->order(5);
            }
            //employee reports 
            if (auth()->user()->can('essentials.employees_reports_view') || true) {
                $menu->dropdown(
                    __('essentials::lang.reports'),
                    function ($sub) use ($enabled_modules) {
                        if (auth()->user()->can('')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsReportController::class, 'index']),
                                __('essentials::lang.employees_information_report'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'emp_info_report']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-plus-circle']

                )->order(5);
            }


            if (auth()->user()->can('essentials.crud_all_essentials_requests') || true) {

                $menu->dropdown(
                    __('followup::lang.requests'),
                    function ($sub) use ($enabled_modules) {
                        if (auth()->user()->can('followup::lang.add_request')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'create']),
                                __('followup::lang.create_request'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_createRequest']
                            );
                        }
                        if (auth()->user()->can('followup::lang.exitRequest')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'exitRequestIndex']),
                                __('followup::lang.exitRequest'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_exitRequest']
                            );
                        }
                        if (auth()->user()->can('followup::lang.returnRequest')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'returnRequestIndex']),
                                __('followup::lang.returnRequest'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_returnRequest']
                            );
                        }
                        if (auth()->user()->can('followup::lang.escapeRequest')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'escapeRequestIndex']),
                                __('followup::lang.escapeRequest'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_escapeRequest']
                            );
                        }
                        if (auth()->user()->can('followup::lang.advanceSalary')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'advanceSalaryIndex']),
                                __('followup::lang.advanceSalary'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_advanceSalary']
                            );
                        }
                        if (auth()->user()->can('followup::lang.leavesAndDepartures')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'leavesAndDeparturesIndex']),
                                __('followup::lang.leavesAndDepartures'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_leavesAndDepartures']
                            );
                        }
                        if (auth()->user()->can('followup::lang.atmCard')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'atmCardIndex']),
                                __('followup::lang.atmCard'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_atmCard']
                            );
                        }

                        if (auth()->user()->can('followup::lang.residenceRenewal')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'residenceRenewalIndex']),
                                __('followup::lang.residenceRenewal'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_residenceRenewal']
                            );
                        }
                        if (auth()->user()->can('followup::lang.residenceCard')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'residenceCardIndex']),
                                __('followup::lang.residenceCard'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_residenceCard']
                            );
                        }
                        if (auth()->user()->can('followup::lang.workerTransfer')) {
                            $sub->url(
                                action([\Modules\Essentials\Http\Controllers\EssentialsRequestController::class, 'workerTransferIndex']),
                                __('followup::lang.workerTransfer'),
                                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'ess_workerTransfer']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-plus-circle']

                )->order(6);
            }
            if (auth()->user()->can('essentials.view_work_cards') || true) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsCardsController::class, 'index']),
                    __('essentials::lang.work_cards'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'cards'],
                )->order(7);
            }

            if (auth()->user()->can('essentials.curd_contracts_end_reasons') || true) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsContractsFinishReasonsController::class, 'index']),
                    __('essentials::lang.contracts_end_reasons'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'contracts-finish-reasons'],
                )->order(7);
            }
            if (auth()->user()->can('essentials.curd_wishes') || true) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsWishesController::class, 'index']),
                    __('essentials::lang.wishes'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'wishes'],
                )->order(7);
            }

            if (auth()->user()->can('essentials.crud_all_leave') || true) {

                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']),
                    __('essentials::lang.leave_requests'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'leave'],
                )->order(8);
            }

            if (auth()->user()->can('essentials.view_all_payroll') || true) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index']),
                    __('essentials::lang.payroll'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'payroll'],
                )->order(9);
            }
            $menu->url(
                action([\App\Http\Controllers\TaxonomyController::class, 'index']),
                __('essentials::lang.loan'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'taxonomies'],
            )->order(10);

            if (auth()->user()->can('essentials.crud_insurance_contracts') || true) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsInsuranceContractController::class, 'index']),
                    __('essentials::lang.insurance_contracts'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'insurance_contracts'],
                )->order(11);
            }
            if (auth()->user()->can('essentials.crud_insurance_companies') || true) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsInsuranceCompanyController::class, 'index']),
                    __('essentials::lang.insurance_companies'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'insurance_companies'],
                )->order(12);
            }
            if (auth()->user()->can('essentials.crud_system_settings') || true) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsSettingsController::class, 'edit']),
                    __('essentials::lang.system_settings'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'settings'],
                )->order(13);
            }

            if (auth()->user()->can('essentials.view_employee_settings') || true) {
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
                )->order(14);
            }

            if (auth()->user()->can('essentials.crud_import_employee') || true) {
                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\EssentialsEmployeeImportController::class, 'index']),
                    __('essentials::lang.import_employees'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'import_employee'],
                )->order(15);
            }

            if (auth()->user()->can('essentials.curd_organizational_structure') || true) {
                $menu->url(

                    action([\Modules\Essentials\Http\Controllers\EssentialsDepartmentsController::class, 'index']),
                    __('essentials::lang.organizational_structure'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'hrm' && request()->segment(2) == 'settings'],
                )->order(16);
            }

            $menu->url(
                action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index']),
                __('essentials::lang.essentials'),
                ['icon' => 'fa fas fa-check-circle', 'active' => request()->segment(1) == 'essentials' && request()->segment(2) == 'todo', 'style' => config('app.env') == 'demo' ? 'background-color: #001f3f !important;' : '']
            )
                ->order(17);
        });
    }
    public function followUpMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(
                action([\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'index']),
                __('followup::lang.followUp'),
                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'notification-templates']
            );
            $menu->header("");
            $menu->header("");
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                [
                    'icon' => 'fas fa-home  ',
                    'active' => request()->segment(1) == 'home'
                ]
            );
            $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpProjectController::class, 'index']), __('followup::lang.projects'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'projects2']);
            $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpWorkerController::class, 'index']), __('followup::lang.workers'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'workers']);
            $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpOperationOrderController::class, 'index']), __('followup::lang.operation_orders'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'operation_orders']);

            $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'requests']), __('followup::lang.requests'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'allRequests']);
            $menu->url(action([\Modules\FollowUp\Http\Controllers\FollowUpRecruitmentRequestController::class, 'index']), __('followup::lang.recruitmentRequests'), ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'recruitmentRequests']);


            $menu->dropdown(
                __('followup::lang.reports.title'),
                function ($sub) use ($enabled_modules) {
                    if (auth()->user()->can('followup::lang.reports.projects')) {
                        $sub->url(
                            action([\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projects']),
                            __('followup::lang.reports.projects'),
                            ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'createRequest']
                        );
                    }

                    if (auth()->user()->can('followup::lang.reports.projectWorkers')) {
                        $sub->url(
                            action([\Modules\FollowUp\Http\Controllers\FollowUpReportsController::class, 'projectWorkers']),
                            __('followup::lang.reports.projectWorkers'),
                            ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'createRequest']
                        );
                    }
                },
                ['icon' => 'fa fas fa-meteor']
            );

            $menu->url(
                action([\Modules\FollowUp\Http\Controllers\FollowUpContractsWishesController::class, 'index']),
                __('followup::lang.contrascts_wishes'),
                ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(1) == 'contrascts_wishes']
            );

            // $menu->dropdown(
            //     __('followup::lang.requests'),
            //     function ($sub) use ($enabled_modules) {
            //         if (auth()->user()->can('followup::lang.create_order')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'create']),
            //                 __('followup::lang.create_request'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'createRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewExitRequests')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'exitRequestIndex']),
            //                 __('followup::lang.exitRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'exitRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewReturnRequest')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'returnRequestIndex']),
            //                 __('followup::lang.returnRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'returnRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewEscapeRequest')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'escapeRequestIndex']),
            //                 __('followup::lang.escapeRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'escapeRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewAdvanceSalary')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'advanceSalaryIndex']),
            //                 __('followup::lang.advanceSalary'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'advanceSalary']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewLeavesAndDepartures')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'leavesAndDeparturesIndex']),
            //                 __('followup::lang.leavesAndDepartures'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'leavesAndDepartures']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewAtmCard')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'atmCardIndex']),
            //                 __('followup::lang.atmCard'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'atmCard']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewResidenceRenewal')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceRenewalIndex']),
            //                 __('followup::lang.residenceRenewal'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'residenceRenewal']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewResidenceCard')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceCardIndex']),
            //                 __('followup::lang.residenceCard'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'residenceCard']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewWorkerTransfer')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workerTransferIndex']),
            //                 __('followup::lang.workerTransfer'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'workerTransfer']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewChamberRequest')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'chamberRequestIndex']),
            //                 __('followup::lang.chamberRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'chamberRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewMofaRequest')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'mofaRequestIndex']),
            //                 __('followup::lang.mofaRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'mofaRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewInsuranceUpgradeRequest')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'insuranceUpgradeRequestIndex']),
            //                 __('followup::lang.insuranceUpgradeRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'insuranceUpgradeRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewBaladyCardRequest')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'baladyCardRequestIndex']),
            //                 __('followup::lang.baladyCardRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'baladyCardRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewResidenceEditRequest')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'residenceEditRequestIndex']),
            //                 __('followup::lang.residenceEditRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'residenceEditRequest']
            //             );
            //         }
            //         if (auth()->user()->can('followup::lang.viewWorkInjuriesRequest')) {
            //             $sub->url(
            //                 action([\Modules\FollowUp\Http\Controllers\FollowUpRequestController::class, 'workInjuriesRequestIndex']),
            //                 __('followup::lang.workInjuriesRequest'),
            //                 ['icon' => 'fa fas fa-meteor', 'active' => request()->segment(2) == 'workInjuriesRequest']
            //             );
            //         }


            //         },
            //         ['icon' => 'fa fas fa-meteor']

            //     );
        });
    }
    public function CUS_salesMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;

            $menu->url(
                route('sales_landing'),
                __('sales::lang.sales'),
                ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'sale'],
            );
            $menu->header("");
            $menu->header("");
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home']);
            $menu->url(
                action([\Modules\Sales\Http\Controllers\ClientsController::class, 'index']),
                __('sales::lang.customers'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'clients'],
            );
            $menu->url(
               route('sale.contactLocations'),
                __('sales::lang.contact_locations'),
                ['icon' => 'fa fas fa-plus-circle'],
            );

            $menu->url(
                action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'create']),
                __('sales::lang.add_offer_price'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'createOfferPrice'],
            );

            $menu->url(
                action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'index']),
                __('sales::lang.offer_price'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'offer-price'],

            );


            $menu->url(
                action([\Modules\Sales\Http\Controllers\ContractsController::class, 'index']),
                __('sales::lang.contracts'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'cotracts'],
            );



            $menu->url(
                action([\Modules\Sales\Http\Controllers\ContractItemController::class, 'index']),
                __('sales::lang.contract_itmes'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'contract_itmes'],
            );

            $menu->url(
                action([\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'index']),
                __('sales::lang.contract_appendics'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'contract_appendices'],
            );
            $menu->url(
                action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'index']),
                __('sales::lang.sale_operation_orders'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'sale_operation_order'],
            );

            $menu->url(
                action([\Modules\Sales\Http\Controllers\SaleSourcesController::class, 'index']),
                __('sales::lang.sale_sources'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'salesources'],
            );

        });
    }
    public function houseMovementsMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(
                action([\Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index']),
                __('housingmovements::lang.housing_move'),

                [
                    'icon' => 'fa fas fa-users',
                    'active' => request()->segment(1) == 'housingmovements',

                ]
            )->order(0);
            $menu->header("");
            $menu->header("");
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home'])->order(0);

            $menu->url(
                action([\Modules\HousingMovements\Http\Controllers\RequestController::class, 'index']),
                __('housingmovements::lang.requests'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'requests'],
            );

            $menu->url(
                action([\Modules\HousingMovements\Http\Controllers\MovementController::class, 'index']),
                __('housingmovements::lang.movement_management'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'movement'],

            )->order(2);
            $menu->dropdown(
                __('housingmovements::lang.building_management'),
                function ($buildingSubMenu) {
                    $buildingSubMenu->url(
                        action([\Modules\HousingMovements\Http\Controllers\BuildingController::class, 'index']),
                        __('housingmovements::lang.buildings'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'buildings']
                    )->order(4);

                    $buildingSubMenu->url(
                        action([\Modules\HousingMovements\Http\Controllers\RoomController::class, 'index']),
                        __('housingmovements::lang.rooms'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'rooms']
                    )->order(5);

                    $buildingSubMenu->url(
                        action([\Modules\HousingMovements\Http\Controllers\FacitityController::class, 'index']),
                        __('housingmovements::lang.facilities'),
                        ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'housingmovements' && request()->segment(2) == 'facilities']
                    )->order(6);
                },
                ['icon' => 'fa fas fa-plus-circle',],
            );
        });
    }

    public function accountingMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;

            // $menu->header("");

            // $menu->header("");
            $menu->url(
                action('\Modules\Accounting\Http\Controllers\AccountingController@dashboard'),
                __('accounting::lang.accounting'),
                [
                    'icon' => 'fas fa-money-check fa',
                    'style' => config('app.env') == 'demo' ? 'background-color: #D483D9;' : '',
                    'active' => request()->segment(1) == 'accounting'
                ]
            );
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                [
                    'icon' => 'fas fa-home  ',
                    'active' => request()->segment(1) == 'home'
                ]
            );
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\CoaController::class, 'index']),
                __('accounting::lang.chart_of_accounts'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'chart-of-accounts']
            )->order(0);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\CostCenterController::class, 'index']),
                __('accounting::lang.cost_center'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'cost_centers']
            )->order(1);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\OpeningBalanceController::class, 'index']),
                __('accounting::lang.opening_balances'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'opening_balances']
            )->order(2);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\ReceiptVouchersController::class, 'index']),
                __('accounting::lang.receipt_vouchers'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'receipt_vouchers']
            )->order(3);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\PaymentVouchersController::class, 'index']),
                __('accounting::lang.payment_vouchers'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'payment_vouchers']
            )->order(4);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\JournalEntryController::class, 'index']),
                __('accounting::lang.journal_entry'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'journal-entry']
            )->order(5);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\AutomatedMigrationController::class, 'index']),
                __('accounting::lang.automatedMigration'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'AutomatedMigration']
            )->order(6);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\TransferController::class, 'index']),
                __('accounting::lang.transfer'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'transfer']
            )->order(7);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\TransactionController::class, 'index']),
                __('accounting::lang.transactions'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'transactions']
            )->order(8);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\BudgetController::class, 'index']),
                __('accounting::lang.budget'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'budget']
            )->order(9);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\ReportController::class, 'index']),
                __('accounting::lang.reports'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'reports']
            )->order(10);
            $menu->url(
                action([\Modules\Accounting\Http\Controllers\SettingsController::class, 'index']),
                __('messages.settings'),
                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(2) == 'settings']
            )->order(11);
        });
    }

    public function getIRMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home'])->order(1);
            $menu->url(
                action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index']),
                __('internationalrelations::lang.International'),
                [
                    'icon' => 'fa fas fa-dharmachakra',
                    'active' => request()->segment(1) == 'internationalRleations',
                    'style' => config('app.env') == 'demo' ? 'background-color: #605ca8 !important;' : '',
                ],
            )->order(0);
            $menu->header("");
            $menu->header("");
            // if (auth()->user()->can('internationalrelations.view_dashboard') || true) {
            //     $menu->url(
            //         action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index']),
            //         __('internationalrelations::lang.dashboard'),
            //         ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'dashboard'],
            //     )->order(1);
            // }

            if (auth()->user()->can('internationalrelations.order_request_view') || true) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\OrderRequestController::class, 'index']),
                    __('internationalrelations::lang.order_request'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'OrderRequest'],
                )->order(1);
            }
            if (auth()->user()->can('internationalrelations.proposed_labor') || true) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'proposed_laborIndex']),
                    __('internationalrelations::lang.proposed_labor'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'proposed_laborIndex'],
                )->order(2);
            }
            if (auth()->user()->can('internationalrelations.view_Airlines') || true) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\AirlinesController::class, 'index']),
                    __('internationalrelations::lang.Airlines'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'Airlines'],
                )->order(3);
            }
            if (auth()->user()->can('internationalrelations.view_EmploymentCompanies') || true) {
                $menu->url(
                    action([\Modules\InternationalRelations\Http\Controllers\EmploymentCompaniesController::class, 'index']),
                    __('internationalrelations::lang.EmploymentCompanies'),
                    ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'ir' && request()->segment(2) == 'EmploymentCompanies'],
                )->order(4);
            }
        });
    }

    public function settingsMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(
                action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings']),
                __('business.settings'),
                [
                    'icon' => 'fa fas fa-cog',
                    // 'active' => request()->segment(1) == 'home'
                ]
            );
            $menu->header("");
            $menu->header("");
            $menu->url(
                action([\App\Http\Controllers\HomeController::class, 'index']),
                __('home.home'),
                [
                    'icon' => 'fas fa-home  ',
                    'active' => request()->segment(1) == 'home'
                ]
            );
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
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(route('purchases.index'),   __('purchase.purchases'), ['icon' => 'fas fa-cart-plus']);
            $menu->header("");
            $menu->header("");

            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home']);
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
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(action([\App\Http\Controllers\ProductController::class, 'index']), __('sale.products'), ['icon' => 'fas fa-chart-pie']);
            $menu->header("");
            $menu->header("");
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home']);
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

    public function superAdminMenu()
    {
        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];
            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];
            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $menu->url(action([\Modules\Superadmin\Http\Controllers\SuperadminController::class, 'index']), __('superadmin::lang.superadmin'), ['icon' => 'fa fas fa-users-cog', 'active' => request()->segment(1) == 'superadmin']);

            $menu->header("");
            $menu->header("");
            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ', 'active' => request()->segment(1) == 'home']);

            if (auth()->user()->can('superadmin.access_package_subscriptions') && auth()->user()->can('business_settings.access')) {


                $menu->url(
                    action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'index']),
                    __('superadmin::lang.subscription'),
                    ['icon' => 'fa fas fa-sync', 'active' => request()->segment(1) == 'subscription']
                );
            }
            //Modules menu
            if (auth()->user()->can('manage_modules')) {
                $menu->url(action([\App\Http\Controllers\Install\ModulesController::class, 'index']), __('lang_v1.modules'), ['icon' => 'fa fas fa-plug', 'active' => request()->segment(1) == 'manage-modules']);
            }
            //Backup menu
            if (auth()->user()->can('backup')) {
                $menu->url(action([\App\Http\Controllers\BackUpController::class, 'index']), __('lang_v1.backup'), ['icon' => 'fa fas fa-hdd', 'active' => request()->segment(1) == 'backup']);
            }
        });
    }

    public function oldHandle($request, Closure $next)
    {
        if (auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')) {
            $menu->dropdown(
                __('user.user_management'),
                function ($sub) {
                    if (auth()->user()->can('user.view')) {
                        $sub->url(
                            action([\App\Http\Controllers\ManageUserController::class, 'index']),
                            __('user.users'),
                            ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users' || request()->segment(1) == 'manage_user']
                        );
                    }
                    // if (auth()->user()->can('roles.view')) {
                    //     $sub->url(
                    //         action([\App\Http\Controllers\RoleController::class, 'index']),
                    //         __('user.roles'),
                    //         ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(1) == 'roles']
                    //     );
                    // }
                    // if (auth()->user()->can('user.create')) {
                    //     $sub->url(
                    //         action([\App\Http\Controllers\SalesCommissionAgentController::class, 'index']),
                    //         __('lang_v1.sales_commission_agents'),
                    //         ['icon' => 'fa fas fa-handshake', 'active' => request()->segment(1) == 'sales-commission-agents']
                    //     );
                    // }
                },
                ['icon' => 'fas fa-user-tie ']
            )->order(10);
        }


        //Contacts dropdown
        if (auth()->user()->can('supplier.view') || auth()->user()->can('customer.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('customer.view_own')) {
            $menu->dropdown(
                __('contact.contacts'),
                function ($sub) {
                    if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own')) {
                        $sub->url(
                            action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'supplier']),
                            __('report.supplier'),
                            ['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'supplier']
                        );
                    }
                    if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own')) {
                        $sub->url(
                            action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']),
                            __('report.customer'),
                            ['icon' => 'fa fas fa-star', 'active' => request()->input('type') == 'customer']
                        );
                        $sub->url(
                            action([\App\Http\Controllers\CustomerGroupController::class, 'index']),
                            __('lang_v1.customer_groups'),
                            ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'customer-group']
                        );
                    }
                    if (auth()->user()->can('supplier.create') || auth()->user()->can('customer.create')) {
                        $sub->url(
                            action([\App\Http\Controllers\ContactController::class, 'getImportContacts']),
                            __('lang_v1.import_contacts'),
                            ['icon' => 'fa fas fa-download', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'import']
                        );
                    }

                    if (!empty(env('GOOGLE_MAP_API_KEY'))) {
                        $sub->url(
                            action([\App\Http\Controllers\ContactController::class, 'contactMap']),
                            __('lang_v1.map'),
                            ['icon' => 'fa fas fa-map-marker-alt', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'map']
                        );
                    }
                },
                ['icon' => 'fas fa-id-card ', 'id' => 'tour_step4']
            )->order(15);
        }

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
            )->order(20);
        }

        // //Purchase dropdown
        // if (in_array('purchases', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') || auth()->user()->can('purchase.update'))) {
        //     $menu->dropdown(
        //         __('purchase.purchases'),
        //         function ($sub) use ($common_settings) {
        //             if (!empty($common_settings['enable_purchase_requisition']) && (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own'))) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']),
        //                     __('lang_v1.purchase_requisition'),
        //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchase-requisition']
        //                 );
        //             }

        //             if (!empty($common_settings['enable_purchase_order']) && (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own'))) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\PurchaseOrderController::class, 'index']),
        //                     __('lang_v1.purchase_order'),
        //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchase-order']
        //                 );
        //             }
        //             if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\PurchaseController::class, 'index']),
        //                     __('purchase.list_purchase'),
        //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == null]
        //                 );
        //             }
        //             if (auth()->user()->can('purchase.create')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\PurchaseController::class, 'create']),
        //                     __('purchase.add_purchase'),
        //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'purchases' && request()->segment(2) == 'create']
        //                 );
        //             }
        //             if (auth()->user()->can('purchase.update')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\PurchaseReturnController::class, 'index']),
        //                     __('lang_v1.list_purchase_return'),
        //                     ['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'purchase-return']
        //                 );
        //             }
        //         },
        //         ['icon' => 'fas fa-cart-plus ', 'id' => 'tour_step6']
        //     )->order(25);
        // }
        //Sell dropdown
        if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping', 'access_sell_return', 'direct_sell.view', 'direct_sell.update', 'access_own_sell_return'])) {
            $menu->dropdown(
                __('sale.sale'),
                function ($sub) use ($enabled_modules, $is_admin, $pos_settings) {
                    if (!empty($pos_settings['enable_sales_order']) && ($is_admin || auth()->user()->hasAnyPermission(['so.view_own', 'so.view_all', 'so.create']))) {
                        $sub->url(
                            action([\App\Http\Controllers\SalesOrderController::class, 'index']),
                            __('lang_v1.sales_order'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sales-order']
                        );
                    }

                    if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'direct_sell.view', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
                        $sub->url(
                            action([\App\Http\Controllers\SellController::class, 'index']),
                            __('lang_v1.all_sales'),
                            ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == null]
                        );
                    }
                    if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
                        $sub->url(
                            action([\App\Http\Controllers\SellController::class, 'create']),
                            __('sale.add_sale'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == 'create' && empty(request()->get('status'))]
                        );
                    }
                    if (auth()->user()->can('sell.create')) {
                        if (in_array('pos_sale', $enabled_modules)) {
                            if (auth()->user()->can('sell.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\SellPosController::class, 'index']),
                                    __('sale.list_pos'),
                                    ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'pos' && request()->segment(2) == null]
                                );
                            }

                            $sub->url(
                                action([\App\Http\Controllers\SellPosController::class, 'create']),
                                __('sale.pos_sale'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'pos' && request()->segment(2) == 'create']
                            );
                        }
                    }

                    if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
                        $sub->url(
                            action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'draft']),
                            __('lang_v1.add_draft'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->get('status') == 'draft']
                        );
                    }
                    if (in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['draft.view_all', 'draft.view_own']))) {
                        $sub->url(
                            action([\App\Http\Controllers\SellController::class, 'getDrafts']),
                            __('lang_v1.list_drafts'),
                            ['icon' => 'fa fas fa-pen-square', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == 'drafts']
                        );
                    }
                    if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
                        $sub->url(
                            action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'quotation']),
                            __('lang_v1.add_quotation'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->get('status') == 'quotation']
                        );
                    }
                    if (in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['quotation.view_all', 'quotation.view_own']))) {
                        $sub->url(
                            action([\App\Http\Controllers\SellController::class, 'getQuotations']),
                            __('lang_v1.list_quotations'),
                            ['icon' => 'fa fas fa-pen-square', 'active' => request()->segment(1) == 'sells' && request()->segment(2) == 'quotations']
                        );
                    }

                    if (auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return')) {
                        $sub->url(
                            action([\App\Http\Controllers\SellReturnController::class, 'index']),
                            __('lang_v1.list_sell_return'),
                            ['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'sell-return' && request()->segment(2) == null]
                        );
                    }

                    if ($is_admin || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
                        $sub->url(
                            action([\App\Http\Controllers\SellController::class, 'shipments']),
                            __('lang_v1.shipments'),
                            ['icon' => 'fa fas fa-truck', 'active' => request()->segment(1) == 'shipments']
                        );
                    }

                    if (auth()->user()->can('discount.access')) {
                        $sub->url(
                            action([\App\Http\Controllers\DiscountController::class, 'index']),
                            __('lang_v1.discounts'),
                            ['icon' => 'fa fas fa-percent', 'active' => request()->segment(1) == 'discount']
                        );
                    }
                    if (in_array('subscription', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
                        $sub->url(
                            action([\App\Http\Controllers\SellPosController::class, 'listSubscriptions']),
                            __('lang_v1.subscriptions'),
                            ['icon' => 'fa fas fa-recycle', 'active' => request()->segment(1) == 'subscriptions']
                        );
                    }

                    if (auth()->user()->can('sell.create')) {
                        $sub->url(
                            action([\App\Http\Controllers\ImportSalesController::class, 'index']),
                            __('lang_v1.import_sales'),
                            ['icon' => 'fa fas fa-file-import', 'active' => request()->segment(1) == 'import-sales']
                        );
                    }
                },
                ['icon' => 'fas fa-universal-access ', 'id' => 'tour_step7']
            )->order(30);
        }
        //Stock transfer dropdown
        if (in_array('stock_transfers', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
            $menu->dropdown(
                __('lang_v1.stock_transfers'),
                function ($sub) {
                    if (auth()->user()->can('purchase.view')) {
                        $sub->url(
                            action([\App\Http\Controllers\StockTransferController::class, 'index']),
                            __('lang_v1.list_stock_transfers'),
                            ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'stock-transfers' && request()->segment(2) == null]
                        );
                    }
                    if (auth()->user()->can('purchase.create')) {
                        $sub->url(
                            action([\App\Http\Controllers\StockTransferController::class, 'create']),
                            __('lang_v1.add_stock_transfer'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'stock-transfers' && request()->segment(2) == 'create']
                        );
                    }
                },
                ['icon' => 'fa fas fa-truck']
            )->order(35);
        }

        //stock adjustment dropdown
        if (in_array('stock_adjustment', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create'))) {
            $menu->dropdown(
                __('stock_adjustment.stock_adjustment'),
                function ($sub) {
                    if (auth()->user()->can('purchase.view')) {
                        $sub->url(
                            action([\App\Http\Controllers\StockAdjustmentController::class, 'index']),
                            __('stock_adjustment.list'),
                            ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'stock-adjustments' && request()->segment(2) == null]
                        );
                    }
                    if (auth()->user()->can('purchase.create')) {
                        $sub->url(
                            action([\App\Http\Controllers\StockAdjustmentController::class, 'create']),
                            __('stock_adjustment.add'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'stock-adjustments' && request()->segment(2) == 'create']
                        );
                    }
                },
                ['icon' => 'fa fas fa-database']
            )->order(40);
        }


        //customer_sales

        // // Expense dropdown
        // if (in_array('expenses', $enabled_modules) && (auth()->user()->can('all_expense.access') || auth()->user()->can('view_own_expense'))) {
        //     $menu->dropdown(
        //         __('expense.expenses'),
        //         function ($sub) {
        //             $sub->url(
        //                 action([\App\Http\Controllers\ExpenseController::class, 'index']),
        //                 __('lang_v1.list_expenses'),
        //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == null]
        //             );

        //             if (auth()->user()->can('expense.add')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ExpenseController::class, 'create']),
        //                     __('expense.add_expense'),
        //                     ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'expenses' && request()->segment(2) == 'create']
        //                 );
        //             }

        //             if (auth()->user()->can('expense.add') || auth()->user()->can('expense.edit')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ExpenseCategoryController::class, 'index']),
        //                     __('expense.expense_categories'),
        //                     ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'expense-categories']
        //                 );
        //             }
        //         },
        //         ['icon' => 'fas fa-shopping-basket ']
        //     )->order(45);
        // }
        //Accounts dropdown
        if (auth()->user()->can('account.access') && in_array('account', $enabled_modules)) {
            $menu->dropdown(
                __('lang_v1.payment_accounts'),
                function ($sub) {
                    $sub->url(
                        action([\App\Http\Controllers\AccountController::class, 'index']),
                        __('account.list_accounts'),
                        ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'account']
                    );
                    $sub->url(
                        action([\App\Http\Controllers\AccountReportsController::class, 'balanceSheet']),
                        __('account.balance_sheet'),
                        ['icon' => 'fa fas fa-book', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'balance-sheet']
                    );
                    $sub->url(
                        action([\App\Http\Controllers\AccountReportsController::class, 'trialBalance']),
                        __('account.trial_balance'),
                        ['icon' => 'fa fas fa-balance-scale', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'trial-balance']
                    );
                    $sub->url(
                        action([\App\Http\Controllers\AccountController::class, 'cashFlow']),
                        __('lang_v1.cash_flow'),
                        ['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'cash-flow']
                    );
                    $sub->url(
                        action([\App\Http\Controllers\AccountReportsController::class, 'paymentAccountReport']),
                        __('account.payment_account_report'),
                        ['icon' => 'fa fas fa-file-alt', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'payment-account-report']
                    );
                },
                ['icon' => 'fa fas fa-money-check-alt']
            )->order(50);
        }

        // //Reports dropdown
        // if (
        //     auth()->user()->can('purchase_n_sell_report.view') || auth()->user()->can('contacts_report.view')
        //     || auth()->user()->can('stock_report.view') || auth()->user()->can('tax_report.view')
        //     || auth()->user()->can('trending_product_report.view') || auth()->user()->can('sales_representative.view') || auth()->user()->can('register_report.view')
        //     || auth()->user()->can('expense_report.view')
        // ) {
        //     $menu->dropdown(
        //         __('report.reports'),
        //         function ($sub) use ($enabled_modules, $is_admin) {
        //             if (auth()->user()->can('profit_loss_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getProfitLoss']),
        //                     __('report.profit_loss'),
        //                     ['icon' => 'fa fas fa-file-invoice-dollar', 'active' => request()->segment(2) == 'profit-loss']
        //                 );
        //             }
        //             if (config('constants.show_report_606') == true) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'purchaseReport']),
        //                     'Report 606 (' . __('lang_v1.purchase') . ')',
        //                     ['icon' => 'fa fas fa-arrow-circle-down', 'active' => request()->segment(2) == 'purchase-report']
        //                 );
        //             }
        //             if (config('constants.show_report_607') == true) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'saleReport']),
        //                     'Report 607 (' . __('business.sale') . ')',
        //                     ['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'sale-report']
        //                 );
        //             }
        //             if ((in_array('purchases', $enabled_modules) || in_array('add_sale', $enabled_modules) || in_array('pos_sale', $enabled_modules)) && auth()->user()->can('purchase_n_sell_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getPurchaseSell']),
        //                     __('report.purchase_sell_report'),
        //                     ['icon' => 'fa fas fa-exchange-alt', 'active' => request()->segment(2) == 'purchase-sell']
        //                 );
        //             }

        //             if (auth()->user()->can('tax_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getTaxReport']),
        //                     __('report.tax_report'),
        //                     ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'tax-report']
        //                 );
        //             }
        //             if (auth()->user()->can('contacts_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getCustomerSuppliers']),
        //                     __('report.contacts'),
        //                     ['icon' => 'fa fas fa-address-book', 'active' => request()->segment(2) == 'customer-supplier']
        //                 );
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getCustomerGroup']),
        //                     __('lang_v1.customer_groups_report'),
        //                     ['icon' => 'fa fas fa-users', 'active' => request()->segment(2) == 'customer-group']
        //                 );
        //             }
        //             if (auth()->user()->can('stock_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
        //                     __('report.stock_report'),
        //                     ['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'stock-report']
        //                 );
        //                 if (session('business.enable_product_expiry') == 1) {
        //                     $sub->url(
        //                         action([\App\Http\Controllers\ReportController::class, 'getStockExpiryReport']),
        //                         __('report.stock_expiry_report'),
        //                         ['icon' => 'fa fas fa-calendar-times', 'active' => request()->segment(2) == 'stock-expiry']
        //                     );
        //                 }
        //                 if (session('business.enable_lot_number') == 1) {
        //                     $sub->url(
        //                         action([\App\Http\Controllers\ReportController::class, 'getLotReport']),
        //                         __('lang_v1.lot_report'),
        //                         ['icon' => 'fa fas fa-hourglass-half', 'active' => request()->segment(2) == 'lot-report']
        //                     );
        //                 }

        //                 if (in_array('stock_adjustment', $enabled_modules)) {
        //                     $sub->url(
        //                         action([\App\Http\Controllers\ReportController::class, 'getStockAdjustmentReport']),
        //                         __('report.stock_adjustment_report'),
        //                         ['icon' => 'fa fas fa-sliders-h', 'active' => request()->segment(2) == 'stock-adjustment-report']
        //                     );
        //                 }
        //             }

        //             if (auth()->user()->can('trending_product_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getTrendingProducts']),
        //                     __('report.trending_products'),
        //                     ['icon' => 'fa fas fa-chart-line', 'active' => request()->segment(2) == 'trending-products']
        //                 );
        //             }

        //             if (auth()->user()->can('purchase_n_sell_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'itemsReport']),
        //                     __('lang_v1.items_report'),
        //                     ['icon' => 'fa fas fa-tasks', 'active' => request()->segment(2) == 'items-report']
        //                 );

        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getproductPurchaseReport']),
        //                     __('lang_v1.product_purchase_report'),
        //                     ['icon' => 'fa fas fa-arrow-circle-down', 'active' => request()->segment(2) == 'product-purchase-report']
        //                 );

        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getproductSellReport']),
        //                     __('lang_v1.product_sell_report'),
        //                     ['icon' => 'fa fas fa-arrow-circle-up', 'active' => request()->segment(2) == 'product-sell-report']
        //                 );

        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'purchasePaymentReport']),
        //                     __('lang_v1.purchase_payment_report'),
        //                     ['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'purchase-payment-report']
        //                 );

        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'sellPaymentReport']),
        //                     __('lang_v1.sell_payment_report'),
        //                     ['icon' => 'fa fas fa-search-dollar', 'active' => request()->segment(2) == 'sell-payment-report']
        //                 );
        //             }
        //             if (in_array('expenses', $enabled_modules) && auth()->user()->can('expense_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getExpenseReport']),
        //                     __('report.expense_report'),
        //                     ['icon' => 'fa fas fa-search-minus', 'active' => request()->segment(2) == 'expense-report']
        //                 );
        //             }
        //             if (auth()->user()->can('register_report.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getRegisterReport']),
        //                     __('report.register_report'),
        //                     ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(2) == 'register-report']
        //                 );
        //             }
        //             if (auth()->user()->can('sales_representative.view')) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getSalesRepresentativeReport']),
        //                     __('report.sales_representative'),
        //                     ['icon' => 'fa fas fa-user', 'active' => request()->segment(2) == 'sales-representative-report']
        //                 );
        //             }
        //             if (auth()->user()->can('purchase_n_sell_report.view') && in_array('tables', $enabled_modules)) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getTableReport']),
        //                     __('restaurant.table_report'),
        //                     ['icon' => 'fa fas fa-table', 'active' => request()->segment(2) == 'table-report']
        //                 );
        //             }

        //             if (auth()->user()->can('tax_report.view') && !empty(config('constants.enable_gst_report_india'))) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'gstSalesReport']),
        //                     __('lang_v1.gst_sales_report'),
        //                     ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'gst-sales-report']
        //                 );

        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'gstPurchaseReport']),
        //                     __('lang_v1.gst_purchase_report'),
        //                     ['icon' => 'fa fas fa-percent', 'active' => request()->segment(2) == 'gst-purchase-report']
        //                 );
        //             }

        //             if (auth()->user()->can('sales_representative.view') && in_array('service_staff', $enabled_modules)) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'getServiceStaffReport']),
        //                     __('restaurant.service_staff_report'),
        //                     ['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'service-staff-report']
        //                 );
        //             }

        //             if ($is_admin) {
        //                 $sub->url(
        //                     action([\App\Http\Controllers\ReportController::class, 'activityLog']),
        //                     __('lang_v1.activity_log'),
        //                     ['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'activity-log']
        //                 );
        //             }
        //         },
        //         ['icon' => 'fa fas fa-chart-bar', 'id' => 'tour_step8']
        //     )->order(55);
        // }

        // //Backup menu
        // if (auth()->user()->can('backup')) {
        //     $menu->url(action([\App\Http\Controllers\BackUpController::class, 'index']), __('lang_v1.backup'), ['icon' => 'fa fas fa-hdd', 'active' => request()->segment(1) == 'backup'])->order(60);
        // }

        // //Modules menu
        // if (auth()->user()->can('manage_modules')) {
        //     $menu->url(action([\App\Http\Controllers\Install\ModulesController::class, 'index']), __('lang_v1.modules'), ['icon' => 'fa fas fa-plug', 'active' => request()->segment(1) == 'manage-modules'])->order(60);
        // }

        // //Booking menu
        // if (in_array('booking', $enabled_modules) && (auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings'))) {
        //     $menu->url(action([\App\Http\Controllers\Restaurant\BookingController::class, 'index']), __('restaurant.bookings'), ['icon' => 'fas fa fa-calendar-check', 'active' => request()->segment(1) == 'bookings'])->order(65);
        // }

        // //Kitchen menu
        // if (in_array('kitchen', $enabled_modules)) {
        //     $menu->url(action([\App\Http\Controllers\Restaurant\KitchenController::class, 'index']), __('restaurant.kitchen'), ['icon' => 'fa fas fa-fire', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'kitchen'])->order(70);
        // }

        // //Service Staff menu
        // if (in_array('service_staff', $enabled_modules)) {
        //     $menu->url(action([\App\Http\Controllers\Restaurant\OrderController::class, 'index']), __('restaurant.orders'), ['icon' => 'fa fas fa-list-alt', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'orders'])->order(75);
        // }

        // //Notification template menu
        // if (auth()->user()->can('send_notifications')) {
        //     $menu->url(action([\App\Http\Controllers\NotificationTemplateController::class, 'index']), __('lang_v1.notification_templates'), ['icon' => 'fa fas fa-envelope', 'active' => request()->segment(1) == 'notification-templates'])->order(80);
        // }

        //Settings Dropdown
        if (
            auth()->user()->can('business_settings.access') ||
            auth()->user()->can('barcode_settings.access') ||
            auth()->user()->can('invoice_settings.access') ||
            auth()->user()->can('tax_rate.view') ||
            auth()->user()->can('tax_rate.create') ||
            auth()->user()->can('access_package_subscriptions')
        ) {
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
            )->order(85);
        }
    }
}
