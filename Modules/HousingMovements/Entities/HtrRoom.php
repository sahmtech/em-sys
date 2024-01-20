<?php

namespace Modules\HousingMovements\Entities;

use App\User;
use Modules\HousingMovements\Entities\HtrBuilding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\HousingMovements\EntitiesHtrRoomsWorkersHistory;

class HtrRoom extends Model
{
    use HasFactory;
    public $table='htr_rooms';
    protected $guarded = ['id'];


    
    public function building()
    {
        return $this->belongsTo(HtrBuilding::class, 'htr_building_id');
    }

    public function roomsWorkersHistory()
        {
            return $this->hasMany(HtrRoomsWorkersHistory::class, 'room_id');
        }
    
}