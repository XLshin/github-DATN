<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Trang tổng quan tài khoản: hiển thị form cập nhật thông tin + danh sách địa chỉ.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->latest()->get();

        return view('client.profile.dashboard', compact('user', 'addresses'));
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
