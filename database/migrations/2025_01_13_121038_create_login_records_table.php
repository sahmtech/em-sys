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
        if (!Schema::hasTable('login_records')) { // Check if the table exists
            Schema::create('login_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');

                // Define the foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->string('ip_address', 191)->nullable();
                $table->string('device', 191)->nullable();
                $table->string('location', 191)->nullable();
                $table->string('browser', 191)->nullable();
                $table->string('os', 191)->nullable();
                $table->boolean('is_successful')->default(true);
                $table->string('session_id', 191)->nullable();
                $table->text('additional_data')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_records');
    }
};
