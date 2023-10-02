<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsQualificationType extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $qualificationType = EssentialsQualificationType::all()->pluck('name','id');

        return $qualificationType;
    }
}
