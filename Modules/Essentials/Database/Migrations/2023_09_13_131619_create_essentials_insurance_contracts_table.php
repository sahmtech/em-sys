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
        Schema::create('essentials_insurance_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number'); 
            $table->string('company_name');
            $table->string('responsible_person');
            $table->string('phone_number');
            $table->string('mobile_number');
            $table->string('address');
            $table->integer('employees_count');
            $table->integer('dependents_count');
            $table->date('insurance_start_date');
            $table->date('insurance_end_date'); 
            $table->json('documents')->nullable(); 
            $table->text('details')->nullable();
            $table->boolean('activation_status');
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
        Schema::dropIfExists('essentials_insurance_contracts');
    }
};
