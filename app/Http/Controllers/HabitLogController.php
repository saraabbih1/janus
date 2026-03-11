<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteHabitLogRequest;
use App\Http\Requests\IndexHabitLogsRequest;
use App\Http\Requests\StoreHabitLogRequest;
use App\Models\Habit;
use App\Models\HabitLog;
use Carbon\Carbon;

class HabitLogController extends Controller
{
    public function store(StoreHabitLogRequest $request, int $id)
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $today = Carbon::today()->toDateString();

        $exists = $habit->logs()->whereDate('date', $today)->exists();

        if ($exists) {
            return $this->errorResponse([
                'habit' => ['Cannot log the same habit twice on the same day.'],
            ], 'Validation error', 422);
        }

        $log = $habit->logs()->create([
            'note' => $request->validated('note'),
            'date' => $today,
        ]);

        return $this->successResponse([
            'log' => $log,
        ], 'Operation successful', 201);
    }

    public function index(IndexHabitLogsRequest $request, int $id)
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $logs = $habit->logs()->latest('date')->latest('id')->get();

        return $this->successResponse([
            'logs' => $logs,
        ]);
    }

    public function destroy(DeleteHabitLogRequest $request, int $id, int $logId)
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $log = $habit->logs()->findOrFail($logId);
        $log->delete();

        return $this->successResponse($this->emptyObject());
    }

    private function findUserHabit(int $userId, int $habitId): Habit
    {
        return Habit::query()
            ->where('user_id', $userId)
            ->findOrFail($habitId);
    }
}
