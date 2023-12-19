<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsAttendanceStatus extends Model
{
    protected $guarded = ['id'];

    public function attendance()
    {
        return $this->hasOne(\Modules\Essentials\Entities\EssentialsAttendance::class, 'status_id');
    }
}
