<?php

namespace Modules\InternationalRelations\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\InternationalRelations\Entities\IrProposedLabor;
use Modules\InternationalRelations\Entities\IrVisaCard;

class DashboardController extends Controller
{
    protected $moduleUtil;



    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }
        $business_id = request()->session()->get('user.business_id');
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where('name', 'LIKE', '%دولي%')
            ->pluck('id')->toArray();
        $requestsProcess_count = FollowupWorkerRequest::where('followup_worker_requests_process.status', 'pending')->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
            ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->whereIn('department_id', $departmentIds)
            ->whereIn('followup_worker_requests.worker_id', $userIds)->where('followup_worker_requests_process.sub_status', null)->count();

        $operations_count = DB::table('sales_orders_operations')
            ->join('contacts', 'sales_orders_operations.contact_id', '=', 'contacts.id')
            ->join('sales_contracts', 'sales_orders_operations.sale_contract_id', '=', 'sales_contracts.id')
            ->where('sales_orders_operations.operation_order_type', '=', 'External')->count();

        $proposed_workers_count = IrProposedLabor::with('transactionSellLine.service', 'agency')->where('interviewStatus', null)->count();
        $accepted_workers_count = IrProposedLabor::with('transactionSellLine.service', 'agency')->where('interviewStatus', 'acceptable')->where('arrival_status', '!=', 1)->count();
        $visaCards_count = IrVisaCard::with(
            'operationOrder.contact',
            'operationOrder.salesContract.transaction.sell_lines.agencies',
            'operationOrder.salesContract.transaction.sell_lines.service'
        )->count();
        return view('internationalrelations::dashboard.IR_dashboard', compact('requestsProcess_count', 'operations_count', 'proposed_workers_count', 'accepted_workers_count', 'visaCards_count'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('internationalrelations::create');
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
        return view('internationalrelations::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('internationalrelations::edit');
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