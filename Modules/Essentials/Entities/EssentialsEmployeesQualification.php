<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsEmployeesQualification extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $qualificationType = EssentialsEmployeesQualification::all()->pluck('id');

        return $qualificationType;
    }
}