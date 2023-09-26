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
        Schema::create('business_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('license_type',['COMMERCIALREGISTER','Gosi','Zatca','Chamber','Balady','Investment','Rent Contract']);
            $table->integer('licence_number');
            $table->date('licence_date');
            $table->date('renew_date');
            $table->date('expiration_date');
            $table->string('issuing_location');
            $table->text('details');
            $table->unsignedInteger('busines_id');
            $table->string('name_file');
            $table->string('path_file');
            $table->foreign('busines_id')->references('id')->on('business');
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
        Schema::dropIfExists('business_documents');
    }
};
