<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Charts\CommonChart;
use App\Currency;
use App\Media;
use App\SentNotification;
use App\SentNotificationsUser;
use App\Transaction;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use App\VariationLocationDetails;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Modules\Essentials\Http\Controllers\MovmentDashboardController;
use Modules\Sales\Entities\SalesProject;

class HomeController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $transactionUtil;

    protected $moduleUtil;

    protected $commonUtil;

    protected $restUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        RestaurantUtil $restUtil
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
    }



    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user = User::where('id', auth()->user()->id)->first();
        $isSuperAdmin =  $user->user_type == 'superadmin';
        if (Str::contains($user->user_type, 'user_customer')) {
            return redirect()->action([\Modules\Crm\Http\Controllers\DashboardController::class, 'index']);
        }

        $business_id = request()->session()->get('user.business_id');

        $is_admin = $this->businessUtil->is_admin(auth()->user());
        $is_customer = $user->user_type == 'customer';
        $roles = auth()->user()->roles;
        $roleHasPermission = false;
        foreach ($roles as $role) {

            if ($is_admin || $role->hasPermissionTo('dashboard.data')) {
                $roleHasPermission = true;
                break;
            }
        }


        if (!($isSuperAdmin  ||  $is_customer ||  auth()->user()->can('dashboard.data') || $roleHasPermission)) {
            return view('home.index');
        }


        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();

        //ensure start date starts from at least 30 days before to get sells last 30 days
        $least_30_days = \Carbon::parse($fy['start'])->subDays(30)->format('Y-m-d');

        //get all sells
        $sells_this_fy = $this->transactionUtil->getSellsCurrentFy($business_id, $least_30_days, $fy['end']);

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();

        //Chart for sells last 30 days
        $labels = [];
        $all_sell_values = [];
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $labels[] = date('j M Y', strtotime($date));

            $total_sell_on_date = $sells_this_fy->where('date', $date)->sum('total_sells');

            if (!empty($total_sell_on_date)) {
                $all_sell_values[] = (float) $total_sell_on_date;
            } else {
                $all_sell_values[] = 0;
            }
        }

        //Group sells by location
        $location_sells = [];
        foreach ($all_locations as $loc_id => $loc_name) {
            $values = [];
            foreach ($dates as $date) {
                $total_sell_on_date_location = $sells_this_fy->where('date', $date)->where('location_id', $loc_id)->sum('total_sells');

                if (!empty($total_sell_on_date_location)) {
                    $values[] = (float) $total_sell_on_date_location;
                } else {
                    $values[] = 0;
                }
            }
            $location_sells[$loc_id]['loc_label'] = $loc_name;
            $location_sells[$loc_id]['values'] = $values;
        }

        $sells_chart_1 = new CommonChart;

        $sells_chart_1->labels($labels)
            ->options($this->__chartOptions(__(
                'home.total_sells',
                ['currency' => $currency->code]
            )));

        if (!empty($location_sells)) {
            foreach ($location_sells as $location_sell) {
                $sells_chart_1->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }

        if (count($all_locations) > 1) {
            $sells_chart_1->dataset(__('report.all_locations'), 'line', $all_sell_values);
        }

        $labels = [];
        $values = [];
        $date = strtotime($fy['start']);
        $last = date('m-Y', strtotime($fy['end']));
        $fy_months = [];
        do {
            $month_year = date('m-Y', $date);
            $fy_months[] = $month_year;

            $labels[] = \Carbon::createFromFormat('m-Y', $month_year)
                ->format('M-Y');
            $date = strtotime('+1 month', $date);

            $total_sell_in_month_year = $sells_this_fy->where('yearmonth', $month_year)->sum('total_sells');

            if (!empty($total_sell_in_month_year)) {
                $values[] = (float) $total_sell_in_month_year;
            } else {
                $values[] = 0;
            }
        } while ($month_year != $last);

        $fy_sells_by_location_data = [];

        foreach ($all_locations as $loc_id => $loc_name) {
            $values_data = [];
            foreach ($fy_months as $month) {
                $total_sell_in_month_year_location = $sells_this_fy->where('yearmonth', $month)->where('location_id', $loc_id)->sum('total_sells');

                if (!empty($total_sell_in_month_year_location)) {
                    $values_data[] = (float) $total_sell_in_month_year_location;
                } else {
                    $values_data[] = 0;
                }
            }
            $fy_sells_by_location_data[$loc_id]['loc_label'] = $loc_name;
            $fy_sells_by_location_data[$loc_id]['values'] = $values_data;
        }

        $sells_chart_2 = new CommonChart;
        $sells_chart_2->labels($labels)
            ->options($this->__chartOptions(__(
                'home.total_sells',
                ['currency' => $currency->code]
            )));
        if (!empty($fy_sells_by_location_data)) {
            foreach ($fy_sells_by_location_data as $location_sell) {
                $sells_chart_2->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }
        if (count($all_locations) > 1) {
            $sells_chart_2->dataset(__('report.all_locations'), 'line', $values);
        }




        //Get Dashboard widgets from module
        $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');

        $widgets = [];

        foreach ($module_widgets as $widget_array) {
            if (!empty($widget_array['position'])) {
                $widgets[$widget_array['position']][] = $widget_array['widget'];
            }
        }

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        if ($is_customer) {
            return redirect()->route('agent_home');
        }
        //essentials
        // $essentialsControllerClass = \Modules\Essentials\Http\Controllers\DataController::class;
        // $essentialsController = new $essentialsControllerClass();
        //$essentialsPermissions = $essentialsController->user_permissions();


        //sales
        // $salesControllerClass = \Modules\Sales\Http\Controllers\DataController::class;
        // $salesController = new $salesControllerClass();
        // $salesPermissions = $salesController->user_permissions();
        // // $salesPermissions = [['value'=>'sales.sales_dashboard']];

        // //internationalRelations
        // $irControllerClass = \Modules\InternationalRelations\Http\Controllers\DataController::class;
        // $irController = new $irControllerClass();
        // $irPermissions = $irController->user_permissions();

        // //housingMovements
        // $houseingMovementControllerClass = \Modules\HousingMovements\Http\Controllers\DataController::class;
        // $houseingMovementController = new $houseingMovementControllerClass();
        // $houseingMovementPermissions = $houseingMovementController->user_permissions();

        // //superadmin
        // $superadminControllerClass = \Modules\Sales\Http\Controllers\DataController::class;
        // $superadminController = new $superadminControllerClass();
        // $superadminPermissions = $superadminController->user_permissions();

        // //accounting
        // $accountingControllerClass = \Modules\Accounting\Http\Controllers\DataController::class;
        // $accountingController = new $accountingControllerClass();
        // $accountingPermissions = $accountingController->user_permissions();


        // //connector
        // $ConnectorControllerClass = \Modules\Connector\Http\Controllers\DataController::class;
        // $ConnectorController = new $ConnectorControllerClass();
        // $ConnectorPermissions = $ConnectorController->user_permissions();


        //AssetManagement
        // $AssetManagementControllerClass = \Modules\AssetManagement\Http\Controllers\DataController::class;
        // $AssetManagementController = new $AssetManagementControllerClass();
        // $AssetManagementPermissions = $AssetManagementController->user_permissions();


        // //CRM
        // $CRMControllerClass = \Modules\Crm\Http\Controllers\DataController::class;
        // $CRMController = new $CRMControllerClass();
        // $CRMPermissions = $CRMController->user_permissions();


        $generalManagmentDashPermission = [
            ['value' => 'generalmanagement.generalmanagement_dashboard'],
        ];
        $generalManagmentOfficeDashPermission = [
            ['value' => 'generalmanagmentoffice.generalmanagmentoffice_dashboard'],
        ];
        $CEODashPermission = [
            ['value' => 'ceomanagment.CEOmanagement_dashboard'],
        ];
        $OperationMangmentDashPermission = [
            ['value' => 'operationsmanagmentgovernment.OperationsManagmentGovernment_dashboard'],
        ];
        $userManagementPermissions = [
            ['value' => 'user.view'],
            ['value' => 'user.create'],
            ['value' => 'user.update'],
            ['value' => 'user.delete'],
        ];
        $essentialsPermissions = [
            ['value' => 'essentials.essentials_dashboard'],
        ];
        $workCardsPermissions = [
            ['value' => 'essentials.essentials_work_cards_dashboard'],
        ];
        $employeeAffairsPermissions = [
            ['value' => 'essentials.view_employee_affairs_dashboard'],
        ];
        $medicalInsurancePermissions = [
            ['value' => 'essentials.medicalInsurance_dashboard'],
        ];
        $ToPermissions = [
            ['value' => 'essentials.essentials_todo_dashboard'],
        ];
        $salesDashPermission = [
            ['value' => 'sales.sales_dashboard'],
        ];
        $followupDashPermission = [
            ['value' => 'followup.followup_dashboard'],
        ];
        $housingPermissions = [
            ['value' => 'housingmovements.housing_move_dashbord'],
        ];
        $movmentsPermissions = [
            ['value' => 'essentials.movement_management_dashbord'],
        ];
        $internationalrelationsDashPermission = [
            ['value' => 'internationalrelations.internationalrelations_dashboard'],
        ];
        $accountingPermissions = [
            ['value' => 'accounting.accounting_dashboard'],
        ];
        $assetPermissions = [
            ['value' => 'asset.assetManagement_dashboard'],
        ];
        $legalAffairsPermissions = [
            ['value' => 'legalaffairs.legalAffairs_dashboard'],
        ];
        $InformationTechnologDashPermission = [
            ['value' => 'informationtechnologymanagment.InformationTechnology_dashboard'],
        ];
        $payrollsPermissions = [
            ['value' => 'essentials.payrolls_management'],
        ];
        $reportsPermissions = [["value" => 'report.reports'],];

        // $settingsPermissions = [
        //     ['value' => 'business_settings.access'],
        //     ['value' => 'barcode_settings.access'],
        //     ['value' => 'invoice_settings.access'],
        //     ['value' => 'tax_rate.view'],
        //     ['value' => 'tax_rate.create'],
        //     ['value' => 'access_package_subscriptions'],

        //     ['value' => 'purchase_n_sell_report.view'],
        //     ['value' => 'contacts_report.view'],
        //     ['value' => 'stock_report.view'],
        //     ['value' => 'tax_report.view'],
        //     ['value' => 'trending_product_report.view'],
        //     ['value' => 'sales_representative.view'],
        //     ['value' => 'expense_report.view'],
        //     ['value' => 'backup'],
        // ];

        //action([\App\Http\Controllers\ManageUserController::class, 'index'])
        $cardsPack = [
            ['id' => 'general_management',  'permissions' => $generalManagmentDashPermission, 'title' => __('generalmanagement::lang.GeneralManagement'), 'icon' => "fas fa-sitemap", 'link' => action([\Modules\GeneralManagement\Http\Controllers\DashboardController::class, 'index'])],
            ['id' => 'general_management_office',  'permissions' => $generalManagmentOfficeDashPermission, 'title' => __('generalmanagmentoffice::lang.generalmanagmentoffice'), 'icon' => "fas fa-sitemap", 'link' => action([\Modules\GeneralManagmentOffice\Http\Controllers\DashboardController::class, 'index'])],
            ['id' => 'ceo_management',  'permissions' => $CEODashPermission, 'title' => __('ceomanagment::lang.CEO_Managment'), 'icon' => "fas fa-chart-line", 'link' => action([\Modules\CEOManagment\Http\Controllers\DashboardController::class, 'index'])],
            ['id' => 'operationsmanagmentgovernment',  'permissions' => $OperationMangmentDashPermission, 'title' => __('operationsmanagmentgovernment::lang.operationsmanagmentgovernment'), 'icon' => "fas fa-cogs", 'link' => action([\Modules\OperationsManagmentGovernment\Http\Controllers\DashboardController::class, 'index'])],

            // ['id' => 'superAdmin',  'permissions' => [], 'title' => __('superadmin::lang.superadmin'), 'icon' => 'fa fas fa-users-cog', 'link' => action([\Modules\Superadmin\Http\Controllers\SuperadminController::class, 'index'])],
            ['id' => 'user_management', 'permissions' =>  $userManagementPermissions, 'title' => __('user.user_management'), 'icon' => 'fas fa-user-tie ', 'link' =>   route('users.index')],
            ['id' => 'hrm',  'permissions' => $essentialsPermissions, 'title' => __('essentials::lang.hrm'), 'icon' => 'fa fas fa-users', 'link' =>   route('essentials_landing')],
            ['id' => 'workCards',  'permissions' => $workCardsPermissions, 'title' => __('essentials::lang.work_cards'), 'icon' => '	far fa-handshake', 'link' =>   route('essentials_word_cards_dashboard')],
            ['id' => 'employeeAffairs',  'permissions' => $employeeAffairsPermissions, 'title' => __('essentials::lang.employees_affairs'), 'icon' => 'fas fa-address-book', 'link' =>   route('employee_affairs_dashboard')],
            ['id' => 'payrolls',  'permissions' => $payrollsPermissions, 'title' => __('essentials::lang.payrolls_management'), 'icon' => 'fas fa-coins', 'link' =>   route('payrolls_dashboard')],
            ['id' => 'medical_insurance',  'permissions' => $medicalInsurancePermissions, 'title' => __('essentials::lang.health_insurance'), 'icon' => 'fa-solid fa-briefcase-medical', 'link' => route('insurance-dashbord')],

            ['id' => 'essentials',  'permissions' => $ToPermissions, 'title' => __('essentials::lang.essentials'), 'icon' => 'fa fas fa-check-circle', 'link' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index'])],
            ['id' => 'informationtechnologymanagment',  'permissions' => $InformationTechnologDashPermission, 'title' => __('informationtechnologymanagment::lang.informationtechnologymanagment'), 'icon' => "fas fa-laptop-code", 'link' => action([\Modules\InformationTechnologyManagment\Http\Controllers\DashboardController::class, 'index'])],

            ['id' => 'sales',  'permissions' => $salesDashPermission, 'title' =>  __('sales::lang.sales'), 'icon' => 'fas fa-dollar-sign', 'link' =>  route('sales_landing')],
            ['id' => 'FollowUp',  'permissions' => $followupDashPermission, 'title' =>  __('followup::lang.followUp'), 'icon' => 'fa fas fa-meteor', 'link' => action([\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'index'])],
            ['id' => 'houseingMovements',  'permissions' => $housingPermissions, 'title' => __('housingmovements::lang.housing_move'), 'icon' => 'fa fas fa-home', 'link' =>   action([\Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index'])],
            ['id' => 'movements',  'permissions' => $movmentsPermissions, 'title' => __('housingmovements::lang.movement_management'), 'icon' => 'fa fa-car', 'link' =>   action([MovmentDashboardController::class, 'index'])],
            ['id' => 'internationalrelations',  'permissions' => $internationalrelationsDashPermission, 'title' => __('internationalrelations::lang.International'), 'icon' => 'fa fas fa-dharmachakra', 'link' =>  action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index'])],
            ['id' => 'legalAffairs',  'permissions' => $legalAffairsPermissions, 'title' => __('legalaffairs::lang.legalaffairs'), 'icon' =>  'fas fa-balance-scale', 'link' =>  route('legalAffairs.dashboard')],
            ['id' => 'reports',  'permissions' => $reportsPermissions, 'title' => __('report.reports'), 'icon' => 'fa fas fa-file-alt', 'link' => route('reports.landing')],

            ['id' => 'purchases',  'permissions' => [], 'title' =>  __('purchase.purchases'), 'icon' => 'fas fa-cart-plus', 'link' => route('purchases.index')],
            // ['id' => 'accounting',  'permissions' => $accountingPermissions, 'title' =>   __('accounting::lang.accounting'),  'icon' => 'fas fa-money-check fa', 'link' =>  action('\Modules\Accounting\Http\Controllers\AccountingController@dashboard'),],
            ['id' => 'accounting',  'permissions' => $accountingPermissions, 'title' =>   __('accounting::lang.accounting'),  'icon' => 'fas fa-money-check fa', 'link' => route('accountingLanding'),],
            ['id' => 'assetManagement',  'permissions' => $assetPermissions, 'title' => __('assetmanagement::lang.asset_management'), 'icon' => 'fas fa fa-boxes', 'link' =>  action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'dashboard'])],
            //  ['id' => 'crm',  'permissions' => $CRMPermissions, 'title' => __('crm::lang.crm'),'icon' =>'fas fa fa-broadcast-tower', 'link' => action([\Modules\Crm\Http\Controllers\CrmDashboardController::class, 'index']),],
            //  ['id' => 'contacts',  'permissions' => [], 'title' => __('contact.contacts'), 'icon' => 'fas fa-id-card ', 'link' => ''],
            // ['id' => 'products',  'permissions' => [], 'title' => __('sale.products'), 'icon' => 'fas fa-chart-pie', 'link' =>  action([\App\Http\Controllers\ProductController::class, 'index']),],
            //  ['id' => 'connector',  'permissions' => [], 'title' => __('connector::lang.clients'), 'icon' => 'fas fa-user-circle', 'link' =>   action([\Modules\Connector\Http\Controllers\ClientController::class, 'index'])],
            ['id' => 'settings',  'permissions' => [], 'title' =>  __('business.settings'), 'icon' => 'fa fas fa-cog', 'link' => action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings'])],
        ];
        $cards = [];

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $is_general_manager = auth()->user()->hasRole('الإدارة العليا#1') ? true : false;

        $user_id = auth()->user()->id;

        if ($is_general_manager) {
            $cards = [
                ['id' => 'general_management',  'permissions' => $generalManagmentDashPermission, 'title' => __('generalmanagement::lang.GeneralManagement'), 'icon' => "fas fa-sitemap", 'link' => action([\Modules\GeneralManagement\Http\Controllers\DashboardController::class, 'index'])],
                ['id' => 'ceo_management',  'permissions' => $CEODashPermission, 'title' => __('ceomanagment::lang.CEO_Managment'), 'icon' => "fas fa-chart-line", 'link' => action([\Modules\CEOManagment\Http\Controllers\DashboardController::class, 'index'])],
                ['id' => 'human_resources_management',  'permissions' => [], 'title' =>  __('generalmanagement::lang.human_resources_management'), 'icon' => 'fa fas fa-users', 'link' => route('generalmanagement.human_resources_management')],
                ['id' => 'financial_accounting_management',  'permissions' => [], 'title' =>  __('generalmanagement::lang.financial_accounting_management'), 'icon' => 'fas fa-money-check fa', 'link' => route('accountingLanding'),],
                ['id' => 'follow_up_management',  'permissions' => [], 'title' =>  __('generalmanagement::lang.follow_up_management'), 'icon' => 'fa fas fa-meteor', 'link' => action([\Modules\FollowUp\Http\Controllers\FollowUpController::class, 'index'])],
                ['id' => 'international_relations_management',  'permissions' => [], 'title' =>  __('generalmanagement::lang.international_relations_management'), 'icon' => 'fa fas fa-dharmachakra', 'link' =>  action([\Modules\InternationalRelations\Http\Controllers\DashboardController::class, 'index'])],
                ['id' => 'housing_movement_management',  'permissions' => [], 'title' =>  __('generalmanagement::lang.housing_movement_management'), 'icon' => 'fa fas fa-home', 'link' => route('generalmanagement.housing_movement_management')],
                ['id' => 'sells_management',  'permissions' => [], 'title' =>  __('generalmanagement::lang.sells_management'), 'icon' => 'fas fa-dollar-sign', 'link' =>  route('sales_landing')],
                ['id' => 'legal_affairs_management',  'permissions' => [], 'title' =>  __('generalmanagement::lang.legal_affairs_management'), 'icon' =>  'fas fa-balance-scale', 'link' =>  route('legalAffairs.dashboard')],
                ['id' => 'reports',  'permissions' => $reportsPermissions, 'title' => __('report.reports'), 'icon' => 'fa fas fa-file-alt', 'link' => route('reports.landing')],
            ];
        } else {
            foreach ($cardsPack as $card) {
                if (!empty($card['permissions'])) {
                    $canAccessCard = false;
                    foreach ($card['permissions'] as $permission) {
                        if ($isSuperAdmin || $is_admin || auth()->user()->can($permission['value'])) {
                            $canAccessCard = true;
                            break;
                        }
                    }

                    if ($canAccessCard) {
                        $cards[] = $card;

                        error_log($card['title']);
                    } else {
                        error_log("cant " . $card['title']);
                    }
                } else {
                    if (($is_admin &&  $user_id == 1) || $isSuperAdmin) {
                        $cards[] = $card;
                    }
                    //$cards[] = $card;
                    error_log("empty " . $card['title']);
                }
            }
        }
        return view('custom_views.custom_home', compact('cards'));

        // return view('custom_views.custom_home', compact('cards', 'sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin'));

        // return view('custom_views.home', compact('sells_chart_1', 'sells_chart_2', 'widgets', 'all_locations', 'common_settings', 'is_admin'));
    }



    public function getMyNotifications()
    {
        try {
            $notifications = SentNotificationsUser::with(['sentNotification.createdBy'])->where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC');

            $nots = $notifications->get();

            foreach ($nots as $notification) {
                if (!($notification->read_at)) {
                    $notification->update(['read_at' => Carbon::now()]);
                }
            }

            if (request()->ajax()) {
                return DataTables::of($notifications)
                    ->addColumn('sender', function ($row) {
                        $tmp =  json_decode($row)->sent_notification->created_by;
                        return $tmp->first_name . ' ' . $tmp->last_name;
                    })
                    ->addColumn('type', function ($row) {
                        return json_decode($row)->sent_notification->type;
                    })
                    ->addColumn('title', function ($row) {
                        return json_decode($row)->sent_notification->title;
                    })
                    ->addColumn('msg', function ($row) {
                        return json_decode($row)->sent_notification->msg;
                    })
                    ->addColumn('read_at', function ($row) {
                        return Carbon::parse($row->read_at)->diffForHumans();
                    })
                    ->addColumn('created_at', function ($row) {
                        return Carbon::parse($row->created_at)->diffForHumans();
                    })
                    ->rawColumns(['sender', 'title', 'msg', 'read_at', 'type', 'created_at'])
                    ->make(true);
            }
        } catch (Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            error_log('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }
        return view('custom_views.my_notifications');
    }


    /**
     * Retrieves purchase and sell details for a given time period.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotals()
    {
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $location_id = request()->location_id;
            $business_id = request()->session()->get('user.business_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end, $location_id);

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start, $end, $location_id);

            $total_ledger_discount = $this->transactionUtil->getTotalLedgerDiscount($business_id, $start, $end);

            $purchase_details['purchase_due'] = $purchase_details['purchase_due'] - $total_ledger_discount['total_purchase_discount'];

            $transaction_types = [
                'purchase_return', 'sell_return', 'expense',
            ];

            $transaction_totals = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                $start,
                $end,
                $location_id
            );

            $total_purchase_inc_tax = !empty($purchase_details['total_purchase_inc_tax']) ? $purchase_details['total_purchase_inc_tax'] : 0;
            $total_purchase_return_inc_tax = $transaction_totals['total_purchase_return_inc_tax'];

            $output = $purchase_details;
            $output['total_purchase'] = $total_purchase_inc_tax;
            $output['total_purchase_return'] = $total_purchase_return_inc_tax;
            $output['total_purchase_return_paid'] = $this->transactionUtil->getTotalPurchaseReturnPaid($business_id, $start, $end, $location_id);

            $total_sell_inc_tax = !empty($sell_details['total_sell_inc_tax']) ? $sell_details['total_sell_inc_tax'] : 0;
            $total_sell_return_inc_tax = !empty($transaction_totals['total_sell_return_inc_tax']) ? $transaction_totals['total_sell_return_inc_tax'] : 0;
            $output['total_sell_return_paid'] = $this->transactionUtil->getTotalSellReturnPaid($business_id, $start, $end, $location_id);

            $output['total_sell'] = $total_sell_inc_tax;
            $output['total_sell_return'] = $total_sell_return_inc_tax;

            $output['invoice_due'] = $sell_details['invoice_due'] - $total_ledger_discount['total_sell_discount'];
            $output['total_expense'] = $transaction_totals['total_expense'];

            //NET = TOTAL SALES - INVOICE DUE - EXPENSE
            $output['net'] = $output['total_sell'] - $output['invoice_due'] - $output['total_expense'];

            return $output;
        }
    }

    /**
     * Retrieves sell products whose available quntity is less than alert quntity.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductStockAlert()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $query = VariationLocationDetails::join(
                'product_variations as pv',
                'variation_location_details.product_variation_id',
                '=',
                'pv.id'
            )
                ->join(
                    'variations as v',
                    'variation_location_details.variation_id',
                    '=',
                    'v.id'
                )
                ->join(
                    'products as p',
                    'variation_location_details.product_id',
                    '=',
                    'p.id'
                )
                ->leftjoin(
                    'business_locations as l',
                    'variation_location_details.location_id',
                    '=',
                    'l.id'
                )
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('p.business_id', $business_id)
                ->where('p.enable_stock', 1)
                ->where('p.is_inactive', 0)
                ->whereNull('v.deleted_at')
                ->whereNotNull('p.alert_quantity')
                ->whereRaw('variation_location_details.qty_available <= p.alert_quantity');

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('variation_location_details.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('variation_location_details.location_id', request()->input('location_id'));
            }

            $products = $query->select(
                'p.name as product',
                'p.type',
                'p.sku',
                'pv.name as product_variation',
                'v.name as variation',
                'v.sub_sku',
                'l.name as location',
                'variation_location_details.qty_available as stock',
                'u.short_name as unit'
            )
                ->groupBy('variation_location_details.id')
                ->orderBy('stock', 'asc');

            return Datatables::of($products)
                ->editColumn('product', function ($row) {
                    if ($row->type == 'single') {
                        return $row->product . ' (' . $row->sku . ')';
                    } else {
                        return $row->product . ' - ' . $row->product_variation . ' - ' . $row->variation . ' (' . $row->sub_sku . ')';
                    }
                })
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0;

                    return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false>' . (float) $stock . '</span> ' . $row->unit;
                })
                ->removeColumn('sku')
                ->removeColumn('sub_sku')
                ->removeColumn('unit')
                ->removeColumn('type')
                ->removeColumn('product_variation')
                ->removeColumn('variation')
                ->rawColumns([2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchasePaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                ->leftJoin(
                    'transaction_payments as tp',
                    'transactions.id',
                    '=',
                    'tp.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'purchase')
                ->where('transactions.payment_status', '!=', 'paid')
                ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as supplier',
                'c.supplier_business_name',
                'ref_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">' .
                        $due . '</span>';
                })
                ->addColumn('action', '@can("purchase.create") <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="btn btn-xs btn-success add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endcan')
                ->removeColumn('supplier_business_name')
                ->editColumn('supplier', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$supplier}}')
                ->editColumn('ref_no', function ($row) {
                    if (auth()->user()->can('purchase.view')) {
                        return  '<a href="#" data-href="' . action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->ref_no . '</a>';
                    }

                    return $row->ref_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesPaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format('Y-m-d H:i:s');

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                ->leftJoin(
                    'transaction_payments as tp',
                    'transactions.id',
                    '=',
                    'tp.transaction_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.payment_status', '!=', 'paid')
                ->whereNotNull('transactions.pay_term_number')
                ->whereNotNull('transactions.pay_term_type')
                ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->input('location_id'))) {
                $query->where('transactions.location_id', request()->input('location_id'));
            }

            $dues = $query->select(
                'transactions.id as id',
                'c.name as customer',
                'c.supplier_business_name',
                'transactions.invoice_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;

                    return '<span class="display_currency" data-currency_symbol="true">' .
                        $due . '</span>';
                })
                ->editColumn('invoice_no', function ($row) {
                    if (auth()->user()->can('sell.view')) {
                        return  '<a href="#" data-href="' . action([\App\Http\Controllers\SellController::class, 'show'], [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->invoice_no . '</a>';
                    }

                    return $row->invoice_no;
                })
                ->addColumn('action', '@if(auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access")) <a href="{{action([\App\Http\Controllers\TransactionPaymentController::class, \'addPayment\'], [$id])}}" class="btn btn-xs btn-success add_payment_modal"><i class="fas fa-money-bill-alt"></i> @lang("purchase.add_payment")</a> @endif')
                ->editColumn('customer', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$customer}}')
                ->removeColumn('supplier_business_name')
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([0, 1, 2, 3])
                ->make(false);
        }
    }

    public function loadMoreNotifications()
    {
        $notifications = SentNotificationsUser::with('sentNotification')->where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->paginate(10);
        $notifications_data = [];
        $icon_classes = ['GeneralManagement' => 'fas fa-user-tie'];
        foreach ($notifications as $notification) {
            $sent_notification = json_decode($notification)->sent_notification;
            $notifications_data[] = [
                'title' =>  $sent_notification->title ?? '',
                'msg' => $sent_notification->msg ?? '',
                'icon_class' =>  $icon_classes[$sent_notification->type] ?? '',
                // 'link' => route('showNotificationModal'),
                'link' => '',
                'created_at' => Carbon::parse($sent_notification->created_at)->diffForHumans() ?? '',
                'read_at' => $notification->read_at ?? '',
            ];
            //  $tmp = SentNotificationsUser::find($notification->id);
            if (!($notification->read_at)) {
                $notification->update(['read_at' => Carbon::now()]);
            }
        }


        return view('layouts.partials.notification_list', compact('notifications_data'));
    }

    // public function loadMoreNotifications()
    // {
    //     $notifications = auth()->user()->notifications()->orderBy('created_at', 'DESC')->paginate(10);

    //     if (request()->input('page') == 1) {
    //         auth()->user()->unreadNotifications->markAsRead();
    //     }
    //     $notifications_data = $this->commonUtil->parseNotifications($notifications);

    //     return view('layouts.partials.notification_list', compact('notifications_data'));
    // }

    /**
     * Function to count total number of unread notifications
     *
     * @return json
     */
    public function getTotalUnreadNotifications()
    {
        $unread_notifications = auth()->user()->unreadNotifications;
        $total_unread = $unread_notifications->count();

        $notification_html = '';
        $modal_notifications = [];
        foreach ($unread_notifications as $unread_notification) {
            if (isset($data['show_popup'])) {
                $modal_notifications[] = $unread_notification;
                $unread_notification->markAsRead();
            }
        }
        if (!empty($modal_notifications)) {
            $notification_html = view('home.notification_modal')->with(['notifications' => $modal_notifications])->render();
        }

        return [
            'total_unread' => $total_unread,
            'notification_html' => $notification_html,
        ];
    }

    private function __chartOptions($title)
    {
        return [
            'yAxis' => [
                'title' => [
                    'text' => $title,
                ],
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => true,
                'layout' => 'vertical',
                'padding' => 20,
            ],
        ];
    }

    public function getCalendar()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->restUtil->is_admin(auth()->user(), $business_id);
        $is_superadmin = auth()->user()->can('superadmin');
        if (request()->ajax()) {
            $data = [
                'start_date' => request()->start,
                'end_date' => request()->end,
                'user_id' => ($is_admin || $is_superadmin) && !empty(request()->user_id) ? request()->user_id : auth()->user()->id,
                'location_id' => !empty(request()->location_id) ? request()->location_id : null,
                'business_id' => $business_id,
                'events' => request()->events ?? [],
                'color' => '#007FFF',
            ];
            $events = [];

            if (in_array('bookings', $data['events'])) {
                $events = $this->restUtil->getBookingsForCalendar($data);
            }

            $module_events = $this->moduleUtil->getModuleData('calendarEvents', $data);

            foreach ($module_events as $module_event) {
                $events = array_merge($events, $module_event);
            }

            return $events;
        }

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();
        $users = [];
        if ($is_admin) {
            $users = User::forDropdown($business_id, false);
        }

        $event_types = [
            'bookings' => [
                'label' => __('restaurant.bookings'),
                'color' => '#007FFF',
            ],
        ];
        $module_event_types = $this->moduleUtil->getModuleData('eventTypes');
        foreach ($module_event_types as $module_event_type) {
            $event_types = array_merge($event_types, $module_event_type);
        }

        return view('home.calendar')->with(compact('all_locations', 'users', 'event_types'));
    }

    public function showNotificationModal()
    {
        return view('custom_views.notification_modal', ['showModal' => true]);
    }

    public function showNotification($id)
    {
        $notification = DatabaseNotification::find($id);

        $data = $notification->data;

        $notification->markAsRead();

        return view('home.notification_modal')->with([
            'notifications' => [$notification],
        ]);
    }

    public function attachMediasToGivenModel(Request $request)
    {
        if ($request->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $model_id = $request->input('model_id');
                $model = $request->input('model_type');
                $model_media_type = $request->input('model_media_type');

                DB::beginTransaction();

                //find model to which medias are to be attached
                $model_to_be_attached = $model::where('business_id', $business_id)
                    ->findOrFail($model_id);

                Media::uploadMedia($business_id, $model_to_be_attached, $request, 'file', false, $model_media_type);

                DB::commit();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (Exception $e) {
                DB::rollBack();

                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function getUserLocation($latlng)
    {
        $latlng_array = explode(',', $latlng);

        $response = $this->moduleUtil->getLocationFromCoordinates($latlng_array[0], $latlng_array[1]);

        return ['address' => $response];
    }
}