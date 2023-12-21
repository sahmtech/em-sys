<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Menu;
class DataController extends Controller
{

       /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'sales_module',
                'label' => __('sales::lang.sales_module'),
                'default' => false,
            ],
        ];
    }

    public function user_permissions()
    {
        return [
           
             [
                'value' => 'sales.crud_contacts',
                'label' => __('sales::lang.crud_contacts'),
                'default' => false,
            ],
            [
                'value' => 'sales.create_offer_price',
                'label' => __('sales::lang.create_offer_price'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_offer_price',
                'label' => __('sales::lang.crud_offer_price'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_contract',
                'label' => __('sales::lang.crud_contract'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_contract_itmes',
                'label' => __('sales::lang.crud_contract_itmes'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_contract_appendics',
                'label' => __('sales::lang.crud_contract_appendics'),
                'default' => false,
            ],

            [
                'value' => 'sales.crud_sale_operation_orders',
                'label' => __('sales::lang.crud_sale_operation_orders'),
                'default' => false,
            ],

            [
                'value' => 'sales.crud_follow_ups',
                'label' => __('sales::lang.crud_follow_ups'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_sales_projects',
                'label' => __('sales::lang.crud_sales_projects'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_requests',
                'label' => __('sales::lang.crud_requests'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_settings',
                'label' => __('sales::lang.crud_settings'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_sale_sources',
                'label' => __('sales::lang.crud_sale_sources'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_sales_templates',
                'label' => __('sales::lang.crud_sales_templates'),
                'default' => false,
            ],
            [
                'value' => 'sales.crud_sales_costs',
                'label' => __('sales::lang.crud_sales_costs'),
                'default' => false,
            ],

        ];
    }
    
    /**
     * Adds followup menus
     *
     * @return null
     */
    public function modifyAdminMenu_CUS_sales()
    {
        $module_util = new ModuleUtil();

        $business_id = session()->get('user.business_id');
        $is_sales_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'sales_module');

        if ($is_sales_enabled) {
            Menu::create('custom_admin-sidebar-menu', function ($menu) {

                $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fas fa-home  ','active' => request()->segment(1) == 'home'])->order(5);
                $menu->dropdown(
                    __('sales::lang.sales'),
                    function ($subMenu) {


                        $subMenu->url(
                            action([\Modules\Sales\Http\Controllers\ClientsController::class, 'index']),
                            __('sales::lang.customers'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'clients'],
                        )->order(1);

                        $subMenu->url(
                            action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'create']),
                             __('sales::lang.add_offer_price'),
                             ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'createOfferPrice'],
                               )->order(2);

                        $subMenu->url(
                            action([\Modules\Sales\Http\Controllers\OfferPriceController::class, 'index']),
                            __('sales::lang.offer_price'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'offer-price'],
                         
                        )->order(3);


                        $subMenu->url(
                            action([\Modules\Sales\Http\Controllers\ContractsController::class, 'index']),
                            __('sales::lang.contracts'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'cotracts'],
                        )->order(4);

                        

                        $subMenu->url(
                            action([\Modules\Sales\Http\Controllers\ContractItemController::class, 'index']),
                            __('sales::lang.contract_itmes'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'contract_itmes'],
                        )->order(5);

                        $subMenu->url(
                            action([\Modules\Sales\Http\Controllers\ContractAppendixController::class, 'index']),
                            __('sales::lang.contract_appendics'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'contract_appendices'],
                        )->order(6);
                        $subMenu->url(
                            action([\Modules\Sales\Http\Controllers\SaleOperationOrderController::class, 'index']),
                            __('sales::lang.sale_operation_orders'),
                            ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'sale' && request()->segment(2) == 'sale_operation_order'],
                        )->order(7);
                      },
                    [
                        'icon' => 'fa fas fa-users',
                        'active' => request()->segment(1) == 'sales',
                        'style' => config('app.env') == 'demo' ? 'background-color: #605ca8 !important;' : '',
                    ]
                )->order(10);
             
              
            });
        }
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('sales::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('sales::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('sales::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('sales::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
