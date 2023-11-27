<?php

namespace Modules\Essentials\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;

class EssentialsEmployeesContract extends Model
{

    protected $guarded = ['id'];
    public static function forDropdown()
    {
        $employeeContract = EssentialsEmployeesContract::all()->pluck('id');

        return $employeeContract;
    }

    public function user()
    {
        $this->belongsTo(User::class, 'employee_id');
    }
}
