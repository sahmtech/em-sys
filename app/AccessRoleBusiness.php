<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRoleBusiness extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function accessRole()
    {
        return $this->belongsTo(AccessRole::class, 'access_role_id');
    }
    public function business()
    {
        
        return $this->belongsTo(Business::class, 'business_id');
    }
}
