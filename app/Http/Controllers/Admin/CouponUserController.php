<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponUserController extends Controller
{
    /**
     * Hiển thị view quản lý gán voucher cho user
     */
    public function edit(Coupon $coupon)
    {
        abort_unless($coupon->isAssigned(), 404);

        $coupon->load('users');
        $allUsers = User::where('role', User::ROLE_CUSTOMER)
            ->withCount('orders')
            ->orderBy('name')
            ->get();

        return view('admin.coupons.assign-users', compact('coupon', 'allUsers'));
    }

    /**
     * Lưu danh sách user được cấp voucher
     */
    public function update(Request $request, Coupon $coupon)
    {
        abort_unless($coupon->isAssigned(), 404);

        $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where('role', User::ROLE_CUSTOMER),
            ],
        ]);

        // Sync users: chỉ những user được chọn sẽ có quyền dùng voucher này
        $coupon->users()->sync($request->input('user_ids', []));

        return redirect()->route('admin.coupons.index')->with('success', 'Cập nhật người dùng được cấp voucher thành công!');
    }
}
