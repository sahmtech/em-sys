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
        Schema::table('essentials_employees_qualifications', function (Blueprint $table) {

                $table->string('qualification_type')->nullable()->change();
                $table->string('graduation_year')->nullable()->change();
                $table->string('graduation_institution')->nullable()->change();
                $table->string('graduation_country')->nullable()->change();
                
                
                
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
