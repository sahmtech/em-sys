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
        Schema::create('water_weight', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->Integer('driver_id')->unsigned()->nullable();
            $table->Integer('contact_id')->unsigned();
            $table->string('plate_number')->nullable();
            $table->Integer('project_id')->nullable();
            $table->string('water_droping_location')->nullable();
            $table->enum('weight_type', ['6_tons', '18_tons', '34_tons'])->default('6_tons');
            $table->string('sample_result')->nullable();
            $table->date('date')->nullable();
            $table->unsignedInteger('created_by')->unsigned();
            $table->unsignedInteger('updated_by')->unsigned()->nullable();
            $table->unsignedInteger('deleted_by')->unsigned()->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('sales_projects');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
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
        Schema::dropIfExists('water_weight');
    }
};
