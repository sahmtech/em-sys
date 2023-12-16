<?php

namespace Modules\FollowUp\Entities;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Essentials\Entities\EssentialsWkProcedure;

class FollowupRequestsAttachment extends Model
{
    use HasFactory;
    protected $table = 'followup_requests_attachments';
    protected $guarded = ['id'];


    public function followupWorkerRequest()
    {
        return $this->belongsTo(FollowupWorkerRequest::class, 'request_id');
    }

}