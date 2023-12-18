<?php

namespace Modules\HousingMovements\Entities;

use App\User;
use Modules\HousingMovements\Entities\HtrBuilding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HtrRoom extends Model
{
    use HasFactory;
    public $table='htr_rooms';
    protected $guarded = ['id'];


    
    public function building()
    {
        return $this->belongsTo(HtrBuilding::class, 'htr_building_id');
    }
    
}