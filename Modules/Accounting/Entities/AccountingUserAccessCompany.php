<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountingUserAccessCompany extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
