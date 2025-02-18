<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use Illuminate\Database\Eloquent\Model;

class PhotoConsents extends Model
{
    protected $guarded = ['id'];

    public $table = 'photo_consents_report';
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}
