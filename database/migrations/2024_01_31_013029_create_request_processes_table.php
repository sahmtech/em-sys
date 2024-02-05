<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_processes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('procedure_id')->nullable();
            $table->unsignedBigInteger('superior_department_id')->nullable();

            $table->unsignedInteger('updated_by')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->boolean('is_returned')->nullable();
            
            $table->boolean('is_escalated')->default(0);
            $table->string('sub_status')->nullable();

            $table->text('reason')->nullable();
            $table->text('note')->nullable();
           

            $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
            $table->foreign('procedure_id')->references('id')->on('wk_procedures')->onDelete('cascade');
            $table->foreign('superior_department_id')->references('id')->on('essentials_departments');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            
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
        Schema::dropIfExists('request_processes');
    }
};
