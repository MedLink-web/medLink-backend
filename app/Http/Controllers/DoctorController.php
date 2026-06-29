<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    // 1️⃣ جلب كل أطباء العيادة
    public function index(Request $request)
    {
        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات العيادة',
            ], 404);
        }

        $doctors = $clinic->doctors()->get();

        return response()->json([
            'success' => true,
            'data'    => $doctors,
        ]);
    }

    // 2️⃣ إضافة طبيب جديد
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
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'specialty' => 'required|string|max:100',
            'password'  => 'required|string|min:8',
        ], [
            'full_name.required' => 'اسم الطبيب مطلوب',
            'email.required'     => 'البريد الإلكتروني مطلوب',
            'email.unique'       => 'هذا البريد الإلكتروني مستخدم مسبقاً',
            'specialty.required' => 'التخصص مطلوب',
            'password.required'  => 'كلمة المرور مطلوبة',
            'password.min'       => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 1. إنشاء حساب المستخدم للطبيب
        $user = User::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'phone'     => $request->phone ?? '',
            'password'  => Hash::make($request->password),
            'role'      => 'doctor',
        ]);

        // 2. إنشاء سجل الطبيب مرتبط بالعيادة
        $doctor = Doctor::create([
            'user_id'   => $user->id,
            'clinic_id' => $clinic->id,
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'specialty' => $request->specialty,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الطبيب بنجاح',
            'data'    => $doctor,
        ], 201);
    }
    // 3️⃣ تعديل بيانات طبيب
    public function update(Request $request, $id)
    {
        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات العيادة',
            ], 404);
        }

        // تأكد إنو الطبيب تابع لهاي العيادة
        $doctor = Doctor::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->first();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'الطبيب غير موجود',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'specialty' => 'required|string|max:100',
            'phone'     => 'nullable|string|max:15',
        ], [
            'full_name.required' => 'اسم الطبيب مطلوب',
            'specialty.required' => 'التخصص مطلوب',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $doctor->update([
            'full_name' => $request->full_name,
            'specialty' => $request->specialty,
        ]);

        // تحديث بيانات المستخدم المرتبط
        $doctor->user->update([
            'full_name' => $request->full_name,
            'phone'     => $request->phone ?? $doctor->user->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الطبيب بنجاح',
            'data'    => $doctor->fresh(),
        ]);
    }
    // 4️⃣ حذف طبيب
    public function destroy(Request $request, $id)
    {
        $clinic = $request->user()->clinic;

        if (!$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على بيانات العيادة',
            ], 404);
        }

        $doctor = Doctor::where('id', $id)
            ->where('clinic_id', $clinic->id)
            ->first();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'الطبيب غير موجود',
            ], 404);
        }

        // حذف حساب المستخدم المرتبط بالطبيب
        $doctor->user()->delete();

        // حذف سجل الطبيب
        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الطبيب بنجاح',
        ]);
    }
}
