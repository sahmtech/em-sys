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
        // Schema::table('contacts', function (Blueprint $table) {
        //     $table->enum('evaluation',['Good','bad'])->after('Interview')->nullable();
        //     $table->enum('type', ['basic', 'appendix'])->default('basic');
        // });
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
