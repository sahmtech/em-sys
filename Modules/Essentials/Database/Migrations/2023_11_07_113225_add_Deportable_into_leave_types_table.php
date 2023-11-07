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
            $table->boolean('Deportable')->default(false)->after('duration')->nullable();
            $table->string('allowance')->after('Deportable')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('essentials_leave_types', function (Blueprint $table) {
         
            $table->dropColumn('Deportable');
        });
    }
};
