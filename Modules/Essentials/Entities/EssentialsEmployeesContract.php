<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsEmployeesContract extends Model
{
   
    protected $guarded = ['id'];
    public static function forDropdown()
    {
        $employeeContract = EssentialsEmployeesContract::all()->pluck('id');

        return $employeeContract;
    }
   
}
