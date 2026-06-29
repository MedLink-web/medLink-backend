<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = [
        'user_id',
        'clinic_name',
        'clinic_address',
        'clinic_phone',
        'clinic_email',
        'specialty',
        'license_number',
        'document_path',
    ];

    // العلاقة مع User - كل عيادة تنتمي لمستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}
