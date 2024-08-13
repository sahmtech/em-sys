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
            $table->dropColumn('approved_by');
        });
        Schema::table('timesheet_groups', function (Blueprint $table) {
            $table->text('approved_by')->nullable()->after('accounting_approved_by');
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
