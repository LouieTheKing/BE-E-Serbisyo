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
        Schema::table('blotters', function (Blueprint $table) {
            // Change received_by from string to unsignedBigInteger
            $table->unsignedBigInteger('received_by')->change();
            
            // Add foreign key constraint
            $table->foreign('received_by')
                ->references('id')
                ->on('accounts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['received_by']);
            
            // Change back to string
            $table->string('received_by')->change();
        });
    }
};
