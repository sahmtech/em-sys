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
        Schema::create('essentials_employee_appointmets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedInteger('business_location_id');
            $table->string('superior');
            $table->string('job_title');
            $table->string('employee_status');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('essentials_departments')->onDelete('cascade');
            $table->foreign('business_location_id')->references('id')->on('business_locations')->onDelete('cascade');
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
        Schema::dropIfExists('');
    }
};
