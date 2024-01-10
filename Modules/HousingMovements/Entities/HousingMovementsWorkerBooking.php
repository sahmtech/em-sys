<?php

namespace Modules\HousingMovements\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Sales\Entities\SalesProject;

class HousingMovementsWorkerBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['project_id', 'created_by', 'user_id', 'booking_start_Date', 'booking_end_Date'];


    public function saleProject()
    {
        return $this->belongsTo(SalesProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
