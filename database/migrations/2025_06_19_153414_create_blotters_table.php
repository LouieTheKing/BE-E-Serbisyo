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
        Schema::create('blotters', function (Blueprint $table) {
            $table->id();
            $table->string('blotter_number')->unique();
            $table->string('status')->default('pending'); // pending, resolved, unresolved
            $table->text('remarks');
            $table->text('incidents');
            $table->string('location');
            $table->date('incident_date');

            $table->foreignId('reporter')
                    ->references('id')
                    ->on('accounts')
                    ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotters');
    }
};
