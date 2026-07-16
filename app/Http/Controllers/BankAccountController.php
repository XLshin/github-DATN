<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\User;
use App\Services\BankAccountService;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct(
        private readonly BankAccountService $bankAccountService,
    ) {}

    public function store(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ], [
            'account_number.regex' => 'Số tài khoản chỉ được chứa chữ số.',
        ]);

        $bankAccount = $this->bankAccountService->link($user, $validated);

        $message = $bankAccount->is_verified
            ? 'Đã liên kết và tự động xác minh tài khoản ngân hàng (tên chủ TK khớp tài khoản của bạn).'
            : 'Đã liên kết tài khoản ngân hàng. Tên chủ TK không khớp tài khoản của bạn nên cần admin xác minh thủ công trước khi có thể rút tiền về tài khoản này.';

        return redirect()->route('wallet.index')->with($bankAccount->is_verified ? 'success' : 'info', $message);
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeOwner($request, $bankAccount);

        $this->bankAccountService->remove($bankAccount);

        return redirect()->route('wallet.index')->with('success', 'Đã xóa liên kết tài khoản ngân hàng.');
    }

    public function setDefault(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeOwner($request, $bankAccount);

        $this->bankAccountService->setDefault($bankAccount);

        return redirect()->route('wallet.index')->with('success', 'Đã đặt làm tài khoản ngân hàng mặc định.');
    }

    private function authorizeOwner(Request $request, BankAccount $bankAccount): void
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user || (int) $bankAccount->user_id !== (int) $user->getKey()) {
            abort(403);
        }
    }
}
