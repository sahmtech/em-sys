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
        Schema::table('sales_un_supported_workers', function (Blueprint $table) {
            $table->integer('total_quantity')->nullable()->after('salary');
            $table->integer('remaining_quantity_for_operation')->nullable()->after('total_quantity');
            $table->integer('remaining_quantity_for_delegation')->nullable()->after('remaining_quantity_for_operation');
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
