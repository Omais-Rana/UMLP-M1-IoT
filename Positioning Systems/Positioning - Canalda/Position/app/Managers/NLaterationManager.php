<?php

namespace App\Managers;

use App\Contracts\PositioningInterface;
use App\Factories\DatasetFactory;

class NLaterationManager
{
    public function __construct(protected PositioningInterface $algorithm) {}

    public function solveStaticScenario(float $precision = 0.05, int $example = 1)
    {
        $emitters = $example == 3 ? DatasetFactory::createThreeEmitterDataset() : DatasetFactory::createTDDataset();

        return [
            'emitters' => $emitters,
            'position' => $this->algorithm->compute($emitters, $precision)
        ];
    }
}
