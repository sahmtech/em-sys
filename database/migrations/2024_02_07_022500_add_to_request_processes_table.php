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
            $table->unsignedBigInteger('started_department_id')->nullable()->after('superior_department_id');
            $table->foreign('started_department_id')->references('id')->on('essentials_departments');

            
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
