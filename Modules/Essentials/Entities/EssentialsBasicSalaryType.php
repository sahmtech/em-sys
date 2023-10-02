<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsBasicSalaryType extends Model
{
    
    protected $guarded = ['id'];
    public static function forDropdown()
    {
        $basicSalaryTypes = EssentialsBasicSalaryType::get()->pluck('type','id');
      
        return $basicSalaryTypes;

    }
}
   

