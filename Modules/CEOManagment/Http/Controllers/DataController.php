<?php

namespace Modules\CEOManagment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DataController extends Controller
{
    public function user_permissions()
    {
        return [

            [
                'group_name' => __('ceomanagment::lang.CEO_Managment'),
                'group_permissions' => [
                    [
                        'value' => 'ceomanagment.CEOmanagement_dashboard',
                        'label' => __('ceomanagment::lang.CEOmanagement_dashboard'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.view_requests',
                        'label' => __('ceomanagment::lang.requests'),
                        'default' => false,
                    ],

                    [
                        'value' => 'ceomanagment.curd_organizational_structure',
                        'label' => __('essentials::lang.organizational_structure'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.crud_all_procedures',
                        'label' => __('essentials::lang.crud_all_procedures'),
                        'default' => false,
                    ],
                    [
                        'value' => 'essentials.delegatingManager_name',
                        'label' => __('essentials::lang.delegatingManager_name'),
                        'default' => false,
                    ],
                    [
                        'value' => 'essentials.add_manager',
                        'label' => __('essentials::lang.add_manager'),
                        'default' => false,
                    ],
                    [
                        'value' => 'essentials.show_depatments',
                        'label' => __('essentials::lang.show_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'essentials.edit_depatments',
                        'label' => __('essentials::lang.edit_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'essentials.delete_depatments',
                        'label' => __('essentials::lang.delete_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'essentials.add_departments',
                        'label' => __('essentials::lang.add_departments'),
                        'default' => false,
                    ],

                ]
            ]
        ];
    }





    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('ceomanagment::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('ceomanagment::create');
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
        return view('ceomanagment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('ceomanagment::edit');
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
