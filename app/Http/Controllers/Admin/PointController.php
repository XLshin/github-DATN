<?php

namespace App\Http\Controllers\Admin;

use App\Models\PointHistory;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class PointController extends Controller
{
    public function __construct(private readonly PointService $pointService) {}

    /**
     * Display user points management page
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        }

        $users = $query->where('role', 'user')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.points.index', compact('users'));
    }

    /**
     * Show user points detail and history
     */
    public function show(User $user)
    {
        $pointHistories = $user->pointHistories()
            ->latest()
            ->paginate(20);

        return view('admin.points.show', compact('user', 'pointHistories'));
    }

    /**
     * Add points to user
     */
    public function addPoints(Request $request, User $user)
    {
        $validated = $request->validate([
            'points' => ['required', 'integer', 'min:1', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->pointService->addPoints(
                $user,
                $validated['points'],
                'admin_adjustment',
                $validated['description'] ?? 'Admin cộng điểm'
            );

            return back()->with('success', "Cộng {$validated['points']} điểm cho {$user->name} thành công!");
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'points' => 'Lỗi khi cộng điểm: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Deduct points from user
     */
    public function deductPoints(Request $request, User $user)
    {
        $validated = $request->validate([
            'points' => ['required', 'integer', 'min:1', 'max:1000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->points < $validated['points']) {
            throw ValidationException::withMessages([
                'points' => "Người dùng chỉ có {$user->points} điểm, không thể trừ {$validated['points']} điểm.",
            ]);
        }

        try {
            $this->pointService->deductPoints(
                $user,
                $validated['points'],
                'admin_adjustment',
                $validated['description'] ?? 'Admin trừ điểm'
            );

            return back()->with('success', "Trừ {$validated['points']} điểm từ {$user->name} thành công!");
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'points' => 'Lỗi khi trừ điểm: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Reset user points to zero
     */
    public function reset(Request $request, User $user)
    {
        $request->validate([
            'confirmation' => ['required', 'accepted'],
        ], [
            'confirmation.required' => 'Bạn phải xác nhận để reset điểm.',
        ]);

        try {
            $oldPoints = $user->points;
            $user->update(['points' => 0]);

            PointHistory::query()->create([
                'user_id' => $user->id,
                'points' => -$oldPoints,
                'type' => 'admin_reset',
                'description' => 'Admin reset điểm',
            ]);

            return back()->with('success', "Reset điểm của {$user->name} (từ {$oldPoints} thành 0) thành công!");
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'reset' => 'Lỗi khi reset điểm: ' . $e->getMessage(),
            ]);
        }
    }
}
