<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\TransactionSellLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Sales\Entities\salesOrdersOperation;

class IrVisaCard extends Model
{
   
    public function operationOrder()
    {
        return $this->belongsTo(salesOrdersOperation::class, 'operation_order_id');
    }
}
