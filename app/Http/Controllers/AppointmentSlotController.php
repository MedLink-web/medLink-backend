<?php

namespace App\Http\Controllers;

use App\Models\AppointmentSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentSlotController extends Controller
{
    // 1️⃣ جلب كل slots العيادة
    public function index(Request $request)
    {
        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات العيادة',
            ], 404);
        }

        $slots = $clinic->appointmentSlots()
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'id'                 => $slot->id,
                    'date'               => $slot->date,
                    'start_time'         => $slot->start_time,
                    'end_time'           => $slot->end_time,
                    'max_capacity'       => $slot->max_capacity,
                    'booked_count'       => $slot->booked_count,
                    'remaining_capacity' => $slot->remaining_capacity,
                    'is_fully_booked'    => $slot->isFullyBooked(),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $slots,
        ]);
    }

    // 2️⃣ إنشاء slot جديد
    public function store(Request $request)
    {
        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات العيادة',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'date'         => 'required|date|after_or_equal:today',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'max_capacity' => 'required|integer|min:1|max:100',
        ], [
            'date.required'            => 'التاريخ مطلوب',
            'date.after_or_equal'      => 'التاريخ يجب أن يكون اليوم أو في المستقبل',
            'start_time.required'      => 'وقت البداية مطلوب',
            'end_time.required'        => 'وقت النهاية مطلوب',
            'end_time.after'           => 'وقت النهاية يجب أن يكون بعد وقت البداية',
            'max_capacity.required'    => 'الحد الأقصى للحجز مطلوب',
            'max_capacity.min'         => 'الحد الأقصى يجب أن يكون على الأقل 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $slot = AppointmentSlot::create([
            'clinic_id'    => $clinic->id,
            'date'         => $request->date,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
            'max_capacity' => $request->max_capacity,
            'booked_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء موعد بنجاح',
            'data'    => [
                'id'                 => $slot->id,
                'date'               => $slot->date,
                'start_time'         => $slot->start_time,
                'end_time'           => $slot->end_time,
                'max_capacity'       => $slot->max_capacity,
                'remaining_capacity' => $slot->max_capacity,
            ],
        ], 201);
    }
}
