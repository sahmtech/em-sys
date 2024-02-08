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
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('accounting_account_types', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('accounting_acc_trans_mappings', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('accounting_acc_trans_mapping_setting_tests', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('accounting_cost_centers', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('accounting_opening_balances', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('reference_counts', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('selling_price_groups', function (Blueprint $table) {
            $table->integer('company_id')->unsigned()->nullable()->after('business_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_opening_balances', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });

        Schema::table('accounting_cost_centers', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });

        Schema::table('accounting_acc_trans_mapping_setting_tests', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });

        Schema::table('accounting_acc_trans_mappings', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });

        Schema::table('accounting_account_types', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });

        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });
        Schema::table('reference_counts', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });
        Schema::table('selling_price_groups', function (Blueprint $table) {
            $table->dropForeign(['company_id']); // Drops foreign key constraint
            $table->dropColumn('company_id'); // Drops column
        });
    }
};
