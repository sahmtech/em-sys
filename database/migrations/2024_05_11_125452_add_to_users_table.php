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
        Schema::table('users', function (Blueprint $table) {

            $table->boolean('has_SIM')->default(0)->after('contact_number');
            $table->string('cell_phone_company')->nullable()->after('has_SIM');
            $table->boolean('residency_print')->default(0)->after('id_proof_number');
            $table->boolean('residency_delivery')->default(0)->after('residency_print');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};