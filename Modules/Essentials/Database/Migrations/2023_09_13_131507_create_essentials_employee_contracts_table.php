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
        Schema::create('essentials_employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number');
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->json('employee_files')->nullable()->change(); 
            $table->date('contract_start_date');
            $table->date('contract_end_date'); 
            $table->integer('contract_duration'); 
            $table->string('probation_period'); 
            $table->decimal('salary', 10, 2);
            $table->string('profile_image');
            $table->boolean('is_active'); 
            $table->boolean('is_renewable');
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
        Schema::dropIfExists('essentials_employee_contracts');
    }
};
