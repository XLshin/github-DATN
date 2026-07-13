<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientCouponController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $coupons = $user->coupons()->where('status', true)->get();

        return view('client.vouchers.index', compact('coupons'));
    }
}
