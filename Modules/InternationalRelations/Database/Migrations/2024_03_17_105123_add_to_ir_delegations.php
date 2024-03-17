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
        Schema::table('ir_delegations', function (Blueprint $table) {
            $table->bigInteger('operation_order_id')->unsigned()->nullable()->after('transaction_sell_line_id');
            $table->foreign('operation_order_id')->references('id')->on('sales_orders_operations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {
        });
    }
};
