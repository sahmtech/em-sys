<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class essentialsAllowanceType extends Model
{
   
    protected $guarded = ['id'];
    public static function forDropdown()
    {
        $allowanceTypes = essentialsAllowanceType::all()->pluck('name','id');

        return $allowanceTypes;
    }
   
}
