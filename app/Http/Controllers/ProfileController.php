<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * US06: Xem thông tin cá nhân
     */
    public function show()
    {
        $user = Auth::user();

        return view('auth.profile', compact('user'));
    }

    /**
     * Form cập nhật thông tin cá nhân
     */
    public function edit()
    {
        $user = Auth::user();

        return view('auth.edit-profile', compact('user'));
    }

    /**
     * US07: Cập nhật thông tin cá nhân
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng bởi tài khoản khác.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',
        ]);

        $user->update($validated);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }
}
