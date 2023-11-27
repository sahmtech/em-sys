<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\TransactionSellLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IrDelegation extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function transactionSellLine()
    {
        return $this->belongsTo(TransactionSellLine::class, 'transaction_sell_line_id');
    }

    public function agency()
    {
        return $this->belongsTo(Contact::class, 'agency_id');
    }
}