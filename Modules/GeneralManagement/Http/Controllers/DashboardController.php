<?php
namespace Modules\GeneralManagement\Http\Controllers;

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
        // dd("*");
        $counts                 = $this->requestUtil->getCounts('generalmanagement');
        $today_requests         = $counts->today_requests;
        $pending_requests       = $counts->pending_requests;
        $pending_requests_sales = $counts->pending_requests_sales;

        $pending_requests_personnel_affairs = $counts->pending_requests_personnel_affairs;

        $pending_requests_ceo                 = $counts->pending_requests_ceo;
        $pending_requests_operations_business = $counts->pending_requests_operations_business;

        $pending_requests_housing_transport = $counts->pending_requests_housing_transport;

        $pending_requests_international_relations = $counts->pending_requests_international_relations;
        $pending_requests_hr                      = $counts->pending_requests_hr;
        $pending_requests_hr_applications         = $counts->pending_requests_hr_applications;

        $pending_requests_legal_affairs        = $counts->pending_requests_legal_affairs;
        $pending_requests_government_relations = $counts->pending_requests_government_relations;

        $completed_requests = $counts->completed_requests;
        $all_requests       = $counts->all_requests;
        return view('generalmanagement::dashboard.generalmanagement_dashboard')
            ->with(compact('today_requests', 'pending_requests', 'completed_requests', 'pending_requests_personnel_affairs', 'pending_requests_hr_applications', 'pending_requests_sales', 'pending_requests_government_relations', 'pending_requests_legal_affairs', 'all_requests', 'pending_requests_hr', 'pending_requests_ceo', 'pending_requests_housing_transport', 'pending_requests_operations_business', 'pending_requests_international_relations'));
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
