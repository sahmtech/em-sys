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
        Schema::table('essentials_employees_qualifications', function (Blueprint $table) {
        $table->unsignedBigInteger('specialization')->nullable()->after('qualification_type');
        $table->unsignedBigInteger('sub_specialization')->nullable()->after('specialization');
        $table->foreign('specialization')->references('id')->on('essentials_professions')->onDelete('cascade'); 
        $table->foreign('sub_specialization')->references('id')->on('essentials_specializations')->onDelete('cascade');    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
};
