<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteHabitLogRequest;
use App\Http\Requests\IndexHabitLogsRequest;
use App\Http\Requests\StoreHabitLogRequest;
use App\Models\Habit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class HabitLogController extends Controller
{
    public function store(StoreHabitLogRequest $request, int $id): JsonResponse
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $today = Carbon::today()->toDateString();

        if ($habit->logs()->whereDate('date', $today)->exists()) {
            return $this->errorResponse([
                'habit' => ['Cannot log the same habit twice on the same day.'],
            ], 'Erreur', 422);
        }

        $log = $habit->logs()->create([
            'note' => $request->validated()['note'] ?? null,
            'date' => $today,
        ]);

        return $this->successResponse([
            'log' => $log,
        ], 'Operation reussie', 201);
    }

    public function index(IndexHabitLogsRequest $request, int $id): JsonResponse
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $logs = $habit->logs()->latest('date')->latest('id')->get();

        return $this->successResponse([
            'logs' => $logs,
        ]);
    }

    public function destroy(DeleteHabitLogRequest $request, int $id, int $logId): JsonResponse
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
