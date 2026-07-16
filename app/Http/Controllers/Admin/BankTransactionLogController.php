<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankTransactionLog;
use Illuminate\Http\Request;

class BankTransactionLogController extends Controller
{
    public function index(Request $request)
    {
        $query = BankTransactionLog::with(['user', 'handledBy'])->latest('occurred_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('bank_name', 'like', "%{$keyword}%")
                    ->orWhere('account_number', 'like', "%{$keyword}%")
                    ->orWhere('account_holder_name', 'like', "%{$keyword}%")
                    ->orWhere('transaction_code', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('occurred_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('occurred_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.bank-transaction-logs.index', compact('logs'));
    }
}
