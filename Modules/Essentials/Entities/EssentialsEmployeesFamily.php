<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsEmployeesFamily extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $EmployeesFamily = EssentialsEmployeesFamily::all()->pluck('id');

        return $EmployeesFamily;
    }
}
