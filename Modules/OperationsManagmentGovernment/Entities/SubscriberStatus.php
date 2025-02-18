<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use Illuminate\Database\Eloquent\Model;

class SubscriberStatus extends Model
{
    protected $guarded = ['id'];

    public $table = 'subscriber_status_report';
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}
