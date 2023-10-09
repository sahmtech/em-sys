<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsAdmissionToWork extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $AdmissionToWorks = EssentialsAdmissionToWork::all()->pluck('name','id');
        
        return $AdmissionToWorks;
    }
}
