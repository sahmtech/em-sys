<?php

namespace Modules\Essentials\Entities;

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
        return $this->belongsTo(User::class, 'employee_id'); // Assuming the foreign key is 'user_id'
    }
   
}
