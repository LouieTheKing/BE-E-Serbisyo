<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('uploaded_document_requirements', 'request_document_id')) {
            Schema::table('uploaded_document_requirements', function (Blueprint $table) {
                $table->foreignId('request_document_id')
                    ->nullable()
                    ->constrained('request_documents')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::table('uploaded_document_requirements', function (Blueprint $table) {
            $table->dropForeign(['request_document_id']);
            $table->dropColumn('request_document_id');
        });
    }
};
