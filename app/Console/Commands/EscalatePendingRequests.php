<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Essentials\Entities\EssentialsDepartment;
use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;

class EscalatePendingRequests extends Command
{
    public function __construct()
    {
        parent::__construct();
    }


    protected $signature = 'escalate:pending-requests';
    protected $description = 'Escalate pending requests after hours';

    public function handle()
    {
        $requests = FollowupWorkerRequestProcess::join('essentials_wk_procedures', 'followup_worker_requests_process.procedure_id', '=', 'essentials_wk_procedures.id')
        ->join('essentials_procedure_escalations', 'essentials_procedure_escalations.procedure_id', '=', 'essentials_wk_procedures.id')
            ->select('followup_worker_requests_process.id as request_id', 'essentials_wk_procedures.id as procedure_id','essentials_wk_procedures.department_id as department')
            ->whereNull('followup_worker_requests_process.sub_status')
            ->whereRaw('TIMESTAMPDIFF(HOUR, followup_worker_requests_process.created_at, NOW()) >= essentials_procedure_escalations.escalates_after')
            ->get();

        foreach ($requests as $request) {
          
            $requestProcess = FollowupWorkerRequestProcess::find($request->request_id);
            if ($requestProcess->is_escalated == '0') {
                $requestProcess->update(['is_escalated' => 1]);
                $nameDepartment = EssentialsDepartment::where('id', $request->department)->first()->name;
                $escalateRequest = new FollowupWorkerRequestProcess();
                $escalateRequest->worker_request_id = $requestProcess->worker_request_id;
                $escalateRequest->procedure_id = $requestProcess->procedure_id;
                $escalateRequest->status = 'pending';
                $escalateRequest->sub_status = 'escalateRequest';
                $escalateRequest->status_note = __('followup::lang.escalated_from') . " " . $nameDepartment;
                $escalateRequest->save();
            }
        }
    }
}
