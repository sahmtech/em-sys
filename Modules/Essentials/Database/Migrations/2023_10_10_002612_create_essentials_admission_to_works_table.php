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
        
        Schema::create('essentials_admission_to_works', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->unsignedBigInteger('department_id');
            $table->enum('admissions_type', ['first_time', 'after_vac']);
            $table->enum('admissions_status', ['on_date', 'delay']);
            $table->date('admissions_date');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('essentials_departments')->onDelete('cascade');
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
        Schema::dropIfExists('essentials_admission_to_works');
    }
};
