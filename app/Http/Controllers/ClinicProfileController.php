<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClinicProfileController extends Controller
{
    // 1️⃣ جلب بيانات العيادة
    public function show(Request $request)
    {
        $user   = $request->user();
        $clinic = $user->clinic;

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات العيادة',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'clinic_name'    => $clinic->clinic_name,
                'clinic_email'   => $clinic->clinic_email,
                'clinic_phone'   => $clinic->clinic_phone,
                'clinic_address' => $clinic->clinic_address,
                'specialty'      => $clinic->specialty,
                'license_number' => $clinic->license_number,
            ]
        ]);
    }

    // 2️⃣ تحديث بيانات العيادة
    public function update(Request $request)
    {
        $user   = $request->user();
        $clinic = $user->clinic;

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات العيادة',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'clinic_name'    => 'required|string|max:255',
            'clinic_phone'   => 'required|string|max:15',
            'clinic_address' => 'required|string|max:255',
            'specialty'      => 'required|string|max:100',
        ], [
            'clinic_name.required'    => 'اسم العيادة مطلوب',
            'clinic_phone.required'   => 'رقم الهاتف مطلوب',
            'clinic_address.required' => 'العنوان مطلوب',
            'specialty.required'      => 'التخصص مطلوب',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $clinic->update([
            'clinic_name'    => $request->clinic_name,
            'clinic_phone'   => $request->clinic_phone,
            'clinic_address' => $request->clinic_address,
            'specialty'      => $request->specialty,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات العيادة بنجاح',
            'data'    => [
                'clinic_name'    => $clinic->clinic_name,
                'clinic_phone'   => $clinic->clinic_phone,
                'clinic_address' => $clinic->clinic_address,
                'specialty'      => $clinic->specialty,
            ]
        ]);
    }
}
