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
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('transfer_account')->nullable()->after('bank_account_number');
            $table->date('paid_on_from')->nullable()->after('transfer_account');
            $table->date('paid_on_to')->nullable()->after('paid_on_from');
            $table->unsignedBigInteger('cost_center')->nullable()->after('paid_on_to');
            $table->foreign('cost_center')->references('id')->on('accounting_cost_centers')->onDelete('set null');
            $table->foreign('transfer_account')->references('id')->on('bank_accounts')->onDelete('set null'); 
        });


        Schema::table('transactions', function (Blueprint $table) {
            $table->string('invoice_type')->nullable()->after('total_worker_number');
            $table->string('purchase_order_number')->nullable()->after('invoice_type');
            $table->string('delegate')->nullable()->after('purchase_order_number');
            $table->string('Ref')->nullable()->after('delegate');
           

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            //
        });
    }
};