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
        Schema::create('lost_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name')->nullable();
            $table->string('item_contents')->nullable();
            $table->unsignedBigInteger('lost_items_reports_id')->unsigned();
            $table->foreign('lost_items_reports_id')->references('id')->on('lost_items_reports')->onDelete('cascade');
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
        Schema::dropIfExists('lost_items_');
    }
};
