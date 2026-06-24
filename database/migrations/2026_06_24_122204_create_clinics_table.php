<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // لو حُذف الـ user تُحذف العيادة تلقائياً
            $table->string('clinic_name');
            $table->string('clinic_address');
            $table->string('clinic_phone');
            $table->string('clinic_email')->unique();
            $table->string('specialty');
            $table->string('license_number')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
