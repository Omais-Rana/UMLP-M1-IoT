<?php

namespace App\Http\Controllers;

use App\Managers\NLaterationManager;
use App\Factories\DatasetFactory;
use App\Services\FingerprintService;
use App\Models\Cellule;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PositioningController extends Controller
{
    /**
     * Landing page with cards for both exercises.
     */
    public function dashboard(): View
    {
        return view('dashboard');
    }

    /**
     * TD n°2: N-Lateration (Geometric Approach)
     * Renamed from index to lateration to match navbar routes.
     */
    public function lateration(Request $request, NLaterationManager $manager): View
    {
        // Allow dynamic precision testing via query parameter (default to 0.1)
        $precision = (float) $request->query('precision', 0.1);
        $example = (int) $request->query('example', 1);

        // Run the Grid Search minimization algorithm and measure time
        $startTime = microtime(true);
        $scenario = $manager->solveStaticScenario($precision, $example);
        $endTime = microtime(true);

        $executionTime = round(($endTime - $startTime) * 1000, 2); // Time in milliseconds

        return view('positioning_map', [
            'emitters' => $scenario['emitters'],
            'result' => $scenario['position'],
            'precision' => $precision,
            'example' => $example,
        ]);
    }
    /**
     * TD n°3: Fingerprinting (Probabilistic Approach)
     */
    public function fingerprint(Request $request): View
    {
        // Get K from request, default to 4 (as in TD)
        $k = (int) $request->query('k', 4);

        // 1. Initialize the Tf grid (Radio Map) using the TD values
        // Centers for i/j (0,1,2) in a 12x12m grid are (2m, 6m, 10m)
        $grid = [
            [new Cellule([-38, -27, -54, -13], 2, 2), new Cellule([-74, -62, -48, -33], 6, 2), new Cellule([-13, -28, -12, -40], 10, 2)],
            [new Cellule([-34, -27, -38, -41], 2, 6), new Cellule([-64, -48, -72, -35], 6, 6), new Cellule([-45, -37, -20, -15], 10, 6)],
            [new Cellule([-17, -50, -44, -33], 2, 10), new Cellule([-27, -28, -32, -45], 6, 10), new Cellule([-30, -20, -60, -40], 10, 10)],
        ];

        // 2. The Mobile Terminal measurement (The "Photography" of the spot)
        $tmRssi = [-26, -42, -13, -46];

        // 3. Compute result using Weighted K-Nearest Neighbors
        $service = new FingerprintService();
        $computed = $service->compute($grid, $tmRssi, $k);

        return view('fingerprint_map', [
            'grid' => $grid,
            'result' => $computed['position'],
            'neighbors' => $computed['neighbors'],
            'all_distances' => $computed['all_distances'],
            'tmRssi' => $tmRssi,
            'k' => $k
        ]);
    }

    public function markov(Request $request): View
    {
        // 1. Get current matrix from session or initialize a fresh one
        $matrix = session('markov_matrix', $this->initializeEmptyMatrix());
        $lastCell = session('last_cell_id');
        $history = session('markov_history', []);

        // 2. If a move was made, update the matrix counts (nb) and stats
        if ($request->has('move_to')) {
            $currentCell = (int) $request->get('move_to');

            // Add to history
            $history[] = $currentCell;
            session(['markov_history' => $history]);

            if ($lastCell !== null) {
                // Logic from Appendix: MM[prev][curr].nb += 1
                $matrix[$lastCell][$currentCell]->nb += 1;
                $matrix[$lastCell][6]->nb += 1; // Totalizer Column

                // Calculate Probability: MM[prev][k].stat = nb / total
                $total = $matrix[$lastCell][6]->nb;
                for ($k = 0; $k < 6; $k++) {
                    $matrix[$lastCell][$k]->stat = $matrix[$lastCell][$k]->nb / $total;
                }
            }

            session(['last_cell_id' => $currentCell]);
            session(['markov_matrix' => $matrix]);
            // Update last cell to current for prediction
            $lastCell = $currentCell;
        }

        // 3. Predict Next Move based on highest probability (Forward Analysis)
        $predictedCells = [];
        $maxProbability = 0;

        if ($lastCell !== null && $matrix[$lastCell][6]->nb > 0) {
            for ($k = 0; $k < 6; $k++) {
                $stat = $matrix[$lastCell][$k]->stat;
                if ($stat > $maxProbability) {
                    $maxProbability = $stat;
                    $predictedCells = [$k];
                } elseif ($stat == $maxProbability && $stat > 0) {
                    $predictedCells[] = $k;
                }
            }
        }

        // 4. Hindsight Analysis (Backward Analysis / Pseudo-HMM)
        // If we are currently in $lastCell (j), what is the most likely previous cell (i)?
        $predictedPrevCells = [];
        $maxBackwardProbability = 0;

        if ($lastCell !== null) {
            $totalArrivals = 0;
            // Sum all transitions arriving AT $lastCell
            for ($i = 0; $i < 6; $i++) {
                $totalArrivals += $matrix[$i][$lastCell]->nb;
            }

            if ($totalArrivals > 0) {
                for ($i = 0; $i < 6; $i++) {
                    $backwardProb = $matrix[$i][$lastCell]->nb / $totalArrivals;
                    if ($backwardProb > $maxBackwardProbability) {
                        $maxBackwardProbability = $backwardProb;
                        $predictedPrevCells = [$i];
                    } elseif ($backwardProb == $maxBackwardProbability && $backwardProb > 0) {
                        $predictedPrevCells[] = $i;
                    }
                }
            }
        }

        return view('markov_map', compact('matrix', 'history', 'predictedCells', 'lastCell', 'maxProbability', 'predictedPrevCells', 'maxBackwardProbability'));
    }

    /**
     * Clears the learning progress.
     */
    public function resetMarkov()
    {
        session()->forget(['markov_matrix', 'last_cell_id', 'markov_history']);
        return redirect()->route('markov')->with('status', 'Matrix Reset Successfully');
    }

    /**
     * Helper to build the 6x7 structure (6 cells + 1 totalizer column).
     */
    private function initializeEmptyMatrix(): array
    {
        $matrix = [];
        for ($i = 0; $i < 6; $i++) {
            for ($j = 0; $j < 7; $j++) {
                $matrix[$i][$j] = (object)['nb' => 0, 'stat' => 0.0];
            }
        }
        return $matrix;
    }
}
