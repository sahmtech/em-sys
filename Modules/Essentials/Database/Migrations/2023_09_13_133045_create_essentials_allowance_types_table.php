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
        Schema::create('essentials_allowance_types', function (Blueprint $table) {
            $table->id();
            $table->string('allowance_name');
            $table->string('allowance_type');
            $table->enum('allowance_value',['constant','percentage_of_salary']);
            $table->integer('months_number');
            $table->boolean('paid_with_salary');
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
        Schema::dropIfExists('essentials_allowance_types');
    }
};
