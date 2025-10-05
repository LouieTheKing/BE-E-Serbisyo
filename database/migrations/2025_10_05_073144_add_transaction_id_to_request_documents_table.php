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
        Schema::table('request_documents', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('id')->index();
        });

        // Populate existing records with unique transaction IDs
        $requestDocuments = \App\Models\RequestDocument::all();
        foreach ($requestDocuments as $doc) {
            do {
                $transactionId = 'TXN_DOC_' . str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
            } while (\App\Models\RequestDocument::where('transaction_id', $transactionId)->exists());

            $doc->update(['transaction_id' => $transactionId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
};
