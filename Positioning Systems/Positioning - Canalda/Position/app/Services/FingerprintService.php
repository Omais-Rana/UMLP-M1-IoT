<?php

namespace App\Services;

use App\Models\Position;

class FingerprintService
{
    public function compute(array $grid, array $tmRssi, int $k = 4): Position
    {
        $distances = [];

        // 1. Flatten the 3x3 grid and calculate signal distances
        foreach ($grid as $row) {
            foreach ($row as $cell) {
                $distances[] = [
                    'cell' => $cell,
                    'd' => $cell->getSignalDistance($tmRssi)
                ];
            }
        }

        // 2. Determine k-neighbouring for the k better cells
        usort($distances, fn($a, $b) => $a['d'] <=> $b['d']);
        $neighbors = array_slice($distances, 0, $k);

        // 3. Weighting Logic (d1 * alpha = d2 => c2 = (1/alpha) * c1)
        $d1 = $neighbors[0]['d'];
        if ($d1 == 0) return new Position($neighbors[0]['cell']->x, $neighbors[0]['cell']->y, 0);

        $sumOfWeights = 0;
        $weights = [];

        foreach ($neighbors as $n) {
            $alpha = $n['d'] / $d1;
            $weightFactor = 1 / $alpha; // c_i = (1/alpha) * c1
            $weights[] = $weightFactor;
            $sumOfWeights += $weightFactor;
        }

        // 4. Calculate Weighted Barycenter (OM = c1*OK1 + c2*OK2...)
        $finalX = 0;
        $finalY = 0;

        foreach ($neighbors as $index => $n) {
            $normalizedWeight = $weights[$index] / $sumOfWeights; // Ensures sum of c = 1
            $finalX += $normalizedWeight * $n['cell']->x;
            $finalY += $normalizedWeight * $n['cell']->y;
        }

        return new Position($finalX, $finalY, 0);
    }
}
