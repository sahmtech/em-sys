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
        Schema::create('essentials_regions', function (Blueprint $table) {
         
                $table->id();
                $table->json('name');
                $table->unsignedBigInteger('city_id');
                $table->text('details')->nullable();
                $table->boolean('is_active');
                $table->timestamps();
                $table->foreign('city_id')->references('id')->on('essentials_cities')->onDelete('cascade');
           
    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('essentials_regions');
    }
};
