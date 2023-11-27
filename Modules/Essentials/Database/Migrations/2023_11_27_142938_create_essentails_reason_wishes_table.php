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
        Schema::create('essentails_reason_wishes', function (Blueprint $table) {
            $table->id();
            $table->text('reason')->nullable();
            $table->string('employee_type')->nullable();
            $table->string('type')->default('Reason');
            $table->enum('reason_type', ['main', 'sub_main'])->nullable();
            $table->unsignedBigInteger('main_reson_id')->nullable();

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
        Schema::dropIfExists('essentails_reason_wishes');
    }
};
