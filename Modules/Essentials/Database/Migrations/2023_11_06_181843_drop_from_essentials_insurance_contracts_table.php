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
        Schema::table('essentials_insurance_contracts', function (Blueprint $table) {
            $table->dropColumn('employees_count');
            $table->dropColumn('dependents_count');
            $table->dropColumn('policy_value');
            $table->dropColumn('attachments');

             	 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
};
