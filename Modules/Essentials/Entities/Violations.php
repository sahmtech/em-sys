<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Violations extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
