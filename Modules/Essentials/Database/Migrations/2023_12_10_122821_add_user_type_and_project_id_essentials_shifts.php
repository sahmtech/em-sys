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
        Schema::table('essentials_shifts', function (Blueprint $table) {
            $table->string('user_type')
            ->after('auto_clockout_time')
            ->nullable();
         
            $table->bigInteger('project_id')
            ->after('user_type')->nullable();

            // $table->foreign('project_id')->references('id')->on('sales_projects');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('essentials_shifts', function (Blueprint $table) {

        });
    }
};