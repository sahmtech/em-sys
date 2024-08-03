<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class PayrollGroup extends Model
{
    protected $guarded = ['id'];

    public function payrollGroupUser()
    {
        return $this->hasMany(\Modules\Essentials\Entities\PayrollGroupUser::class, 'payroll_group_id');
    }
}
