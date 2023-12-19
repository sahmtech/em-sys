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
        Schema::create('sales_proposal_templates', function (Blueprint $table) {
                $table->bigIncrements('id');
    
                $table->integer('business_id')->unsigned();
                $table->foreign('business_id')
                    ->references('id')->on('business')
                    ->onDelete('cascade');
    
                $table->text('subject');
                $table->longText('body');
                $table->integer('created_by')->index();
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
        Schema::dropIfExists('sales_proposal_templates');
    }
};
