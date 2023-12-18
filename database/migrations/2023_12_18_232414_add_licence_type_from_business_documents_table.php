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
            $table->enum('licence_type',['COMMERCIALREGISTER','Gosi','Zatca','Chamber','Balady','saudizationCertificate','VAT','Investment','Rent','Contract' ,'memorandum_of_association','national_address','activity'])
            ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_documents', function (Blueprint $table) {
            //
        });
    }
};
