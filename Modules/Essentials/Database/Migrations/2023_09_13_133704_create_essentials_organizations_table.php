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
        Schema::create('essentials_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); 
            $table->enum('level_type',['one_level','other']);
            $table->string('parent_level');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('account_number')->nullable();
            $table->text('details')->nullable();
            $table->boolean('is_active');
            $table->timestamps();
            $table->foreign('bank_id')->references('id')->on('essentials_bank_accounts')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('essentials_organizations');
    }
};
