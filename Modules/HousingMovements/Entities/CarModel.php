<?php

namespace Modules\HousingMovements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarModel extends Model
{
    use HasFactory;

    public $table = 'housing_movements_car_models';
    protected $fillable = [
        'name_ar', 'name_en', 'car_type_id'
    ];

    public function CarType()
    {
        return $this->belongsTo(CarType::class, 'car_type_id');
    }
}