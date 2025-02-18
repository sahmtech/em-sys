<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use Illuminate\Database\Eloquent\Model;


class LostItems extends Model
{
    protected $guarded = ['id'];

    public $table = 'lost_items_reports';
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    public function items()
    {
        return $this->hasMany(LostItem::class, 'lost_items_reports_id');
    }
}
