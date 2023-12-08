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
            
            $table->Integer('transaction_sell_line_id')->unsigned()->nullable()->after('id');
            $table->foreign('transaction_sell_line_id')->references('id')->on('transaction_sell_lines')->onDelete('cascade');

            $table->Integer('agency_id')->unsigned()->nullable()->after('transaction_sell_line_id');
            $table->foreign('agency_id')->references('id')->on('contacts')->onDelete('cascade');
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
