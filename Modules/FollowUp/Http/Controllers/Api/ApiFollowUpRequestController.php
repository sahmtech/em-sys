<?php

namespace Modules\FollowUp\Http\Controllers\Api;

use App\Business;
use App\User;
use App\ContactLocation;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Modules\Essentials\Entities\EssentialsWkProcedure;
use Modules\FollowUp\Entities\FollowupWorkerRequest;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Connector\Http\Controllers\Api\ApiController;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\UserLeaveBalance;

class ApiFollowUpRequestController extends ApiController
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

    public function getMyLeaves()
    {



        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $user = Auth::user();


            $requests = FollowupWorkerRequest::select([
                'followup_worker_requests.request_no',
                'followup_worker_requests.id',
                'followup_worker_requests.type as type',
                'followup_worker_requests.essentials_leave_type_id as leave_type_id',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.created_at',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.status_note as note',
                'followup_worker_requests.reason',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',

                'essentials_wk_procedures.department_id as department_id',
                'users.id_proof_number',


            ])
                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                ->where('users.id', $user->id)->get();

            $business_id = $user->business_id;
            // $leave_types = EssentialsLeaveType::where('business_id', $business_id)
            //     ->select(['id', 'leave_type', 'duration', 'max_leave_count',])->get();

            $leaves = UserLeaveBalance::with(['leave_type' => function ($query) use ($business_id) {
                $query->where('business_id', $business_id)
                    ->select(['id', 'leave_type', 'duration', 'max_leave_count']);
            }])->where('user_id', $user->id)->get();

            $statistics = [];
            foreach ($leaves as  $leave) {
                $count =  $requests->where('leave_type_id', $leave->essentials_leave_type_id)->count();
                $statistics[] = [
                    'leave_type_id' => $leave->essentials_leave_type_id,
                    'leave_type' => $leave->leave_type->leave_type,
                    'max_leave_count' => (int)($leave->amount),
                    'taken_leave_count' => $count,
                ];
            }
            $requestsArr = [];
            foreach ($requests as  $request) {

                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->start_date);
                error_log($startDate);
                error_log($endDate);
                $duration = $startDate->diffInDays($endDate);
                $requestsArr[] = [
                    "id" => $request->id,
                    "request_no" => $request->request_no,
                    "duration" => $duration,
                    "type" => $request->type,
                    "user" => $request->user,
                    "status" => $request->status,
                    "note" => $request->note,
                    "reason" => $request->reason,
                    "department_id" => $request->department_id,
                    "id_proof_number" => $request->id_proof_number,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ];
            }







            $res = [
                'statistics' => $statistics,
                'requests' =>  $requestsArr
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }

    public function getMyRequests()
    {



        if (!$this->moduleUtil->isModuleInstalled('Essentials')) {
            //temp  abort(403, 'Unauthorized action.');
        }

        try {
            $status_filter = request()->status;
            $user = Auth::user();


            $requests = FollowupWorkerRequest::select([
                'followup_worker_requests.request_no',
                'followup_worker_requests.id',
                'followup_worker_requests.type as type',
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'followup_worker_requests.created_at',
                'followup_worker_requests_process.status',
                'followup_worker_requests_process.status_note as note',
                'followup_worker_requests.reason',
                'essentials_wk_procedures.department_id as department_id',
                'users.id_proof_number',
                'followup_worker_requests.start_date',
                'followup_worker_requests.end_date',


            ])
                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                ->where('users.id', $user->id);

            if ($status_filter) {

                $requests = $requests->where('followup_worker_requests_process.status', $status_filter);
            }


            $res = [
                'requests' =>  $requests->get(),
            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
}
