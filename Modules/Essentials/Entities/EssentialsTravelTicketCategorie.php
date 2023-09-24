<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsTravelTicketCategorie extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $travel_categories = EssentialsTravelTicketCategorie::all()->pluck('name','id');
        
        return $travel_categories;
    }
}
