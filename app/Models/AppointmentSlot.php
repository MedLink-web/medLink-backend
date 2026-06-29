<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentSlot extends Model
{
    protected $fillable = [
        'clinic_id',
        'date',
        'start_time',
        'end_time',
        'max_capacity',
        'booked_count',
    ];

    // علاقة مع Clinic
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    // هل الـ slot ممتلئ؟
    public function isFullyBooked(): bool
    {
        return $this->booked_count >= $this->max_capacity;
    }

    // الأماكن المتبقية
    public function getRemainingCapacityAttribute(): int
    {
        return $this->max_capacity - $this->booked_count;
    }
}
