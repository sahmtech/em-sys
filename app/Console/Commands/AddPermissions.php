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
            'essentials.add_Violations',
            'essentials.edit_Violations',
            'essentials.add_Main_Violations',
            'essentials.edit_Main_Violations',
            'essentials.delete_Main_Violations',
            'essentials.delete_Violations',
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
