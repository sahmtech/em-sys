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
        Schema::create('sales_orders_operations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('contract_id')->unsigned();
            $table->foreign('contract_id')->references('id')->on('essentials_employees_contracts')->onDelete('cascade'); 

            $table->Integer('contact_id')->unsigned()->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->Integer('agency_id')->unsigned()->nullable();
            $table->foreign('agency_id')->references('id')->on('contacts')->onDelete('cascade');
            
            $table->string('operation_order_no')->nullable();
            $table->enum('operation_order_type', ['Internal', 'External'])->nullable();
            $table->enum('Interview', ['Client ', 'Company'])->nullable();

            $table->string('Location')->nullable();
            $table->string('Delivery')->nullable();
            $table->string('Status')->nullable();
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
