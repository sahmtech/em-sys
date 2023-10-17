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
        Schema::create('sales_targeted_clients', function (Blueprint $table) {
            $table->id();
            $table->string('profession')->nullable();
            $table->string('specialization')->nullable();
            $table->string('nationality')->nullable();
            $table->enum('gender',['male','female'])->nullable();
            $table->integer('number')->nullable();
            $table->decimal('salary')->nullable();
            $table->enum('food_allowance',['cash','insured_by_the_other'])->nullable();
            $table->enum('housing_allowance',['cash','insured_by_the_other'])->nullable();
            $table->decimal('monthly_cost')->nullable();
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
        Schema::dropIfExists('sales_targeted_clients');
    }
};
