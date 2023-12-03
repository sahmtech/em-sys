<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class salesSource extends Model
{
    use HasFactory;

    protected $table = 'sales_sources';
    protected $guarded = ['id'];
  
}
