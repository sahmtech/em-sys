<?php

namespace Modules\FollowUp\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Essentials\Entities\EssentialsWkProcedure;

class FollowupWorkerRequest extends Model
{
    use HasFactory;
    protected $table = 'followup_worker_requests';
    protected $guarded = ['id'];
    public function user()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
    public function createdUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function procedure()
    {
        return $this->belongsTo(EssentialsWkProcedure::class, 'type', 'type');
    }
   
   
    public function followupWorkerRequestProcess()
    {
        return $this->hasMany(followupWorkerRequestProcess::class, 'worker_request_id');
    }
}
