<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uploaded_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');

            $table->foreignId('uploader')
                    ->references('id')
                    ->on('accounts')
                    ->onUpdate('cascade');

            $table->foreignId('requirement')
                    ->references('id')
                    ->on('document_requirements')
                    ->onUpdate('cascade');

            $table->foreignId('document')
                    ->references('id')
                    ->on('documents')
                    ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_document_requirements');
    }
};
