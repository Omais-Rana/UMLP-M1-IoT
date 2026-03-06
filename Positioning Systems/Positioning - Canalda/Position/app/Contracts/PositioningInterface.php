<?php

namespace App\Contracts;

use App\Models\Position;

interface PositioningInterface
{
    /**
     * @param array $emitters Array of Emitter objects
     * @param float $precision Step size for the grid search
     */
    public function compute(array $emitters, float $precision): Position;
}
