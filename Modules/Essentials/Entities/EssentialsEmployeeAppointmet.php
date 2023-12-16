<?php

namespace Modules\Essentials\Entities;

use App\BusinessLocation;
use App\User;
use Illuminate\Database\Eloquent\Model;

class EssentialsEmployeeAppointmet extends Model
{
   
    protected $guarded = ['id'];
    public static function forDropdown()
    {
        $employeeAppointment = EssentialsEmployeeAppointmet::all()->pluck('id');

        return $employeeAppointment;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    public function profession()
    {
        return $this->belongsTo(EssentialsProfession::class, 'profession_id');
    }
    
    public function Specialization()
    {
        return $this->belongsTo(EssentialsSpecialization::class, 'specialization_id');
    }
    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'business_location_id');
    }
}