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
        Schema::create('hd_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->nullable();
            $table->Integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('title')->nullable();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('hd_ticket_statuses')->onDelete('set null');
            $table->enum('urgency', ['low', 'mid', 'high', 'urgent'])->default('mid');
            $table->dateTime('last_reply')->nullable();
            $table->boolean('is_read')->default(0);
            $table->boolean('is_replied')->default(0);
            $table->dateTime('reply_time')->nullable();
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
        Schema::dropIfExists('hd_tickets');
    }
};
