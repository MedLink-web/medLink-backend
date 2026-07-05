<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Pharmacy;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\ClinicRequest;
use App\Models\PharmacyRequest;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function statistics(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => [

                // ── إحصائيات المستخدمين ──────────────────
                'users' => [
                    'total'     => User::count(),
                    'patients'  => User::where('role', 'patient')->count(),
                    'doctors'   => User::where('role', 'doctor')->count(),
                    'clinics'   => User::where('role', 'clinic')->count(),
                    'pharmacies' => User::where('role', 'pharmacy')->count(),
                    'active'    => User::where('is_active', true)->count(),
                    'inactive'  => User::where('is_active', false)->count(),
                ],

                // ── إحصائيات العيادات ─────────────────────
                'clinics' => [
                    'total'    => Clinic::count(),
                    'pending'  => ClinicRequest::where('status', 'pending')->count(),
                    'approved' => ClinicRequest::where('status', 'approved')->count(),
                    'rejected' => ClinicRequest::where('status', 'rejected')->count(),
                ],

                // ── إحصائيات الصيدليات ────────────────────
                'pharmacies' => [
                    'total'    => Pharmacy::count(),
                    'pending'  => PharmacyRequest::where('status', 'pending')->count(),
                    'approved' => PharmacyRequest::where('status', 'approved')->count(),
                    'rejected' => PharmacyRequest::where('status', 'rejected')->count(),
                ],

                // ── إحصائيات المواعيد ─────────────────────
                'appointments' => [
                    'total'     => Appointment::count(),
                    'confirmed' => Appointment::where('status', 'confirmed')->count(),
                    'cancelled' => Appointment::where('status', 'cancelled')->count(),
                ],

                // ── إحصائيات الوصفات ──────────────────────
                'prescriptions' => [
                    'total' => Prescription::count(),
                ],
            ],
        ]);
    }
}
