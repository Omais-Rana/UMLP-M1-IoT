<?php

namespace App\Factories;

class FingerprintFactory
{
    public static function createTfGrid(): array
    {
        // 12x12m area with 9 squares of 4x4m. Centrums: 2, 6, 10.
        $rssiData = [
            [[-38, -27, -54, -13], [-74, -62, -48, -33], [-13, -28, -12, -40]],
            [[-34, -27, -38, -41], [-64, -48, -72, -35], [-45, -37, -20, -15]],
            [[-17, -50, -44, -33], [-27, -28, -32, -45], [-30, -20, -60, -40]]
        ];

        $grid = [];
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $grid[$i][$j] = [
                    'rssi' => $rssiData[$i][$j],
                    'x' => ($j * 4) + 2, // centers for j=0,1,2 are 2,6,10
                    'y' => ($i * 4) + 2  // centers for i=0,1,2 are 2,6,10
                ];
            }
        }
        return $grid;
    }

    public static function getTM(): array
    {
        return [-26, -42, -13, -46]; // Mobile Terminal RSSI
    }
}
