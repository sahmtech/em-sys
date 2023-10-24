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
        Schema::table('sales_orders_operations', function (Blueprint $table) {
            // Define the foreign key column and the referenced table
            $table->bigInteger('sale_contract_id')->unsigned()->nullable();
            $table->foreign('sale_contract_id')->references('id')->on('sales_contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_orders_operations', function (Blueprint $table) {
            // Remove the foreign key constraint
            $table->dropForeign(['sale_contract_id']);
            $table->dropColumn('sale_contract_id'); // If you want to drop the column as well
        });
    }
};
