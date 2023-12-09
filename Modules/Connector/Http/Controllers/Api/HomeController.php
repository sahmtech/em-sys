<?php

namespace Modules\Connector\Http\Controllers\Api;

use App\Business;
use App\Utils\ModuleUtil;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Connector\Transformers\CommonResource;
use Modules\Essentials\Entities\EssentialsUserShift;
use Modules\Essentials\Entities\ToDo;
use Modules\FollowUp\Entities\FollowupWorkerRequest;

/**
 * @group Taxonomy management
 * @authenticated
 *
 * APIs for managing taxonomies
 */
class HomeController extends ApiController
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function home()
    {


        try {
            $user = Auth::user();
            $business_id = $user->business_id;
            $business = Business::where('id', $business_id)->first();
            $shift = EssentialsUserShift::where('user_id', $user->id)->first()->shift;
            $lastRequest = FollowupWorkerRequest::select([
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


            ])
                ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
                ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
                ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')
                ->where('users.id', $user->id)->latest('followup_worker_requests.created_at')
                ->first();


            $todo = ToDo::where('business_id', $business_id)
                ->with(['assigned_by'])
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->select('*')->latest('created_at')
                ->first();

            $lastTask = [
                'id' => $todo->id,
                'business_id' => $todo->business_id,
                'task' => $todo->task,
                'date' => $todo->date,
                'end_date' => $todo->end_date,
                'task_id' => $todo->task_id,
                'description' => $todo->description,
                'status' => $todo->status,
                'estimated_hours' => $todo->estimated_hours,
                'priority' => $todo->priority,
                'assigned_by' => $todo->assigned_by->first_name . ' ' . $todo->assigned_by->last_name,
            ];


            $res = [
                'new_notifications' => 0,
                'work_day_start' => Carbon::parse($shift->start_time)->format('h:i A'),
                'work_day_end' => Carbon::parse($shift->end_time)->format('h:i A'),
                'business_name' => $business->name,
                'request' => $lastRequest,
                'task' => $lastTask,

            ];


            return new CommonResource($res);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            return $this->otherExceptions($e);
        }
    }
}
