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
        Schema::create('hd_ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('color')->nullable();
            $table->smallInteger('sortorder')->nullable();
            $table->boolean('showactive')->default(0);
            $table->boolean('showawaiting')->default(0);
            $table->boolean('autoclose')->default(0);
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
        Schema::dropIfExists('hd_ticket_statuses');
    }
};
