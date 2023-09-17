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
       
        Schema::table('essentials_employee_contracts', function (Blueprint $table) {
        $table->unsignedBigInteger('basic_salary_type_id'); 
        $table->unsignedBigInteger('work_type_id'); 
        $table->unsignedBigInteger('travel_ticket_category_id'); 
      
        $table->unsignedBigInteger('allowances_id'); // البدلات
        $table->unsignedBigInteger('deductions_id'); // الاستحقاقات
        
        $table->foreign('basic_salary_type_id')->references('id')->on('essentials_basic_salary_types')->onDelete('cascade');
        $table->foreign('work_type_id')->references('id')->on('essentials_work_types')->onDelete('cascade');
        $table->foreign('travel_ticket_category_id')->references('id')->on('essentials_travel_ticket_categories')->onDelete('cascade');
        $table->foreign('allowances_id')->references('id')->on('essentials_allowance_types')->onDelete('cascade');
        $table->foreign('deductions_id')->references('id')->on('essentials_entitlement_types')->onDelete('cascade');
        
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
