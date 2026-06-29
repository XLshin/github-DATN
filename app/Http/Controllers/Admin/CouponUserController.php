<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponUserController extends Controller
{
    /**
     * Hiển thị view quản lý gán voucher cho user
     */
    public function edit(Coupon $coupon)
    {
        $coupon->load('users');
        $allUsers = User::where('role', 'customer')->orderBy('name')->get();

        return view('admin.coupons.assign-users', compact('coupon', 'allUsers'));
    }

    /**
     * Lưu danh sách user được cấp voucher
     */
    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        // Sync users: chỉ những user được chọn sẽ có quyền dùng voucher này
        $coupon->users()->sync($request->user_ids);

        return redirect()->route('coupons.index')->with('success', 'Cập nhật người dùng được cấp voucher thành công!');
    }
}
