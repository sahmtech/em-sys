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
        Schema::create('sales_salaries_requests', function (Blueprint $table) {
            $table->id();
           
            $table->unsignedInteger('worker_id');
            $table->foreign('worker_id')->references('id')->on('users')->onDelete('cascade');
        
            $table->integer('salary')->nullable(); 
            $table->text('file')->nullable(); 
            $table->string('arrival_period')->nullable(); 
            $table->integer('recruitment_fees')->nullable(); 
        
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
        Schema::dropIfExists('sales_salaries_requests');
    }
};
