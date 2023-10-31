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
        Schema::create('ir_delegations', function (Blueprint $table) {
            $table->id();

            $table->Integer('transaction_sell_line_id')->unsigned()->nullable();
            $table->foreign('transaction_sell_line_id')->references('id')->on('transaction_sell_lines')->onDelete('cascade');

            $table->Integer('agency_id')->unsigned()->nullable();
            $table->foreign('agency_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->integer('targeted_quantity')->nullable();
            
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
        Schema::dropIfExists('ir_delegations');
    }
};
