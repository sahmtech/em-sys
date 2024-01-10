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
        Schema::table('housing_movements_worker_bookings', function (Blueprint $table) {
            $table->date('booking_start_Date');
            $table->date('booking_end_Date');
        });
    }
 
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('housing_movements_worker_bookings', function (Blueprint $table) {

        });
    }
};