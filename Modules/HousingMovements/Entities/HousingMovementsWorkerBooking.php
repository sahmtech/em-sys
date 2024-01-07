<?php

namespace Modules\HousingMovements\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Sales\Entities\SalesProject;

class HousingMovementsWorkerBooking extends Model
{
    use HasFactory;

    protected $fillable = ['project_id','created_by','user_id'];
    

    public function saleProject(){
        return $this->belongsTo(SalesProject::class,'project_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function creator(){
        return $this->belongsTo(User::class,'created_by');
    }
}