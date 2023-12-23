<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('total_worker_number')->nullable()->after('is_quotation');
            $table->string('business_fees')->nullable()->after('total_worker_number');
            $table->string('total_worker_monthly')->nullable()->after('business_fees');
            $table->string('contract_duration')->nullable()->after('total_worker_monthly');
            $table->string('total_contract_cost')->nullable()->after('contract_duration');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
