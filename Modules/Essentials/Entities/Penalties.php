<?php

namespace Modules\Essentials\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penalties extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function addedBy(){
        return $this->belongsTo(User::class,'added_by');
    }

    public function violationPenalties(){
        return $this->belongsTo(violationPenalties::class,'violation_penalties_id');
    }
    
}