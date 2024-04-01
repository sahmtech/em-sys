<?php

namespace Modules\HousingMovements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarImage extends Model
{
    use HasFactory;

    protected $fillable = [ 'car_image', 'car_id'];

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

}