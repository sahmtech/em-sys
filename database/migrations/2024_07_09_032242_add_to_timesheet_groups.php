<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timesheet_groups', function (Blueprint $table) {
            $table->boolean('is_invoice_issued')->default(0)->after('approved_by');
            $table->boolean('is_payrolls_issued')->default(0)->after('is_invoice_issued');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timesheet_groups', function (Blueprint $table) {
            //
        });
    }
};
