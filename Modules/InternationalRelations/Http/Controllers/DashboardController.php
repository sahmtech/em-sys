<?php

namespace Modules\InternationalRelations\Http\Controllers;

use App\User;
use App\Request as UserRequest;
use App\Utils\ModuleUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
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
        $requestsProcess_count = UserRequest::where('request_processes.status', 'pending')->leftjoin('request_processes', 'request_processes.request_id', '=', 'requests.id')
            ->leftjoin('wk_procedures', 'wk_procedures.id', '=', 'request_processes.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')->whereIn('wk_procedures.department_id', $departmentIds)
            ->whereIn('requests.related_to', $userIds)->whereNull('request_processes.sub_status')->count();

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