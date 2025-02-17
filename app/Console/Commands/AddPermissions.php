<?php
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class AddPermissions extends Command
{
    protected $signature   = 'permissions:add';
    protected $description = 'Add predefined permissions to the permissions table';

    public function handle()
    {
        $permissions = [

            // Project Diagram

            'operationsmanagmentgovernment.project_diagram',
            'operationsmanagmentgovernment.add_project_diagram',
            'operationsmanagmentgovernment.delete_project_diagram',
            'operationsmanagmentgovernment.view_project_diagram',
            'operationsmanagmentgovernment.project_report',
            'operationsmanagmentgovernment.view_project_report',
            'operationsmanagmentgovernment.add_project_report',
            'operationsmanagmentgovernment.delete_project_report',

            // security guard
            'operationsmanagmentgovernment.delete_security_guard',
            'operationsmanagmentgovernment.view_security_guards',
            'operationsmanagmentgovernment.add_security_guard',
            'operationsmanagmentgovernment.edit_security_guard',

            // outside communication
            'operationsmanagmentgovernment.view_outside_communication',
            'operationsmanagmentgovernment.delete_project_department',

            // payrolls  import_new_arrival_workers Feat
            'payrolls.new_arrival_for_workers',
            'payrolls.import_new_arrival_workers',
            'payrolls.housed',
            'payrolls.advanceSalaryRequest',
            'payrolls.medicalExamination',
            'payrolls.medicalInsurance',
            'payrolls.workCardIssuing',
            'payrolls.bankAccount',
            'payrolls.SIMCard',
            'payrolls.contract',
            'payrolls.residencyAdd',
            'payrolls.residencyDelivery',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            );

            $this->info("Permission {$permission} processed.");
        }
    }
}
