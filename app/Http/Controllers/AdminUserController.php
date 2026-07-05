<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // 1️⃣ جلب كل المستخدمين مع البحث
    public function index(Request $request)
    {
        $query = User::orderBy('created_at', 'desc');

        // بحث بالاسم أو الإيميل
        if ($request->has('search') && $request->search !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email',     'LIKE', '%' . $request->search . '%')
                  ->orWhere('phone',     'LIKE', '%' . $request->search . '%');
            });
        }

        // فلتر بالدور
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        $users = $query->get()->map(function ($user) {
            return [
                'id'         => $user->id,
                'full_name'  => $user->full_name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'role'       => $user->role,
                'is_active'  => $user->is_active,
                'created_at' => $user->created_at->format('Y-m-d'),
            ];
        });

        if ($users->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'لا يوجد مستخدمون',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $users,
            'count'   => $users->count(),
        ]);
    }

    // 2️⃣ تعطيل/تفعيل حساب مستخدم
    public function toggleActive($id, Request $request)
    {
        $admin = $request->user();

        // لا يمكن تعطيل حساب الأدمن نفسه
        if ($admin->id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك تعطيل حسابك الخاص',
            ], 400);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود',
            ], 404);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active
                ? 'تم تفعيل الحساب بنجاح'
                : 'تم تعطيل الحساب بنجاح',
            'data'    => [
                'id'        => $user->id,
                'full_name' => $user->full_name,
                'is_active' => $user->is_active,
            ],
        ]);
    }
}
