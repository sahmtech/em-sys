<?php

namespace Modules\CEOManagment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Essentials\Entities\EssentialsDepartment;

class WkProcedure extends Model
{
//    use HasFactory;

    protected $guarded = ['id'];
    
    public function department()
    {
        return $this->belongsTo(EssentialsDepartment::class, 'department_id');
    }
    public function nextdepartment()
    {
        return $this->belongsTo(EssentialsDepartment::class, 'next_department_id');
    }
    public function request_type()
    {
        return $this->belongsTo(RequestsType::class, 'request_type_id');
    }
}
