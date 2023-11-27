<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EssentialsReasonWish extends Model
{
    use HasFactory;

    protected $table = 'essentials_reason_wishes';

    protected $fillable = [
        'reason',
        'employee_type',
        'type',
        'reason_type',
        'main_reason_id',
    ];
    
    protected static function newFactory()
    {
        return \Modules\Essentials\Database\factories\EssentialsReasonWishFactory::new();
    }
}
