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
        Schema::create('essentials_companies_insurances_contracts', function (Blueprint $table) {
            $table->id();


            $table->integer('company_id')->unsigned()->nullable(); 
            $table->foreign('company_id')->references('id')
            ->on('companies')->onDelete('cascade');



            $table->integer('insur_id')->unsigned()->nullable(); 
            $table->foreign('insur_id')->references('id')
            ->on('contacts')->onDelete('cascade');

            $table->date('insurance_start_date')->nullable(); 
            $table->date('insurance_end_date')->nullable(); 


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
        Schema::dropIfExists('essentials_companies_insurances_contracts');
    }
};
