<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTopup;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WalletTopupController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    public function index(Request $request)
    {
        $query = WalletTopup::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $topups = $query->paginate(20)->withQueryString();

        return view('admin.wallet-topups.index', compact('topups'));
    }

    public function show(WalletTopup $topup)
    {
        $topup->load(['user', 'confirmedBy', 'rejectedBy']);

        return view('admin.wallet-topups.show', compact('topup'));
    }

    /**
     * Admin xác nhận đã nhận được tiền nạp ví — bắt buộc nhập đúng số tiền thực nhận để đối soát.
     */
    public function confirm(Request $request, WalletTopup $topup)
    {
        $validated = $request->validate([
            'confirmed_amount' => ['required', 'numeric', 'min:0'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ], [
            'confirmed_amount.required' => 'Vui lòng nhập số tiền thực nhận để xác nhận.',
        ]);

        try {
            $this->walletService->confirmTopupByAdmin(
                $topup,
                $request->user(),
                (float) $validated['confirmed_amount'],
                $validated['admin_note'] ?? null
            );
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return back()->with('error', $firstError);
        }

        return redirect()->route('admin.wallet-topups.show', $topup)
            ->with('success', 'Đã xác nhận và cộng tiền vào ví cho khách hàng.');
    }

    /**
     * Admin từ chối yêu cầu nạp ví (không nhận được tiền / sai số tiền / sai nội dung...).
     */
    public function reject(Request $request, WalletTopup $topup)
    {
        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:500'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        try {
            $this->walletService->rejectTopup($topup, $request->user(), $validated['reject_reason']);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return back()->with('error', $firstError);
        }

        return redirect()->route('admin.wallet-topups.show', $topup)
            ->with('success', 'Đã từ chối yêu cầu nạp ví.');
    }
}
