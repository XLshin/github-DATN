<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    /**
     * Trang tổng quan tài khoản: hiển thị form cập nhật thông tin + danh sách địa chỉ.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $addresses = [];
        $couponCount = 0;
        $warrantyCount = 0;

        if (Schema::hasTable('addresses')) {
            $addresses = $user->addresses()->latest()->get();
        }

        if (Schema::hasTable('coupon_user')) {
            $couponCount = $user->coupons()->count();
        }

        if (Schema::hasTable('warranties')) {
            $warrantyCount = $user->warranties()->count();
        }

        return view('client.profile.dashboard', compact('user', 'addresses', 'couponCount', 'warrantyCount'));
    }

    public function show()
    {
        // Giữ nguyên hoặc có thể chuyển hướng sang dashboard
        return redirect()->route('dashboard');
    }

    public function edit()
    {
        // Nếu bạn vẫn muốn trang edit riêng, giữ nguyên
        $user = Auth::user();
        return view('client.profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        Auth::user()->update($request->validated());

        return back()->with('profile_success', 'Cập nhật thông tin thành công.');
    }
}
