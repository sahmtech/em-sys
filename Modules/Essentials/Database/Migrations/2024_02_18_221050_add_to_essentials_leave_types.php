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
        Schema::table('essentials_leave_types', function (Blueprint $table) {
            $table->string('due_date')->nullable()->after('max_leave_count');
            $table->string('include_salary')->nullable()->after('due_date');
            $table->boolean('extendable')->default('0')->nullable()->after('include_salary');
            $table->string('gender')->nullable()->after('extendable');


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
