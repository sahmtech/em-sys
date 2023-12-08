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
        Schema::rename('housingmovements_car_models', 'housing_movements_car_models');
        Schema::rename('housingmovements_car_types', 'housing_movements_car_types');
        Schema::rename('housingmovements_cars', 'housing_movements_cars');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};