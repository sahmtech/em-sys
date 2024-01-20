<?php

namespace Modules\Essentials\Entities;
use Modules\Essentials\Entities\EssentialsEmployeesFamily;
use Illuminate\Database\Eloquent\Model;
use App\User;
class EssentialsEmployeesInsurance extends Model
{
   
    protected $guarded = ['id'];
   
    public function user()
    {
        return $this->belongsTo(User::class ,'employee_id');
    }

    public function essentialsEmployeesFamily()
    {
        return $this->belongsTo(EssentialsEmployeesFamily::class, 'family_id');
    }
  
}
