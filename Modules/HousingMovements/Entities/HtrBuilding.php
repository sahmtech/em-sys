<?php

namespace Modules\HousingMovements\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HtrBuilding extends Model
{
    use HasFactory;
    public $table='htr_buildings';
    protected $guarded = ['id'];

   
}