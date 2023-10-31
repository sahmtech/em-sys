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
        Schema::create('htr_rooms', function (Blueprint $table) {
            $table->id();
            $table->integer('room_number');
            $table->unsignedBigInteger('htr_building_id');
            $table->decimal('area');
            $table->integer('beds_count');
            $table->text('contents')->nullable();
            $table->foreign('htr_building_id')->references('id')->on('htr_buildings');
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
        Schema::dropIfExists('htr_rooms');
    }
};
