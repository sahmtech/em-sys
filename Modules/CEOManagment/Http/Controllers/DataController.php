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
                        'value' => 'ceomanagment.view_CEO_requests',
                        'label' => __('ceomanagment::lang.view_CEO_requests'),
                        'default' => false,
                    ],
                    // [
                    //     'value' => 'ceomanagment.view_CEO_escalate_requests',
                    //     'label' => __('ceomanagment::lang.view_CEO_escalate_requests'),
                    //     'default' => false,
                    // ],
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
                        'value' => 'ceomanagment.view_timesheet_wk',
                        'label' => __('ceomanagment::lang.view_timesheet_wk'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.view_payroll_checkpoint',
                        'label' => __('essentials::lang.view_payroll_checkpoint'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.confirm_payroll_checkpoint',
                        'label' => __('essentials::lang.confirm_payroll_checkpoint'),
                        'default' => false,
                    ],
                    [
                        'value' => 'ceomanagment.show_payroll_checkpoint',
                        'label' => __('essentials::lang.show_payroll_checkpoint'),
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
