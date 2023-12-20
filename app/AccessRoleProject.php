<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Sales\Entities\SalesProject;

class AccessRoleProject extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function accessRole()
    {
        return $this->belongsTo(AccessRole::class, 'access_role_id');
    }
    public function saleProject()
    {
        return $this->belongsTo(SalesProject::class, 'sales_project_id');
    }
}
