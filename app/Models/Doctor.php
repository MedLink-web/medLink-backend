<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'clinic_id',
        'full_name',
        'email',
        'specialty',
    ];

    // علاقة مع User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع Clinic
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
