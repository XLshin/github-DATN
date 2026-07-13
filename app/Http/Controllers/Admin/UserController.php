<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role', 'all');

        if (! in_array($role, ['all', 'admin', 'staff', 'customer'], true)) {
            $role = 'all';
        }

        $users = User::query()
            ->when($role !== 'all', function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('orders')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function create()
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Chỉ admin mới được tạo tài khoản quản trị hoặc nhân viên.');
        }

        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Chỉ admin mới được tạo tài khoản quản trị hoặc nhân viên.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được sử dụng.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Admin chỉ được tạo tài khoản admin hoặc nhân viên.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => $validated['role'],
            'password' => $validated['password'],
            'total_spent' => 0,
            'points' => 0,
            'membership_level' => 'bronze',
            'is_locked' => false,
        ]);

        return redirect()
            ->route('admin.users.index', ['role' => $validated['role']])
            ->with('success', 'Tạo tài khoản thành công.');
    }

    public function show(User $user)
    {
        if ($user->isCustomer()) {
            $orders = $user->orders()
                ->with(['items.product'])
                ->latest()
                ->paginate(10, ['*'], 'ordersPage')
                ->withQueryString();

            $warranties = $user->warranties()
                ->with(['imei', 'order'])
                ->latest()
                ->paginate(10, ['*'], 'warrantiesPage')
                ->withQueryString();
        } else {
            $orders = new LengthAwarePaginator(collect(), 0, 10, request()->query('page', 1), [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
            $warranties = new LengthAwarePaginator(collect(), 0, 10, request()->query('page', 1), [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }

        return view('admin.users.show', compact('user', 'orders', 'warranties'));
    }

    public function toggleLock(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể khóa tài khoản đang đăng nhập.');
        }

        if (! in_array($user->role, ['customer', 'staff'], true)) {
            return back()->with('error', 'Chỉ được khóa hoặc mở khóa tài khoản khách hàng và nhân viên.');
        }

        $user->update([
            'is_locked' => ! $user->is_locked,
        ]);

        $roleLabel = $user->role === 'staff' ? 'nhân viên' : 'khách hàng';

        return back()->with(
            'success',
            $user->is_locked
                ? "Đã khóa tài khoản {$roleLabel}."
                : "Đã mở khóa tài khoản {$roleLabel}."
        );
    }
}
