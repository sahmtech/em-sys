<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Yajra\DataTables\Facades\DataTables;

class EssentialsCancelContractsController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
      
    }
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $main_reasons = DB::table('essentails_reason_wishes')->pluck('reason', 'id');
        $sub_reasons = DB::table('essentails_reason_wishes')->pluck('sub_reason', 'id');

     

        $requestsProcess = null;

        $requestsProcess = FollowupWorkerRequest::select([
            'followup_worker_requests.request_no',
            'followup_worker_requests.id',
            'followup_worker_requests.status as status',
            'followup_worker_requests.type as type',
            'followup_worker_requests.created_at',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            'followup_worker_requests.contract_main_reason_id as main_reason',
            'followup_worker_requests.note as note',
            'users.id_proof_number',
            'followup_worker_requests.contract_sub_reason_id as sub_reason',
            'users.status as userStatus','essentials_employees_contracts.contract_end_date as contract_end_date'
            
        ])
           
            ->where('type','cancleContractRequest')->where('followup_worker_requests.status','approved')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->leftJoin('essentials_employees_contracts', 'essentials_employees_contracts.employee_id', '=', 'users.id')
            ->whereIn('followup_worker_requests.worker_id', $userIds);




        if (request()->ajax()) {
            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('main_reason', function ($row) use($main_reasons) {
                    return $main_reasons[$row->main_reason];
                })
                ->editColumn('sub_reason', function ($row) use($sub_reasons) {
                    return $sub_reasons[$row->sub_reason];
                })
                ->rawColumns(['sub_reason','main_reason'])
                ->make(true);
        }

        return view('essentials::requests.cancel_contract_requests');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('essentials::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
  

    public function finish_contract_procedure($id)
{
    try {
        $followupWorkerRequest = FollowupWorkerRequest::find($id);
        if (!$followupWorkerRequest) {
            return ['success' => false, 'msg' => __('messages.not_found')];
        }

        $user = User::find($followupWorkerRequest->worker_id);
        if (!$user) {
            return ['success' => false, 'msg' => __('messages.user_not_found')];
        }
        error_log($user);
        $user->update([
            'status' => 'inactive',
            'allow_login' => '0'
        ]);

        $output = [
            'success' => true,
            'msg' => __('lang_v1.finished_success'),
        ];
    } catch (\Exception $e) {
        \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
        $output = ['success' => false, 'msg' => __('messages.something_went_wrong')];
    }

    return $output;
}

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('essentials::edit');
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
