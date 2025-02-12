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
        Schema::create('project_document_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_document_id')->constrained('projects_documents')->onDelete('cascade'); 
            $table->string('file_name');  
            $table->string('file_path');  
            $table->softDeletes();
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
        Schema::dropIfExists('project_document_attachments');
    }
};
