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
        Schema::create('communication_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_message_id')->nullable();
            $table->foreign('communication_message_id')->references('id')->on('communication_messages')->onDelete('cascade');
            $table->text('replay')->nullable();
            $table->integer('replied_by')->nullable();
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
        Schema::dropIfExists('communication_replies');
    }
};