<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PharmacyProfileController extends Controller
{
    // 1️⃣ جلب بيانات الصيدلية
    public function show(Request $request)
    {
        $user     = $request->user();
        $pharmacy = $user->pharmacy;

        if (!$pharmacy) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات الصيدلية',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'pharmacy_name'        => $pharmacy->pharmacy_name,
                'pharmacy_email'       => $pharmacy->pharmacy_email,
                'pharmacy_phone'       => $pharmacy->pharmacy_phone,
                'pharmacy_address'     => $pharmacy->pharmacy_address,
                'pharmacy_description' => $pharmacy->pharmacy_description,
            ]
        ]);
    }

    // 2️⃣ تحديث بيانات الصيدلية
    public function update(Request $request)
    {
        $user     = $request->user();
        $pharmacy = $user->pharmacy;

        if (!$pharmacy) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات الصيدلية',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'pharmacy_name'        => 'required|string|max:255',
            'pharmacy_phone'       => 'required|string|max:15',
            'pharmacy_address'     => 'required|string|max:255',
            'pharmacy_description' => 'nullable|string',
        ], [
            'pharmacy_name.required'    => 'اسم الصيدلية مطلوب',
            'pharmacy_phone.required'   => 'رقم الهاتف مطلوب',
            'pharmacy_address.required' => 'العنوان مطلوب',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pharmacy->update([
            'pharmacy_name'        => $request->pharmacy_name,
            'pharmacy_phone'       => $request->pharmacy_phone,
            'pharmacy_address'     => $request->pharmacy_address,
            'pharmacy_description' => $request->pharmacy_description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الصيدلية بنجاح',
            'data'    => [
                'pharmacy_name'        => $pharmacy->pharmacy_name,
                'pharmacy_phone'       => $pharmacy->pharmacy_phone,
                'pharmacy_address'     => $pharmacy->pharmacy_address,
                'pharmacy_description' => $pharmacy->pharmacy_description,
            ]
        ]);
    }
}
