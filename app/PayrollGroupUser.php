<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayrollGroupUser extends Model
{
    protected $guarded = ['id'];

    public function payrollGroup()
    {
        return $this->belongsTo(PayrollGroup::class, 'payroll_group_id');
    }
}
