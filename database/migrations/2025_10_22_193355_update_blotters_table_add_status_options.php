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
            // Drop the old enum column and recreate with new values
            $table->dropColumn('status');
        });

        Schema::table('blotters', function (Blueprint $table) {
            // Add the new enum column with additional status options
            $table->enum('status', ['filed', 'ongoing', 'settled', 'reopen', 'unsettled'])->default('filed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blotters', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('blotters', function (Blueprint $table) {
            $table->enum('status', ['filed', 'ongoing', 'settled'])->default('filed');
        });
    }
};
