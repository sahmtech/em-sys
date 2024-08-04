<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('communication_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_message_id')->nullable();
            $table->foreign('communication_message_id')->references('id')->on('communication_messages')->onDelete('set null');
            $table->unsignedBigInteger('communication_reply_id')->nullable();
            $table->foreign('communication_reply_id')->references('id')->on('communication_replies')->onDelete('set null');
            $table->enum('type', ['message', 'reply'])->nullable();
            $table->text('path');
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
        Schema::dropIfExists('communication_attachments');
    }
};