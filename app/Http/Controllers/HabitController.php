<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Http\Requests\DeleteHabitRequest;
use App\Http\Requests\IndexHabitsRequest;
use App\Http\Requests\ShowHabitRequest;
use App\Http\Requests\StoreHabitRequest;
use App\Http\Requests\UpdateHabitRequest;

class HabitController extends Controller
{
    public function index(IndexHabitsRequest $request)
    {
        $habits = $request->user()
            ->habits()
            ->when($request->has('active'), function ($query) use ($request) {
                $query->where('is_active', filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
            })
            ->latest('id')
            ->get();

        return $this->successResponse([
            'habits' => $habits,
        ]);
    }

    public function store(StoreHabitRequest $request)
    {
        $habit = $request->user()->habits()->create($request->validated());

        return $this->successResponse([
            'habit' => $habit,
        ], 'Operation successful', 201);
    }

    public function show(ShowHabitRequest $request, int $id)
    {
        $habit = $this->findUserHabit($request->user()->id, $id);

        return $this->successResponse([
            'habit' => $habit,
        ]);
    }

    public function update(UpdateHabitRequest $request, int $id)
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $habit->update($request->validated());

        return $this->successResponse([
            'habit' => $habit->fresh(),
        ]);
    }

    public function destroy(DeleteHabitRequest $request, int $id)
    {
        $habit = $this->findUserHabit($request->user()->id, $id);
        $habit->delete();

        return $this->successResponse($this->emptyObject());
    }

    private function findUserHabit(int $userId, int $habitId): Habit
    {
        return Habit::query()
            ->where('user_id', $userId)
            ->findOrFail($habitId);
    }
}
