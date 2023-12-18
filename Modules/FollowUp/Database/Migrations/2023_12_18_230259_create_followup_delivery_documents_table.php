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
        Schema::create('followup_delivery_documents', function (Blueprint $table) {
            $table->id();
            $table->integer('worker_id');
            $table->unsignedInteger('document_id');
            $table->text('file_path');
            $table->text('nots')->nullable();
            $table->foreign('worker_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('followup_documents')->onDelete('cascade');

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
        Schema::dropIfExists('followup_delivery_documents');
    }
};
