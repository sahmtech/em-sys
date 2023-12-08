<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\TransactionSellLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IrProposedLabor extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function transactionSellLine()
    {
        return $this->belongsTo(TransactionSellLine::class);
    }


    public function agency()
    {
        return $this->belongsTo(Contact::class);
    }
}