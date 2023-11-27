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
                $table->unsignedBigInteger('insurance_classes_id')->unsigned()->nullable()->after('installmentsNumber');
                $table->foreign('insurance_classes_id')->references('id')->on('essentials_insurance_classes')->onDelete('cascade');
                $table->enum('baladyCardType',['renew','issuance'])->after('insurance_classes_id')->nullable();
                $table->enum('resCardEditType',['name','religion'])->after('baladyCardType')->nullable();
                $table->date('workInjuriesDate')->after('resCardEditType')->nullable();
    
    
    
         
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
