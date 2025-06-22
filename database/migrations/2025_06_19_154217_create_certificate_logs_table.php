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
        Schema::create('certificate_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_request')
                    ->references('id')
                    ->on('request_documents')
                    ->onUpdate('cascade');

            $table->foreignId('staff')
                    ->references('id')
                    ->on('accounts')
                    ->onUpdate('cascade');

            $table->string('remark');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_logs');
    }
};
