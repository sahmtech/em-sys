<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('communication_messages', 'contact_id')) {
            Schema::table('communication_messages', function (Blueprint $table) {

                $table->unsignedInteger('contact_id')->nullable()->after('sender_id');
                $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('communication_messages', function (Blueprint $table) {
            //
            $table->dropForeign(['contact_id']);

        });
    }
};
