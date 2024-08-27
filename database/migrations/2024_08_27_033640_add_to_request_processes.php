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
        Schema::table('request_processes', function (Blueprint $table) {
            $table->boolean('is_transfered_from_GM')->default(0)->after('superior_department_id');
            $table->unsignedBigInteger('to_department_after_escalation')->nullable()->after('is_transfered_from_GM');
            $table->foreign('to_department_after_escalation')->references('id')->on('essentials_departments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_processes', function (Blueprint $table) {
            //
        });
    }
};