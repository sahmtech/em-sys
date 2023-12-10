<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRoleProject extends Model
{
    use HasFactory;


    public function accessRole()
    {
        return $this->belongsTo(AccessRole::class, 'access_role_id');
    }
    public function saleProject()
    {
        return $this->belongsTo(saleProject::class, 'sales_project_id');
    }
}
