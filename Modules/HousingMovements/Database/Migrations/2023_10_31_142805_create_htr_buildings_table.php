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
        Schema::create('htr_buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('city_id');
            $table->string('address');
            $table->unsignedInteger('guard_id');
            $table->unsignedInteger('supervisor_id');
            $table->unsignedInteger('cleaner_id');

            $table->foreign('city_id')->references('id')->on('essentials_cities');
            $table->foreign('guard_id')->references('id')->on('users');
            $table->foreign('supervisor_id')->references('id')->on('users');
            $table->foreign('cleaner_id')->references('id')->on('users');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('htr_buildings');
    }
};
