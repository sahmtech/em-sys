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
        Schema::table('essentials_official_documents', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->nullable()->after('employee_id');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedInteger('updated_by')->nullable()->after('created_by');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedInteger('deleted_by')->nullable()->after('updated_by');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');

            $table->dateTime('deleted_at')->nullable()->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {
        });
    }
};