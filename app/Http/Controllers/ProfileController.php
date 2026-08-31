<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $patient = $user->patient;

        return response()->json([
            'success' => true,
            'data' => [
                'name'          => $user->full_name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'role'          => $user->role,
                'full_name'     => $patient?->full_name ?? $user->full_name,
                'date_of_birth' => $patient?->date_of_birth,
                'gender'        => $patient?->gender,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users,phone,' . $user->id,
        ], [
            'name.required'  => 'الاسم مطلوب',
            'name.max'       => 'الاسم يجب أن لا يتجاوز 255 حرف',
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.max'      => 'رقم الجوال يجب أن لا يتجاوز 15 رقم',
            'phone.unique'   => 'رقم الجوال مستخدم من قِبل مستخدم آخر',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update([
            'full_name' => $request->input('full_name', $request->name),
            'phone'     => $request->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'data'    => [
                'name'      => $user->full_name,
                'full_name' => $user->full_name,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'role'      => $user->role,
            ]
        ]);
    }
}
