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
            $table->string('organization');
            $table->enum('level', ['one', 'other']);
            $table->unsignedBigInteger('parent_department_id')->nullable();
            $table->boolean('is_main_department')->default(false);
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
