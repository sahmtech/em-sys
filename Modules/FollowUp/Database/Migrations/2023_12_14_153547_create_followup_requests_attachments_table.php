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
        Schema::create('followup_requests_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->text('file_path');
            $table->foreign('request_id')->references('id')->on('followup_worker_requests')->onDelete('cascade');
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
        Schema::dropIfExists('followup_requests_attachments');
    }
};
