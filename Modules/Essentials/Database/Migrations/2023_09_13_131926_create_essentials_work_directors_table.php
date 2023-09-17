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
        Schema::create('essentials_admissions_to_work', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->enum('dmissions_type', ['first_time', 'after_vac']);
            $table->enum('dmissions_status', ['on_date', 'delay']); // حالة المباشرة
            $table->text('details')->nullable();
            $table->boolean('is_active');
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
