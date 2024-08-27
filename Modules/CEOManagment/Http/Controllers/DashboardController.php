<?php

namespace Modules\CEOManagment\Http\Controllers;

use App\Utils\RequestUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    protected $requestUtil;
    public function __construct(RequestUtil $requestUtil)
    {
        $this->requestUtil = $requestUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $counts =  $this->requestUtil->getCounts('ceomanagment');
        $today_requests =   $counts->today_requests;
        $pending_requests =   $counts->pending_requests;
        $completed_requests =   $counts->completed_requests;
        $all_requests =   $counts->all_requests;
        return view('ceomanagment::dashboard.ceo_dashboard')
            ->with(compact('today_requests', 'pending_requests', 'completed_requests', 'all_requests'));
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
