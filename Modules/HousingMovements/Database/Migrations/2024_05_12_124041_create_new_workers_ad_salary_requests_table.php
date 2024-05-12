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
        Schema::create('new_workers_ad_salary_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->unique();
            $table->unsignedInteger('related_to');
            $table->integer('advSalaryAmount')->nullable();
            $table->integer('monthlyInstallment')->nullable();
            $table->integer('installmentsNumber')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->string('status')->nullable();
            $table->string('attachment')->nullable();
            $table->text('note')->nullable();
            $table->foreign('related_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('new_workers_ad_salary_requests');
    }
};