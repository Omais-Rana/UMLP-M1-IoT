<?php

namespace App\Models;

class Cellule
{
    public function __construct(
        public array $rssi, //
        public float $x,
        public float $y
    ) {}

    /**
     * Calculates the "Signal Distance" between this cell and a live measurement.
     * We use Euclidean distance in signal space.
     */
    public function getSignalDistance(array $tmRssi): float
    {
        $sum = 0;
        foreach ($this->rssi as $index => $val) {
            $sum += pow($val - $tmRssi[$index], 2);
        }
        return sqrt($sum);
    }
}
