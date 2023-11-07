<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsRegion extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $regions = EssentialsRegion::all();

        $res=collect();
        foreach ($regions as $region){
            $res->put($region->id,(json_decode($region->name,true))['ar'],(json_decode($region->name,true))['en'],);
        }
        return $res;
    }
 
    public static function forDropdown2()
    {
        $regions = EssentialsRegion::all();

        $res=collect();
        foreach ($regions as $region){
            $res->put((json_decode($region->name,true))['ar'],(json_decode($region->name,true))['en'],);
        }
        return $res;
    }
}
