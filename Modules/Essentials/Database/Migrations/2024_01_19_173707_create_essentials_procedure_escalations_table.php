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
        Schema::create('essentials_procedure_escalations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procedure_id');
            $table->foreign('procedure_id')->references('id')->on('essentials_wk_procedures')->onDelete('cascade');
            $table->unsignedBigInteger('escalates_to')->nullable();
            $table->foreign('escalates_to')->references('id')->on('essentials_departments');
            $table->integer('escalates_after')->nullable();
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
        Schema::dropIfExists('essentials_procedure_escalations');
    }
};
