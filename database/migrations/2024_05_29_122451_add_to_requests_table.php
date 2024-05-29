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
        Schema::table('requests', function (Blueprint $table) {

            $table->string('authorized_entity')->nullable()->after('contract_sub_reason_id');
            $table->string('commissioner_info')->nullable()->after('authorized_entity');


            $table->string('trip_type')->nullable()->after('commissioner_info');
            $table->string('Take_off_location')->nullable()->after('trip_type');
            $table->string('destination')->nullable()->after('Take_off_location');
            $table->string('weight_of_furniture')->nullable()->after('destination');
            $table->date('date_of_take_off')->nullable()->after('weight_of_furniture');
            $table->time('time_of_take_off')->nullable()->after('date_of_take_off');
            $table->date('return_date')->nullable()->after('time_of_take_off');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            //
        });
    }
};
