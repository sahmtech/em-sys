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
        Schema::table('followup_recruitment_requests', function (Blueprint $table) {
            $table->boolean('change_status')->default(false)->after('assigned_to'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('followuprecuirementRequest', function (Blueprint $table) {

        });
    }
};
