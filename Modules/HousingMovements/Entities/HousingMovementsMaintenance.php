<?php

namespace Modules\HousingMovements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HousingMovementsMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'current_speedometer',
        'maintenance_type',
        'maintenance_description',
        'invoice_no',
        'attachment',
        'date'
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
