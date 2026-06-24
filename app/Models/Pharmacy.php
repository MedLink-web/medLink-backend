<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    protected $fillable = [
        'user_id',
        'pharmacy_name',
        'pharmacy_address',
        'pharmacy_phone',
        'pharmacy_email',
        'pharmacy_description',
        'document_path',
    ];

    // علاقة مع User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع Medications (لاحقاً US-35)
    // public function medications()
    // {
    //     return $this->hasMany(PharmacyMedication::class);
    // }
}
