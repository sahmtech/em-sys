<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    protected $guarded = ['id'];

    public $table = 'incident_report';

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}
