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
        Schema::create('essentials_residency_histories', function (Blueprint $table) {
            $table->id();
            $table->date('renew_start_date')->nullable();
            $table->date('renew_end_date')->nullable();
            $table->string('residency_number')->nullable();

             $table->unsignedInteger('worker_id');
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
        Schema::dropIfExists('essentails_residency_history');
    }
};
