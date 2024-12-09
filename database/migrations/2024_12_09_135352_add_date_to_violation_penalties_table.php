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
        Schema::table('violation_penalties', function (Blueprint $table) {
            $table->date('date')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('violation_penalties', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }

};
