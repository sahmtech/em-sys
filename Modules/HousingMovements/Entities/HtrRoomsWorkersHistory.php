<?php

namespace Modules\HousingMovements\Entities;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HtrRoomsWorkersHistory extends Model
{
    use HasFactory;

    use HasFactory;
    public $table='htr_rooms_workers_histories';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
