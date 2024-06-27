<?php

namespace Modules\InformationTechnologyManagment\Http\Controllers;

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
                'group_name' => __('ceomanagment::lang.informationtechnologymanagment'),
                'group_permissions' => [
                    [
                        'value' => 'informationtechnologymanagment.InformationTechnology_dashboard',
                        'label' => __('informationtechnologymanagment::lang.InformationTechnology_dashboard'),
                        'default' => false,
                    ],


                ]
            ]
        ];
    }
    public function index()
    {
        return view('informationtechnologymanagment::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('informationtechnologymanagment::create');
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
        return view('informationtechnologymanagment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('informationtechnologymanagment::edit');
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
