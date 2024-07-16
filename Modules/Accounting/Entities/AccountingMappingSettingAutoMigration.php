<?php

namespace Modules\Accounting\Entities;

use App\BusinessLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountingMappingSettingAutoMigration extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'status', 'payment_status', 'method', 'active', 'created_by', 'company_id'];

    // public function businessLocation()
    // {
    //     return $this->belongsTo(BusinessLocation::class, 'location_id');
    // }
}