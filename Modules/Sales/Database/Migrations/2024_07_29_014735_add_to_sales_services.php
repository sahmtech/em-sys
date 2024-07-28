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
        Schema::table('sales_services', function (Blueprint $table) {
            $table->decimal('gosi_amount', 15, 2)->nullable()->after('additional_allwances');
            $table->decimal('vacation_amount', 15, 2)->nullable()->after('gosi_amount');
            $table->decimal('end_service_amount', 15, 2)->nullable()->after('vacation_amount');
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
