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
        Schema::table('housing_movements_cars', function (Blueprint $table) {
            $table->string('plate_registration_type');
            $table->date('manufacturing_year');
            $table->bigInteger('serial_number');
            $table->string('structure_no');
            $table->string('vehicle_status');
            // $table->date('expiry_date');
            $table->date('test_end_date');
            $table->string('examination_status');
            $table->string('insurance_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('housing_movements_cars', function (Blueprint $table) {
        });
    }
};