<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Essentials\Entities\EssentialsEmployeesInsurance;

class EssentialsEmployeesFamily extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $EmployeesFamily = EssentialsEmployeesFamily::all()->pluck('id');

        return $EmployeesFamily;
    }

    public function user()
    {
        return $this->belongsTo(User::class ,'employee_id');
    }

    public function essentialsEmployeesInsurance()
    {
        return $this->hasOne(EssentialsEmployeesInsurance::class, 'family_id');
    }
}
