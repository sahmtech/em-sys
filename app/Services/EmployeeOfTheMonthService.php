<?php

namespace App\Services;

class EmployeeOfTheMonthService
{
    public function evaluate()
    {
        // Fetch users excluding Super Admin and count their orders and logins
        $users = User::withCount(['orders', 'logins'])
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'super_admin'); // Exclude Super Admin
            })
            ->get();

        // Find the user with the highest combined performance
        $idealEmployee = $users->sortByDesc(function ($user) {
            return $user->orders_count + $user->logins_count; // Performance metric
        })->first();

        // Reset all users' status
        User::query()->update(['is_employee_of_the_month' => false]);

        // Mark the ideal employee
        if ($idealEmployee) {
            $idealEmployee->update(['is_employee_of_the_month' => true]);
        }
    }
}
