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
        Schema::table('ir_proposed_labors', function (Blueprint $table) {
            $table->unsignedBigInteger('unSupportedworker_order_id')->nullable()->after('transaction_sell_line_id');
            $table->foreign('unSupportedworker_order_id')->references('id')->on('sales_un_supported_workers')->onDelete('set null');
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