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
        Schema::create('sales_costs', function (Blueprint $table) {
            $table->id();
            $table->string('description')->nullable();
            $table->string('amount')->nullable();
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
        Schema::dropIfExists('sales_costs');
    }
};
