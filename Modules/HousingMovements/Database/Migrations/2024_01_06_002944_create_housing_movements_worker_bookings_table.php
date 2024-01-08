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
        Schema::create('housing_movements_worker_bookings', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->integer('user_id')->unsigned();
            $table->integer('created_by')->unsigned();
           
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
          
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('housing_movements_worker_bookings');
    }
};