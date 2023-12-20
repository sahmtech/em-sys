<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class AccessRole extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function accessRoleProjects()
    {
        return $this->hasMany(AccessRoleProject::class, 'access_role_id');
    }
    public function accessRoleBusinesses()
    {
        return $this->hasMany(AccessRoleBusiness::class, 'access_role_id');
    }
}
