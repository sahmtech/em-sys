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
        Schema::table('ir_proposed_labors', function (Blueprint $table) {
            $table->bigInteger('visa_id')->unsigned()->nullable()->after('is_accepted_by_worker');
            $table->foreign('visa_id')->references('id')->on('ir_visa_cards')->onDelete('cascade');
            $table->date('date_of_offer')->nullable()->after('is_price_offer_sent');
            $table->boolean('medical_examination')->default('0')->after('visa_id');
            $table->boolean('fingerprinting')->default('0')->after('medical_examination');
            $table->boolean('is_passport_stamped')->default('0')->after('fingerprinting');
            $table->string('passport_number')->nullable()->after('current_address');


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
