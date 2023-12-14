<?php

namespace Modules\FollowUp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Essentials\Entities\EssentialsWkProcedure;

class FollowupWorkerRequestProcess extends Model
{
    use HasFactory;
    protected $table = 'followup_worker_requests_process';
    protected $guarded = ['id'];


    public function followupWorkerRequest()
    {
        return $this->belongsTo(FollowupWorkerRequest::class, 'worker_request_id');
    }

    public function procedure()
    {
        return $this->belongsTo(EssentialsWkProcedure::class, 'procedure_id');
    }
}