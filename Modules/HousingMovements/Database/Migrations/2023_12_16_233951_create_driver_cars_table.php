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
        Schema::create('housing_movements_driver_cars', function (Blueprint $table) {
            $table->id();

            $table->string('car_image');
            $table->date('delivery_date');
            $table->bigInteger('counter_number');

            $table->integer('user_id')->unsigned();
            $table->unsignedBigInteger('car_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('car_id')->references('id')->on('cars');


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
        Schema::dropIfExists('housing_movements_driver_cars');
    }
};