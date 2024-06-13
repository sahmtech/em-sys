<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\CEOManagment\Entities\RequestsType;

class AccessRoleRequest extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function accessRole()
    {
        return $this->belongsTo(AccessRole::class, 'access_role_id');
    }
    public function request()
    {
        return $this->belongsTo(RequestsType::class, 'request_id');
    }
}