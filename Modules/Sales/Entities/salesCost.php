<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class salesCost extends Model
{
    use HasFactory;

    protected $table = 'sales_costs';
    protected $guarded = ['id'];
  
}
