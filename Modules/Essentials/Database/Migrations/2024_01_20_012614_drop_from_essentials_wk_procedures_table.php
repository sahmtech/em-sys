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
    {   Schema::table('essentials_wk_procedures', function (Blueprint $table) {
        $table->dropColumn(['escalates_after' ,'escalates_to']);
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('from_essentials_wk_procedures', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->timestamps();
        });
    }
};
