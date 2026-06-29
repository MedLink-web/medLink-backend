<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    // جلب كل العيادات
    public function index()
    {
        $clinics = Clinic::with('doctors')->get();

        $data = $clinics->map(function ($clinic) {
            return [
                'id'             => $clinic->id,
                'clinic_name'    => $clinic->clinic_name,
                'specialty'      => $clinic->specialty,
                'clinic_phone'   => $clinic->clinic_phone,
                'clinic_email'   => $clinic->clinic_email,
                'clinic_address' => $clinic->clinic_address,
                'doctors'        => $clinic->doctors->map(function ($d) {
                    return [
                        'id'        => $d->id,
                        'full_name' => $d->full_name,
                        'specialty' => $d->specialty,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'count'   => $data->count(),
        ]);
    }

    // جلب تفاصيل عيادة واحدة
    public function show($id)
    {
        $clinic = Clinic::with('doctors')->find($id);

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'العيادة غير موجودة',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $clinic->id,
                'clinic_name'    => $clinic->clinic_name,
                'specialty'      => $clinic->specialty,
                'clinic_phone'   => $clinic->clinic_phone,
                'clinic_email'   => $clinic->clinic_email,
                'clinic_address' => $clinic->clinic_address,
                'doctors'        => $clinic->doctors->map(function ($d) {
                    return [
                        'id'        => $d->id,
                        'full_name' => $d->full_name,
                        'specialty' => $d->specialty,
                    ];
                }),
            ],
        ]);
    }
}
