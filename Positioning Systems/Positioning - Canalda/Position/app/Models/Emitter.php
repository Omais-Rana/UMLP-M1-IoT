<?php

namespace App\Models;

class Emitter
{
    public function __construct(
        public Position $position,
        public float $measuredDistance
    ) {}
}
