<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Transaction;
class salesContract extends Model
{
    protected $guarded = ['id'];

    public function transaction()
    {    
        return $this->belongsTo(Transaction::class, 'offer_price_id');
    }

    public function salesOrderOperation() {
      
        return $this->hasOne(SalesOrdersOperation::class, 'sale_contract_id');
    }
}
