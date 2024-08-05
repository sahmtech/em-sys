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
        Schema::create('communication_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_department_id')->nullable();
            $table->foreign('sender_department_id')->references('id')->on('essentials_departments')->onDelete('cascade');
            $table->unsignedBigInteger('reciever_department_id')->nullable();
            $table->foreign('reciever_department_id')->references('id')->on('essentials_departments')->onDelete('cascade');
            $table->integer('sender_id')->nullable();
            $table->text('title')->nullable();
            $table->text('message')->nullable();
            $table->enum('urgency', ['low', 'mid', 'high', 'urgent'])->default('mid');
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
        Schema::dropIfExists('communication_messages');
    }
};