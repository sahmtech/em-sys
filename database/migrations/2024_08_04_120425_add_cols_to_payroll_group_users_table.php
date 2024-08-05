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
        Schema::table('payroll_group_users', function (Blueprint $table) {
            $table->unsignedBigInteger('timesheet_user_id')->nullable();
            $table->foreign('timesheet_user_id')->references('id')->on('timesheet_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_group_users', function (Blueprint $table) {
            //
        });
    }
};
