<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\CEOManagment\Entities\RequestsType;
use Modules\CEOManagment\Entities\WkProcedure;
use Modules\Essentials\Entities\EssentialsLeaveType;

class Request extends Model
{

    protected $table = 'requests';
    protected $guarded = ['id'];

    public function related_to_user()
    {
        return $this->belongsTo(User::class, 'related_to');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function procedure()
    {
        return $this->belongsTo(WkProcedure::class, 'request_type_id', 'request_type_id');
    }


    public function process()
    {
        return $this->hasMany(RequestProcess::class, 'request_id');
    }

    public function attachments()
    {

        return $this->hasMany(RequestAttachment::class, 'request_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(EssentialsLeaveType::class, 'essentials_leave_type_id');
    }
    public function requestType()
    {
        return $this->belongsTo(RequestsType::class, 'request_type_id');
    }
}
