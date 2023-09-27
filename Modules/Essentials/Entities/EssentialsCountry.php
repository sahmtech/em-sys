<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsCountry extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $countries = EssentialsCountry::all();

        $res=collect();
        foreach ($countries as $country){
            $res->put($country->id,(json_decode($country->name,true))['ar'],(json_decode($country->name,true))['en'],);
        }
        return $res;
    }
    public static function forDropdown2()
    {
        $countries = EssentialsCountry::all();

        $res=collect();
        foreach ($countries as $country){
            $res->put((json_decode($country->name,true))['ar'],(json_decode($country->name,true))['en'],);
        }
        return $res;
    }
}
