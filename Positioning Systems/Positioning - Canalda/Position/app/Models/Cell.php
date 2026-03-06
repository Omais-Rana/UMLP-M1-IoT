<?php

namespace App\Models;

class Cell
{
    public function __construct(
        public Position $position,
        public float $totalError = 0.0
    ) {}
}
