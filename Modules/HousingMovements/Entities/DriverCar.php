<?php

namespace Modules\HousingMovements\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverCar extends Model
{
    use HasFactory;

    public $table = 'housing_movements_driver_cars';
    protected $fillable = ['user_id', 'car_image', 'delivery_date', 'counter_number', 'car_id'];

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
