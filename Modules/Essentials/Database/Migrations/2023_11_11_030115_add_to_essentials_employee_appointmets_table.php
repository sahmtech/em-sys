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
        Schema::table('essentials_employee_appointmets', function (Blueprint $table) {
            $table->enum('type',['appoint','delegating'])->default('appoint')->after('business_location_id');
            $table->date('start_from')->nullable()->after('type');
            $table->date('end_at')->nullable()->after('start_from');


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
