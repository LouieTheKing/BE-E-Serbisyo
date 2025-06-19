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
        Schema::create('request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requestor')
                    ->references('id')
                    ->on('accounts')
                    ->onUpdate('cascade');

            $table->foreignId('document')
                    ->references('id')
                    ->on('documents')
                    ->onUpdate('cascade');
            $table->string('status')->default('pending'); // pending, released, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_documents');
    }
};
