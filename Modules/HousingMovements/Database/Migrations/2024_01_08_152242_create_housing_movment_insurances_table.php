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
        Schema::create('housing_movment_insurances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
           
            $table->integer('insurance_company_id')->unsigned();
            $table->date('insurance_start_Date');
            $table->date('insurance_end_date');
            $table->foreign('car_id')->references('id')->on('housing_movements_cars');
            $table->foreign('insurance_company_id')->references('id')->on('contacts');



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
        Schema::dropIfExists('housing_movment_insurances');
    }
};