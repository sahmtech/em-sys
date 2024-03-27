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
        Schema::create('sales_un_supported_operation_orders', function (Blueprint $table) {

            $table->id();


            $table->string('operation_order_no')->nullable();
            $table->integer('orderQuantity');

            $table->enum('Interview', ['Client', 'Company'])->nullable();
            $table->string('Industry')->nullable();
            $table->string('Location')->nullable();
            $table->string('Delivery')->nullable();

            $table->enum('status', ['done', 'under_process', 'not_started'])->default('not_started');

            $table->boolean('has_visa')->nullable();
            $table->Integer('agency_id')->unsigned()->nullable();
            $table->foreign('agency_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->unsignedBigInteger('workers_order_id')->nullable();
            $table->foreign('workers_order_id')->references('id')->on('sales_un_supported_workers')->onDelete('cascade');

            $table->integer('DelegatedQuantity')->default('0');

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
        Schema::dropIfExists('sales_un_supported_operation_orders');
    }
};
