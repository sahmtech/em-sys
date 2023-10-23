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
        Schema::create('', function (Blueprint $table) {
            $table->id();
            $table->integer('contract_number')->nullable();
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable(); 
            $table->string('contract_duration')->nullable(); 
            $table->string('probation_period')->nullable();
            $table->string('status')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_renewable')->nullable();
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
