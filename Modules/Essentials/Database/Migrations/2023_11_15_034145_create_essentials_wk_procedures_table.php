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
        Schema::create('essentials_wk_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('next_department_id')->nullable();
            $table->boolean('start')->nullable();
            $table->boolean('end')->nullable();
            $table->boolean('can_reject')->nullable();
            $table->boolean('can_return')->nullable();
            $table->timestamps();
            $table->foreign('department_id')->references('id')->on('essentials_departments');
            $table->foreign('next_department_id')->references('id')->on('essentials_departments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('essentials_wk_procedures');
    }
};
