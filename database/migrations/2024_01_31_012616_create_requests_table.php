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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->unique();

            $table->unsignedBigInteger('request_type_id'); 

            $table->unsignedInteger('related_to');
            $table->unsignedInteger('created_by')->nullable();

            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
          
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('essentials_leave_type_id')->nullable()->index();

            $table->time('escape_time')->nullable();

            $table->integer('advSalaryAmount')->nullable();
            $table->integer('monthlyInstallment')->nullable();
            $table->integer('installmentsNumber')->nullable();

            $table->enum('atmCardType',['release','re_issuing','update'])->nullable();

            $table->bigInteger('visa_number')->nullable();

            $table->unsignedBigInteger('insurance_classes_id')->nullable();


            $table->enum('baladyCardType',['renew','issuance'])->nullable();
            $table->enum('resCardEditType',['name','religion'])->nullable();
          
            $table->date('workInjuriesDate')->nullable();

            $table->unsignedBigInteger('contract_main_reason_id')->nullable();
            $table->unsignedBigInteger('contract_sub_reason_id')->nullable();
            
            $table->text('reason')->nullable();
            $table->text('note')->nullable();


            $table->foreign('request_type_id')->references('id')->on('requests_types');
            $table->foreign('related_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('insurance_classes_id')->references('id')->on('essentials_insurance_classes')->onDelete('cascade');
            $table->foreign('contract_main_reason_id')->references('id')->on('essentails_reason_wishes')->onDelete('cascade');
            $table->foreign('contract_sub_reason_id')->references('id')->on('essentails_reason_wishes')->onDelete('cascade');
           
           
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
        Schema::dropIfExists('requests');
    }
};
