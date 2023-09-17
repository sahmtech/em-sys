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
        Schema::create('essentials_work_types', function (Blueprint $table) {
            $table->id();
            $table->json('type_name');
            $table->integer('delay_start_after');
            $table->boolean('delay_allowance_period'); // التأخير يشمل فترة السماح (عدد الدقائق)
            $table->integer('delay_allowance_count'); // عدد مرات السماح
            $table->enum('delay_deduction_type',['once','multiple']); // نوع خصم التأخير
            $table->boolean('early_checkout_deduction'); // خصم الخروج المبكر
            $table->integer('overtime_hours'); // ساعات العمل الإضافية
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
        Schema::dropIfExists('essentials_work_types');
    }
};
