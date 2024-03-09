<?php

namespace Modules\CEOManagment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcedureTask extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}