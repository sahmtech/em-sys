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
        Schema::create('penalty_reports', function (Blueprint $table) {
            $table->id();
            $table->string('national_id')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('penalty_type')->nullable();
            $table->text('violation_note')->nullable();
            $table->string('security_supervisor')->nullable();
            $table->string('contact_supervisor')->nullable();
            $table->unsignedBigInteger('report_id');
            $table->foreign('report_id')->references('id')->on('operations_reports')->onDelete('cascade');
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
        Schema::dropIfExists('penalty_reports');
    }
};
