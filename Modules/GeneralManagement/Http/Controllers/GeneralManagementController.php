<?php

namespace Modules\GeneralManagement\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GeneralManagementController extends Controller
{

    public function human_resources_management()
    {
        $cards = [
            ['id' => 'user_management', 'permissions' =>  [], 'title' => __('user.user_management'), 'icon' => 'fas fa-user-tie ', 'link' =>   route('users.index')],
            ['id' => 'hrm',  'permissions' => [], 'title' => __('essentials::lang.hrm'), 'icon' => 'fa fas fa-users', 'link' =>   route('essentials_landing')],
            ['id' => 'workCards',  'permissions' => [], 'title' => __('essentials::lang.work_cards'), 'icon' => '	far fa-handshake', 'link' =>   route('essentials_word_cards_dashboard')],
            ['id' => 'employeeAffairs',  'permissions' => [], 'title' => __('essentials::lang.employees_affairs'), 'icon' => 'fas fa-address-book', 'link' =>   route('employee_affairs_dashboard')],
            ['id' => 'payrolls',  'permissions' => [], 'title' => __('essentials::lang.payrolls_management'), 'icon' => 'fas fa-coins', 'link' =>   route('payrolls_dashboard')],
            ['id' => 'medical_insurance',  'permissions' => [], 'title' => __('essentials::lang.health_insurance'), 'icon' => 'fa-solid fa-briefcase-medical', 'link' => route('insurance-dashbord')],
            ['id' => 'essentials',  'permissions' => [], 'title' => __('essentials::lang.essentials'), 'icon' => 'fa fas fa-check-circle', 'link' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index'])],
            ['id' => 'assetManagement',  'permissions' => [], 'title' => __('assetmanagement::lang.asset_management'), 'icon' => 'fas fa fa-boxes', 'link' =>  action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'dashboard'])],
        ];
        return view('custom_views.custom_home', compact('cards'));
    }
    public function financial_accounting_management()
    {
        $cards = [];
        return view('custom_views.custom_home', compact('cards'));
    }
    public function follow_up_management()
    {
        $cards = [];
        return view('custom_views.custom_home', compact('cards'));
    }
    public function international_relations_management()
    {
        $cards = [];
        return view('custom_views.custom_home', compact('cards'));
    }
    public function housing_movement_management()
    {
        $cards = [
            ['id' => 'houseingMovements',  'permissions' => [], 'title' => __('housingmovements::lang.housing_move'), 'icon' => 'fa fas fa-home', 'link' =>   action([\Modules\HousingMovements\Http\Controllers\DashboardController::class, 'index'])],
            ['id' => 'movements',  'permissions' => [], 'title' => __('housingmovements::lang.movement_management'), 'icon' => 'fa fa-car', 'link' =>   action([\Modules\Essentials\Http\Controllers\MovmentDashboardController::class, 'index'])],
        ];
        return view('custom_views.custom_home', compact('cards'));
    }
    public function sells_management()
    {
        $cards = [];
        return view('custom_views.custom_home', compact('cards'));
    }
    public function legal_affairs_management()
    {
        $cards = [];
        return view('custom_views.custom_home', compact('cards'));
    }






    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('generalmanagement::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('generalmanagement::create');
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
        return view('generalmanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('generalmanagement::edit');
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
