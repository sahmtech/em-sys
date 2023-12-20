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
        Schema::table('essentials_attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable()->after('id');
            $table->foreign('status_id')->references('id')->on('essentials_attendance_statuses')->onDelete('set null');
            $table->unsignedInteger('business_location_id')->nullable()->after('business_id');
            $table->foreign('business_location_id')->references('id')->on('business_locations')->onDelete('set null');
            $table->double('clock_in_lng', 10, 6)->nullable()->after('clock_in_location');
            $table->double('clock_in_lat', 10, 6)->nullable()->after('clock_in_location');
            $table->double('clock_out_lng', 10, 6)->nullable()->after('clock_out_location');
            $table->double('clock_out_lat', 10, 6)->nullable()->after('clock_out_location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('essentials_attendances', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropForeign(['business_location_id']);
            $table->dropColumn('status_id');
            $table->dropColumn('business_location_id');
            $table->dropColumn('clock_out_lat');
            $table->dropColumn('clock_out_lng');
            $table->dropColumn('clock_in_lat');
            $table->dropColumn('clock_in_lng');
        });
    }
};
