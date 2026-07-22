<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use App\Notifications\NewVoucherNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('discount_type', 'like', "%{$search}%");
        }

        $coupons = $query->latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'usage_limit' => ['required', 'integer', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'boolean'],
            'distribution' => ['required', Rule::in([Coupon::DISTRIBUTION_ASSIGNED, Coupon::DISTRIBUTION_PUBLIC])],
        ]);

        $validated['code'] = Str::upper($validated['code']);

        $coupon = Coupon::create($validated);

        // Voucher công khai (không gán riêng cho ai) thì báo ngay cho toàn bộ khách hàng đang hoạt
        // động để họ biết mà lưu dùng; voucher "assigned" chỉ báo khi admin gán cụ thể (xem CouponUserController).
        if ($coupon->distribution === Coupon::DISTRIBUTION_PUBLIC && $coupon->status) {
            User::where('role', User::ROLE_CUSTOMER)
                ->where('is_locked', false)
                ->get()
                ->each(fn (User $user) => $user->notify(new NewVoucherNotification($coupon)));
        }

        return redirect()->route('admin.coupons.index')->with('success', 'Tạo voucher thành công.');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'usage_limit' => ['required', 'integer', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'boolean'],
            'distribution' => ['required', Rule::in([Coupon::DISTRIBUTION_ASSIGNED, Coupon::DISTRIBUTION_PUBLIC])],
        ]);

        $validated['code'] = Str::upper($validated['code']);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Cập nhật voucher thành công.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Voucher đã được xóa.');
    }
}
