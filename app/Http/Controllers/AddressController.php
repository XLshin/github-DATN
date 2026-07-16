<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Lưu địa chỉ mới
     */
    public function store(AddressRequest $request)
    {
        $data = $request->validated();
        $isFirstAddress = ! Auth::user()->addresses()->exists();

        if ($isFirstAddress || ! empty($data['is_default'])) {
            Auth::user()->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $address = Auth::user()->addresses()->create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'address' => $address]);
        }

        return redirect()->route('dashboard')
            ->with('address_success', 'Địa chỉ mới đã được thêm.');
    }

    /**
     * Cập nhật địa chỉ
     */
    public function update(AddressRequest $request, Address $address)
    {
        // Kiểm tra chủ sở hữu
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền sửa địa chỉ này.');
        }

        $address->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('dashboard')
            ->with('address_success', 'Địa chỉ đã được cập nhật.');
    }

    /**
     * Xóa địa chỉ
     */
    public function destroy(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa địa chỉ này.');
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // Nếu xóa địa chỉ mặc định thì chọn địa chỉ mới nhất làm mặc định
        if ($wasDefault) {
            $latest = Auth::user()->addresses()->latest()->first();
            if ($latest) {
                $latest->update(['is_default' => true]);
            }
        }

        return redirect()->route('dashboard')
            ->with('address_success', 'Địa chỉ đã được xóa.');
    }

    /**
     * Đặt địa chỉ làm mặc định
     */
    public function setDefault(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thao tác với địa chỉ này.');
        }

        // Bỏ mặc định tất cả địa chỉ khác
        Auth::user()->addresses()->update(['is_default' => false]);

        // Đặt địa chỉ này làm mặc định
        $address->update(['is_default' => true]);

        return redirect()->route('dashboard')
            ->with('address_success', 'Đã đặt làm địa chỉ mặc định.');
    }
}
