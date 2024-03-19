<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLeaveBalance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function leave_type()
    {
        return $this->belongsTo(\Modules\Essentials\Entities\EssentialsLeaveType::class, 'essentials_leave_type_id');
    }
}
