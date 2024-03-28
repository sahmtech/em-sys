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
            $table->unsignedBigInteger('unSupportedworker_order_id')->nullable()->after('operation_order_id');
            $table->foreign('unSupportedworker_order_id')->references('id')->on('sales_un_supported_workers')->onDelete('set null');
            $table->unsignedBigInteger('unSupported_operation_id')->nullable()->after('unSupportedworker_order_id');
            $table->foreign('unSupported_operation_id')->references('id')->on('sales_un_supported_operation_orders')->onDelete('set null');
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
