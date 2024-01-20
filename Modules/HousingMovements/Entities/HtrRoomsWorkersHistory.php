<?php

namespace Modules\HousingMovements\Entities;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\HousingMovements\Entities\HtrRoom;

class HtrRoomsWorkersHistory extends Model
{
    use HasFactory;

    use HasFactory;
    public $table='htr_rooms_workers_histories';
    protected $guarded = ['id'];

   
    public function user()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

   
    public function room()
    {
        return $this->belongsTo(HtrRoom::class, 'room_id');
    }

}
