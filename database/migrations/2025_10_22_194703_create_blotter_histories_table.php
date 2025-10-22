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
        Schema::create('blotter_histories', function (Blueprint $table) {
            $table->id();
            $table->string('case_number'); // Foreign key reference to blotters table
            $table->enum('status', ['filed', 'ongoing', 'settled', 'reopen', 'unsettled']);
            $table->unsignedBigInteger('updated_by'); // User ID who made the change
            $table->text('notes')->nullable(); // Optional notes about the status change
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('case_number')
                ->references('case_number')
                ->on('blotters')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('updated_by')
                ->references('id')
                ->on('accounts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Add index for better query performance
            $table->index(['case_number', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotter_histories');
    }
};
