<?php

namespace Modules\GeneralManagement\Http\Controllers;

use App\AccessRole;
use App\AccessRoleBusiness;
use App\AccessRoleCompany;
use App\AccessRoleProject;
use App\Business;
use App\Company;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\User;;

use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use App\ContactLocation;
use Exception;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Sales\Entities\SalesProject;

class RequestController extends Controller
{
    protected $moduleUtil;
    protected $statuses;
    protected $statuses2;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
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
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;


        $crud_requests = auth()->user()->can('followup.crud_requests');
        if (!($is_admin || $crud_requests)) {
            //temp  abort(403, 'Unauthorized action.');
        }

        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();
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

        $requestsProcess = null;
        $requestsProcess = FollowupWorkerRequest::select([
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
            'users.assigned_to'
        ])
            ->leftJoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
            ->leftJoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->whereNull('followup_worker_requests_process.sub_status')
            ->whereIn('users.id', $userIds)
            ->groupBy('followup_worker_requests.id');



        if (request()->ajax()) {


            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
                })

                ->editColumn('status', function ($row) use ($business_id) {
                    try {
                        $statusClass = $this->statuses[$row->status]['class'];
                        $statusName = $this->statuses[$row->status]['name'];
                        $status = $row->status;
                       

                        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
                            ->where(function ($query) {
                                $query->where('name', 'like', '%تنفيذ%')
                                    ->orWhere('name', 'like', '%مجلس%')
                                    ->orWhere('name', 'like', '%عليا%');
                            })
                            ->pluck('id')->toArray();

                        if ($departmentIds) {
                        

                            if (in_array($row->status, ['approved', 'rejected'])) {
                                $status = trans('followup::lang.' . $row->status);
                            } elseif ($row->status == 'pending' && in_array($row->department_id, $departmentIds)) {
                                $status = '<span class="label ' . $statusClass . '">' . $statusName . '</span>';
                                $status = '<a href="#" class="change_status" data-request-id="' . $row->process_id . '" data-orig-value="' . $row->status . '" data-status-name="' . $statusName . '"> ' . $status . '</a>';
                            } elseif ($row->status == 'pending' && !in_array($row->department_id, $departmentIds)) {
                                $status = trans('followup::lang.under_process');
                            }
                        } else {
                            $status = trans('followup::lang.' . $row->status);
                        }

                        return $status;
                    } catch (\Exception $e) {
                        return '';
                    }
                })

                ->rawColumns(['status'])


                ->make(true);
        }

        $statuses = $this->statuses;


        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
            ->where(function ($query) {
                $query->where('name', 'like', '%تنفيذ%')
                    ->orWhere('name', 'like', '%مجلس%')
                    ->orWhere('name', 'like', '%عليا%');
            })
            ->pluck('id')->toArray();


        return view('generalmanagement::requests.allRequest')->with(compact('statuses', 'departmentIds'));
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
    public function escalateRequests()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();
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
        $escalatedRequests = FollowupWorkerRequest::where('sub_status', 'escalateRequest')->select([
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
            'users.assigned_to'

        ])
            ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
            ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->where('followup_worker_requests_process.status', 'pending')->whereIn('users.id', $userIds);

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


    public function changeStatus(Request $request)
    {
        try {
            $input = $request->only(['status', 'reason', 'note', 'request_id']);

            $requestProcesses = FollowupWorkerRequestProcess::where('worker_request_id', $input['request_id'])->where('status', 'pending')->get();

            foreach ($requestProcesses as $requestProcess) {

                $requestProcess->status = $input['status'];
                $requestProcess->reason = $input['reason'] ?? null;
                $requestProcess->status_note = $input['note'] ?? null;
                $requestProcess->updated_by = auth()->user()->id;

                $requestProcess->save();
            }


            $mainRequest = FollowupWorkerRequest::find($input['request_id']);
            $mainRequest->status = $input['status'];
            $mainRequest->save();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
}
