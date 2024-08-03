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
        Schema::create('payroll_groups', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_group_name')->nullable();
            $table->string('payroll_group_status')->nullable();
            $table->string('total_payrolls')->nullable();
            $table->string('transaction_date')->nullable();

            $table->boolean('hr_management_cleared')->default(false);
            $table->dateTime('hr_management_cleared_at')->nullable();
            $table->unsignedInteger('hr_management_cleared_by')->nullable();
            $table->foreign('hr_management_cleared_by')->references('id')->on('users')->onDelete('cascade');

            $table->boolean('accountant_cleared')->default(false);
            $table->dateTime('accountant_cleared_at')->nullable();
            $table->unsignedInteger('accountant_cleared_by')->nullable();
            $table->foreign('accountant_cleared_by')->references('id')->on('users')->onDelete('cascade');

            $table->boolean('financial_management_cleared')->default(false);
            $table->dateTime('financial_management_at')->nullable();
            $table->unsignedInteger('financial_management_by')->nullable();
            $table->foreign('financial_management_by')->references('id')->on('users')->onDelete('cascade');

            $table->boolean('ceo_cleared')->default(false);
            $table->dateTime('ceo_cleared_at')->nullable();
            $table->unsignedInteger('ceo_cleared_by')->nullable();
            $table->foreign('ceo_cleared_by')->references('id')->on('users')->onDelete('cascade');


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
