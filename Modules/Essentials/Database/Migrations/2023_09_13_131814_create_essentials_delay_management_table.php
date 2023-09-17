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
        Schema::create('essentials_delay_management', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->date('from_date');
            $table->time('from_time');
            $table->date('to_date');
            $table->time('to_time');
            $table->integer('deduction_duration');
            $table->boolean('employee_recall');
            $table->boolean('employee_attendance');
            $table->text('details')->nullable();
            $table->boolean('is_active');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('essentials_delay_management');
    }
};
