<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pharmacy_name');
            $table->string('pharmacy_address');
            $table->string('pharmacy_phone');
            $table->string('pharmacy_email')->unique();
            $table->string('license_number')->nullable();
            $table->text('pharmacy_description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_requests');
    }
};
