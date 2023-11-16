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
        Schema::table('followup_worker_requests', function (Blueprint $table) {
           $table->time('escape_time')->nullable()->after('end_date');
           $table->integer('advSalaryAmount')->nullable()->after('escape_time');
           $table->integer('monthlyInstallment')->nullable()->after('advSalaryAmount');
           $table->integer('installmentsNumber')->nullable()->after('monthlyInstallment');

            
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
