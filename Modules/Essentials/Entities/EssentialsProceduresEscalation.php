<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsProceduresEscalation extends Model
{
    protected $guarded = ['id'];
    protected $table = 'essentials_procedure_escalations';
    
    public function escalatesDepartment()
    {
        return $this->belongsTo(EssentialsDepartment::class, 'escalates_to');
    }
     
    public function procedure()
    {
        return $this->belongsTo(EssentialsWkProcedure::class, 'procedure_id');
    }
}
