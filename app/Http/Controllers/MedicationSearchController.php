<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicationSearchController extends Controller
{
    // بحث عن دواء عبر كل الصيدليات
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2',
        ], [
            'name.required' => 'يرجى إدخال اسم الدواء',
            'name.min'      => 'اسم الدواء يجب أن يكون حرفين على الأقل',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $medications = Medication::where('medication_name', 'LIKE', '%' . $request->name . '%')
            ->with('pharmacy')
            ->orderBy('is_available', 'desc') // المتوفر أولاً
            ->get()
            ->map(function ($medication) {
                return [
                    'medication_id'   => $medication->id,
                    'medication_name' => $medication->medication_name,
                    'price'           => $medication->price,
                    'is_available'    => $medication->is_available,
                    'description'     => $medication->description,
                    'pharmacy'        => [
                        'id'      => $medication->pharmacy?->id,
                        'name'    => $medication->pharmacy?->pharmacy_name,
                        'address' => $medication->pharmacy?->pharmacy_address,
                        'phone'   => $medication->pharmacy?->pharmacy_phone,
                    ],
                ];
            });

        if ($medications->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'لم يتم العثور على هذا الدواء في أي صيدلية',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $medications,
            'count'   => $medications->count(),
        ]);
    }
}
