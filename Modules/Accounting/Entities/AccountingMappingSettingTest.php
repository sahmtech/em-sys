<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountingMappingSettingTest extends Model
{
    use HasFactory;

    protected $guarded = ['id']; 

    protected $table ="accounting_mapping_setting_tests";
}