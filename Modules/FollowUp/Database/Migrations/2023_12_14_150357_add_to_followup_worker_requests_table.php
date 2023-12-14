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
            $table->enum('type', [
                'exitRequest',
                'returnRequest',
                'escapeRequest',
                'advanceSalary',
                'leavesAndDepartures',
                'atmCard',
                'residenceRenewal',
                'residenceCard',
                'workerTransfer',
                'workInjuriesRequest',
                'residenceEditRequest',
                'baladyCardRequest',
                'recruitmentRequest',
                'insuranceUpgradeRequest',
                'mofaRequest',
                'chamberRequest','cancleContractRequest',
                'WarningRequest'
            ])->after('worker_id');
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
