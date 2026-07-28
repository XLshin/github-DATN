<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletTopup;
use App\Models\WalletWithdrawal;
use App\Services\WalletService;
use App\Services\WalletWithdrawalService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    /** Phương thức nạp qua cổng có phiên giao dịch giới hạn thời gian. */
    private const EXPIRING_METHODS = ['vietqr'];

    public function __construct(
        private readonly WalletService $walletService,
        private readonly WalletWithdrawalService $withdrawalService,
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $transactions = $user->walletTransactions()->latest()->paginate(15);
        $bankAccounts = $user->bankAccounts()->orderByDesc('is_default')->latest()->get();
        $withdrawals = $user->walletWithdrawals()->latest()->take(10)->get();

        return view('client.wallet.index', compact('transactions', 'bankAccounts', 'withdrawals'));
    }

    public function withdraw(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:' . WalletWithdrawal::MIN_AMOUNT, 'max:' . (float) $user->wallet_balance],
        ], [
            'amount.max' => 'Số tiền rút không được vượt quá số dư ví hiện có.',
        ]);

        $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);

        try {
            $this->withdrawalService->request($user, $bankAccount, (float) $validated['amount']);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return redirect()->route('wallet.index')->with('error', $firstError);
        }

        return redirect()->route('wallet.index')
            ->with('success', 'Đã gửi yêu cầu rút tiền. Tiền sẽ được chuyển về tài khoản ngân hàng trong tối đa '
                . WalletWithdrawal::MIN_PROCESSING_DAYS . ' ngày làm việc.');
    }

    public function topup(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:100000000'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,vietqr'],
        ], [
            'amount.min' => 'Số tiền nạp tối thiểu là 10.000 đ.',
            'amount.max' => 'Số tiền nạp tối đa là 100.000.000 đ.',
        ]);

        try {
            $topup = $this->walletService->initiateTopup($user, (float) $validated['amount'], $validated['payment_method']);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return redirect()->route('wallet.index')->with('error', $firstError);
        }

        return redirect()->route('wallet.topup.payment', $topup);
    }

    public function showTopupPayment(Request $request, WalletTopup $topup)
    {
        $this->authorizeTopup($request, $topup);

        if ($topup->isExpired()) {
            $this->walletService->expireTopup($topup);
            $topup->refresh();
        }

        return view('client.wallet.payment', compact('topup'));
    }

    public function retryTopupPayment(Request $request, WalletTopup $topup)
    {
        $this->authorizeTopup($request, $topup);

        if ($topup->payment_status !== 'failed' || ! in_array($topup->payment_method, self::EXPIRING_METHODS, true)) {
            return redirect()->route('wallet.topup.payment', $topup);
        }

        $topup->update([
            'payment_status' => 'pending',
            // Giữ nguyên transaction_code (mã "NAPVI..." khách đã ghi nội dung chuyển khoản).
            'transaction_code' => $topup->transaction_code,
            'payer_name' => null,
            'payer_note' => null,
            'expires_at' => now()->addMinutes(15),
            'simulate_confirm_at' => in_array($topup->payment_method, WalletService::AUTO_CONFIRM_METHODS, true)
                ? now()->addSeconds(random_int(8, 20))
                : null,
        ]);

        return redirect()->route('wallet.topup.payment', $topup)
            ->with('info', 'Đã mở lại phiên nạp tiền mới, vui lòng hoàn tất trong thời gian quy định.');
    }

    /**
     * Trang nạp ví chuyển khoản poll endpoint này để tự động phát hiện khi được xác nhận.
     * Vì đây là đồ án không kết nối ngân hàng thật, khi đã quá simulate_confirm_at thì lần
     * poll kế tiếp sẽ tự cộng tiền — hành vi phía người dùng giống hệt một webhook ngân hàng thật.
     */
    public function topupStatus(Request $request, WalletTopup $topup)
    {
        $this->authorizeTopup($request, $topup);

        if (
            $topup->payment_status === 'pending'
            && in_array($topup->payment_method, WalletService::AUTO_CONFIRM_METHODS, true)
            && $topup->simulate_confirm_at
            && $topup->simulate_confirm_at->isPast()
            && ! $topup->isExpired()
        ) {
            $this->walletService->confirmSimulated($topup);
            $topup->refresh();
        }

        return response()->json([
            'status' => $topup->payment_status,
            'paid'   => $topup->payment_status === 'paid',
            'receipt' => $topup->payment_status === 'paid' ? [
                'payer_name'       => $topup->payer_name,
                'amount'           => (float) $topup->amount,
                'transaction_code' => $topup->transaction_code,
                'paid_at'          => $topup->paid_at?->format('H:i:s d/m/Y'),
                'business_account' => [
                    'bank_id'        => config('services.sepay.bank_id'),
                    'account_number' => config('services.sepay.account_number'),
                    'account_name'   => config('services.sepay.account_name'),
                ],
            ] : null,
        ]);
    }

    /**
     * Khách xác nhận thủ công — dùng làm lối dự phòng khi việc tự động xác nhận
     * (webhook SePay) chưa kịp chạy. vietqr/bank_transfer chỉ có ảnh chụp màn hình nên vẫn cần
     * admin đối soát.
     */
    public function confirmTopupPayment(Request $request, WalletTopup $topup)
    {
        $this->authorizeTopup($request, $topup);

        if ($topup->payment_status === 'paid') {
            return redirect()->route('wallet.index');
        }

        if ($topup->isExpired()) {
            $this->walletService->expireTopup($topup);
            return redirect()->route('wallet.topup.payment', $topup)
                ->with('error', 'Giao dịch đã hết hạn do quá thời gian thanh toán. Vui lòng thử lại.');
        }

        $method = $topup->payment_method;

        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ], [
            'proof_image.required' => 'Vui lòng tải lên ảnh chụp màn hình xác nhận giao dịch.',
            'proof_image.image' => 'File tải lên phải là hình ảnh.',
        ]);

        $payerNote = match ($method) {
            'bank_transfer' => 'Khách báo đã chuyển khoản lúc ' . now()->format('H:i d/m/Y') . ' — chờ đối soát.',
            'vietqr' => 'Khách báo đã thanh toán qua VietQR — chờ đối soát.',
            default => 'Khách báo đã thanh toán — chờ đối soát.',
        };

        $path = $request->file('proof_image')->store('wallet-topup-proofs', 'public');

        $topup->update([
            'payer_name' => $request->user()->name,
            'payer_note' => $payerNote,
            'proof_image' => $path,
        ]);

        return redirect()->route('wallet.index')
            ->with('info', 'Chúng tôi đã ghi nhận yêu cầu nạp tiền. Số dư sẽ được cộng sau khi đối soát (thường trong 30 phút).');
    }

    private function authorizeTopup(Request $request, WalletTopup $topup): void
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user || (int) $topup->user_id !== (int) $user->getKey()) {
            abort(403);
        }
    }

}
