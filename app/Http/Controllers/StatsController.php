<?php

namespace App\Http\Controllers;

use App\Http\Requests\HabitStatsRequest;
use App\Http\Requests\OverviewStatsRequest;
use App\Models\Habit;
use App\Models\HabitLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatsController extends Controller
{
    public function habitStats(HabitStatsRequest $request, int $id)
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $dates = $this->habitLogDates($habit);
        $streaks = $this->calculateStreaks($dates);
        $lastThirtyDays = Carbon::today()->subDays(29)->toDateString();
        $totalCompletions = $dates->count();
        $recentCompletions = $dates->filter(fn (Carbon $date) => $date->toDateString() >= $lastThirtyDays)->count();

        return $this->successResponse([
            'current_streak' => $streaks['current_streak'],
            'longest_streak' => $streaks['longest_streak'],
            'total_completions' => $totalCompletions,
            'completion_rate' => round(($recentCompletions / 30) * 100, 2),
        ]);
    }

    public function overview(OverviewStatsRequest $request)
    {
        $user = $request->user();
        $activeHabits = $user->habits()->where('is_active', true)->with('logs:id,habit_id,date')->get();
        $today = Carbon::today()->toDateString();
        $lastSevenDays = Carbon::today()->subDays(6)->toDateString();
        $activeHabitCount = $activeHabits->count();
        $completedToday = HabitLog::query()
            ->whereHas('habit', fn ($query) => $query->where('user_id', $user->id))
            ->whereDate('date', $today)
            ->count();
        $logsLastSevenDays = HabitLog::query()
            ->whereHas('habit', fn ($query) => $query->where('user_id', $user->id))
            ->whereDate('date', '>=', $lastSevenDays)
            ->count();
        $longestActiveStreak = $activeHabits
            ->map(fn (Habit $habit) => $this->calculateStreaks($this->habitLogDates($habit))['current_streak'])
            ->max() ?? 0;

        $completionRate = $activeHabitCount > 0
            ? round(($logsLastSevenDays / ($activeHabitCount * 7)) * 100, 2)
            : 0.0;

        return $this->successResponse([
            'total_active_habits' => $activeHabitCount,
            'completed_today' => $completedToday,
            'longest_active_streak' => $longestActiveStreak,
            'completion_rate_last_7_days' => $completionRate,
        ]);
    }

    private function findUserHabit(int $userId, int $habitId): Habit
    {
        return Habit::query()
            ->where('user_id', $userId)
            ->findOrFail($habitId);
    }

    private function habitLogDates(Habit $habit): Collection
    {
        return $habit->logs()
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->startOfDay())
            ->unique(fn (Carbon $date) => $date->toDateString())
            ->values();
    }

    private function calculateStreaks(Collection $dates): array
    {
        if ($dates->isEmpty()) {
            return [
                'current_streak' => 0,
                'longest_streak' => 0,
            ];
        }

        $sortedDates = $dates->sort()->values();
        $longestStreak = 1;
        $runningStreak = 1;

        for ($index = 1; $index < $sortedDates->count(); $index++) {
            $previous = $sortedDates[$index - 1];
            $current = $sortedDates[$index];

            if ($current->diffInDays($previous) === 1) {
                $runningStreak++;
                $longestStreak = max($longestStreak, $runningStreak);
            } else {
                $runningStreak = 1;
            }
        }

        $expectedDate = Carbon::today();
        $currentStreak = 0;

        foreach ($sortedDates->sortDesc() as $date) {
            if ($date->equalTo($expectedDate)) {
                $currentStreak++;
                $expectedDate = $expectedDate->copy()->subDay();
                continue;
            }

            if ($date->lt($expectedDate)) {
                break;
            }
        }

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
        ];
    }
}
