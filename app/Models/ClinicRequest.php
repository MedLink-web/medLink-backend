<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicRequest extends Model
{
    protected $fillable = [
        'clinic_name',
        'clinic_address',
        'clinic_phone',
        'clinic_email',
        'license_number',
        'specialty',
        'document_path',
        'status',
    ];
}
