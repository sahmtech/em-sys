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
        Schema::create('ir_workers_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('worker_id')->unsigned()->nullable();
            $table->enum('type',['offer_price','acceptance_offer','visa'])->nullable();
            $table->text('attachment');
            $table->foreign('worker_id')->references('id')->on('ir_proposed_labors')->onDelete('cascade');
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
        Schema::dropIfExists('ir_workers_documents');
    }
};
