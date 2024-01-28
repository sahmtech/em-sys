<?php

namespace Modules\CEOManagment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use App\AccessRole;
use App\AccessRoleCompany;
use App\Company;
use App\User;;

use Yajra\DataTables\Facades\DataTables;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Carbon\Carbon;

use Modules\Sales\Entities\SalesProject;
class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;
    protected $statuses;
    protected $statuses2;
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

        $can_change_status = auth()->user()->can('ceomanagment.change_request_status');
        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
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
   
        // ->leftJoin(DB::raw('(SELECT followup_worker_requests_process.id as pro_id, followup_worker_requests.id as id, MAX(created_at) as max_created_at FROM followup_worker_requests_process GROUP BY id) as latest_table1'), 'latest_table1.id', '=', 'followup_worker_requests.id')
        // ->leftJoin('followup_worker_requests_process', function($join) {
        //     $join->on('followup_worker_requests_process.id', '=', 'latest_table1.id')
        //          ->on('followup_worker_requests_process.created_at', '=', 'latest_table1.max_created_at');
        // })
            ->leftJoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
            ->leftJoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
            ->whereNull('followup_worker_requests_process.sub_status')->where('users.status', '!=', 'inactive')
            ->whereIn('users.id', $userIds)->groupBy('id');
            



        if (request()->ajax()) {


            return DataTables::of($requestsProcess ?? [])

                ->editColumn('created_at', function ($row) {


                    return Carbon::parse($row->created_at);
                })

                ->editColumn('status', function ($row) use ($business_id,$is_admin,$can_change_status) {
                    try {
                        $statusClass = $this->statuses[$row->status]['class'];
                        $statusName = $this->statuses[$row->status]['name'];
                        $status = $row->status;
                       

                        $departmentIds = EssentialsDepartment::where('business_id', $business_id)
                            ->where(function ($query) {
                                $query->where('name', 'like', '%تنفيذ%');
                                    // ->orWhere('name', 'like', '%مجلس%')
                                    // ->orWhere('name', 'like', '%عليا%');
                            })
                            ->pluck('id')->toArray();

                        if ($departmentIds) {
                        

                            if (in_array($row->status, ['approved', 'rejected'])) {
                                $status = trans('followup::lang.' . $row->status);
                            } elseif ($row->status == 'pending' && in_array($row->department_id, $departmentIds)) {
                                if ($is_admin || $can_change_status) {
                                $status = '<span class="label ' . $statusClass . '">' . $statusName . '</span>';
                                $status = '<a href="#" class="change_status" data-request-id="' . $row->process_id . '" data-orig-value="' . $row->status . '" data-status-name="' . $statusName . '"> ' . $status . '</a>';
                                }
                                else{
                                    $status = trans('followup::lang.' . $row->status);
                                }
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
                $query->where('name', 'like', '%تنفيذ%');
                    // ->orWhere('name', 'like', '%مجلس%')
                    // ->orWhere('name', 'like', '%عليا%');
            })
            ->pluck('id')->toArray();


        return view('ceomanagment::requests.allRequest')->with(compact('statuses', 'departmentIds'));
    }
    
    public function escalateRequests()
    {
        $business_id = request()->session()->get('user.business_id');
        $is_admin = auth()->user()->hasRole('Admin#1') ? true : false;
        $companies_ids = Company::pluck('id')->toArray();
        $userIds = User::whereNot('user_type','admin')->pluck('id')->toArray();
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
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->where('users.status', '!=', 'inactive')
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
        return view('ceomanagment::requests.escalate_requests')->with(compact('statuses'));
    }


    public function changeStatus(Request $request)
    {


        try {
            $input = $request->only(['status', 'reason', 'note', 'request_id']);
            $first_step = FollowupWorkerRequestProcess::where('worker_request_id', $input['request_id'])->where('status', 'pending')->where('sub_status', null)->first();
            $requestProcess = FollowupWorkerRequestProcess::where('worker_request_id', $input['request_id'])->where('status', 'pending')->where('sub_status', null)->get()[1];

            $procedure = EssentialsWkProcedure::where('id', $requestProcess->procedure_id)->first()->can_reject;


            if ($procedure == 0 && $input['status'] == 'rejected') {
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.this_department_cant_reject_this_request'),
                ];
                return $output;
            }

            $requestProcess->status = $input['status'];
            $requestProcess->reason = $input['reason'] ?? null;
            $requestProcess->status_note = $input['note'] ?? null;
            $requestProcess->updated_by = auth()->user()->id;

            $requestProcess->save();

            if ($input['status'] == 'approved') {
                $procedure = EssentialsWkProcedure::find($requestProcess->procedure_id);


                if ($procedure && $procedure->end == 1) {
                    $requestProcess->followupWorkerRequest->status = 'approved';
                    
                    $requestProcess->followupWorkerRequest->save();
                    $first_step->status = 'approved';
                    $first_step->save();
                } else {
                    $nextDepartmentId = $procedure->next_department_id;
                    $nextProcedure = EssentialsWkProcedure::where('department_id', $nextDepartmentId)
                        ->where('type', $requestProcess->followupWorkerRequest->type)
                        ->first();

                    if ($nextProcedure) {
                        $newRequestProcess = new FollowupWorkerRequestProcess();
                        $newRequestProcess->worker_request_id = $requestProcess->worker_request_id;
                        $newRequestProcess->procedure_id = $nextProcedure->id;
                        $newRequestProcess->status = 'pending';
                        $newRequestProcess->save();
                    }
                }
            }
            if ($input['status'] == 'rejected') {
                $requestProcess->followupWorkerRequest->status = 'rejected';
                $first_step->status = 'rejected';
                $first_step->save();
                $requestProcess->followupWorkerRequest->save();
            }

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

    public function viewRequest($id)
    {
        
        $request = FollowupWorkerRequest::with([
            'user', 'createdUser', 'followupWorkerRequestProcess.procedure.department', 'attachments'
        ])->where('id', $id)->first();

        if (!$request) {
            return response()->json(['error' => 'Request not found'], 404);
        }


        $requestInfo = [
            'id' => $request->id,
            'request_no' => $request->request_no,
            'status' => trans("followup::lang.{$request->status}"),
            'type' => trans("followup::lang.{$request->type}"),
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ];
        $workflow = [];
        $currentStep = EssentialsWkProcedure::where('id', $request->followupWorkerRequestProcess[0]->procedure_id)->first();
   
        while ($currentStep && !$currentStep->end ) {

            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => optional(DB::table('essentials_departments')->where('id', $currentStep->next_department_id)->first())->name,
            ];

            $currentStep = EssentialsWkProcedure::where('type', $request->type)
                ->where('department_id', $currentStep->next_department_id)
                ->first();
        }

        if ($currentStep && $currentStep->end == 1) {
            $workflow[] = [
                'id' => $currentStep->id,
                'process_id' => $this->getProcessIdForStep($request, $currentStep),
                'status' => $this->getProcessStatusForStep($request, $currentStep),
                'department' => optional(DB::table('essentials_departments')->where('id', $currentStep->department_id)->first())->name,
                'next_department' => null,
            ];
        };

        $attachments = null;
        if ($request->attachments) {

            $attachments = $request->attachments->map(function ($attachments) {
                return [
                    'request_id' => $attachments->request_id,
                    'file_path' => $attachments->file_path,
                    'created_at' => $attachments->created_at,
                ];
            });
        }

        $userInfo = [
            'worker_id' => $request->user->id,
            'user_type' => trans("followup::lang.{$request->user->user_type}"),
            'nationality' => optional(DB::table('essentials_countries')->where('id', $request->user->nationality_id)->first())->nationality,
            'assigned_to' =>  $this->getContactLocation($request->user->assigned_to),
            'worker_full_name' => $request->user->first_name . ' ' . $request->user->last_name,
            'id_proof_number' => $request->user->id_proof_number,
            'contract_end_date' => optional(DB::table('essentials_employees_contracts')->where('employee_id', $request->user->id)->first())->contract_end_date,
            'eqama_end_date' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->user->id)->where('type', 'residence_permit')->first())->expiration_date,
            'passport_number' => optional(DB::table('essentials_official_documents')->where('employee_id', $request->user->id)->where('type', 'passport')->first())->number,

        ];

        $createdUserInfo = [
            'created_user_id' => $request->createdUser->id,
            'user_type' => $request->createdUser->user_type,
            'nationality_id' => $request->createdUser->nationality_id,
            'created_user_full_name' => $request->createdUser->first_name . ' ' . $request->createdUser->last_name,
            'id_proof_number' => $request->createdUser->id_proof_number,

        ];

        $followupProcesses = [];
        foreach ($request->followupWorkerRequestProcess as $process) {
            $processInfo = [
                'id' => $process->id,
                'status' => trans("followup::lang.{$process->status}"),
                'procedure_id' => $process->procedure_id,
                'is_returned' => $process->is_returned,
                'updated_by' =>  $this->getFullName($process->updated_by),
                'reason' => $process->reason,
                'status_note' => $process->status_note,
                'department' => [
                    'id' => $process->procedure->department->id,
                    'name' => $process->procedure->department->name,
                ],

            ];
            $followupProcesses[] = $processInfo;
        }

        $result = [

            'request_info' => $requestInfo,
            'user_info' => $userInfo,
            'created_user_info' => $createdUserInfo,
            'followup_processes' => $followupProcesses,
            'workflow' => $workflow,
            'attachments' => $attachments,
        ];

        return response()->json($result);
    }
    private function getFullName($userId)
    {
        $user = User::find($userId);

        if ($user) {
            return $user->first_name . ' ' . $user->last_name;
        }

        return null;
    }
    private function getContactLocation($id)
    {
        $contact = SalesProject::find($id);

        if ($contact) {
            return $contact->name;
        }

        return null;
    } 
    private function getProcessIdForStep($request, $step)
    {
        return optional($request->followupWorkerRequestProcess->where('procedure_id', $step->id)->first())->id;
    }
    private function getProcessStatusForStep($request, $step)
    {
        return optional($request->followupWorkerRequestProcess->where('procedure_id', $step->id)->first())->status;
    }
}
