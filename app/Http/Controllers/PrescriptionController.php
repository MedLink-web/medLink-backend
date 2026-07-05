<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrescriptionController extends Controller
{
    // 1️⃣ إنشاء وصفة طبية
    public function store(Request $request)
    {
        $user   = $request->user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الخدمة متاحة للأطباء فقط',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'patient_id'             => 'required|exists:patients,id',
            'appointment_id'         => 'nullable|exists:appointments,id',
            'diagnosis'              => 'required|string',
            'notes'                  => 'nullable|string',
            'valid_until'            => 'nullable|date|after:today',
            'items'                  => 'required|array|min:1',
            'items.*.medication_name'=> 'required|string|max:255',
            'items.*.dosage'         => 'required|string|max:100',
            'items.*.frequency'      => 'required|string|max:100',
            'items.*.duration'       => 'required|string|max:100',
            'items.*.instructions'   => 'nullable|string',
        ], [
            'patient_id.required'              => 'المريض مطلوب',
            'diagnosis.required'               => 'التشخيص مطلوب',
            'items.required'                   => 'يجب إضافة دواء واحد على الأقل',
            'items.*.medication_name.required' => 'اسم الدواء مطلوب',
            'items.*.dosage.required'          => 'الجرعة مطلوبة',
            'items.*.frequency.required'       => 'تكرار الجرعة مطلوب',
            'items.*.duration.required'        => 'مدة العلاج مطلوبة',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $doctor) {

            // 1. إنشاء الوصفة
            $prescription = Prescription::create([
                'doctor_id'      => $doctor->id,
                'patient_id'     => $request->patient_id,
                'appointment_id' => $request->appointment_id,
                'diagnosis'      => $request->diagnosis,
                'notes'          => $request->notes,
                'valid_until'    => $request->valid_until,
            ]);

            // 2. إضافة الأدوية
            foreach ($request->items as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medication_name' => $item['medication_name'],
                    'dosage'          => $item['dosage'],
                    'frequency'       => $item['frequency'],
                    'duration'        => $item['duration'],
                    'instructions'    => $item['instructions'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الوصفة الطبية بنجاح',
                'data'    => $prescription->load('items'),
            ], 201);
        });
    }
}
