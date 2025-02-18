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
        Schema::create('incident_report', function (Blueprint $table) {
            $table->id();
            $table->string('supervisor_name')->nullable();
            $table->json('rotion_damage_types')->nullable();
            $table->string('location')->nullable();
            $table->string('squar')->nullable();
            $table->string('full_name')->nullable();
            $table->string('national_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('car_model')->nullable();
            $table->string('car_year')->nullable();
            $table->text('notes')->nullable();
            $table->string('damage_quantity')->nullable();
            $table->boolean('full_damage')->nullable();
            $table->boolean('partial_damage')->nullable();
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
        Schema::dropIfExists('incident_report');
    }
};
