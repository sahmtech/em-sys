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
        Schema::table('projects_documents', function (Blueprint $table) {
            $table->integer('project_department_id')->nullable()->after('sales_project_id');
            $table->foreign('project_department_id')->references('id')->on('project_departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_documents', function (Blueprint $table) {
            $table->dropForeign(['project_department_id']);
            $table->dropColumn('project_department_id');
        });
    }
};
