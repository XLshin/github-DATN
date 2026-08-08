<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct(
        private readonly BankAccountService $bankAccountService,
    ) {}

    public function index(Request $request)
    {
        $query = BankAccount::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('is_verified', $request->status === 'verified');
        }

        $bankAccounts = $query->paginate(20)->withQueryString();

        return view('admin.bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Admin xác minh thủ công 1 tài khoản ngân hàng có tên không khớp tự động
     * (ví dụ tài khoản người thân) — chỉ nên duyệt khi đã kiểm tra thêm giấy tờ/thông tin xác nhận.
     */
    public function verify(Request $request, BankAccount $bankAccount)
    {
        $this->bankAccountService->verifyManually($bankAccount, $request->user());

        return back()->with('success', 'Đã xác minh tài khoản ngân hàng cho khách hàng.');
    }
}
