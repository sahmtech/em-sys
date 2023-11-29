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
        Schema::table('followup_worker_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_main_reason_id')->unsigned()->nullable()->after('installmentsNumber');
            $table->foreign('contract_main_reason_id')->references('id')->on('essentails_reason_wishes')->onDelete('cascade');
            $table->unsignedBigInteger('contract_sub_reason_id')->unsigned()->nullable()->after('contract_main_reason_id');
            $table->foreign('contract_sub_reason_id')->references('id')->on('essentails_reason_wishes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
};
