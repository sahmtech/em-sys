<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class PayrollGroupUser extends Model
{
    protected $guarded = ['id'];

    public function payrollGroup()
    {
        return $this->belongsTo(\Modules\Essentials\Entities\PayrollGroup::class, 'payroll_group_id');
    }
}
