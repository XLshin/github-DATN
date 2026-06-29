<?php

namespace App\Services;

use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Add points to a user
     */
    public function addPoints(User $user, int $points, string $type = 'purchase', string $description = ''): PointHistory
    {
        return DB::transaction(function () use ($user, $points, $type, $description) {
            $user->increment('points', $points);

            return PointHistory::query()->create([
                'user_id' => $user->id,
                'points' => $points,
                'type' => $type,
                'description' => $description,
            ]);
        });
    }

    /**
     * Deduct points from a user
     */
    public function deductPoints(User $user, int $points, string $type = 'usage', string $description = ''): PointHistory
    {
        return DB::transaction(function () use ($user, $points, $type, $description) {
            $user->decrement('points', max(0, $points));

            return PointHistory::query()->create([
                'user_id' => $user->id,
                'points' => -$points,
                'type' => $type,
                'description' => $description,
            ]);
        });
    }

    /**
     * Calculate points from order amount
     * New rate: 1 point per 100 VND (1%)
     */
    public function calculatePointsFromOrder(float $orderAmount): int
    {
        return (int) floor($orderAmount / 100);
    }

    /**
     * Get user points with history
     */
    public function getUserPointsHistory(User $user, int $limit = 20)
    {
        return $user->pointHistories()
            ->latest()
            ->limit($limit)
            ->get();
    }
}
