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
        Schema::table('housing_movements_driver_cars', function (Blueprint $table) {
            $table->string('end_car_image')->nullable()->after('car_image');
            $table->dateTime('ended_at')->nullable()->after('car_image');
            $table->unsignedInteger('ended_by')->nullable()->after('car_image');
            $table->foreign('ended_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('housing_movements_driver_cars', function (Blueprint $table) {});
    }
};