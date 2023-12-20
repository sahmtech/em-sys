<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class salesOfferPricesCost extends Model
{
    use HasFactory;

    protected $table = 'sales_offer_prices_costs';
    protected $guarded = ['id'];
  
}
