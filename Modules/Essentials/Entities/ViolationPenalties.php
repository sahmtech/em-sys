<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViolationPenalties extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function violation(){
        return $this->belongsTo(Violations::class,'violation_id');
    }
   
}