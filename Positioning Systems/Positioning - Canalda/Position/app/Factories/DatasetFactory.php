<?php

namespace App\Factories;

use App\Models\Position;
use App\Models\Emitter;

class DatasetFactory
{
    public static function createTDDataset(): array
    {
        return [
            new Emitter(new Position(0.5, 0.5, 0.5), 3.0),
            new Emitter(new Position(4.0, 0.0, 0.0), 2.0),
            new Emitter(new Position(4.0, 5.0, 5.0), 4.2),
            new Emitter(new Position(3.0, 3.0, 3.0), 2.5),
        ];
    }

    public static function createThreeEmitterDataset(): array
    {
        return [
            new Emitter(new Position(0.5, 0.5, 0.5), 3.0),
            new Emitter(new Position(4.0, 0.0, 0.0), 2.0),
            new Emitter(new Position(4.0, 5.0, 5.0), 4.2),
        ];
    }
}
