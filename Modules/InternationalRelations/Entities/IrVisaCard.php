<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\TransactionSellLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Sales\Entities\SalesOrdersOperation;
use Modules\InternationalRelations\Entities\IrDelegation;

class IrVisaCard extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function operationOrder()
    {
        return $this->belongsTo(SalesOrdersOperation::class, 'operation_order_id');
    }

    public function delegation()
    {
        return $this->belongsTo(IrDelegation::class, 'transaction_sell_line_id', 'transaction_sell_line_id');
    }
}
