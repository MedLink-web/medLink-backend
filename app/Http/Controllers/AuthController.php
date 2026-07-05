<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
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
    public function login(Request $request)
    {
        // 1️⃣ التحقق من صحة البيانات المُدخلة
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2️⃣ البحث عن المستخدم بالإيميل
        $user = User::where('email', $request->email)->first();

        // 3️⃣ التحقق من وجود المستخدم وصحة كلمة المرور معاً
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ], 401);
        }

        // 4️⃣ التحقق من أنو الحساب نشط
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حسابك معطّل، يرجى التواصل مع الإدارة',
            ], 403);
        }

        // 5️⃣ توليد توكن جديد لهذا المستخدم
        $token = $user->createToken('auth_token')->plainTextToken;

        // 6️⃣ إرجاع التوكن + بيانات المستخدم (بما فيها الـ role)
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user'    => $user,
            'token'   => $token,
        ], 200);
    }
}
