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
        Schema::table('essentials_employee_contracts', function (Blueprint $table) {
            $table->dropForeign('essentials_employee_contracts_work_type_id_foreign');
            $table->dropColumn('work_type_id');
            $table->dropColumn('profile_image');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('from_essentials_employee_contracts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->timestamps();
        });
    }
};
