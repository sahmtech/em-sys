<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('accounting_accounts', function (Blueprint $table) {
            DB::statement('ALTER TABLE accounting_accounts MODIFY COLUMN account_category VARCHAR(255)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            DB::statement('ALTER TABLE accounting_accounts MODIFY COLUMN account_category VARCHAR(255)');
        });
    }
};