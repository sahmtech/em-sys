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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('added_by');
            $table->bigInteger('violation_penalties_id');
            $table->string('file_path')->nullable();
            $table->bigInteger('business_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('penalties');
    }
};