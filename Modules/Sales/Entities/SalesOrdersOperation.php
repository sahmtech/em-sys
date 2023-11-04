<?php

namespace Modules\Sales\Entities;
use Modules\Sales\Entities\SalesContract;
use App\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class salesOrdersOperation extends Model
{
    protected $guarded = ['id'];
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function salesContract()
    {
        return $this->belongsTo(SalesContract::class, 'sale_contract_id');
    }
}
