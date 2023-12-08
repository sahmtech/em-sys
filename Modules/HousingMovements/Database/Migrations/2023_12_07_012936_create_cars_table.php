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
        Schema::create('housing_movements_cars', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->string('color');
            $table->string('user_id');

            $table->unsignedBigInteger('car_model_id');
            $table->foreign('car_model_id')->references('id')->on('housing_movements_car_models');

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
        Schema::dropIfExists('housing_movements_cars');
    }
};