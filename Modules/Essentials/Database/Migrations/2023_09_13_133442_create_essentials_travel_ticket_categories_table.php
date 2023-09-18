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
        Schema::create('essentials_travel_ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('employee_ticket_value', 10, 2);
            $table->decimal('wife_ticket_value', 10, 2);
            $table->decimal('children_ticket_value', 10, 2);
            $table->text('details')->nullable();
            $table->boolean('is_active');
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
        Schema::dropIfExists('essentials_travel_ticket_categories');
    }
};
