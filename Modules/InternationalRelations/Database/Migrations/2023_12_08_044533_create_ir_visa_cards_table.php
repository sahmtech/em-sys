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
        Schema::create('ir_visa_cards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('visa_number')->nullable();
            $table->date('arrival_date')->nullable();
            $table->bigInteger('operation_order_id')->unsigned()->nullable();
            $table->foreign('operation_order_id')->references('id')->on('sales_orders_operations')->onDelete('cascade');
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
        Schema::dropIfExists('ir_visa_cards');
    }
};
