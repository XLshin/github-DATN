<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletWithdrawal;
use App\Services\WalletWithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WalletWithdrawalController extends Controller
{
    public function __construct(
        private readonly WalletWithdrawalService $withdrawalService,
    ) {}

    public function index(Request $request)
    {
        $query = WalletWithdrawal::with('user')->latest('requested_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        return view('admin.wallet-withdrawals.index', compact('withdrawals'));
    }

    public function show(WalletWithdrawal $withdrawal)
    {
        $withdrawal->load(['user', 'bankAccount', 'confirmedBy', 'rejectedBy']);

        return view('admin.wallet-withdrawals.show', compact('withdrawal'));
    }

    public function markProcessing(WalletWithdrawal $withdrawal)
    {
        $this->withdrawalService->markProcessing($withdrawal);

        return back()->with('success', 'Đã chuyển yêu cầu sang trạng thái đang xử lý.');
    }

    /**
     * Admin xác nhận đã chuyển khoản cho khách — bắt buộc đính kèm ảnh minh chứng đã chuyển khoản.
     */
    public function complete(Request $request, WalletWithdrawal $withdrawal)
    {
        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ], [
            'proof_image.required' => 'Vui lòng tải lên ảnh minh chứng đã chuyển khoản cho khách hàng.',
        ]);

        $path = $request->file('proof_image')->store('withdrawal-proofs', 'public');

        try {
            $this->withdrawalService->complete($withdrawal, $request->user(), $path, $validated['admin_note'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('admin.wallet-withdrawals.show', $withdrawal)
            ->with('success', 'Đã xác nhận hoàn tất chuyển khoản cho khách hàng.');
    }

    public function reject(Request $request, WalletWithdrawal $withdrawal)
    {
        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:500'],
        ], [
            'reject_reason.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        try {
            $this->withdrawalService->reject($withdrawal, $request->user(), $validated['reject_reason']);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('admin.wallet-withdrawals.show', $withdrawal)
            ->with('success', 'Đã từ chối yêu cầu và hoàn lại số dư vào ví khách hàng.');
    }
}
