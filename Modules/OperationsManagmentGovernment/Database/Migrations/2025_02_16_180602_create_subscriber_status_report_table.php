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
        Schema::create('subscriber_status_report', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('location')->nullable();
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('national_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('plate_number')->nullable();
            $table->text('status_details')->nullable();
            $table->string('commercial_register_number')->nullable();
            $table->string('security_supervisor')->nullable();
            $table->string('government_sector')->nullable();
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
        Schema::dropIfExists('subscriber_status_report');
    }
};
