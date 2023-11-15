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
           
            $table->bigInteger('number')->nullable()->change();
            $table->date('issue_date')->nullable()->change();
            $table->text('issue_place')->nullable()->change();
            $table->date('expiration_date')->nullable()->change();
            $table->text('file_path')->nullable()->change();
          
            $table->unsignedInteger('employee_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
