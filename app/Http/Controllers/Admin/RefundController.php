<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RefundController extends Controller
{
    public function __construct(
        private readonly RefundService $refundService,
    ) {}

    public function index(Request $request)
    {
        $query = RefundRequest::with(['order', 'user'])->latest('requested_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        $refunds = $query->paginate(20)->withQueryString();

        return view('admin.refunds.index', compact('refunds'));
    }

    public function show(RefundRequest $refund)
    {
        $refund->load(['order', 'user']);

        return view('admin.refunds.show', compact('refund'));
    }

    public function markProcessing(RefundRequest $refund)
    {
        $this->refundService->markProcessing($refund);

        return back()->with('success', 'Đã chuyển yêu cầu sang trạng thái đang xử lý.');
    }

    /**
     * Admin xác nhận đã chuyển khoản hoàn tiền — bắt buộc đính kèm ảnh minh chứng đã chuyển khoản.
     */
    public function complete(Request $request, RefundRequest $refund)
    {
        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ], [
            'proof_image.required' => 'Vui lòng tải lên ảnh minh chứng đã chuyển khoản cho khách hàng.',
        ]);

        $path = $request->file('proof_image')->store('refund-proofs', 'public');

        try {
            $this->refundService->completeBankRefund($refund, $request->user(), $path, $validated['admin_note'] ?? null);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return back()->with('error', $firstError);
        }

        return redirect()->route('admin.refunds.show', $refund)
            ->with('success', 'Đã xác nhận hoàn tất chuyển khoản hoàn tiền cho khách hàng.');
    }
}
