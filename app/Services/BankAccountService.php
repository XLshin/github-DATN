<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankAccountService
{
    /**
     * Liên kết tài khoản ngân hàng mới. Tự động xác minh (is_verified = true) nếu tên chủ TK
     * khớp tên tài khoản đăng ký — đúng thực tế các ví điện tử (Momo/ZaloPay) yêu cầu tài khoản
     * ngân hàng nhận tiền phải cùng chủ sở hữu với tài khoản ví để chống rửa tiền/chuyển nhầm.
     * Nếu không khớp, tài khoản vẫn được lưu nhưng ở trạng thái "chưa xác minh" và KHÔNG thể
     * dùng để rút tiền cho tới khi admin xác minh thủ công.
     */
    public function link(User $user, array $data): BankAccount
    {
        $isNameMatch = BankAccount::namesMatch($data['account_holder_name'], $user->name);

        return DB::transaction(function () use ($user, $data, $isNameMatch) {
            if (! empty($data['is_default'])) {
                $user->bankAccounts()->update(['is_default' => false]);
            }

            $isFirst = ! $user->bankAccounts()->exists();

            return $user->bankAccounts()->create([
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'account_holder_name' => $data['account_holder_name'],
                'is_verified' => $isNameMatch,
                'is_default' => $isFirst || ! empty($data['is_default']),
                'verified_at' => $isNameMatch ? now() : null,
            ]);
        });
    }

    public function remove(BankAccount $bankAccount): void
    {
        $bankAccount->delete();
    }

    /**
     * Admin xác minh thủ công 1 tài khoản ngân hàng có tên không khớp tự động
     * (ví dụ: tài khoản người thân, cần kiểm tra thêm giấy tờ trước khi cho phép rút tiền về).
     */
    public function verifyManually(BankAccount $bankAccount, User $admin): void
    {
        $bankAccount->update([
            'is_verified' => true,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);
    }

    public function setDefault(BankAccount $bankAccount): void
    {
        DB::transaction(function () use ($bankAccount) {
            $bankAccount->user->bankAccounts()->update(['is_default' => false]);
            $bankAccount->update(['is_default' => true]);
        });
    }
}
