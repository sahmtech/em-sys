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
        Schema::create('timesheet_users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->text('timesheet_group_id')->nullable();
            $table->integer('nationality_id');
            $table->string('id_proof_number');
            $table->decimal('monthly_cost', 10, 2)->nullable();
            $table->integer('work_days')->nullable();
            $table->integer('absence_days')->nullable();
            $table->decimal('absence_amount', 10, 2)->nullable();
            $table->integer('over_time_hours')->default(0);
            $table->decimal('over_time_amount', 10, 2)->default(0);
            $table->decimal('other_deduction', 10, 2)->default(0);
            $table->decimal('other_addition', 10, 2)->default(0);
            $table->decimal('cost_2', 10, 2)->nullable();
            $table->decimal('invoice_value', 10, 2)->nullable();
            $table->decimal('vat', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('project_id')->nullable();
            $table->decimal('basic', 10, 2)->default(0);
            $table->decimal('housing', 10, 2)->default(0);
            $table->decimal('transport', 10, 2)->default(0);
            $table->decimal('other_allowances', 10, 2)->default(0);
            $table->decimal('total_salary', 10, 2)->nullable();
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('additions', 10, 2)->default(0);
            $table->decimal('final_salary', 10, 2)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('timesheet_users');
    }
};