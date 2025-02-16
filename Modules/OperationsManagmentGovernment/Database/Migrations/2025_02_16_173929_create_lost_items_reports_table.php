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
        Schema::create('lost_items_reports', function (Blueprint $table) {
            $table->id();
            $table->string('receiving_entity_name')->nullable();
            $table->string('recipient_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('supervisor')->nullable();
            $table->string('ref_number');
            $table->Integer('report_id')->unsigned();
            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
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
        Schema::dropIfExists('lost_items_reports');
    }
};
