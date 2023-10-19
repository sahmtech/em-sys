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
        Schema::create('essentials_specializations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('en_name')->nullable();
            $table->unsignedBigInteger('profession_id');
            $table->foreign('profession_id')->references('id')->on('essentials_professions')->onDelete('cascade');
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
        Schema::dropIfExists('essentials_specializations');
    }
};
