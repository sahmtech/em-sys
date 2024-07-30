<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use App\Request as UserRequest;
use Modules\CEOManagment\Entities\RequestsType;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Yajra\DataTables\Facades\DataTables;
use App\Company;

class EssentialsCancelContractsController extends Controller
{
    protected $moduleUtil;


    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {

        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;

        $userIds = User::whereNot('user_type', 'admin')->whereNot('user_type', 'customer')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();
        }

        $main_reasons = DB::table('essentails_reason_wishes')->pluck('reason', 'id');
        $sub_reasons = DB::table('essentails_reason_wishes')->pluck('sub_reason', 'id');
        $companies = Company::all()->pluck('name', 'id');
        $requestsProcess = null;

        $types = RequestsType::where('type', 'cancleContractRequest')->pluck('id')->toArray();
        $requestsProcess = UserRequest::select([
            'requests.request_no', 'requests.id', 'requests.request_type_id', 'requests.created_at', 'requests.status',

            'requests.contract_main_reason_id as main_reason',  'requests.note as note', 'requests.contract_sub_reason_id as sub_reason',

            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"), 'users.id_proof_number', 'users.company_id',

            'users.status as userStatus', 'essentials_employees_contracts.contract_end_date as contract_end_date'

        ])

            ->whereIn('requests.request_type_id', $types)->where('requests.status', 'approved')
            ->leftJoin('users', 'users.id', '=', 'requests.related_to')
            ->leftJoin('essentials_employees_contracts', 'essentials_employees_contracts.employee_id', '=', 'users.id')
            ->whereIn('requests.related_to', $userIds);




        if (request()->ajax()) {
            return DataTables::of($requestsProcess ?? [])
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at);
                })
                ->editColumn('company_id', function ($row) use ($companies) {
                    if ($row->company_id) {
                        return $companies[$row->company_id];
                    }
                })
                ->editColumn('main_reason', function ($row) use ($main_reasons) {
                    if ($row->main_reason) {
                        return $main_reasons[$row->main_reason];
                    } else {
                        return '';
                    }
                })
                ->editColumn('sub_reason', function ($row) use ($sub_reasons) {
                    if ($row->sub_reason) {
                        return $sub_reasons[$row->sub_reason];
                    } else {
                        return '';
                    }
                })
                ->rawColumns(['sub_reason', 'main_reason'])
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
            $userRequest = UserRequest::find($id);
            if (!$userRequest) {
                return ['success' => false, 'msg' => __('messages.not_found')];
            }

            $user = User::find($userRequest->related_to);
            if (!$user) {
                return ['success' => false, 'msg' => __('messages.user_not_found')];
            }

            $user->update([
                'status' => 'inactive',
                'allow_login' => '0',
                'updated_by' => auth()->user()->id
            ]);

            $appointment = EssentialsEmployeeAppointmet::where('employee_id', $userRequest->related_to)->where('is_active', '1')->first();
            $appointment->update([
                'is_active' => '0'
            ]);

            $appointment = EssentialsAdmissionToWork::where('employee_id', $userRequest->related_to)->where('is_active', '1')->first();
            $appointment->update([
                'is_active' => '0'
            ]);


            $userRequest->update([
                'is_done' => '1'
            ]);
            $output = [
                'success' => true,
                'msg' => __('lang_v1.finished_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
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
