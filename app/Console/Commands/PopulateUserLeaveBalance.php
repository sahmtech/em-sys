<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Modules\Essentials\Entities\EssentialsLeaveType;
use Carbon\Carbon;
use Modules\Essentials\Entities\UserLeaveBalance;

class PopulateUserLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:userleavebalances';
    protected $description = 'Populate leave balances for users with appointments and no leave balance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userTypes = ['employee', 'worker', 'manager'];
        $users = User::whereHas('appointment', function ($query) {
            $query->whereNotNull('start_from');
        })->doesntHave('userLeaveBalances')->whereIn('user_type', $userTypes)->get();


        $leaveTypes = EssentialsLeaveType::where('leave_type', 'not like', '%سنوية%')->get();
        $today = now();

        $annualLeaveTypeIdFor21 = EssentialsLeaveType::where('leave_type', 'like', '%سنوية_21%')
            ->first()
            ->id ?? null;
        $annualLeaveDueDate21 = $annualLeaveTypeIdFor21 ? EssentialsLeaveType::find($annualLeaveTypeIdFor21)->due_date ?? 0 : 0;

        $annualLeaveTypeIdFor30 = EssentialsLeaveType::where('leave_type', 'like', '%سنوية_30%')
            ->first()
            ->id ?? null;
        $annualLeaveDueDate30 = $annualLeaveTypeIdFor30 ? EssentialsLeaveType::find($annualLeaveTypeIdFor30)->due_date ?? 0 : 0;

        foreach ($users as $user) {
            $appointmentStartDate = optional($user->appointment)->start_from;

            if (is_null($appointmentStartDate) || now()->lt(new Carbon($appointmentStartDate))) {
                continue;
            }

            $appointmentStartCarbon = new Carbon($appointmentStartDate);
            $monthsDifference = $appointmentStartCarbon ? now()->diffInMonths($appointmentStartCarbon) : 0;


            foreach ($leaveTypes as $leaveType) {
                if (
                    $monthsDifference < $leaveType->due_date ||
                    ($user->gender != $leaveType->gender && $leaveType->gender != 'both')
                ) {
                    continue;
                }

                $amount = is_null($leaveType->max_leave_count)
                    ? $leaveType->duration
                    : $leaveType->duration * $leaveType->max_leave_count;

                UserLeaveBalance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'essentials_leave_type_id' => $leaveType->id,
                    ],
                    ['amount' => $amount]
                );
            }

            if ($user->max_anuual_leave_days == 21 && $monthsDifference > $annualLeaveDueDate21 && $annualLeaveTypeIdFor21) {
                UserLeaveBalance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'essentials_leave_type_id' => $annualLeaveTypeIdFor21,
                    ],
                    ['amount' => 21]
                );
            } elseif (in_array($user->max_anuual_leave_days, [30, 31]) && $monthsDifference > $annualLeaveDueDate30 && $annualLeaveTypeIdFor30) {
                UserLeaveBalance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'essentials_leave_type_id' => $annualLeaveTypeIdFor30,
                    ],
                    ['amount' => 30]
                );
            }
        }
        $this->info('Leave balances populated successfully.');
    }
}
