<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsEntitlementType extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $entitlementTypes = EssentialsEntitlementType::all()->pluck('name','id');

        return $entitlementTypes;
    }
}
