<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsEmployeeContract extends Model
{
   
    protected $guarded = ['id'];
    public static function forDropdown()
    {
        $employeeContract = EssentialsEmployeeContract::all()->pluck('name','id');

        return $employeeContract;
    }
   
}
