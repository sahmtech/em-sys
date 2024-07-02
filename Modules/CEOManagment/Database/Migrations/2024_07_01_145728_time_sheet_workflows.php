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

        Schema::create('time_sheet_workflows', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->unsigned()->nullable();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('next_department_id')->nullable();
            $table->boolean('clients_allowed')->default(false);
            $table->boolean('start')->default(false);
            $table->boolean('end')->default(false);
            $table->integer('step_number')->nullable();
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
        //
    }
};
