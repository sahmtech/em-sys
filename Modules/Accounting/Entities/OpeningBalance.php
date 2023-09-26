<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OpeningBalance extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function transaction(){
        return $this->hasOne(AccountingAccountsTransaction::class, 'id','accounts_account_transaction_id');
    }
}
