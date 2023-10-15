<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsInsuranceClass extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $banks = EssentialsInsuranceClass::all()->pluck('name','id');
        
        return $banks;
    }
}
