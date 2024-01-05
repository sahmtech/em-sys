<?php

namespace Modules\HousingMovements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HousingMovementsCarsChangeOil extends Model
{
    use HasFactory;

    protected $fillable = ['car_id','current_speedometer','next_change_oil','invoice_no','date'];
    
   
    public function car(){
        return $this->belongsTo(Car::class);
    }
}