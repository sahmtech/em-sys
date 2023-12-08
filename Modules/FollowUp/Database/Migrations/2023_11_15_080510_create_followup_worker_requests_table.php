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
        Schema::create('followup_worker_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->unique();
            $table->unsignedInteger('worker_id');
            $table->enum('type', [
                'exitRequest',
                'returnRequest',
                'escapeRequest',
                'advanceSalary',
                'leavesAndDepartures',
                'atmCard',
                'residenceRenewal',
                'residenceCard',
                'workerTransfer',
            ]);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('reason');
            $table->text('note')->nullable();
            $table->string('attachment')->nullable();
            $table->string('status')->default('under process');
     
            $table->foreign('worker_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('followup_worker_requests');
    }
};
