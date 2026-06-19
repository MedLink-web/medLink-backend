<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1️⃣ التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique'        => 'هذا البريد الإلكتروني مستخدم من قبل',
            'password.confirmed'  => 'كلمة المرور وتأكيدها غير متطابقين',
            'password.min'        => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2️⃣ حفظ البيانات (users + patients) بـ transaction واحدة
        try {
            $user = DB::transaction(function () use ($request) {
                $user = User::create([
                    'full_name' => $request->fullName,
                    'email'     => $request->email,
                    'phone'     => $request->phone,
                    'password'  => $request->password, // بينعمل hash تلقائياً (شرحناها بالخطوة 3)
                    'role'      => 'patient',
                ]);

                $user->patient()->create([
                    // الحقول الباقية (date_of_birth, gender) رح يعبيها المريض لاحقاً من بروفايله
                ]);

                return $user;
            });

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'user'    => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحساب، حاول مرة أخرى',
            ], 500);
        }
    }
}

