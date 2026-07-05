<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicationController extends Controller
{
    // 1️⃣ جلب أدوية الصيدلية
    public function index(Request $request)
    {
        $pharmacy = $request->user()->pharmacy;

        if (!$pharmacy) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات الصيدلية',
            ], 404);
        }

        $medications = $pharmacy->medications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $medications,
            'count'   => $medications->count(),
        ]);
    }

    // 2️⃣ إضافة دواء
    public function store(Request $request)
    {
        $pharmacy = $request->user()->pharmacy;

        if (!$pharmacy) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات الصيدلية',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'medication_name' => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
            'is_available'    => 'boolean',
            'description'     => 'nullable|string',
        ], [
            'medication_name.required' => 'اسم الدواء مطلوب',
            'price.required'           => 'السعر مطلوب',
            'price.numeric'            => 'السعر يجب أن يكون رقماً',
            'price.min'                => 'السعر يجب أن يكون أكبر من 0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $medication = Medication::create([
            'pharmacy_id'     => $pharmacy->id,
            'medication_name' => $request->medication_name,
            'price'           => $request->price,
            'is_available'    => $request->is_available ?? true,
            'description'     => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الدواء بنجاح',
            'data'    => $medication,
        ], 201);
    }

    // 3️⃣ تعديل دواء
    public function update(Request $request, $id)
    {
        $pharmacy = $request->user()->pharmacy;

        if (!$pharmacy) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات الصيدلية',
            ], 404);
        }

        // تأكد إنو الدواء تابع لهاي الصيدلية
        $medication = Medication::where('id', $id)
            ->where('pharmacy_id', $pharmacy->id)
            ->first();

        if (!$medication) {
            return response()->json([
                'success' => false,
                'message' => 'الدواء غير موجود',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'medication_name' => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
            'is_available'    => 'boolean',
            'description'     => 'nullable|string',
        ], [
            'medication_name.required' => 'اسم الدواء مطلوب',
            'price.required'           => 'السعر مطلوب',
            'price.numeric'            => 'السعر يجب أن يكون رقماً',
            'price.min'                => 'السعر يجب أن يكون أكبر من 0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $medication->update([
            'medication_name' => $request->medication_name,
            'price'           => $request->price,
            'is_available'    => $request->is_available ?? $medication->is_available,
            'description'     => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الدواء بنجاح',
            'data'    => $medication->fresh(),
        ]);
    }

    // 4️⃣ تغيير حالة توفر الدواء
    public function toggleAvailability(Request $request, $id)
    {
        $pharmacy = $request->user()->pharmacy;

        if (!$pharmacy) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات الصيدلية',
            ], 404);
        }

        $medication = Medication::where('id', $id)
            ->where('pharmacy_id', $pharmacy->id)
            ->first();

        if (!$medication) {
            return response()->json([
                'success' => false,
                'message' => 'الدواء غير موجود',
            ], 404);
        }

        // نعكس الحالة الحالية
        $medication->update([
            'is_available' => !$medication->is_available,
        ]);

        return response()->json([
            'success' => true,
            'message' => $medication->is_available
                ? 'تم تحديث الدواء إلى متوفر'
                : 'تم تحديث الدواء إلى غير متوفر',
            'data'    => [
                'id'           => $medication->id,
                'medication_name' => $medication->medication_name,
                'is_available' => $medication->is_available,
            ],
        ]);
    }
}
