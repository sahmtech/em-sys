<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsCountry extends Model
{

    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $countries = EssentialsCountry::all();

        $res = collect();
        foreach ($countries as $country) {
            $res->put($country->id, (json_decode($country->name, true))['ar'], (json_decode($country->name, true))['en'],);
        }
        return $res;
    }
    public static function nationalityForDropdown()
    {
        $countries = EssentialsCountry::all()->pluck('nationality', 'id');

        return $countries;
    }
    public static function forDropdown2()
    {
        $countries = EssentialsCountry::all();

        $res = collect();
        foreach ($countries as $country) {
            $res->put((json_decode($country->name, true))['ar'], (json_decode($country->name, true))['en'],);
        }
        return $res;
    }
    public static function forDropdown3()
    {
        $countries = EssentialsCountry::all();

        $res = collect();
        foreach ($countries as $country) {
            $res->put($country->id, ['id' => $country->id, 'ar' => (json_decode($country->name, true))['ar'], 'en' => (json_decode($country->name, true))['en'],],);
        }
        return $res;
    }
}
