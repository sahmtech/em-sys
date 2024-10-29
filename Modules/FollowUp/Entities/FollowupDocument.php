<?php

namespace Modules\FollowUp\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowupDocument extends Model
{
    use HasFactory;

    protected $fillable = ['name_ar', 'name_en', 'type'];
}
