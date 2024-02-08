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
        Schema::table('htr_buildings', function (Blueprint $table) {
            $table->unsignedBigInteger('guard_id')->nullable()->change();
            $table->unsignedBigInteger('supervisor_id')->nullable()->change();
            $table->unsignedBigInteger('cleaner_id')->nullable()->change();
            $table->string('address')->nullable()->change();
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
