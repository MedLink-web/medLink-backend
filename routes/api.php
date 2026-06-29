<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClinicRequestController;
use App\Http\Controllers\PharmacyRequestController;
use App\Http\Controllers\ClinicProfileController;
use App\Http\Controllers\DoctorController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/clinic-requests', [ClinicRequestController::class, 'store']);
Route::post('/pharmacy-requests', [PharmacyRequestController::class, 'store']);
Route::get('/admin/clinic-requests/all', [ClinicRequestController::class, 'all']);

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
});
