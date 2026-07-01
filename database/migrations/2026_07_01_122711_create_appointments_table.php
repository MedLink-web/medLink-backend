<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                  ->constrained('patients')
                  ->onDelete('cascade');
            $table->foreignId('clinic_id')
                  ->constrained('clinics')
                  ->onDelete('cascade');
            $table->foreignId('slot_id')
                  ->constrained('appointment_slots')
                  ->onDelete('cascade');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])
                  ->default('confirmed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
