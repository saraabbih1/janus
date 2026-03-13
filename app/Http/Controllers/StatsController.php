<?php

namespace App\Http\Controllers;

use App\Http\Requests\HabitStatsRequest;
use App\Http\Requests\OverviewStatsRequest;
use App\Models\Habit;
use App\Models\HabitLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class StatsController extends Controller
{
    public function habitStats(HabitStatsRequest $request, int $id): JsonResponse
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $dates = $this->habitLogDates($habit);
        $streaks = $this->calculateStreaks($dates);
        $lastThirtyDays = Carbon::today()->subDays(29)->toDateString();
        $totalCompletions = $dates->count();
        $recentCompletions = $dates
            ->filter(fn (Carbon $date) => $date->toDateString() >= $lastThirtyDays)
            ->count();

        return $this->successResponse([
            'current_streak' => $streaks['current_streak'],
            'longest_streak' => $streaks['longest_streak'],
            'total_completions' => $totalCompletions,
            'completion_rate' => round(($recentCompletions / 30) * 100, 2),
        ]);
    }

    public function overview(OverviewStatsRequest $request): JsonResponse
    {
        $user = $request->user();
        $habits = $user->habits()->with('logs:id,habit_id,date')->get();
        $activeHabits = $habits->where('is_active', true)->values();
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

        $habitWithLongestStreak = $habits->reduce(function (?array $best, Habit $habit) {
            $streaks = $this->calculateStreaks($this->habitLogDates($habit));
            $candidate = [
                'id' => $habit->id,
                'title' => $habit->title,
                'current_streak' => $streaks['current_streak'],
                'longest_streak' => $streaks['longest_streak'],
            ];

            if ($best === null) {
                return $candidate;
            }

            if ($candidate['longest_streak'] > $best['longest_streak']) {
                return $candidate;
            }

            if (
                $candidate['longest_streak'] === $best['longest_streak']
                && $candidate['current_streak'] > $best['current_streak']
            ) {
                return $candidate;
            }

            return $best;
        });

        $completionRate = $activeHabitCount > 0
            ? round(($logsLastSevenDays / ($activeHabitCount * 7)) * 100, 2)
            : 0.0;

        return $this->successResponse([
            'total_active_habits' => $activeHabitCount,
            'completed_today' => $completedToday,
            'habit_with_longest_streak' => $habitWithLongestStreak ?? $this->emptyObject(),
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

        $sortedDates = $dates->sortBy(fn (Carbon $date) => $date->timestamp)->values();
        $longestStreak = 1;
        $runningStreak = 1;

        for ($index = 1; $index < $sortedDates->count(); $index++) {
            $previous = $sortedDates[$index - 1];
            $current = $sortedDates[$index];

            if (abs($current->diffInDays($previous)) == 1) {
                $runningStreak++;
                $longestStreak = max($longestStreak, $runningStreak);
            } else {
                $runningStreak = 1;
            }
        }

        $expectedDate = Carbon::today()->startOfDay();
        $currentStreak = 0;

        foreach ($sortedDates->sortByDesc(fn (Carbon $date) => $date->timestamp) as $date) {
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
