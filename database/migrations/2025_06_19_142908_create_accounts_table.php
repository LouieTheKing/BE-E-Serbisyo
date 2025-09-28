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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('pending'); // active, inactive, pending
            $table->string('type')->default('staff'); // admin, staff, residence
            $table->string('civil_status');

            // Personal Information
            $table->string('first_name');
            $table->string('middle_name')->nullable()->default(null);
            $table->string ('last_name');
            $table->string('suffix')->nullable()->default(null);
            $table->string('sex');
            $table->string('nationality')->default('Filipino');
            $table->date('birthday');
            $table->string('contact_no');
            $table->string('birth_place');
            $table->string('pwd_number')->nullable()->default(null);
            $table->string('single_parent_number')->nullable()->default(null);
            $table->string('profile_picture_path')->nullable()->default(null);

            // Other Information
            $table->string('municipality');
            $table->string('barangay');
            $table->string('house_no');
            $table->string('zip_code');
            $table->string('street');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
