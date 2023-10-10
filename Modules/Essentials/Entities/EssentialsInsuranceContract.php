<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsInsuranceContract extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $banks = EssentialsInsuranceContract::all()->pluck('name','id');
        
        return $banks;
    }
}
