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
        Schema::table('business_documents', function (Blueprint $table) {
            $table->integer('register_number')->nullable()->after('licence_number');
            $table->string('capital')->nullable()->after('register_number');
            $table->string('national_address')->nullable()->after('capital');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('_business_documents', function (Blueprint $table) {
            //
        });
    }
};
