<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class SellingPriceGroup extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('selling_price_groups.is_active', 1);
    }

    /**
     * Return list of selling price groups
     *
     * @param  int  $business_id
     * @return array
     */
    public static function forDropdown($business_id, $with_default = true)
    {
        $price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->active()
            ->get();

        $dropdown = [];

        if ($with_default && auth()->user()->can('access_default_selling_price')) {
            $dropdown[0] = __('lang_v1.default_selling_price');
        }

        foreach ($price_groups as $price_group) {
            if (auth()->user()->can('selling_price_group.' . $price_group->id)) {
                $dropdown[$price_group->id] = $price_group->name;
            }
        }

        return $dropdown;
    }

    public static function forDropdownWithCompany($business_id, $company_id = null, $with_default = true)
    {
        $price_groups = SellingPriceGroup::where('business_id', $business_id)->where('company_id', $company_id)
            ->active()
            ->get();

        $dropdown = [];

        if ($with_default && auth()->user()->can('access_default_selling_price')) {
            $dropdown[0] = __('lang_v1.default_selling_price');
        }

        foreach ($price_groups as $price_group) {
            if (auth()->user()->can('selling_price_group.' . $price_group->id)) {
                $dropdown[$price_group->id] = $price_group->name;
            }
        }

        return $dropdown;
    }
    /**
     * Counts total number of selling price groups
     *
     * @param  int  $business_id
     * @return array
     */
    public static function countSellingPriceGroups($business_id)
    {
        $company_id = Session::get('selectedCompanyId');

        $count = SellingPriceGroup::where('business_id', $business_id)->where('company_id', $company_id)
            ->active()
            ->count();

        return $count;
    }
}