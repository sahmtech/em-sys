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
        Schema::create('access_role_company_user_types', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('access_role_company_id')->unsigned()->nullable();
            $table->foreign('access_role_company_id')->references('id')->on('access_role_companies')->onDelete('cascade');
            $table->string('user_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_role_company_user_types');
    }
};
