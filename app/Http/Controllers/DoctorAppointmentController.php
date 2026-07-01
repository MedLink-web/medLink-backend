<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorAppointmentController extends Controller
{
    // جلب مواعيد الدكتور القادمة
    public function index(Request $request)
    {
        $user   = $request->user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الخدمة متاحة للأطباء فقط',
            ], 403);
        }

        // جلب مواعيد عيادة الدكتور
        $appointments = Appointment::where('clinic_id', $doctor->clinic_id)
            ->where('status', 'confirmed')
            ->with(['slot', 'patient.user'])
            ->whereHas('slot', function ($query) {
                $query->where('date', '>=', now()->toDateString());
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id'           => $appointment->id,
                    'status'       => $appointment->status,
                    'status_label' => 'مؤكد',
                    'patient'      => [
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
                ];
            });

        if ($appointments->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'لا توجد مواعيد قادمة',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $appointments,
            'count'   => $appointments->count(),
        ]);
    }
}
