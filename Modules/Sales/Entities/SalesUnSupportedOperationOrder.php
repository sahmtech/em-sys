<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesUnSupportedOperationOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public function unSupported_worker()
    {
        return $this->belongsTo(SalesUnSupportedWorker::class, 'workers_order_id');
    }
}
