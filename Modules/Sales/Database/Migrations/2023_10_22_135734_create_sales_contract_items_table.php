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
        Schema::create('sales_contract_items', function (Blueprint $table) {
            $table->id();
            $table->integer('number_of_item');
            $table->string('name_of_item');
            $table->text('details')->nullable();
            $table->enum('type', ['basic', 'appendix'])->default('basic');
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
        Schema::dropIfExists('sales_contract_items');
    }
};
