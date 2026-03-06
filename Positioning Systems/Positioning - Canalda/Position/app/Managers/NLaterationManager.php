<?php

namespace App\Managers;

use App\Contracts\PositioningInterface;
use App\Factories\DatasetFactory;

class NLaterationManager
{
    public function __construct(protected PositioningInterface $algorithm) {}

    public function solveStaticScenario()
    {
        $emitters = DatasetFactory::createTDDataset();

        return $this->algorithm->compute($emitters, 0.05); // 0.05m precision
    }
}
