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
        Schema::create('payroll_groups', function (Blueprint $table) {
            $table->id();

            $table->string('payroll_group_name')->nullable();
            $table->string('payroll_group_status')->nullable();
            $table->string('total_payrolls')->nullable();
            $table->string('transaction_date')->nullable();

            $table->boolean('hr_management_cleared')->default(false);
            $table->text('hr_management_cleared_by')->nullable();

            $table->boolean('accountant_cleared')->default(false);
            $table->text('accountant_cleared_by')->nullable();

            $table->boolean('financial_management_cleared')->default(false);
            $table->text('financial_management_cleared_by')->nullable();

            $table->boolean('ceo_cleared')->default(false);
            $table->text('ceo_cleared_by')->nullable();





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
        Schema::dropIfExists('payroll_groups');
    }
};
