<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales_services', function (Blueprint $table) {

            // $table->unsignedBigInteger('contact_id')->nullable()->after('id');

            // $table->unsignedBigInteger('sales_project_id')->nullable()->after('contact_id');

            // Add foreign key constraints
            // $table->foreign('sales_project_id')->references('id')->on('sales_projects')->onDelete('cascade');
            // $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('sales_services', function (Blueprint $table) {
            $table->dropForeign(['sales_project_id']);
            $table->dropForeign(['contact_id']);
            $table->dropColumn(['sales_project_id', 'contact_id']);
        });
    }
};
