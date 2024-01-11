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
        Schema::table('essentials_insurance_classes', function (Blueprint $table) {
            $table->enum('name',['VIP+','VIP','A+','A','B+','B','C+','C','CR+','CR','VVIP','A+S','VIP+S','C6','C4','C6S']);
        });
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
