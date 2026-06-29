<?php

namespace App\Http\Controllers;

use App\Models\PharmacyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Pharmacy;
use App\Mail\PharmacyApprovedMail;
use App\Mail\PharmacyRejectedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PharmacyRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pharmacy_name'        => 'required|string|max:255',
            'pharmacy_address'     => 'required|string|max:255',
            'pharmacy_phone'       => 'required|string|max:15',
            'pharmacy_email'       => 'required|email|unique:pharmacy_requests,pharmacy_email',
            'license_number'       => 'required|string|max:100',
            'pharmacy_description' => 'nullable|string',
        ], [
            'pharmacy_name.required'    => 'اسم الصيدلية مطلوب',
            'pharmacy_address.required' => 'عنوان الصيدلية مطلوب',
            'pharmacy_phone.required'   => 'رقم هاتف الصيدلية مطلوب',
            'pharmacy_email.required'   => 'البريد الإلكتروني مطلوب',
            'pharmacy_email.email'      => 'صيغة البريد الإلكتروني غير صحيحة',
            'pharmacy_email.unique'     => 'هذا البريد الإلكتروني مسجّل مسبقاً',
            'document.mimes'            => 'المستند يجب أن يكون PDF أو صورة',
            'document.max'              => 'حجم المستند يجب أن لا يتجاوز 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('pharmacy_documents', 'public');
        }

        $pharmacyRequest = PharmacyRequest::create([
            'pharmacy_name'        => $request->pharmacy_name,
            'pharmacy_address'     => $request->pharmacy_address,
            'pharmacy_phone'       => $request->pharmacy_phone,
            'pharmacy_email'       => $request->pharmacy_email,
            'pharmacy_description' => $request->pharmacy_description,
            'document_path'        => $documentPath,
            'status'               => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب تسجيل الصيدلية بنجاح! سيتم مراجعته من قِبل الإدارة.',
            'data'    => $pharmacyRequest,
        ], 201);
    }
    // 1️⃣ جلب كل الطلبات المعلقة
    public function index()
    {
        $requests = PharmacyRequest::where('status', 'pending')
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
        $pharmacyRequest = PharmacyRequest::find($id);

        if (!$pharmacyRequest) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $pharmacyRequest,
        ]);
    }
    public function approve($id)
    {
        $pharmacyRequest = PharmacyRequest::find($id);

        if (!$pharmacyRequest) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود',
            ], 404);
        }

        if ($pharmacyRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الطلب تمت معالجته مسبقاً',
            ], 400);
        }

        // 1️⃣ إنشاء كلمة سر عشوائية
        $password = Str::random(10);

        // 2️⃣ إنشاء حساب المستخدم بجدول users
        $user = User::create([
            'full_name' => $pharmacyRequest->pharmacy_name,
            'email'     => $pharmacyRequest->pharmacy_email,
            'phone'     => $pharmacyRequest->pharmacy_phone,
            'password'  => Hash::make($password),
            'role'      => 'pharmacy',
        ]);

        // 3️⃣ إنشاء سجل الصيدلية بجدول pharmacies
        Pharmacy::create([
            'user_id'              => $user->id,
            'pharmacy_name'        => $pharmacyRequest->pharmacy_name,
            'pharmacy_address'     => $pharmacyRequest->pharmacy_address,
            'pharmacy_phone'       => $pharmacyRequest->pharmacy_phone,
            'pharmacy_email'       => $pharmacyRequest->pharmacy_email,
            'pharmacy_description' => $pharmacyRequest->pharmacy_description,
            'document_path'        => $pharmacyRequest->document_path,
        ]);

        // 4️⃣ تحديث حالة الطلب
        $pharmacyRequest->update(['status' => 'approved']);

        // 5️⃣ إرسال الإيميل
        Mail::to($pharmacyRequest->pharmacy_email)->send(
            new PharmacyApprovedMail(
                $pharmacyRequest->pharmacy_name,
                $pharmacyRequest->pharmacy_email,
                $password
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'تم قبول الطلب وإنشاء حساب الصيدلية بنجاح',
        ]);
    }

    public function reject(Request $request, $id)
    {
        $pharmacyRequest = PharmacyRequest::find($id);

        if (!$pharmacyRequest) {
            return response()->json([
                'success' => false,
                'message' => 'الطلب غير موجود',
            ], 404);
        }

        if ($pharmacyRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'هذا الطلب تمت معالجته مسبقاً',
            ], 400);
        }

        // 1️⃣ تحديث حالة الطلب - يبقى محفوظ للمراجعة (auditing)
        $pharmacyRequest->update(['status' => 'rejected']);

        // 2️⃣ إرسال إيميل إشعار للصيدلية
        Mail::to($pharmacyRequest->pharmacy_email)->send(
            new PharmacyRejectedMail(
                $pharmacyRequest->pharmacy_name,
                $request->reason ?? 'لم يتم استيفاء متطلبات التسجيل'
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'تم رفض الطلب بنجاح',
        ]);
    }
}
