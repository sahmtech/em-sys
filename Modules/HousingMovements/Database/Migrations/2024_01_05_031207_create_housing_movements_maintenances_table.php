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
        Schema::create('housing_movements_maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->bigInteger('current_speedometer');
            $table->text('maintenance_type');
            $table->text('maintenance_description');
            $table->string('invoice_no');
            $table->text('attachment');
            $table->timestamp('date');
            $table->foreign('car_id')->references('id')->on('housing_movements_cars');
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
        Schema::dropIfExists('housing_movements_maintenances');
    }
};