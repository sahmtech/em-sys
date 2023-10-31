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
        Schema::create('sales_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profession_id')->nullable();
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->foreign('profession_id')->references('id')->on('essentials_professions')->onDelete('cascade'); 
            $table->foreign('specialization_id')->references('id')->on('essentials_specializations')->onDelete('cascade'); 
            $table->foreign('nationality_id')->references('id')->on('essentials_countries')->onDelete('cascade'); 
            $table->enum('gender',['male','female'])->nullable();
            $table->decimal('service_price')->nullable();
            $table->decimal('monthly_cost_for_one')->nullable();
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade'); 
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
        Schema::dropIfExists('sales_services');
    }
};
