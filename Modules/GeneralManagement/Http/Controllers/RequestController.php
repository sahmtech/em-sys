<?php

namespace Modules\GeneralManagement\Http\Controllers;

use App\AccessRole;
use App\AccessRoleCompany;
use App\Company;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use App\User;;

use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use App\Utils\RequestUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\FollowUp\Entities\FollowupWorkerRequest;

use Carbon\Carbon;


class RequestController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    protected $moduleUtil;
    protected $requestUtil;

    protected $statuses;
    protected $statuses2;
    public function __construct(ModuleUtil $moduleUtil, RequestUtil $requestUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->requestUtil = $requestUtil;

        $this->statuses = [
            'approved' => [
                'name' => __('followup::lang.approved'),
                'class' => 'bg-green',
            ],
            'rejected' => [
                'name' => __('followup::lang.rejected'),
                'class' => 'bg-red',
            ],
            'pending' => [
                'name' => __('followup::lang.pending'),
                'class' => 'bg-yellow',
            ],
        ];
        $this->statuses2 = [
            'approved' => [
                'name' => __('followup::lang.approved'),
                'class' => 'bg-green',
            ],

            'pending' => [
                'name' => __('followup::lang.pending'),
                'class' => 'bg-yellow',
            ],
        ];
    }
    public function index()
    {

        $business_id = request()->session()->get('user.business_id');

        $can_change_status = auth()->user()->can('generalmanagement.change_request_status');
        $can_return_request = auth()->user()->can('generalmanagement.return_request');
        $can_show_request = auth()->user()->can('generalmanagement.view_request');
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)->pluck('id')->toArray();

        $departmentIdsForGeneralManagment = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%');
            })
            ->pluck('id')->toArray();

        $ownerTypes = ['employee', 'manager', 'worker'];
        return $this->requestUtil->getRequests($departmentIds, $ownerTypes, 'generalmanagement::requests.allRequest', $can_change_status, $can_return_request, $can_show_request, $departmentIdsForGeneralManagment);
    }

    public function escalateRequests()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type', 'admin')->pluck('id')->toArray();
        if (!$is_admin) {
            $userIds = [];
            $userIds = $this->moduleUtil->applyAccessRole();

            $companies_ids = [];
            $roles = auth()->user()->roles;
            foreach ($roles as $role) {

                $accessRole = AccessRole::where('role_id', $role->id)->first();

                if ($accessRole) {
                    $companies_ids = AccessRoleCompany::where('access_role_id', $accessRole->id)->pluck('company_id')->toArray();
                }
            }
        }
        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->Where('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%');
            })
            ->pluck('id')->toArray();


        $escalatedRequests = FollowupWorkerRequest::where('followup_worker_requests_process.sub_status', 'escalateRequest')->select([
            'followup_worker_requests.request_no',
            'followup_worker_requests_process.id as process_id',
            'followup_worker_requests.id',
            'followup_worker_requests.type as type',
            DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            'followup_worker_requests.created_at',
            'followup_worker_requests_process.status',
            'followup_worker_requests_process.status_note as note',
            'followup_worker_requests.reason',
            'essentials_wk_procedures.department_id as department_id',
            'users.id_proof_number',
            'essentials_wk_procedures.can_return',
            'users.assigned_to',
            'essentials_procedure_escalations.escalates_to'

        ])
            ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
            ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->join('essentials_procedure_escalations', 'essentials_procedure_escalations.procedure_id', '=', 'essentials_wk_procedures.id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->whereIn('essentials_procedure_escalations.escalates_to', $departmentIds)
            ->where('followup_worker_requests_process.status', 'pending')->where('users.status', '!=', 'inactive')->whereIn('users.id', $userIds);


        if (request()->ajax()) {


            return DataTables::of($escalatedRequests ?? [])

                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
                })
                ->editColumn('status', function ($row) {
                    $status = '';

                    if ($row->status == 'pending') {
                        $status = '<span class="label ' . $this->statuses[$row->status]['class'] . '">'
                            . __($this->statuses[$row->status]['name']) . '</span>';


                        $status = '<a href="#" class="change_status" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    } elseif (in_array($row->status, ['approved', 'rejected'])) {
                        $status = trans('followup::lang.' . $row->status);
                    }

                    return $status;
                })

                ->rawColumns(['status'])


                ->make(true);
        }
        $statuses = $this->statuses;
        return view('generalmanagement::requests.escalate_requests')->with(compact('statuses'));
    }
}