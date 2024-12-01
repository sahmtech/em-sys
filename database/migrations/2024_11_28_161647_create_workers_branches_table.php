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
        Schema::create('workers_branches', function (Blueprint $table) {
            $table->id(); // Defaults to unsignedBigInteger
            $table->unsignedInteger('user_id'); // Matches int(10) unsigned in users
            $table->unsignedBigInteger('contact_location_id'); // Matches bigint(20) unsigned in contact_locations
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('contact_location_id')->references('id')->on('contact_locations')->onDelete('cascade');
            $table->dateTime('leave_date')->nullable();
            $table->boolean('is_active')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workers_branches');
    }
};
