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
        Schema::table('units', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        
        Schema::table('variation_templates', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
    
        Schema::table('warranties', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('product_racks', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
    }
    
    


    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('variation_templates', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
        
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
        Schema::table('product_racks', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
        
    }
};