<?php

namespace Modules\OperationsManagmentGovernment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DataController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function user_permissions()
    {
        return [
            [
                'group_name'        => __('operationsmanagmentgovernment::lang.operationsmanagmentgovernment'),
                'group_permissions' => [
                    [
                        'value'   => 'operationsmanagmentgovernment.OperationsManagmentGovernment_dashboard',
                        'label'   => __('operationsmanagmentgovernment::lang.OperationsManagmentGovernment_dashboard'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.view_requests',
                        'label'   => __('operationsmanagmentgovernment::lang.view_requests'),
                        'default' => false,
                    ],

                    //water
                    [
                        'value'   => 'operationsmanagmentgovernment.water_reports',
                        'label'   => __('operationsmanagmentgovernment::lang.water_reports'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.add_water_weight',
                        'label'   => __('operationsmanagmentgovernment::lang.add_water_weight'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.edit_water_weight',
                        'label'   => __('operationsmanagmentgovernment::lang.edit_water_weight'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.delete_water_weight',
                        'label'   => __('operationsmanagmentgovernment::lang.delete_water_weight'),
                        'default' => false,
                    ],

                    //asset_assessment
                    [
                        'value'   => 'operationsmanagmentgovernment.asset_assessment',
                        'label'   => __('operationsmanagmentgovernment::lang.asset_assessment'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.add_asset_assessment',
                        'label'   => __('operationsmanagmentgovernment::lang.add_asset_assessment'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.edit_asset_assessment',
                        'label'   => __('operationsmanagmentgovernment::lang.edit_asset_assessment'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.delete_asset_assessment',
                        'label'   => __('operationsmanagmentgovernment::lang.delete_asset_assessment'),
                        'default' => false,
                    ],


                    //project zone
                    [
                        'value'   => 'operationsmanagmentgovernment.project_zones',
                        'label'   => __('operationsmanagmentgovernment::lang.project_zone'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.add_project_zone',
                        'label'   => __('operationsmanagmentgovernment::lang.add_project_zone'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.edit_project_zone',
                        'label'   => __('operationsmanagmentgovernment::lang.edit_project_zone'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.delete_project_zone',
                        'label'   => __('operationsmanagmentgovernment::lang.delete_project_zone'),
                        'default' => false,
                    ],

                    //permissions
                    [
                        'value'   => 'operationsmanagmentgovernment.permissions',
                        'label'   => __('operationsmanagmentgovernment::lang.permissions'),
                        'default' => false,
                    ],

                    [
                        'value'   => 'operationsmanagmentgovernment.edit_permissions',
                        'label'   => __('operationsmanagmentgovernment::lang.edit_permissions'),
                        'default' => false,
                    ],

                    [
                        'value'   => 'operationsmanagmentgovernment.change_request_status',
                        'label'   => __('operationsmanagmentgovernment::lang.change_request_status'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.return_request',
                        'label'   => __('operationsmanagmentgovernment::lang.return_request'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.show_request',
                        'label'   => __('operationsmanagmentgovernment::lang.show_request'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.add_request',
                        'label'   => __('operationsmanagmentgovernment::lang.add_request'),
                        'default' => false,
                    ],

                    // project departments
                    
                    [
                        'value'   => 'operationsmanagmentgovernment.view_project_departments',
                        'label'   => __('operationsmanagmentgovernment::lang.view_project_departments'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.delete_project_department',
                        'label'   => __('operationsmanagmentgovernment::lang.delete_project_department'),
                        'default' => false,
                    ],

                    // Project Diagram
                    [
                        'value'   => 'operationsmanagmentgovernment.project_diagram',
                        'label'   => __('operationsmanagmentgovernment::lang.project_diagram'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.add_project_diagram',
                        'label'   => __('operationsmanagmentgovernment::lang.add_project_diagram'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.delete_project_diagram',
                        'label'   => __('operationsmanagmentgovernment::lang.delete_project_diagram'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.view_project_diagram',
                        'label'   => __('operationsmanagmentgovernment::lang.view_project_diagram'),
                        'default' => false,
                    ],

                    // Project Report
                    [
                        'value'   => 'operationsmanagmentgovernment.project_report',
                        'label'   => __('operationsmanagmentgovernment::lang.project_report'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.view_project_report',
                        'label'   => __('operationsmanagmentgovernment::lang.view_project_report'),
                        'default' => false,
                    ],

                    [
                        'value'   => 'operationsmanagmentgovernment.add_project_report',
                        'label'   => __('operationsmanagmentgovernment::lang.add_project_report'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.delete_project_report',
                        'label'   => __('operationsmanagmentgovernment::lang.delete_project_report'),
                        'default' => false,
                    ],

                    // Security  Guard
                    [
                        'value'   => 'operationsmanagmentgovernment.view_security_guards',
                        'label'   => __('operationsmanagmentgovernment::lang.view_security_guards'),
                        'default' => false,
                    ],
                    [
                        'value'   => 'operationsmanagmentgovernment.add_security_guard',
                        'label'   => __('operationsmanagmentgovernment::lang.add_security_guard'),
                        'default' => false,
                    ],

                    [
                        'value'   => 'operationsmanagmentgovernment.delete_security_guard',
                        'label'   => __('operationsmanagmentgovernment::lang.delete_security_guard'),
                        'default' => false,
                    ],

                    // outside communication
                    [
                        'value'   => 'operationsmanagmentgovernment.view_outside_communication',
                        'label'   => __('operationsmanagmentgovernment::lang.view_outside_communication'),
                        'default' => false,
                    ],

                ],
            ],
        ];
    }

    public function index()
    {
        return view('operationsmanagmentgovernment::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('operationsmanagmentgovernment::create');
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
        return view('operationsmanagmentgovernment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('operationsmanagmentgovernment::edit');
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
