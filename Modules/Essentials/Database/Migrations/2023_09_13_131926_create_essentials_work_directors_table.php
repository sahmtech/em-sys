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
        Schema::create('essentials_work_directors', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->enum('directive_type', ['first_time', 'after_vac']);
            $table->enum('directive_status', ['on_date', 'delay']); // حالة المباشرة
            $table->text('details')->nullable();
            $table->boolean('activation_status');
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
        Schema::dropIfExists('essentials_work_directors');
    }
};
