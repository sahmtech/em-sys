<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetDefaultPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:set-default-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a default password for all users except specific ones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultPassword = Hash::make('Aa@123');

        // Update users except those with IDs
        $updatedRows = DB::table('users')
            ->whereNotIn('id', [1, 8792])
            ->update(['password' => $defaultPassword]);

        $this->info("Default password set for $updatedRows users, excluding IDs Super Admin and CEO.");
    }
}