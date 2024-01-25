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
                        'value' => 'ceomanagment.curd_organizational_structure',
                        'label' => __('ceomanagment::lang.organizational_structure'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.crud_all_procedures',
                        'label' => __('ceomanagment::lang.crud_all_procedures'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.delegatingManager_name',
                        'label' => __('ceomanagment::lang.delegatingManager_name'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.add_manager',
                        'label' => __('ceomanagment::lang.add_manager'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.add_deputy',
                        'label' => __('ceomanagment::lang.add_deputy'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.show_depatments',
                        'label' => __('ceomanagment::lang.show_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.edit_depatments',
                        'label' => __('ceomanagment::lang.edit_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.delete_depatments',
                        'label' => __('ceomanagment::lang.delete_depatments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.add_departments',
                        'label' => __('ceomanagment::lang.add_departments'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.view_CEO_requests',
                        'label' => __('ceomanagment::lang.view_CEO_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.view_CEO_escalate_requests',
                        'label' => __('ceomanagment::lang.view_CEO_escalate_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.change_request_status',
                        'label' => __('ceomanagment::lang.change_request_status'),
                        'default' => false,
                    ],
                 
                    [
                        'value' => 'ceomanagment.return_request',
                        'label' => __('ceomanagment::lang.return_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.view_request',
                        'label' => __('ceomanagment::lang.view_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.delete_procedure',
                        'label' => __('ceomanagment::lang.delete_procedure'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.edit_procedure',
                        'label' => __('ceomanagment::lang.edit_procedure'),
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
