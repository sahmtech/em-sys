<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\CEOManagment\Entities\WkProcedure;

class RequestProcess extends Model
{
    use HasFactory;
    protected $table = 'request_processes';
    protected $guarded = ['id'];


    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function procedure()
    {
        return $this->belongsTo(WkProcedure::class, 'procedure_id');
    }
    public function superior_department_id()
    {
        return $this->belongsTo(WkProcedure::class, 'superior_department_id');
    }
}