<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\TransactionSellLine;
use Modules\InternationalRelations\Entities\IrVisaCard;
use Modules\InternationalRelations\Entities\IrWorkersDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IrProposedLabor extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function transactionSellLine()
    {
        return $this->belongsTo(TransactionSellLine::class,'transaction_sell_line_id');
    }


    public function user()
    {
        return $this->belongsTo(user::class, 'proposal_worker_id');
    }


    public function agency()
    {
        return $this->belongsTo(Contact::class);
    }



    public function visa()
    {
        return $this->belongsTo(IrVisaCard::class, 'visa_id');
    }


    public function worker_documents()
    {
        return $this->hasMany(IrWorkersDocument::class, 'worker_id');
    }
  
}