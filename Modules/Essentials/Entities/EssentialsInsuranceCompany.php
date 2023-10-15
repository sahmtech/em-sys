<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsInsuranceCompany extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $EssentialsInsuranceCompany = EssentialsInsuranceCompany::all()->pluck('name','id');
        
        return $EssentialsInsuranceCompany;
    }
}
