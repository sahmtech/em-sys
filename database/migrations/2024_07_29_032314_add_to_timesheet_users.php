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
        Schema::table('timesheet_users', function (Blueprint $table) {
            $table->boolean('is_approved')->default(0)->after('final_salary');
            $table->integer('approved_by')->nullable()->after('final_salary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timesheet_users', function (Blueprint $table) {
            //
        });
    }
};
