<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_requests', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_name');
            $table->string('clinic_address');
            $table->string('clinic_phone');
            $table->string('clinic_email')->unique();
            $table->string('license_number')->nullable();
            $table->string('specialty');
            $table->string('document_path')->nullable(); // رفع المستند (اختياري)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_requests');
    }
};
