<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyRequest extends Model
{
    protected $fillable = [
        'pharmacy_name',
        'pharmacy_address',
        'pharmacy_phone',
        'pharmacy_email',
        'pharmacy_description',
        'license_number',
        'status',
    ];
}
