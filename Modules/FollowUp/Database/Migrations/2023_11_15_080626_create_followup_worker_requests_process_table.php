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
        Schema::create('followup_worker_requests_process', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_request_id');
            $table->unsignedBigInteger('procedure_id');
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->text('reason')->nullable();
            $table->text('status_note')->nullable();
            $table->timestamps();

            $table->foreign('worker_request_id')->references('id')->on('followup_worker_requests');
            $table->foreign('procedure_id')->references('id')->on('essentials_wk_procedures');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followup_worker_requests_process');
    }
};
