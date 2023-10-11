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
        Schema::create('essentials_employees_qualifications', function (Blueprint $table) {
            $table->id();
            $table->string('qualification_type');
            $table->string('major');
            $table->string('graduation_year');
            $table->string('graduation_institution');
            $table->string('graduation_country');
            $table->string('degree');
            $table->unsignedInteger('employee_id');
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
        Schema::dropIfExists('essentials_employees_qualifications');
    }
};
