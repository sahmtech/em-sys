<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;
class EssentialsAdmissionToWork extends Model
{
   
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        $AdmissionToWorks = EssentialsAdmissionToWork::all()->pluck('name','id');
        
        return $AdmissionToWorks;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

}
