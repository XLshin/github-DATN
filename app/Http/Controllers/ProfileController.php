<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        return view('client.profile.show', ['user' => Auth::user()]);
    }

    public function edit()
    {
        return view('client.profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được sử dụng.',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return redirect()->route('profile.show')->with('success', 'Cập nhật thông tin thành công.');
    }
}
