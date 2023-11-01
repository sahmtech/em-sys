<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('essentials_employee_appointmets', function (Blueprint $table) {
            $table->unsignedBigInteger('profession_id')->nullable();
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->foreign('profession_id')->references('id')->on('essentials_professions')->onDelete('cascade'); 
            $table->foreign('specialization_id')->references('id')->on('essentials_specializations')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
};
