<?php

namespace Modules\FollowUp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowupDocument extends Model
{
    use HasFactory;

    protected $fillable = ['name_ar', 'name_en'];
}
