<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
  
            $table->unsignedBigInteger('profession_id')->nullable()->after('type');
            $table->unsignedBigInteger('specialization_id')->nullable()->after('profession_id');
            $table->unsignedBigInteger('nationality_id')->nullable()->after('specialization_id');
            $table->foreign('profession_id')->references('id')->on('essentials_professions')->onDelete('cascade'); 
            $table->foreign('specialization_id')->references('id')->on('essentials_specializations')->onDelete('cascade'); 
            $table->foreign('nationality_id')->references('id')->on('essentials_countries')->onDelete('cascade'); 
            $table->enum('gender',['male','female'])->nullable()->after('nationality_id');
            $table->decimal('service_price')->nullable()->after('gender');
            $table->decimal('monthly_cost_for_one')->nullable()->after('service_price');
     
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
