<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetGroup extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function timesheetUsers()
    {
        return $this->hasMany(TimesheetUser::class, 'timesheet_group_id', 'id');
    }
}