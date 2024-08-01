<?php

namespace Modules\Accounting\Entities;

use App\Company;
use App\User;
use Illuminate\Database\Eloquent\Model;

class AccountingAccTransMapping extends Model
{
    protected $guarded = ['id'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
  
}