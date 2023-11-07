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
        Schema::create('essentials_work_cards', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('project')->nullable();
            $table->integer('workcard_duration')->nullable();
            $table->integer('Payment_number')->nullable();
            $table->string('fees')->nullable();
            $table->string('company_name')->nullable();
            $table->string('fixnumber')->nullable();
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
        Schema::dropIfExists('essentials_work_cards');
    }
};
