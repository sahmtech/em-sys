<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\TransactionSellLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\InternationalRelations\Entities\IrVisaCard;

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

    public function visaCard()
    {
        return $this->hasOne(IrVisaCard::class, 'transaction_sell_line_id', 'transaction_sell_line_id');
    }

    public function lastArrivalproposedLabors($agencyId)
    {
        return $this->hasMany(IrProposedLabor::class, 'transaction_sell_line_id', 'transaction_sell_line_id')
            ->where('agency_id', $agencyId)
            ->where('interviewStatus', 'acceptable')
            ->orderByDesc('arrival_date');
    }
}
