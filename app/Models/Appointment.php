<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'clinic_id',
        'slot_id',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function slot()
    {
        return $this->belongsTo(AppointmentSlot::class, 'slot_id');
    }
}
