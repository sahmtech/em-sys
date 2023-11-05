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
        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('subscription_type',['Qiwa','Muqeem','Tamm']);
            $table->integer('subscription_number');
            $table->date('subscription_date');
            $table->date('renew_date');
            $table->date('expiration_date');
            
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
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
        Schema::dropIfExists('business_subscriptions');
    }
};
