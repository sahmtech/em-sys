<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRoleCompanyUserType extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function accessRoleCompany()
    {
        return $this->belongsTo(AccessRoleCompany::class, 'access_role_company_id');
    }
}
