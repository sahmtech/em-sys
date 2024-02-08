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
       
            $table->dropColumn('guard_id');
            $table->json('guard_ids_data')->nullable()->after('building_contract_end_date');
        });
    }

    /**building_contract_end_date
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
