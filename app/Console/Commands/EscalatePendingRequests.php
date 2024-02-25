<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Essentials\Entities\EssentialsDepartment;
use App\Request as UserRequest;
use App\RequestProcess;

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
        $requests = RequestProcess::join('wk_procedures', 'request_processes.procedure_id', '=', 'wk_procedures.id')
            ->join('procedure_escalations', 'procedure_escalations.procedure_id', '=', 'wk_procedures.id')
            ->select('request_processes.id as request_id', 'wk_procedures.id as procedure_id', 'wk_procedures.department_id as department')
            ->whereNull('request_processes.sub_status')
            ->whereRaw('TIMESTAMPDIFF(HOUR, request_processes.created_at, NOW()) >= procedure_escalations.escalates_after')
            ->get();

        foreach ($requests as $request) {
            $requestProcess = RequestProcess::find($request->request_id);
            if ($requestProcess->is_escalated == '0') {

                $requestProcess->update(['is_escalated' => 1]);
                $nameDepartment = EssentialsDepartment::where('id', $request->department)->first()->name;
                $escalateRequest = new RequestProcess();
                $escalateRequest->request_id = $requestProcess->request_id;
                $escalateRequest->procedure_id = $requestProcess->procedure_id;
                $escalateRequest->status = 'pending';
                $escalateRequest->sub_status = 'escalateRequest';
                $escalateRequest->note = __('followup::lang.escalated_from') . " " . $nameDepartment;
                $escalateRequest->save();
            }
        }
    }
}
