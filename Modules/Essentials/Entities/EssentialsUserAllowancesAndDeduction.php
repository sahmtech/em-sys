<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Essentials\Entities\essentialsAllowanceType;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
class EssentialsUserAllowancesAndDeduction extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'essentials_user_allowance_and_deductions';
    public function allowanceType()
    {
      
        return $this->belongsTo(EssentialsAllowanceType::class, 'allowance_deduction_id');
    }


    public function allowancedescription()
    {
      
        return $this->belongsTo(EssentialsAllowanceAndDeduction::class, 'allowance_deduction_id');
    }

}
