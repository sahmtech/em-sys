<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\TransactionSellLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Sales\Entities\SalesOrdersOperation;

class IrWorkersDocument extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
  
}
