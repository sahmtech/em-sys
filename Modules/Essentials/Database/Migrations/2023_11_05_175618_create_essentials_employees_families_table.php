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
        Schema::create('essentials_employees_families', function (Blueprint $table) {
          
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->text('address')->nullable();
                $table->integer('age')->nullable();
                $table->enum('gender',['male','female'])->nullable();;
                $table->enum('relative_relation', ['father', 'mother', 'sibling', 'spouse', 'child', 'other'])->nullable();
                $table->integer('eqama_number')->nullable();
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
        Schema::dropIfExists('essentials_employees_families');
    }
};
