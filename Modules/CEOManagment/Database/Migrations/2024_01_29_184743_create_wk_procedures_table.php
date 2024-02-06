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
        Schema::create('wk_procedures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_type_id'); 
            $table->integer('business_id')->unsigned();
            $table->enum('request_owner_type',['employee','worker']); 
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('next_department_id')->nullable();
          
            $table->boolean('start')->nullable();
            $table->boolean('end')->nullable();
            $table->boolean('can_reject')->nullable();
            $table->boolean('can_return')->nullable();
       
         
            $table->foreign('request_type_id')->references('id')->on('requests_types');
            $table->foreign('department_id')->references('id')->on('essentials_departments');
            $table->foreign('next_department_id')->references('id')->on('essentials_departments');
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
           
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
        Schema::dropIfExists('wk_procedures');
    }
};
