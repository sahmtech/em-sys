<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    protected $guarded = ['id'];

    public $table = 'penalty_reports';
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}
