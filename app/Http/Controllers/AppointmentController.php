<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Patient;
use App\Models\User;

class AppointmentController extends Controller
{
    // 1️⃣ حجز موعد
    public function store(Request $request)
    {
        $user    = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الخدمة متاحة للمرضى فقط',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'slot_id' => 'required|exists:appointment_slots,id',
        ], [
            'slot_id.required' => 'يرجى اختيار موعد',
            'slot_id.exists'   => 'الموعد المختار غير موجود',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // استخدام Transaction لضمان التزامن
        return DB::transaction(function () use ($request, $patient) {

            // 1. جلب الـ slot مع lock عشان ما يصير تعارض
            $slot = AppointmentSlot::lockForUpdate()
                ->find($request->slot_id);

            // 2. تحقق من الـ capacity
            if ($slot->isFullyBooked()) {
                return response()->json([
                    'success' => false,
                    'message' => 'عذراً، هذا الموعد ممتلئ',
                ], 400);
            }

            // 3. تحقق إنو المريض ما حجز نفس الـ slot من قبل
            $existingBooking = Appointment::where('patient_id', $patient->id)
                ->where('slot_id', $slot->id)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد قمت بحجز هذا الموعد مسبقاً',
                ], 400);
            }

            // 4. إنشاء الحجز
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'clinic_id'  => $slot->clinic_id,
                'slot_id'    => $slot->id,
                'status'     => 'confirmed',
            ]);

            // 5. تحديث عدد الحجوزات
            $slot->increment('booked_count');

            return response()->json([
                'success' => true,
                'message' => 'تم حجز الموعد بنجاح',
                'data'    => [
                    'appointment_id'     => $appointment->id,
                    'date'               => $slot->date,
                    'start_time'         => $slot->start_time,
                    'end_time'           => $slot->end_time,
                    'status'             => $appointment->status,
                    'remaining_capacity' => $slot->remaining_capacity - 1,
                ],
            ], 201);
        });
    }
    // 2️⃣ إلغاء موعد
    public function cancel(Request $request, $id)
    {
        $user    = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الخدمة متاحة للمرضى فقط',
            ], 403);
        }

        // تأكد إنو الموعد تابع لهاد المريض
        $appointment = Appointment::where('id', $id)
            ->where('patient_id', $patient->id)
            ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'الموعد غير موجود',
            ], 404);
        }

        // تحقق إنو الموعد مش ملغي مسبقاً
        if ($appointment->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الموعد ملغي مسبقاً',
            ], 400);
        }

        return DB::transaction(function () use ($appointment) {

            // 1. تغيير حالة الموعد
            $appointment->update(['status' => 'cancelled']);

            // 2. تحديث عدد الحجوزات بالـ slot
            $slot = $appointment->slot;
            if ($slot && $slot->booked_count > 0) {
                $slot->decrement('booked_count');
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الموعد بنجاح',
                'data'    => [
                    'appointment_id'     => $appointment->id,
                    'status'             => 'cancelled',
                    'remaining_capacity' => $slot ? $slot->fresh()->remaining_capacity : null,
                ],
            ]);
        });
    }
    // 3️⃣ جلب مواعيد المريض
    public function myAppointments(Request $request)
    {
        $user    = $request->user();
        $patient = $user->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الخدمة متاحة للمرضى فقط',
            ], 403);
        }

        $appointments = Appointment::where('patient_id', $patient->id)
            ->with(['slot', 'clinic'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id'           => $appointment->id,
                    'status'       => $appointment->status,
                    'status_label' => match ($appointment->status) {
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'pending'   => 'قيد الانتظار',
                        default     => $appointment->status,
                    },
                    'clinic'  => [
                        'id'          => $appointment->clinic?->id,
                        'name'        => $appointment->clinic?->clinic_name,
                        'address'     => $appointment->clinic?->clinic_address,
                        'phone'       => $appointment->clinic?->clinic_phone,
                        'specialty'   => $appointment->clinic?->specialty,
                    ],
                    'slot' => [
                        'date'       => $appointment->slot?->date,
                        'start_time' => $appointment->slot?->start_time,
                        'end_time'   => $appointment->slot?->end_time,
                    ],
                ];
            });

        if ($appointments->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'لا توجد مواعيد مسجّلة حتى الآن',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $appointments,
            'count'   => $appointments->count(),
        ]);
    }
    // 4️⃣ جلب حجوزات العيادة (للأدمن)
    public function clinicBookings(Request $request)
    {
        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات العيادة',
            ], 404);
        }

        $appointments = Appointment::where('clinic_id', $clinic->id)
            ->with(['slot', 'patient.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id'           => $appointment->id,
                    'status'       => $appointment->status,
                    'status_label' => match ($appointment->status) {
                        'confirmed' => 'مؤكد',
                        'cancelled' => 'ملغي',
                        'pending'   => 'قيد الانتظار',
                        default     => $appointment->status,
                    },
                    'patient' => [
                        'id'    => $appointment->patient?->id,
                        'name'  => $appointment->patient?->user?->full_name,
                        'phone' => $appointment->patient?->user?->phone,
                        'email' => $appointment->patient?->user?->email,
                    ],
                    'slot' => [
                        'date'       => $appointment->slot?->date,
                        'start_time' => $appointment->slot?->start_time,
                        'end_time'   => $appointment->slot?->end_time,
                    ],
                    'booked_at' => $appointment->created_at->format('Y-m-d H:i'),
                ];
            });

        if ($appointments->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'لا توجد حجوزات حتى الآن',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $appointments,
            'count'   => $appointments->count(),
        ]);
    }
}
