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
        Schema::create('sales_offer_prices_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cost_id')->nullable();
            $table->foreign('cost_id')->references('id')->on('sales_costs')->onDelete('cascade');
            $table->unsignedInteger('offer_price_id')->nullable();   
            $table->foreign('offer_price_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->string('amount')->nullable();
            $table->string('description')->nullable();
            $table->string('duration_by_month')->nullable();
            $table->string('monthly_cost')->nullable();
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
        Schema::dropIfExists('sales_offer_prices_costs');
    }
};
