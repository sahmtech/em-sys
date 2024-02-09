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
        Schema::create('worker_projects_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('worker_id')->nullable();
            $table->foreign('worker_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('type')->nullable();


            $table->unsignedBigInteger('old_project_id')->unsigned()->nullable();
            $table->foreign('old_project_id')->references('id')->on('sales_projects')->onDelete('set null');

            $table->unsignedBigInteger('new_project_id')->unsigned()->nullable();
            $table->foreign('new_project_id')->references('id')->on('sales_projects')->onDelete('set null');

            $table->date('canceled_date')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('worker_projects_histories');
    }
};
