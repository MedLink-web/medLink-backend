<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClinicRequestController;
use App\Http\Controllers\PharmacyRequestController;
use App\Http\Controllers\ClinicProfileController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\AppointmentSlotController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorAppointmentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PharmacyProfileController;
use App\Http\Controllers\MedicationController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/clinic-requests', [ClinicRequestController::class, 'store']);
Route::post('/pharmacy-requests', [PharmacyRequestController::class, 'store']);
Route::get('/admin/clinic-requests/all', [ClinicRequestController::class, 'all']);
Route::get('/clinics',      [ClinicController::class, 'index']);
Route::get('/clinics/{id}', [ClinicController::class, 'show']);
// Public Routes
Route::get('/clinics',                        [ClinicController::class,          'index']);
Route::get('/clinics/{id}',                   [ClinicController::class,          'show']);
Route::get('/clinics/{id}/slots',             [AppointmentSlotController::class, 'availableSlots']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::get('/admin/clinic-requests',        [ClinicRequestController::class, 'index']);

    Route::get('/admin/clinic-requests/{id}',   [ClinicRequestController::class, 'show']);

    Route::post('/admin/clinic-requests/{id}/approve', [ClinicRequestController::class, 'approve']);

    Route::post('/admin/clinic-requests/{id}/reject', [ClinicRequestController::class, 'reject']);

    Route::get('/admin/pharmacy-requests',       [PharmacyRequestController::class, 'index']);

    Route::get('/admin/pharmacy-requests/{id}',  [PharmacyRequestController::class, 'show']);

    Route::post('/admin/pharmacy-requests/{id}/approve', [PharmacyRequestController::class, 'approve']);

    Route::post('/admin/pharmacy-requests/{id}/reject', [PharmacyRequestController::class, 'reject']);

    Route::get('/clinic/profile',  [ClinicProfileController::class, 'show']);
    Route::put('/clinic/profile',  [ClinicProfileController::class, 'update']);

    Route::get('/clinic/doctors',  [DoctorController::class, 'index']);
    Route::post('/clinic/doctors', [DoctorController::class, 'store']);

    Route::put('/clinic/doctors/{id}', [DoctorController::class, 'update']);
    Route::delete('/clinic/doctors/{id}', [DoctorController::class, 'destroy']);

    Route::get('/clinic/slots',  [AppointmentSlotController::class, 'index']);
    Route::post('/clinic/slots', [AppointmentSlotController::class, 'store']);

    Route::put('/clinic/slots/{id}', [AppointmentSlotController::class, 'update']);
    Route::post('/appointments', [AppointmentController::class, 'store']);

    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);

    Route::get('/appointments/my', [AppointmentController::class, 'myAppointments']);

    Route::get('/clinic/bookings', [AppointmentController::class, 'clinicBookings']);

    Route::get('/doctor/appointments', [DoctorAppointmentController::class, 'index']);

    Route::get('/doctor/appointments/{id}/patient', [DoctorAppointmentController::class, 'patientInfo']);

    Route::post('/prescriptions', [PrescriptionController::class, 'store']);
    Route::get('/prescriptions', [PrescriptionController::class, 'index']);

    Route::get('/patient/prescriptions', [PrescriptionController::class, 'patientPrescriptions']);

    Route::get('/pharmacy/profile', [PharmacyProfileController::class, 'show']);
    Route::put('/pharmacy/profile', [PharmacyProfileController::class, 'update']);

    Route::get('/pharmacy/medications',  [MedicationController::class, 'index']);
    Route::post('/pharmacy/medications', [MedicationController::class, 'store']);

    Route::put('/pharmacy/medications/{id}', [MedicationController::class, 'update']);

    Route::patch('/pharmacy/medications/{id}/toggle', [MedicationController::class, 'toggleAvailability']);
});
