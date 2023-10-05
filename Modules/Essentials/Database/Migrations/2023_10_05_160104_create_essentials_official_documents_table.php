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
        Schema::create('essentials_official_documents', function (Blueprint $table) {
           
                $table->id();
                $table->enum('type',['national_id','passport','residence_permit','drivers_license','car_registration','international_certificate']);
                $table->bigInteger('number');
                $table->date('issue_date');
                $table->text('issue_place');
                $table->date('expiration_date');
                $table->text('file_path');
                $table->enum('status',['valid','expired']);
                $table->unsignedInteger('employee_id');
                $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('essentials_official_documents');
    }
};
