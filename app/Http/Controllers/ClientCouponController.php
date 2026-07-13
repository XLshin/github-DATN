<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class ClientCouponController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $ownedCoupons = $user->coupons()->valid()->get();

        $availablePublicCoupons = Coupon::query()
            ->valid()
            ->where('distribution', Coupon::DISTRIBUTION_PUBLIC)
            ->whereDoesntHave('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->orderBy('end_date')
            ->get();

        return view('client.vouchers.index', compact('ownedCoupons', 'availablePublicCoupons'));
    }

    public function claim(Request $request, Coupon $coupon)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (! $coupon->isPublic() || ! $coupon->isValid()) {
            return redirect()->route('client.vouchers.index')->with('error', 'Voucher này không thể nhận.');
        }

        if ($coupon->users()->where('user_id', $user->id)->exists()) {
            return redirect()->route('client.vouchers.index')->with('info', 'Bạn đã nhận voucher này rồi.');
        }

        $coupon->users()->syncWithoutDetaching([$user->id]);

        return redirect()->route('client.vouchers.index')->with('success', 'Bạn đã nhận voucher thành công.');
    }
}
