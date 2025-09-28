<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blotters', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique(); // auto-generated case #
            $table->string('complainant_name');
            $table->string('respondent_name');
            $table->string('case_type');
            $table->json('additional_respondent')->nullable(); // array of names
            $table->text('complaint_details');
            $table->text('relief_sought');
            $table->date('date_filed');
            $table->string('received_by'); // staff name
            $table->unsignedBigInteger('created_by'); // user id
            $table->enum('status', ['filed', 'ongoing', 'settled'])->default('filed');
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('accounts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blotters');
    }
};
