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
        Schema::create('payroll_group_users', function (Blueprint $table) {
            $table->id();
            $table->boolean('hr_management_cleared')->default(false);
            $table->boolean('accountant_cleared')->default(false);
            $table->boolean('financial_management_cleared')->default(false);
            $table->boolean('ceo_cleared')->default(false);
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('payroll_group_id');
            $table->foreign('payroll_group_id')->references('id')->on('payroll_groups')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('nationality')->nullable();
            $table->string('identity_card_number')->nullable();
            $table->string('company')->nullable();
            $table->string('project_name')->nullable();
            $table->string('region')->nullable();
            $table->string('work_days')->nullable();
            $table->string('salary')->nullable();
            $table->string('housing_allowance')->nullable();
            $table->string('transportation_allowance')->nullable();
            $table->string('other_allowance')->nullable();
            $table->string('total')->nullable();
            $table->string('violations')->nullable();
            $table->string('absence')->nullable();
            $table->string('absence_deduction')->nullable();
            $table->string('late')->nullable();
            $table->string('late_deduction')->nullable();
            $table->string('other_deductions')->nullable();
            $table->string('loan')->nullable();
            $table->string('total_deduction')->nullable();
            $table->string('over_time_hours')->nullable();
            $table->string('over_time_hours_addition')->nullable();
            $table->string('additional_addition')->nullable();
            $table->string('total_additions')->nullable();
            $table->string('final_salary')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('payroll_group_users');
    }
};
