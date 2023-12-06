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
use Modules\FollowUp\Entities\followupWorkerRequest;
use Modules\FollowUp\Entities\followupWorkerRequestProcess;
use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Carbon\Carbon;
use Modules\Essentials\Entities\EssentialsEmployeesContract;

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


    public function requests()
    {




        $requestsProcess = FollowupWorkerRequest::select([
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
            'users.assigned_to'

        ])
            ->leftjoin('followup_worker_requests_process', 'followup_worker_requests_process.worker_request_id', '=', 'followup_worker_requests.id')
            ->leftjoin('essentials_wk_procedures', 'essentials_wk_procedures.id', '=', 'followup_worker_requests_process.procedure_id')
            ->leftJoin('users', 'users.id', '=', 'followup_worker_requests.worker_id')->where('user_type', 'worker')->get();
    }

}
