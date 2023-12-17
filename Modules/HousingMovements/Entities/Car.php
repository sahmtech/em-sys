<?php

namespace Modules\HousingMovements\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Car extends Model
{
    use HasFactory;
    public $table='housing_movements_cars';

    protected $fillable = [
        'plate_number', 'color', 'car_model_id','number_seats'
    ];
     public function CarModel()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }
 

    public function User(){
        return $this->belongsTo(User::class,'user_id');
    }
}