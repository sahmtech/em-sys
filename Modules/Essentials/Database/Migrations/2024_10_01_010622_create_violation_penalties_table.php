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
        Schema::create('violation_penalties', function (Blueprint $table) {
            $table->id();
            $table->string('descrption'); 
            $table->string('occurrence');
            $table->string('type');
            $table->string('amount_type');
            $table->decimal('amount');          
            $table->bigInteger('violation_id');
            $table->bigInteger('business_id')->nullable();
            $table->bigInteger('company_id')->nullable();
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
        Schema::dropIfExists('violation_penalties');
    }
};