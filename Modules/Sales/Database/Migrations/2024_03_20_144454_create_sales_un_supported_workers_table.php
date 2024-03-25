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
        Schema::create('sales_un_supported_workers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profession_id')->nullable();
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('salary')->nullable();
            $table->date('date')->nullable();
            $table->text('note')->nullable();
            $table->text('attachment')->nullable();
            $table->enum('status', ['pending', 'ended'])->nullable()->default('pending');
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
        Schema::dropIfExists('sales_un_supported_workers');
    }
};
