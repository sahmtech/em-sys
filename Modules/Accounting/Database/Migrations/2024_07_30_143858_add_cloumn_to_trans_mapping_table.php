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
        Schema::table('accounting_acc_trans_mappings', function (Blueprint $table) {
            $table->text('path_file')->nullable();
        });

        Schema::table('accounting_acc_trans_mapping_histories', function (Blueprint $table) {
            $table->text('path_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trans_mapping', function (Blueprint $table) {
        });
    }
};
