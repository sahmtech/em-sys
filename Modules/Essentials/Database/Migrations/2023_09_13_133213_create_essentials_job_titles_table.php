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
        Schema::create('essentials_job_titles', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->string('job_code')->unique();
            $table->text('responsibilities');
            $table->text('supervision_scope');
            $table->text('authorities_and_permissions');
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
        Schema::dropIfExists('essentials_job_titles');
    }
};
