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
        Schema::create('sale_operation_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('contract_id')->unsigned();
            $table->foreign('contract_id')->references('id')->on('essentials_employees_contracts')->onDelete('cascade'); 
            $table->string('Industry')->nullable();
            $table->string('Interview')->nullable();
            $table->string('Location')->nullable();
            $table->string('Delivery')->nullable();
            $table->string('Agency')->nullable();
            $table->string('Profession')->nullable();
            $table->string('Notes')->nullable();
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
        Schema::dropIfExists('sale_operation_orders');
    }
};
