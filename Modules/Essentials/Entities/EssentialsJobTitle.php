<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsJobTitle extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $jobs = EssentialsJobTitle::all()->pluck('name','id');

        return $jobs;
    }
}
