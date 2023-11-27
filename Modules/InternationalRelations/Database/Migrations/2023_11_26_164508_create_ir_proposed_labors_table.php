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
        Schema::create('ir_proposed_labors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');   
            $table->string('mid_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('email')->nullable();
            $table->string('profile_image')->nullable();
            $table->date('dob')->nullable();
            $table->enum('marital_status', ['married', 'unmarried', 'divorced'])->nullable();
            $table->char('blood_group', 10)->nullable();
            $table->char('contact_number', 20)->nullable();
            $table->text('permanent_address')->nullable();
            $table->text('current_address')->nullable();
            $table->unsignedBigInteger('profession_id')->nullable();
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->foreign('profession_id')->references('id')->on('essentials_professions')->onDelete('cascade'); 
            $table->foreign('specialization_id')->references('id')->on('essentials_specializations')->onDelete('cascade'); 
            $table->foreign('nationality_id')->references('id')->on('essentials_countries')->onDelete('cascade');
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
        Schema::dropIfExists('ir_proposed_labors');
    }
};
