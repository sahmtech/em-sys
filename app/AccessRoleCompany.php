<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRoleCompany extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function accessRole()
    {
        return $this->belongsTo(AccessRole::class, 'access_role_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function accessRoleCompanyUserTypes()
    {
        return $this->hasMany(AccessRoleCompanyUserType::class, 'access_role_company_id');
    }

    public function userTypes()
    {
        return AccessRoleCompanyUserType::where('access_role_company_id', $this->id)
            ->pluck('user_type')
            ->unique()
            ->reject(function ($value) {
                return $value == 'customer' || $value == 'customer_user' || $value == 'admin' || $value == 'user';
            })
            ->toArray();
    }
}
