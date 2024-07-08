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
        Schema::create('timesheet_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('business_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->text('timesheet_date')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')
                ->default('due');
            $table->decimal('total', 22, 4)
                ->default('0');
            $table->integer('created_by')->nullable();
            $table->boolean('is_approved')->default(0);
            $table->integer('approved_by')->nullable();

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
        Schema::dropIfExists('timesheet_groups');
    }
};
