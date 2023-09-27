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
        Schema::create('accounting_mapping_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100)->comment('debit, credit etc');
            $table->string('sub_type', 100);
            $table->unsignedBigInteger('accounting_account_id');
            $table->string('map_type', 100)->nullable();
            $table->enum('method', ['cash', 'card', 'cheque', 'bank_transfer', 'other']);
            $table->integer('created_by');
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
        Schema::dropIfExists('accounting_mapping_settings');
    }
};
