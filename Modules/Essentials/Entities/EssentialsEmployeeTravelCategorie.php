<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsEmployeeTravelCategorie extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $employeeTravelCategories = EssentialsEmployeeTravelCategorie::all()->pluck('id');

        return $employeeTravelCategories;
    }
}
