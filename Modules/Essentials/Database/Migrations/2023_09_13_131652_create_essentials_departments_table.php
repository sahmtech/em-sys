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
        Schema::create('essentials_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('level', ['one', 'second','third','fourth','fifth']);
            $table->unsignedBigInteger('parent_department_id')->nullable();
            
            $table->date('creation_date'); 
            $table->string('location');
            $table->text('details')->nullable();
            $table->boolean('is_active');
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
        Schema::dropIfExists('essentials_departments');
    }
};
