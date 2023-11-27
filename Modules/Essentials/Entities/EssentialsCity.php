<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsCity extends Model
{

    protected $guarded = ['id'];
    public static function forDropdown()
    {
        $cities = EssentialsCity::all();

        $res = collect();
        foreach ($cities as $city) {
            $res->put($city->id, (json_decode($city->name, true))['ar'], (json_decode($city->name, true))['en'],);
        }
        return $res;
    }
    public static function forDropdown2()
    {
        $cities = EssentialsCity::all();

        $res = collect();
        foreach ($cities as $city) {
            $res->put((json_decode($city->name, true))['ar'], (json_decode($city->name, true))['en'],);
        }
        return $res;
    }
    public static function forDropdown3()
    {
        $cities = EssentialsCity::all();

        $res = collect();
        foreach ($cities as $city) {
            $res->put($city->id, ['id' => $city->id, 'country_id' => $city->country_id, 'ar' => (json_decode($city->name, true))['ar'], 'en' => (json_decode($city->name, true))['en']]);
        }
        return $res;
    }
}
