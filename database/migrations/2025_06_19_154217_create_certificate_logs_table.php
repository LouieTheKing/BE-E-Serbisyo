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

            $table->foreign('requestor')
                    ->references('id')
                    ->on('accounts')
                    ->onUpdate('cascade');

            $table->foreign('staff')
                    ->references('id')
                    ->on('accounts')
                    ->onUpdate('cascade');

            $table->foreign('document')
                    ->references('id')
                    ->on('documents')
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
