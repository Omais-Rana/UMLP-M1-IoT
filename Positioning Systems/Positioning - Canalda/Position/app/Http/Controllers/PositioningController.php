<?php

namespace App\Http\Controllers;

use App\Managers\NLaterationManager;
use App\Factories\DatasetFactory;
use Illuminate\View\View;

class PositioningController extends Controller
{
    /**
     * Handle the positioning request and return the view.
     */
    public function index(NLaterationManager $manager): View
    {
        $emitters = DatasetFactory::createTDDataset();

        $result = $manager->solveStaticScenario();

        return view('positioning_map', [
            'emitters' => $emitters,
            'result' => $result
        ]);
    }
}
