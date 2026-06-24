<?php

namespace App\Http\Controllers;

use App\Models\ClinicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Clinic;
use App\Mail\ClinicApprovedMail;
use App\Mail\ClinicRejectedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClinicRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clinic_name'        => 'required|string|max:255',
            'clinic_address'     => 'required|string|max:255',
            'clinic_phone'       => 'required|string|max:15',
            'clinic_email'       => 'required|email|unique:clinic_requests,clinic_email',
            'clinic_description' => 'nullable|string',
            'specialty'          => 'required|string|max:100',
            'document'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'clinic_name.required'    => 'اسم العيادة مطلوب',
            'clinic_address.required' => 'عنوان العيادة مطلوب',
            'clinic_phone.required'   => 'رقم هاتف العيادة مطلوب',
            'clinic_email.required'   => 'البريد الإلكتروني مطلوب',
            'clinic_email.email'      => 'صيغة البريد الإلكتروني غير صحيحة',
            'clinic_email.unique'     => 'هذا البريد الإلكتروني مسجّل مسبقاً',
            'specialty.required'      => 'التخصص مطلوب',
            'document.mimes'          => 'المستند يجب أن يكون PDF أو صورة',
            'document.max'            => 'حجم المستند يجب أن لا يتجاوز 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // رفع المستند إذا موجود
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('clinic_documents', 'public');
        }

        // إنشاء الطلب بحالة pending تلقائياً
        $clinicRequest = ClinicRequest::create([
            'clinic_name'        => $request->clinic_name,
            'clinic_address'     => $request->clinic_address,
            'clinic_phone'       => $request->clinic_phone,
            'clinic_email'       => $request->clinic_email,
            'license_number'     => $request->license_number,
            'specialty'          => $request->specialty,
            'document_path'      => $documentPath,
            'status'             => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب تسجيل العيادة بنجاح! سيتم مراجعته من قِبل الإدارة.',
            'data'    => $clinicRequest,
        ], 201);
    }
    // 1️⃣ جلب كل الطلبات المعلقة
    public function index()
    {
        $requests = ClinicRequest::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $requests,
            'count'   => $requests->count(),
        ]);
    }

    // 2️⃣ جلب تفاصيل طلب واحد
    public function show($id)
    {
        $clinicRequest = ClinicRequest::find($id);

        if (!$clinicRequest) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $clinicRequest,
        ]);
    }
    public function approve($id)
    {
        $clinicRequest = ClinicRequest::find($id);

        if (!$clinicRequest) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود',
            ], 404);
        }

        if ($clinicRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الطلب تمت معالجته مسبقاً',
            ], 400);
        }

        // 1️⃣ إنشاء كلمة سر عشوائية
        $password = Str::random(10);

        // 2️⃣ إنشاء حساب المستخدم بجدول users
        $user = User::create([
            'full_name' => $clinicRequest->clinic_name,
            'email'     => $clinicRequest->clinic_email,
            'phone'     => $clinicRequest->clinic_phone,
            'password'  => Hash::make($password),
            'role'      => 'clinic',
        ]);

        // 3️⃣ إنشاء سجل العيادة بجدول clinics مرتبط بالـ user
        Clinic::create([
            'user_id'        => $user->id,
            'clinic_name'    => $clinicRequest->clinic_name,
            'clinic_address' => $clinicRequest->clinic_address,
            'clinic_phone'   => $clinicRequest->clinic_phone,
            'clinic_email'   => $clinicRequest->clinic_email,
            'specialty'      => $clinicRequest->specialty,
            'license_number' => $clinicRequest->license_number,
            'document_path'  => $clinicRequest->document_path,
        ]);

        // 4️⃣ تحديث حالة الطلب
        $clinicRequest->update(['status' => 'approved']);

        // 5️⃣ إرسال الإيميل
        Mail::to($clinicRequest->clinic_email)->send(
            new ClinicApprovedMail(
                $clinicRequest->clinic_name,
                $clinicRequest->clinic_email,
                $password
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'تم قبول الطلب وإنشاء حساب العيادة بنجاح',
        ]);
    }
    public function reject(Request $request, $id)
    {
        $clinicRequest = ClinicRequest::find($id);

        if (!$clinicRequest) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود',
            ], 404);
        }

        if ($clinicRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الطلب تمت معالجته مسبقاً',
            ], 400);
        }

        // 1️⃣ تحديث حالة الطلب
        $clinicRequest->update(['status' => 'rejected']);

        // 2️⃣ إرسال إيميل إشعار للعيادة
        Mail::to($clinicRequest->clinic_email)->send(
            new ClinicRejectedMail(
                $clinicRequest->clinic_name,
                $request->reason ?? 'لم يتم استيفاء متطلبات التسجيل'
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'تم رفض الطلب بنجاح',
        ]);
    }
}
