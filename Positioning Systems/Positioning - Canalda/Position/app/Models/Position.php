<?php

namespace App\Models;

class Position
{
    public function __construct(
        public float $x,
        public float $y,
        public float $z
    ) {}
}
