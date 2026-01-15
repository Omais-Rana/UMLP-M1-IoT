<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Score;

class ScoreController extends Controller
{
    /**
     * Get top 3 scores.
     */
    public function index()
    {
        return Score::orderBy('time_taken', 'asc')
            ->take(3)
            ->get();
    }

    /**
     * Store a new score.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_name' => 'required|string|max:255',
            'time_taken' => 'required|numeric|min:0',
        ]);

        $score = Score::create($validated);

        return response()->json($score, 201);
    }
}
