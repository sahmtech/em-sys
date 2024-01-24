<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsWkProcedure extends Model
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
    public function department()
    {
        return $this->belongsTo(EssentialsDepartment::class, 'department_id');
    }
    public function nextdepartment()
    {
        return $this->belongsTo(EssentialsDepartment::class, 'next_department_id');
    }
    public function escalations()
    {
        return $this->hasMany('App\EssentialsProcedureEscalation', 'procedure_id');
    }

    
    protected $table = 'essentials_wk_procedures';
}
