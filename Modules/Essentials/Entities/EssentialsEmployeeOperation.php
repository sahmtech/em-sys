<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EssentialsEmployeeOperation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\Essentials\Database\factories\EssentialsEmployeeOperationFactory::new();
    }
}
