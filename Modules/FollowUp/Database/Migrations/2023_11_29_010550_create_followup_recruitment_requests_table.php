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
        Schema::create('followup_recruitment_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profession_id')->nullable();
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->date('date')->nullable();
            $table->text('note')->nullable();
            $table->text('attachment')->nullable();
            $table->foreign('profession_id')->references('id')->on('essentials_professions')->onDelete('cascade'); 
            $table->foreign('specialization_id')->references('id')->on('essentials_specializations')->onDelete('cascade'); 
            $table->foreign('nationality_id')->references('id')->on('essentials_countries')->onDelete('cascade'); 
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
        Schema::dropIfExists('followup_recruitment_requests');
    }
};
