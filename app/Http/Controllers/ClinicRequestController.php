<?php

namespace App\Http\Controllers;

use App\Models\ClinicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
}
