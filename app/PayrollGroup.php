<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayrollGroup extends Model
{
    protected $guarded = ['id'];

    public function payrolls()
    {
        return $this->hasMany(PayrollGroupUser::class, 'payroll_group_id');
    }
}
