<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsBankAccounts extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $banks = EssentialsBankAccounts::all()->pluck('name','id');
        
        return $banks;
    }
}
