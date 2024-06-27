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
                'group_name' => __('operationsmanagmentgovernment::lang.operationsmanagmentgovernment'),
                'group_permissions' => [
                    [
                        'value' => 'operationsmanagmentgovernment.OperationsManagmentGovernment_dashboard',
                        'label' => __('operationsmanagmentgovernment::lang.OperationsManagmentGovernment_dashboard'),
                        'default' => false,
                    ],
                    [
                        'value' => 'operationsmanagmentgovernment.view_requests',
                        'label' => __('operationsmanagmentgovernment::lang.view_requests'),
                        'default' => false,
                    ],
                    [
                        'value' => 'operationsmanagmentgovernment.change_request_status',
                        'label' => __('operationsmanagmentgovernment::lang.change_request_status'),
                        'default' => false,
                    ],
                    [
                        'value' => 'operationsmanagmentgovernment.return_request',
                        'label' => __('operationsmanagmentgovernment::lang.return_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'operationsmanagmentgovernment.show_request',
                        'label' => __('operationsmanagmentgovernment::lang.show_request'),
                        'default' => false,
                    ],
                    [
                        'value' => 'operationsmanagmentgovernment.add_request',
                        'label' => __('operationsmanagmentgovernment::lang.add_request'),
                        'default' => false,
                    ],

                ]
            ]
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
