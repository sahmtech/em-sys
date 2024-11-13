<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('followup_documents', function (Blueprint $table) {
            $table->enum('type', ['Attached', 'Document'])->default('Document')->after('name_en');
        });
    }

    public function down()
    {
        Schema::table('followup_documents', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
    
};
