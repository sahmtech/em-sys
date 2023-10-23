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
        Schema::create('sales_contract_appendics', function (Blueprint $table) {
            $table->id();
            $table->string('number_of_appendix')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->unsignedBigInteger('contract_item_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreign('contract_id')->references('id')->on('sales_contracts')->onDelete('cascade');
            $table->foreign('contract_item_id')->references('id')->on('sales_contract_items')->onDelete('cascade');
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
        Schema::dropIfExists('sales_contract_appendics');
    }
};
