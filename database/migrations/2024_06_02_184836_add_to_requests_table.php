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
        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedBigInteger('job_title_id')->nullable()->after('return_date');
            $table->unsignedBigInteger('specialization_id')->nullable()->after('job_title_id');
            $table->unsignedBigInteger('nationality_id')->nullable()->after('specialization_id');
            $table->string('number_of_salary_inquiry')->nullable()->after('nationality_id');
            $table->unsignedBigInteger('sale_project_id')->nullable()->after('number_of_salary_inquiry');
            $table->date('interview_date')->nullable()->after('sale_project_id');
            $table->time('interview_time')->nullable()->after('interview_date');
            $table->string('interview_place')->nullable()->after('interview_time');

            $table->foreign('sale_project_id')->references('id')->on('sales_projects')->onDelete('cascade');
            $table->foreign('nationality_id')->references('id')->on('essentials_countries')->onDelete('cascade');
            $table->foreign('job_title_id')->references('id')->on('essentials_professions')->onDelete('cascade');
            $table->foreign('specialization_id')->references('id')->on('essentials_specializations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            //
        });
    }
};
