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
        Schema::create('request_travel_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id')->nullable();
            $table->integer('travel_agency_id')->unsigned()->nullable();
            $table->date('tripDate')->nullable();
            $table->time('tripTime')->nullable();

            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('travel_agency_id')->references('id')->on('contacts');

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
        Schema::dropIfExists('request_travel_categories');
    }
};