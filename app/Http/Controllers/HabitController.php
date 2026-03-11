<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    // CREATE
    public function store(Request $request)
    {
        $habit = Habit::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'frequency' => $request->frequency,
            'target_days' => $request->target_days
        ]);

        return response()->json($habit);
    }

    // READ
    public function index()
    {
        return Habit::all();
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $habit = Habit::findOrFail($id);

        $habit->update($request->all());

        return response()->json($habit);
    }

    // DELETE
    public function destroy($id)
    {
        Habit::destroy($id);

        return response()->json([
            "message" => "Habit deleted"
        ]);
    }
}