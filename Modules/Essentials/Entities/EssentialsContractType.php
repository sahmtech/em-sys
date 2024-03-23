<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsContractType extends Model
{

    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $contractTypes = EssentialsContractType::all()->pluck('id', 'type');


        return $contractTypes;
    }

    public function contract()
    {
        return $this->hasOne(EssentialsEmployeesContract::class, 'contract_type_id');
    }
}
