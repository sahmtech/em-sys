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
        Schema::create('accounting_accounts_transaction_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_account_id');
            $table->integer('acc_trans_mapping_history_id');
            $table->integer('acc_trans_mapping_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->decimal('amount', 22, 4);
            $table->string('type', 100);
            $table->string('sub_type', 100);
            $table->bigInteger('partner_id')->nullable();
            $table->text('partner_type')->nullable();
            $table->string('map_type', 100)->nullable();
            $table->integer('created_by');
            $table->dateTime('operation_date');
            $table->text('note')->nullable();
            $table->text('additional_notes')->nullable();
          
            $table->timestamps();
        });

        Schema::table('accounting_accounts_transactions', function (Blueprint $table) {
            $table->text('additional_notes')->nullable();
            $table->bigInteger('partner_id')->nullable()->after('sub_type');
            $table->text('partner_type')->nullable()->after('partner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_accounts_transaction_histories');
    }
};