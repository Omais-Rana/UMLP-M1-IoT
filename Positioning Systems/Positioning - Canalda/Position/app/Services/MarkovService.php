<?php

namespace App\Services;

class MarkovService
{
    public function buildTransitionMatrix(array $history): array
    {
        $nbStates = 9; // 3x3 Grid
        $totalCol = $nbStates;

        // Initialize Matrix
        $matrix = array_fill(0, $nbStates, array_fill(0, $nbStates + 1, (object)['nb' => 0, 'stat' => 0.0]));

        // Increment transitions
        for ($i = 1; $i < count($history); $i++) {
            $prev = $history[$i - 1];
            $curr = $history[$i];

            $matrix[$prev][$curr]->nb += 1;
            $matrix[$prev][$totalCol]->nb += 1;
        }

        // Convert counts to probabilities
        for ($prev = 0; $prev < $nbStates; $prev++) {
            $rowTotal = $matrix[$prev][$totalCol]->nb;
            if ($rowTotal > 0) {
                for ($curr = 0; $curr < $nbStates; $curr++) {
                    $matrix[$prev][$curr]->stat = (float)$matrix[$prev][$curr]->nb / (float)$rowTotal;
                }
            }
        }

        return $matrix;
    }
}
