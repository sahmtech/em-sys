<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use Illuminate\Database\Eloquent\Model;

class LostItem extends Model
{
    protected $guarded = ['id'];

    public $table = 'lost_items';
    public function lostItemsReport()
    {
        return $this->belongsTo(LostItems::class, 'lost_items_reports_id');
    }
}
