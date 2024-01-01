<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Modules\FollowUp\Entities\FollowupWorkerRequestProcess;

class EscalatePendingRequests extends Command
{
    public function __construct()
    {
        parent::__construct();
    }


    protected $signature = 'escalate:pending-requests';
    protected $description = 'Escalate pending requests after 24 hours';


    public function handle()
    {

        $pendingRequests = FollowupWorkerRequestProcess::where('status', 'pending')
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, NOW()) >= 2')
            ->get();
      
            // $requests = FollowupWorkerRequestProcess::join('essentials_wk_procedures', 'followup_worker_requests_process.procedure_id', '=', 'essentials_wk_procedures.id')
            // ->whereRaw('TIMESTAMPDIFF(HOUR, followup_worker_requests_process.created_at, NOW()) >= essentials_wk_procedures.escalates_after')
            // ->get();
    

        foreach ($pendingRequests as $request) {
            $this->escalateRequest($request);
        }
    }


    private function escalateRequest($request)
    {

        $request->update([
            'is_escalated' => '1',
        ]);
    }
}
