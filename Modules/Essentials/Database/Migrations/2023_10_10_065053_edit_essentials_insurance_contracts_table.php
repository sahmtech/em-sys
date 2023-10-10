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
        Schema::table('essentials_insurance_contracts', function (Blueprint $table) {
            $table->dropColumn('responsible_person');
            $table->dropColumn('phone_number');
            $table->dropColumn('mobile_number');
            $table->dropColumn('social_number');
            $table->dropColumn('address');
            $table->dropColumn('company_name');
            $table->integer('insurance_company_id')->unsigned()->after('details'); ;
            $table->foreign('insurance_company_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->bigInteger('policy_number')->after('insurance_company_id');    
            $table->bigInteger('policy_value')->after('policy_number'); ;
            $table->text('attachments')->after('policy_value'); ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
