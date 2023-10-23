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
        Schema::create('sales_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number_of_contract')->nullable();
            $table->unsignedInteger('offer_price_id')->nullable();   
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('items_ids')->nullable();
            $table->boolean('is_renwable')->nullable();
            $table->enum('status', ['valid', 'finished'])->default('valid');
            $table->boolean('operation_order')->default(0);
            $table->text('file')->nullable();
            $table->text('notes')->nullable();
            $table->foreign('offer_price_id')->references('id')->on('transactions')->onDelete('cascade');
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
        Schema::dropIfExists('sales_contracts');
    }
};
