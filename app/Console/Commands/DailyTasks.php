<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Carbon\Carbon;

class DailyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:dialy-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {




        $this->contracts();
    }

    public function contracts()
    {
        $today = Carbon::today();
        $contracts = EssentialsEmployeesContract::where('is_active', 1)->where('status', 'valid')->get();
        // about to expired


        if ($today->isLastDayOfMonth()) {
            $about_to_expired = $contracts->whereDate('contract_end_date', '<=', Carbon::today()->addMonths(2));
            //send notification 
        }

        //expired
        $contracts = $contracts->where('is_renewable', 1)->where('contract_end_date', $today);
        foreach ($contracts as $contract) {

            DB::beginTransaction();
            try {

                $contract->status = 'canceled';
                $contract->save();


                $newStartDate = $today->copy()->addDay();
                $newEndDate = $newStartDate->copy();

                if ($contract->contract_per_period === 'year') {
                    $newEndDate->addYears($contract->probation_period);
                } elseif ($contract->contract_per_period === 'month') {
                    $newEndDate->addMonths($contract->probation_period);
                }


                $newContract = $contract->replicate();
                $newContract->contract_start_date = $newStartDate;
                $newContract->contract_end_date = $newEndDate;
                $newContract->status = 'valid';
                $newContract->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
    }
}
