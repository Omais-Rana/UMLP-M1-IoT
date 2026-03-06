<?php

namespace App\Services;

use App\Contracts\PositioningInterface;
use App\Models\Position;

class GridSearchService implements PositioningInterface
{
    public function compute(array $emitters, float $precision = 0.1): Position
    {
        $bestPos = null;
        $minError = INF;

        for ($x = 0; $x <= 5.0; $x += $precision) {
            for ($y = 0; $y <= 5.0; $y += $precision) {
                for ($z = 0; $z <= 5.0; $z += $precision) {

                    $currentPos = new Position($x, $y, $z);
                    $totalError = 0;

                    foreach ($emitters as $emitter) {
                        $calcDist = sqrt(
                            pow($currentPos->x - $emitter->position->x, 2) +
                                pow($currentPos->y - $emitter->position->y, 2) +
                                pow($currentPos->z - $emitter->position->z, 2)
                        );
                        $totalError += abs($calcDist - $emitter->measuredDistance);
                    }

                    if ($totalError < $minError) {
                        $minError = $totalError;
                        $bestPos = $currentPos;
                    }
                }
            }
        }
        return $bestPos;
    }
}
