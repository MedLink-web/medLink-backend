<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClinicRequestController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/clinic-requests', [ClinicRequestController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::get('/admin/clinic-requests',        [ClinicRequestController::class, 'index']);
    Route::get('/admin/clinic-requests/{id}',   [ClinicRequestController::class, 'show']);
    Route::post('/admin/clinic-requests/{id}/approve', [ClinicRequestController::class, 'approve']);
    Route::post('/admin/clinic-requests/{id}/reject', [ClinicRequestController::class, 'reject']);
});
