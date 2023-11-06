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
        Schema::create('essentials_employees_insurances', function (Blueprint $table) {
       
                $table->id();
                $table->unsignedInteger('employee_id');
                $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedBigInteger('insurance_classes_id')->unsigned()->nullable();
                $table->foreign('insurance_classes_id')->references('id')->on('essentials_insurance_classes')->onDelete('cascade');
                $table->integer('insurance_company_id')->unsigned()->nullable();
                $table->foreign('insurance_company_id')->references('id')->on('contacts')->onDelete('cascade');
                $table->enum('status',['new_entry','transferring_sponsorship','fleeing','final_exit','modification_from_borders_to_residence'])->nullable();
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
        Schema::dropIfExists('essentials_employees_health_insurances');
    }
};
