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
        Schema::create('htr_rooms_workers_histories', function (Blueprint $table) {
            $table->id();


            $table->unsignedBigInteger('room_id')->nullable();
            $table->foreign('room_id')->references('id')->on('htr_rooms');

            $table->unsignedInteger('worker_id')->nullable();
            $table->foreign('worker_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('');
    }
};
