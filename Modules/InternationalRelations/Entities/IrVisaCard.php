<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\TransactionSellLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Sales\Entities\SalesOrdersOperation;
use Modules\InternationalRelations\Entities\IrDelegation;
use Modules\Sales\Entities\SalesUnSupportedOperationOrder;
use Modules\Sales\Entities\SalesUnSupportedWorker;

class IrVisaCard extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $foreignKey;
    protected $transaction_sell_line_id;



    public function operationOrder()
    {
        return $this->belongsTo(SalesOrdersOperation::class, 'operation_order_id');
    }
    public function unSupported_operation()
    {
        return $this->belongsTo(SalesUnSupportedOperationOrder::class, 'unSupported_operation_id');
    }

    public function unSupportedworker_order()
    {
        return $this->belongsTo(SalesUnSupportedWorker::class, 'unSupportedworker_order_id');
    }

    public function delegation()
    {
        return $this->belongsTo(IrDelegation::class, 'transaction_sell_line_id', 'transaction_sell_line_id');
    }

    public function transaction_sell_line()
    {
        return $this->belongsTo(TransactionSellLine::class, 'transaction_sell_line_id', 'id');
    }


    // public function delegation()
    // {
    //     $foreignKey = $this->transaction_sell_line_id !== null ? 'transaction_sell_line_id' : 'unSupportedworker_order_id';
    //     return $this->belongsTo(IrDelegation::class, $foreignKey, $foreignKey);
    // }
}
