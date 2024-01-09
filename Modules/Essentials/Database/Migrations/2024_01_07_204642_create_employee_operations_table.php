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
        Schema::create('essentails_employee_operations', function (Blueprint $table) {
           
                $table->id();
              
                $table->enum('operation_type', ['absent_report', 'final_visa', 'return_visa']);
                $table->date('start_date');
                $table->date('end_date');

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
        Schema::dropIfExists('employee_operations');
    }
};
