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
        Schema::table('ir_proposed_labors', function (Blueprint $table) {
          
            $table->dropColumn(['profession_id']);
            $table->dropColumn(['specialization_id']);
            $table->dropColumn(['nationality_id']);

         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('from_ir_proposed_labors', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->timestamps();
        });
    }
};
