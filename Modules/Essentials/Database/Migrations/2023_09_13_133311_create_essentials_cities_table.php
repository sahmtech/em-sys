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
        Schema::create('essentials_cities', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en'); 
            $table->unsignedBigInteger('country_id');
            $table->text('details')->nullable();
            $table->boolean('activation_status');
            $table->timestamps();
            $table->foreign('country_id')->references('id')->on('essentials_countries')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('essentials_cities');
    }
};
