<?php

namespace Modules\HousingMovements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarType extends Model
{
    use HasFactory;
    public $table='housing_movements_car_types';

    protected $fillable = [
        'name_ar', 'name_en'

    ];

    public function CarModel()
    {
        return $this->hasMany(CarModel::class, 'car_type_id');
    }
}