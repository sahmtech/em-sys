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
        Schema::create('essentials_financial_months', function (Blueprint $table) {
            $table->id();
            $table->json('month_info');
            $table->json('year_info');
            $table->integer('sequence');
            $table->integer('month_duration');
            $table->date('month_start_date');
            $table->date('month_end_date');
            $table->text('details')->nullable();
            $table->boolean('is_active');
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
        Schema::dropIfExists('essentials_financial_months');
    }
};
