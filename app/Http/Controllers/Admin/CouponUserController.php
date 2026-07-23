<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use App\Notifications\NewVoucherNotification;
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

        $previousUserIds = $coupon->users()->pluck('users.id')->all();

        // Sync users: chỉ những user được chọn sẽ có quyền dùng voucher này
        $coupon->users()->sync($request->input('user_ids', []));

        // Chỉ báo cho những user mới được cấp thêm, tránh spam lại thông báo cho người đã có từ trước.
        $newlyAssignedIds = array_diff($request->input('user_ids', []), $previousUserIds);

        if (! empty($newlyAssignedIds)) {
            User::whereIn('id', $newlyAssignedIds)->get()->each(
                fn (User $user) => $user->notify(new NewVoucherNotification($coupon))
            );
        }

        return redirect()->route('admin.coupons.index')->with('success', 'Cập nhật người dùng được cấp voucher thành công!');
    }
}
