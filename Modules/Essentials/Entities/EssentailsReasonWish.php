<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EssentailsReasonWish extends Model
{
   
    use HasFactory;
   
    protected $table = 'essentails_reason_wishes';
    
    protected $guarded = ['id'];

   public static function forDropdown()
   {
    $main_reasons = EssentailsReasonWish::where('reason_type','main')->pluck('reason', 'id');


       return $main_reasons;
   }
}
