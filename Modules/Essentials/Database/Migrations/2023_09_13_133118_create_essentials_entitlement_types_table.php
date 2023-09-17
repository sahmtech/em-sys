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
        Schema::create('essentials_entitlement_types', function (Blueprint $table) {
            $table->id();
            $table->string('entitlement_name');
            $table->decimal('percentage', 5, 2);
            $table->string('from');
            $table->text('details')->nullable(); 
            $table->boolean('activation_status');
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
        Schema::dropIfExists('essentials_entitlement_types');
    }
};
